<?php
namespace App\Repositories\Template;


use Exception;
use App\Models\Type;
use App\Models\Level;
use App\Models\Heading;
use App\Models\Template;
use App\Traits\LogTrait;
use App\Models\Subheading;
use App\Models\HeadingInfo;
use Illuminate\Http\Request;
use App\Models\TemplateHeading;
use App\Models\TemplateApplicant;
use App\Classes\MessageHelperUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TemplateHeadingLevel;
use Symfony\Component\Finder\Gitignore;
use App\Models\TemplateHeadingSubheading;
use App\DBTransactions\Template\TemplateUpdate;
use App\Interfaces\Template\TemplateRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TemplateRepository implements TemplateRepositoryInterface
{
    use LogTrait;

    public function __construct()
    {
        $this->msgHelpUtil = new MessageHelperUtil;
    }


    /**
     * Get specific template data from DB.
     *
     * @author Thu Ta
     * @create 21/06/2022
     * @param  int  $templateId
     * @return array
     */
    public function getTemplateData ($templateId) {
        #Validate template ID
        $tempQuery = Template::where('id', $templateId);
        //check template exits in table.
        if ($tempQuery->doesntExist()){
            //check template is deleted by users.
            if ($tempQuery->withTrashed()->exists()) return ['status'=> false, 'message'=> 'SE026'];
            return ['status'=> false, 'message'=> 'SE020'];
        }
        $template = $tempQuery->select('id as template_id','name as title','layout_id','active_flag')->first();
        //check template active status.
        if ($template->active_flag == config('ZERO')) return ['status'=> false, 'message'=> 'SE025'];
        //check template is used by applicants.
        $checkTempUsed = DB::table('template_applicant')->where('template_id', $templateId)->whereNull('deleted_at')->exists();
        if ($checkTempUsed) return ['status'=> false, 'message'=> 'SE022'];

        #Get template data
        $headings = TemplateHeading::where('template_id', $templateId)
                                ->join('headings','headings.id', '=', 'template_heading.heading_id')
                                ->join('heading_info', 'heading_info.id', '=', 'headings.heading_info_id')
                                ->whereNull('template_heading.deleted_at')
                                ->select('headings.id as heading_id',
                                    'heading_info.name as heading_name',
                                    'headings.type_id',
                                    'headings.require_flag'
                                )->get();

        //get heading's subheadings and levels
        $headings->each(function ($heading) {
            if ($heading->type_id == config('HEADING_TYPE_DATA_LIST') ||
                $heading->type_id == config('HEADING_TYPE_SINGLE_CHOICE') ||
                $heading->type_id == config('HEADING_TYPE_CHECKBOX')) {
                //pluck subheadings for each heading
                $subheadings = DB::table('template_heading_subheading as tempHeadSub')
                                     ->where('tempHeadSub.heading_id', $heading->heading_id)
                                    ->join('subheadings', 'subheadings.id', '=', 'tempHeadSub.subheading_id')
                                    ->whereNull('tempHeadSub.deleted_at')
                                    ->pluck('subheadings.name as subheading_name');

                $heading->subheadings = $subheadings->all();

                //get level for multiple choice heading
                if ( $heading->type_id == config('HEADING_TYPE_CHECKBOX')) {
                    //default level data
                    $level = [
                        'level_cat' => config('LEVEL_NONE'),
                        'level_data'=> array()
                    ];
                    $levels = DB::table('template_heading_level as tempHeadLvl')
                                        ->where('tempHeadLvl.heading_id', $heading->heading_id)
                                        ->join('levels', 'levels.id', '=', 'tempHeadLvl.level_id')
                                        ->whereNull('tempHeadLvl.deleted_at')
                                        ->select('levels.level_category_id as level_cat',
                                                'levels.level as level_data')
                                        ->get();
                    //add level data
                    if ($levels->isNotEmpty()) {
                        $level['level_cat'] = $levels[0]->level_cat;
                            $level['level_data'] = $levels->pluck('level_data')->all();
                    }
                    $heading->level = $level;
                }
            }
        });
        $template->headings = $headings->all();

        return ['status'=> true, 'data'=> $template];
    }

    /**
     * Update template data into DB.
     *
     * @author Thu Ta
     * @create 21/06/2022
     * @param  Request  $tempData
     * @return array
     */
    public function updateTemplate (Request $tempData) {
        #Template Data Validation in DB
        $tempQuery = Template::where('id', $tempData->template_id);
        //check template exits in table
        if ($tempQuery->doesntExist()) {
            //check template is deleted
            if ($tempQuery->withTrashed()->exists()) return ['status'=> false, 'message'=> 'SE026'];
            return ['status'=> false, 'message'=> 'SE020'];
        }
        //check active status.
        if ($tempQuery->first()->active_flag == config('ZERO')) return ['status'=> false, 'message'=> 'SE025'];
        //check template used by applicants
        $checkTempUsed = DB::table('template_applicant')->where('template_id', $tempData->template_id)->whereNull('deleted_at')->exists();
        if ($checkTempUsed) return ['status'=> false, 'message'=> 'SE022'];
        //check template title distinctiveness
        $checkTitle = DB::table('templates')->where([
                            ['id','<>', $tempData->template_id],
                            ['name','=', $tempData->title]
                        ])->whereNull('deleted_at')->exists();
        if ($checkTitle) return ['status'=> false, 'message'=> 'SE023'];

        #UPDATE TEMPLATE DATA
        $templateUpdate = new TemplateUpdate($tempData);
        $result = $templateUpdate->executeProcess();

        //fail state
        if(!$result) return ['status'=> false, 'message'=> 'SE021'];

        //success state
        //write update operation logs to crud_logs table
        $description = "Update Template data to database";
        $form = "Update Template Data";
        $this->writeCRUDLog($tempData->login_id, $description, $form, config('UPDATE'));

        return ['status'=> true, 'message'=> 'SS020'];
    }

    /**
     * Update template active status to DB.
     *
     * @author Thu Ta
     * @create 11/07/2022
     * @param  array  $tempData
     * @return array
     */
    public function updateTemplateActiveFlag (array $tempData) {
        try {
            $tempQuery = Template::where('id', $tempData['template_id']);
            //check template exists in DB
            if ($tempQuery->doesntExist()) {
                if ($tempQuery->withTrashed()->exists()) return ['status'=> false,'message' => 'SE026'];
                return ['status'=> false,'message' => 'SE020'];
            }
            //update template active status
            $tempQuery->update([
                'active_flag' => $tempData['active_flag'],
                'updated_at' => now()
            ]);
            return ['status'=> true,'message' => 'SS021'];
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return ['status'=> false,'message' => 'SE024'];
        }
    }


     /**
     * Show all types data.
     *
     * @author Thu Ta
     * @create  08/06/2022
     */
    public function getAllTypes()
    {
        $getAllData = Type::select('id','name')->get();
        return $getAllData;
    }
     /**
     * Show all templates data.
     *
     * @author Thu Ta
     * @create  23/06/2022
     */
    public function getAllTemplates()
    {
        $getAllData = Template::select('id as template_id','name as template_name',DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d') as date"),'link as link', 'active_flag')
                        ->orderBy('id','desc')
                        ->paginate(config('PAGING_LIMIT_20'));
        $getUseApplicant = TemplateApplicant::pluck('template_id')->unique();
        $updategetAllData = $getAllData->getCollection()->map(function($data) use ($getUseApplicant){
            if (in_array($data->template_id,$getUseApplicant->toArray())) {
                $data->isUse = true;

            } else {
                $data->isUse = false;
            }
            return $data;
        });

        $getAllData->setCollection($updategetAllData);
        return $getAllData;
    }
     /**
     * Show  templates data.
     *
     * @author Thu Ta
     * @create  24/06/2022
     */
    public function viewTemplate($tempId)
    {
        $queryTemp = Template::where('id',$tempId);

        if ($queryTemp->doesntExist()) {
            if ($queryTemp->withTrashed()->exists()) return ['status'=> false, 'message'=>'SE026'];
            return ['status'=> false, 'message'=>'SE020'];
        }
        
            $queryTemp = Template::where('id',$tempId)
                        ->select('id as template_id', 'name as template_name', 'layout_id as layout_id')
                        ->first();

            $queryHeading = TemplateHeading::leftjoin('headings',function($join) use ($tempId) {
                            $join->on('template_heading.heading_id','=','headings.id')
                            ->whereNull('template_heading.deleted_at');
                            })
                            ->leftjoin('heading_info','headings.heading_info_id','heading_info.id')
                            ->leftjoin('types','headings.type_id','types.id')
                            ->where('template_heading.template_id',$tempId)
                            ->select('types.id as type_id','types.name as type_name',
                            'headings.id as heading_id','heading_info.name as heading_name','headings.require_flag as require_flag')
                            ->get();

                            $queryHeading->each(function ($heading) use ($tempId) {
                                    $subheadings = DB::table('template_heading_subheading as tempHeadSub')
                                        ->where('tempHeadSub.template_id',$tempId)
                                        ->where('tempHeadSub.heading_id', $heading->heading_id)
                                        ->join('subheadings', 'subheadings.id', '=', 'tempHeadSub.subheading_id')
                                        ->whereNull('tempHeadSub.deleted_at')
                                        ->select('subheadings.id as subheading_id','subheadings.name as subheading_name');
                                        $heading->subheadings = $subheadings->get();

                                    $levels = DB::table('template_heading_level as tempHeadLvl')
                                            ->where('tempHeadLvl.template_id',$tempId)
                                            ->where('tempHeadLvl.heading_id', $heading->heading_id)
                                            ->join('levels', 'levels.id', '=', 'tempHeadLvl.level_id')
                                            ->whereNull('tempHeadLvl.deleted_at')
                                            ->select('levels.id as level_id','levels.level as level_name');
                                            $heading->levels = $levels->get();

                                });

            $queryTemp->headings = $queryHeading;
            return ['status'=> true, 'data'=>$queryTemp];
        
    }

    /**
     * Show template's id and name for Applicant list search
     *
     * @author Thu Ta
     * @create 21/06/2022
     * @return Response object
     */
    public function templateAll()
    {
        #select query from templates table
        $template_name = Template::select('id as template_id','name as template_name' ,'active_flag')->where('deleted_at', null)->orderBy('name', 'ASC')->get();
        if ($template_name) {
            return response()->json(['status' => 'OK', 'data' => $template_name], 200); //return template name
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200')); //return error message
        }
    }

    /**
     * Search template by name.
     *
     * @author Thu Ta
     * @create 21/06/2022
     * @param  Request $request
     * @return Response object
     */
    public function search($request)
    {
        #get template name and created date from template table
        $query = DB::table('templates')
        ->selectRaw("id as 'template_id', name as 'template_name',  DATE_FORMAT(updated_at, '%Y-%m-%d') as 'date', link as 'link', active_flag")
        ->where('deleted_at', null);
        #check $template_name exists
        if(isset($request->name)) {
            $template_name = Template::where('name', "LIKE", "%" . $request->name . "%"); //find template name in template table
            if ($template_name->doesntExist()) { // if template doesn't exist
                if (!empty($template_name->withTrashed()->first())) {
                    return $this->msgHelpUtil->errorMessage('errorMessage.SE026',config('HTTP_CODE_200')); // template exists but already deleted
                }
                return $this->msgHelpUtil->errorMessage('errorMessage.SE020',config('HTTP_CODE_200')); // template never exists
            }
            if ($template_name->exists()) {
                $query->where('name', "LIKE", "%" . $request->name . "%"); // show template if exists
            }
        }
        #check template is active or not
        if($request->active_flag == config('ONE')) {
            $query->where('active_flag', '=', config('ONE')); //find template active
        }
        if ($request->active_flag == config('TWO')) {
            $query->where('active_flag', '=', config('ZERO')); //find template inactive
        }
        #get the query data with paginate
        $result = $query->orderBy('template_id', 'DESC')->paginate(config('PAGING_LIMIT_20'));

        $useTemplate = TemplateApplicant::pluck('template_id')->unique(); //get template id from template_applicants table

        collect($result->items())->map(function($template) use ($useTemplate){ //check template if applicant uses or not
            if (in_array($template->template_id,$useTemplate->toArray())) {
                        $template->isUse = true;
                    } else {
                        $template->isUse = false;
                    }
            return $template;
        });
        #check $result is empty or not
        if($result->isEmpty()) {
            if ($request->active_flag == config('ONE')) {
                if (isset($request->name)) {
                    return $this->msgHelpUtil->errorMessage('errorMessage.SE037',config('HTTP_CODE_200'));
                }
                return $this->msgHelpUtil->errorMessage('errorMessage.SE035',config('HTTP_CODE_200'));
            }
            if ($request->active_flag == config('TWO')) {
                if (isset($request->name)) {
                    return $this->msgHelpUtil->errorMessage('errorMessage.SE038',config('HTTP_CODE_200'));
                }
                return $this->msgHelpUtil->errorMessage('errorMessage.SE036',config('HTTP_CODE_200'));
            }
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'),['attribute' => 'Template']);
        }
        return response()->json ([
            'status' => 'OK',
            'data' => $result
        ],config('HTTP_CODE_200'));
    }
    /**
     * Delete template's data in storage.
     *
     * @author Thu Ta
     * @create 20/06/2022
     * @param  Request $request
     * @return Response object
     */
    public function delete($request)
    {
        $template_id = $request->template_id;
        // dd($template_id);
        DB::beginTransaction();
        try {
            #find $template_id in templates table
            $query = Template::where('id', $template_id);
            #$data is or not
            if($query->exists()) {
                $data = $query->first();
                $templateName = $data->name;
                // dd($templateName);
                #find $template_id in template_applicant table
                $tmp = TemplateApplicant::where('template_id', $template_id)->first();
                #$tmp is or not
                if (!isset($tmp)) {
                    $templateDelete = $data->delete(); //template delete
                    #heading delete
                    $headingIdExist = TemplateHeading::where('template_id', $template_id)->pluck('heading_id')->toArray(); // heading_id with template_id
                    if($headingIdExist) {
                        foreach ($headingIdExist as $headingId) {
                            $headingIdCheck = Heading::find($headingId); //find heading_id in headings table
                            if ($headingIdCheck) {
                                $headingInfoId = Heading::where('id',$headingId)->pluck('heading_info_id'); //heading_info_id with heading_id
                                if ($headingInfoId) {
                                    $heading = Heading::where('heading_info_id', $headingInfoId)->pluck('id')->toArray();//heading_id search with heading_info_id
                                    $headingIdIntersect = array_intersect($headingIdExist, $heading);
                                    // $heading_id
                                    $headingDelete = Heading::where('id', $headingIdIntersect)->delete(); //delete in headings table
                                    $tempHeadDelete = TemplateHeading::where('heading_id', $headingIdIntersect)->delete(); //delete in template_heading table
                                    $headingIdDiff = array_diff($heading, $headingIdExist); //to get heading_id that not use in other template
                                    #check template_id in template_heading table to delete
                                    $templateIdCheck  = TemplateHeading::where('heading_id', $headingIdDiff)->exists();
                                    if($templateIdCheck == false) {
                                        $headingInfoDelete = HeadingInfo::where('id', $headingInfoId)->delete(); //delete heading_info that not use in other template
                                    }
                                }
                            }
                        }
                    }

                    #subheading delete
                    $subheadingIdExist = TemplateHeadingSubheading::where('template_id', $template_id)->pluck('subheading_id')->toArray(); //get subheading_id with template_id in array
                    // dd($subheading_id_exist);

                    if($subheadingIdExist) { //check subheading_id exist or not
                        $subheadingIdAll = TemplateHeadingSubheading::whereIn('subheading_id', $subheadingIdExist)->where('template_id','!=',$template_id)->pluck('subheading_id')->unique()->all(); //get subheading_id array
                        // dd($subheading_id_all);

                        $subheadingIdDiff = array_diff($subheadingIdExist, $subheadingIdAll);//get subheading_id array that not use in other template
                        // dd($subheading_id_diff);

                        foreach ($subheadingIdDiff as $subheadingIdFiltered) {
                            $subheadingDelete = Subheading::where('id',$subheadingIdFiltered)->delete(); //delete subheading_id that not use in other template
                        }
                        $tempSubheadDelete = TemplateHeadingSubheading::where('template_id',$template_id)->delete(); //delete template_id in template_heading_subheading

                    }

                    #level delete
                    $levelIdExist = DB::table('template_heading_level as tempHeadLvl')
                                    ->join('levels','levels.id','=','tempHeadLvl.level_id')
                                    ->where('levels.level_category_id','=',config('TWO'))
                                    ->where('tempHeadLvl.template_id', $template_id)
                                    ->where('tempHeadLvl.deleted_at', null)
                                    ->pluck('tempHeadLvl.level_id')->toArray(); //get level_id with template_id in array
                    // dd($level_id_exist);
                    if($levelIdExist) { //check level_id exist or not
                        $levelIdAll = TemplateHeadingLevel::whereIn('level_id', $levelIdExist)->where('template_id','!=',$template_id)->pluck('level_id')->unique()->all(); //get level_id array
                        // dd($level_id_all);
                        $levelIdDiff = array_diff($levelIdExist, $levelIdAll); //get level_id array that not use in other template
                        // dd($level_id_diff);
                        $level_delete = Level::whereIn('id',$levelIdDiff)->delete(); //delete level_id that not use in other template
                        $tempLevelDelete = TemplateHeadingLevel::where('template_id',$template_id)->delete(); //delete template_id in template_heading_level
                    }

                    DB::commit(); //delete in database
                    // dd($template_delete);
                    if($templateDelete && $headingDelete && $tempHeadDelete || $headingInfoDelete || $subheadingDelete || $tempSubheadDelete || $level_delete || $tempLevelDelete )
                    {
                        $description = "Delete ".$templateName;
                        $form = "Template List";
                        $this->writeCRUDLog($request->login_id, $description, $form, config("DELETE"));
                        // dd('delete okk');
                        return $this->msgHelpUtil->successMessage('successMessage.SS003',config('HTTP_CODE_200')); //Delte successfully
                    }

                } else { #can't delete, $tmp is in template_applicant table
                    return $this->msgHelpUtil->errorMessage('errorMessage.SE011',config('HTTP_CODE_200')); //
                }
            } else { #$template_id is not found in template table
                if($query->doesntExist())
                {
                    if ( !empty($query->withTrashed()->first())) {
                        return $this->msgHelpUtil->errorMessage('errorMessage.SE026',config('HTTP_CODE_200')); // template exists but already deleted
                    }
                }
                return $this->msgHelpUtil->errorMessage('errorMessage.SE020',config('HTTP_CODE_200')); // template never exists
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage());
        }
    }
//Daniel Adams
    /**
     * Show Dashboard.
     *
     * @author Thu Ta
     * @create 13/07/2022
     * @param  Request
     * @return Response object
     */
    public function dashboard()
    {

        $dashboard = TemplateApplicant::join('applicants',function($join){
            $join->on('template_applicant.applicant_id','=','applicants.id')
            ->whereNull('applicants.deleted_at');
            })
        ->select( DB::raw("COUNT(CASE status WHEN ".config('ONE')." THEN 1 ELSE NULL END) AS applicant_pending"),
        DB::raw("COUNT(CASE status WHEN ".config('THREE')." THEN 1 ELSE NULL END) AS applicant_processing"),
        DB::raw("COUNT(CASE status WHEN ".config('FOUR')." THEN 1 ELSE NULL END) AS applicant_success"))
        ->first();
        
        $dashboard->template_actives = Template::where('active_flag',config('ONE'))->count();
        $dashboard->template_inactives = Template::where('active_flag',config('ZERO'))->count();
        $dashboard->template_total = Template::count('id');

        return $dashboard;
    }
}

