<?php

namespace App\DBTransactions\Template;

use App\Models\Level;
use App\Models\Heading;
use App\Models\Template;
use App\Models\Subheading;
use App\Models\HeadingInfo;
use Illuminate\Http\Request;
use App\Classes\DBTransaction;
use App\Models\TemplateHeading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TemplateHeadingLevel;
use App\Models\TemplateHeadingSubheading;

/**
 * To update template and its data into template-related table
 *
 * @author  Yan Naing Aung
 * @create  2022/07/25
 */
class TemplateUpdate extends DBTransaction
{
    private $tempData;

    /**
     * Constructor to assign interface to variable
     */
    public function __construct(Request $request)
    {
        $this->tempData = $request;
    }

    /**
	 * Update Template Data
     *
     * @author  Yan Naing Aung
     * @create  2022/07/25
     * @return  array
	 */
    public function process()
    {
        $tempId = $this->tempData->template_id;
        #Update Template Section
        $tempUpdates = [
            'name' => $this->tempData->title,
            'layout_id' => $this->tempData->layout_id,
            'updated_emp' => $this->tempData->login_id,
            'updated_at' => now()
        ];
        $affected[] = Template::where('id', $tempId)->update($tempUpdates);

        $newHeadIds = array();
        #Headings and its peripherals Update Section
        foreach ($this->tempData->headings as $heading) {
            //seperate update and new headings with heading_id
            if (!empty($heading['heading_id'])) {
                #Update Existing Heading
                $checkTempHead = DB::table('template_heading')->where([
                                            ['heading_id', $heading['heading_id']],
                                            ['template_id', $tempId]
                                        ])->whereNull('template_heading.deleted_at')->exists();
                //check heading_id is valid with template_id
                if ($checkTempHead)
                    $affected[] = $this->updateHeadingData($heading, $tempId, $this->tempData->login_id);
            } else {
                #Insert New Heading
                $addResult = $this->addHeadingData($heading, $tempId, $this->tempData->login_id);
                //save new heading_id if operation success.
                if ($addResult) $newHeadIds[] = $addResult;
                else $affected[] = $addResult;
            }
        }

        #Removed Heading Delete Section
        //combine update headings and new added headings
        $updateHeadIds = collect($this->tempData->headings)->pluck('heading_id')->filter()
                            ->concat($newHeadIds)->all();
        //get removed heading id
        $removedHeadIds = TemplateHeading::where('template_id', $tempId)
                            ->whereNotIn('heading_id', $updateHeadIds)
                            ->pluck('heading_id')->all();
        //delete removed headings
        if (!empty($removedHeadIds))
            $affected[] = $this->deleteHeadingData($removedHeadIds, $tempId);

        #Check Template Data Validation
        $affected[] = DB::table('templates')->where('id', $tempId)->whereNotNull('deleted_at')->doesntExist();
        //check template is used by applicants
        $affected[] = DB::table('template_applicant')->where('template_id', $tempId)->whereNull('deleted_at')->doesntExist();

        #DB Transactions Commitment Section
        //check DB operations: one of them fails -> DB rollback.
        $result = collect($affected)->contains(false) ? false : true;
        if (!$result)
            return ['status'=> false, 'error'=> 'Update Failed!'];
        //write all transactions to DB
        return ['status'=> true, 'error'=> ''];
    }

    /**
     * Update Heading's data (subheadings, levels) into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $heading
     * @param  int  $tempId
     * @param  int  $loginId
     * @return boolean
     */
    private function updateHeadingData (array $heading, $tempId, $loginId) {
        $oldHeadType = DB::table('headings')->where('id', $heading['heading_id'])->whereNull('deleted_at')->value('type_id');
        //Check Same type or Different type
        if ($oldHeadType == $heading['type_id']) {
            #Same Heading Type Case
            # Update Heading Name (for all level fields)
            $result[] = $this->updateHeading($heading, $loginId);

            # Update Subheading (for DD,Radio,Checkbox)
            if ($heading['type_id'] == config('HEADING_TYPE_DATA_LIST') ||
                $heading['type_id'] == config('HEADING_TYPE_SINGLE_CHOICE') ||
                $heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {

                $result[] = $this->updateSubheading($heading, $tempId, $loginId);
                # Update Levels (for Checkbox)
                if ($heading['type_id'] == config('HEADING_TYPE_CHECKBOX'))
                    $result[] = $this->updateLevel($heading['level'], $heading['heading_id'], $tempId, $loginId);
            }
        } else {
            #Different Heading Type Case

            $result[] = $this->updateHeading($heading, $loginId);

            //old heading - data_list or single_choice
            if ($oldHeadType == config('HEADING_TYPE_DATA_LIST') || $oldHeadType == config('HEADING_TYPE_SINGLE_CHOICE')) {
                //updated heading type - multiple choice
                if ($heading['type_id'] == config('HEADING_TYPE_DATA_LIST') ||
                    $heading['type_id'] == config('HEADING_TYPE_SINGLE_CHOICE') ||
                    $heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {
                    //update subheadings
                    $result[] = $this->updateSubheading($heading, $tempId, $loginId);

                    if ($heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {
                        //add levels
                        if ($heading['level']['level_cat'] != config('LEVEL_NONE'))
                            $result[] = $this->addLevels($heading['level'], $heading['heading_id'], $tempId, $loginId);
                    }
                } else {
                    //updated heading type - one level fields
                    //delete subheadings of old heading
                    $result[] = $this->deleteSubheadings(null, $heading['heading_id'], true);
                }
            } elseif ($oldHeadType == config('HEADING_TYPE_CHECKBOX')) {
                #old heading - multiple choice

                //updated heading - datalist or single choice
                if ($heading['type_id'] == config('HEADING_TYPE_DATA_LIST') || $heading['type_id'] == config('HEADING_TYPE_SINGLE_CHOICE')) {
                    // update subheadings
                    $result[] = $this->updateSubheading($heading, $tempId, $loginId);
                    // delete old heading levels
                    $result[] = $this->deleteLevels(null, $heading['heading_id'], true);
                } else {
                    //updated heading - other one level fields (TextBox, Comment Box, etc.)
                    // delete old heading's subheadings.
                    $result[] = $this->deleteSubheadings(null, $heading['heading_id'], true);
                    // delete old heading's levels.
                    $result[] = $this->deleteLevels(null, $heading['heading_id'], true);
                }
            } else {
                #old heading - one level fields (TextBox, Comment Box, etc.)

                //updated heading - datalist or single choice or multiple choice
                if ($heading['type_id'] == config('HEADING_TYPE_DATA_LIST') ||
                    $heading['type_id'] == config('HEADING_TYPE_SINGLE_CHOICE') ||
                    $heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {
                    //add subheadings of new headings.
                    $result[] = $this->addSubheadings($heading['subheadings'], $heading['heading_id'], $tempId, $loginId);

                    //updated heading - multiple choice
                    if ($heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {
                        //add levels of multiple choice.
                        if ($heading['level']['level_cat'] != config('LEVEL_NONE'))
                            $result[] = $this->addLevels($heading['level'], $heading['heading_id'], $tempId, $loginId);
                    }
                }
            }
        }
        return collect($result)->contains(false) ? false : true;
    }

    /**
     * Insert new Heading's data (subheadings, levels) into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $heading
     * @param  int  $tempId
     * @param  int  $loginId
     * @return boolean
     */
    private function addHeadingData (array $heading, $tempId, $loginId) {
        //add new heading data
        $headingId = $this->addHeading($heading, $tempId, $loginId);
        //check new heading id
        if (empty($headingId)) return false;

        $result = array();
        //add subheading, level for DataList, Multi, Single Choice
        if ($heading['type_id'] == config('HEADING_TYPE_DATA_LIST') ||
            $heading['type_id'] == config('HEADING_TYPE_SINGLE_CHOICE') ||
            $heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {
                $result[] = $this->addSubheadings($heading['subheadings'], $headingId, $tempId, $loginId);
            //add level data for multiple choice
            if ($heading['type_id'] == config('HEADING_TYPE_CHECKBOX')) {
                if ($heading['level']['level_cat'] != config('LEVEL_NONE'))
                    $result[] = $this->addLevels($heading['level'], $headingId, $tempId, $loginId);
            }
        }
        return collect($result)->contains(false) ? false : $headingId;
    }

    /**
     * Delete removed Heading's data (subheadings, levels) from DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $removedHeadIds
     * @param  int  $tempId
     * @return boolean
     */
    private function deleteHeadingData (array $removedHeadIds, $tempId) {
        $twoLevelHeadToDelete = Heading::whereIn('id', $removedHeadIds)
                                            ->whereIn('type_id', [
                                                config('HEADING_TYPE_DATA_LIST'),
                                                config('HEADING_TYPE_SINGLE_CHOICE'),
                                                config('HEADING_TYPE_CHECKBOX')
                                            ])->select('id','type_id')->get();
        //delete heading
        $result[] = $this->deleteHeading($removedHeadIds, $tempId);

        //delete two or more level field's subheadings and levels.
        $twoLevelHeadToDelete->each(function ($heading) use (&$result){
            //delete heading's subheadings
            $result[] = $this->deleteSubheadings(null, $heading->id, true);
            //delete heading's levels for multiple choice
            if ($heading->type_id == config('HEADING_TYPE_CHECKBOX'))
                $result[] = $this->deleteLevels(null, $heading->id, true);
        });

        return collect($result)->contains(false) ? false : true;
    }

    /**
     * Update heading data into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $heading
     * @param  int  $loginId
     * @return boolean
     */
    private function updateHeading (array $heading, $loginId) {
        $headingUpdates = [
            'type_id' => $heading['type_id'],
            'require_flag' => $heading['require_flag'],
            'updated_emp' => $loginId,
            'updated_at' => now(),
        ];

        try {
            #Check Heading Name is changed
            $checkNameUpdate = DB::table('headings')->where('headings.id', $heading['heading_id'])
                                ->join('heading_info','heading_info.id','=','headings.heading_info_id')
                                ->where('heading_info.name', $heading['heading_name'])
                                ->whereNull('headings.deleted_at')
                                ->doesntExist();
            //check heading name
            if ($checkNameUpdate) {
                #delete old heading name in heading_info table
                $headingInfoId = Heading::where('headings.id', $heading['heading_id'])
                                        ->value('heading_info_id');
                $checkHeadInfo = Heading::where([
                                            ['heading_info_id','=', $headingInfoId],
                                            ['id','<>', $heading['heading_id']]
                                        ])->doesntExist();
                if ($checkHeadInfo) HeadingInfo::where('id', $headingInfoId)->delete();

                #insert new heading if heading name doesn't exist. Or use existing one.
                $headInfoInsert = [
                    'created_emp' => $loginId,
                    'updated_emp' => $loginId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $headingUpdates['heading_info_id'] = HeadingInfo::firstOrCreate(['name' => $heading['heading_name']], $headInfoInsert)->id;
            }
            //update heading infos in headings table.
            Heading::where('id', $heading['heading_id'])->update($headingUpdates);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }

    /**
     * Insert new heading data into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $heading
     * @param  int  $tempId
     * @param  int  $loginId
     * @return boolean
     */
    private function addHeading (array $heading, $tempId, $loginId) {
        $tempHeadInsert = [
            'template_id' => $tempId,
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $headingInsert = [
            'type_id' => $heading['type_id'],
            'require_flag' => $heading['require_flag'],
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $headInfoInsert = [
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            //if heading_info exists, use existing one or create new heading_info.
            $headingInsert['heading_info_id'] = HeadingInfo::firstOrCreate(['name' => $heading['heading_name']], $headInfoInsert)->id;
            //create new heading
            $headingId = Heading::create($headingInsert)->id;
            //add heading to template
            $tempHeadInsert['heading_id'] = $headingId;
            TemplateHeading::insert($tempHeadInsert);

            return $headingId;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }

    /**
     * Delete removed headings from DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $headingIds
     * @param  int  $tempId
     * @return boolean
     */
    private function deleteHeading (array $headingIds, $tempId) {

        $headingInfoIds = Heading::whereIn('id', $headingIds)->pluck('heading_info_id')->unique();

        $headingInfoExist = Heading::whereNotIn('id', $headingIds)
                                ->whereIn('heading_info_id', $headingInfoIds)
                                ->pluck('heading_info_id');

        $headInfoToDelete = $headingInfoIds->diff($headingInfoExist)->all();

        try {
            //delete heading info data
            HeadingInfo::whereIn('id', $headInfoToDelete)->delete();
            //delete heading data
            Heading::whereIn('id', $headingIds)->delete();
            //delete template-heading connection
            TemplateHeading::where('template_id', $tempId)->whereIn('heading_id', $headingIds)->delete();

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }

    /**
     * Update subheading data into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $heading
     * @param  int  $tempId
     * @param  int  $loginId
     * @return boolean
     */
    private function updateSubheading (array $heading, $tempId, $loginId) {
        $result = array();
        $oldSubheadings = DB::table('template_heading_subheading as tempHeadSub')->where('tempHeadSub.heading_id', $heading['heading_id'])
                            ->join('subheadings','subheadings.id','=','tempHeadSub.subheading_id')
                            ->whereNull('tempHeadSub.deleted_at')
                            ->pluck('subheadings.name')->all();

        $newSubHeadings = array_udiff($heading['subheadings'], $oldSubheadings, 'strcasecmp');

        $removeSubHeadings = array_udiff($oldSubheadings, $heading['subheadings'], 'strcasecmp');

        #Delete removed subheadings //need to be placed before update subheads!!
        if (!empty($removeSubHeadings))
            $result[] = $this->deleteSubheadings($removeSubHeadings, $heading['heading_id']);

        #update subheading records in user-input order.
        $result[] = $this->addSubheadings($newSubHeadings, null, null, $loginId, true);
        $result[] = $this->insertTempHeadSub($heading['subheadings'], $heading['heading_id'], $tempId, $loginId, true);

        return collect($result)->contains(false) ? false : true;
    }

    /**
     * Insert subheadings into template_heading_subheading in user-input order.
     *
     * @author yannaingaung
     * @create 14/07/2022
     * @param  array  $subheadings
     * @param  int  $heading_id
     * @param  int  $tempId
     * @param  int  $loginId
     * @param  int  $updateStatus for subheadings update case
     * @return boolean
     */
    private function insertTempHeadSub (array $subheadings, $headingId, $tempId, $loginId, $updateStatus = false) {
        //return if subhead data is empty.
        if (empty($subheadings)) return true;

        $tempSubheadInserts = array();
        $tempSubheadInsert = [
            'template_id' => $tempId,
            'heading_id' => $headingId,
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        //get subheading ids in user-input subheadings order.
        $str = "CASE "; $count = 1;
        foreach ($subheadings as $subheading) {
            $str .= "WHEN name = '".$subheading."' THEN ".$count." "; $count++;
        }
        $str .= "ELSE ".$count." END";
        $subheadIds = Subheading::whereIn('name', $subheadings)->orderByRaw($str)->pluck('id')->all();

        if ($updateStatus) {
            //if subheadings aren't changed even the order.
            $oldSubheadIds = TemplateHeadingSubheading::where('heading_id',$headingId)->pluck('subheading_id')->all();
            //same order old headings will be left undeleted.
            $oldSubheadOrder = array_slice($subheadIds, 0, count($oldSubheadIds), true);
            if ($oldSubheadOrder === $oldSubheadIds) {
                $subheadIds = array_diff($subheadIds, $oldSubheadOrder);
            } else {
                //delete all heading-subheading records.
                TemplateHeadingSubheading::where('heading_id',$headingId)->delete();
            }
        }
        //insert subheads into template_heading_subheading.
        foreach($subheadIds as $subheadId) {
            $tempSubheadInsert['subheading_id'] = $subheadId;
            $tempSubheadInserts[] = $tempSubheadInsert;
        }
        TemplateHeadingSubheading::insert($tempSubheadInserts); 
        
        return true;
    }

    /**
     * Insert new subheading data into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $newSubHeadings
     * @param  int  $heading_id
     * @param  int  $tempId
     * @param  int  $loginId
     * @param  boolean  $addSubheadOnly to add only subheadings
     * @return boolean
     */
    private function addSubheadings (array $newSubHeadings, $headingId = null, $tempId = null, $loginId, $addSubheadOnly = false) {
        //return if subhead data is empty.
        if (empty($newSubHeadings)) return true;

        $newSubheads = array();
        $subheadInsert = [
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        //seperate existing subheads and new subheads to add.
        $subheadsExist = Subheading::whereIn('name', $newSubHeadings)->select('id','name')->get();
        $subheadToAdd = array_udiff($newSubHeadings, $subheadsExist->pluck('name')->all(), 'strcasecmp');
        
        //to add only new subheading records.
        foreach ($subheadToAdd as $subhead) {
            $subheadInsert['name'] = $subhead;
            $newSubheads[] = $subheadInsert;
        }
        try {
            Subheading::insert($newSubheads);
            //to add subhead only for updateSubHead.
            if ($addSubheadOnly) return true;
            //insert subheadings to template_heading_subheading in user-input order.
            $this->insertTempHeadSub($newSubHeadings, $headingId, $tempId, $loginId);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }

    /**
     * Delete removed or all subheadings from DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $removeSubHeadings
     * @param  int  $heading_id
     * @param  boolean  $deleteAll
     * @return boolean
     */
    private function deleteSubheadings ($removeSubHeadings = array(), $heading_id, $deleteAll = false) {
        try {
            //check to delete all subheadings or specific subheadings of heading.
            if ($deleteAll) {
                $subheadIds = TemplateHeadingSubheading::where('heading_id', $heading_id)->pluck('subheading_id')->all();
                //delete all levels of heading
                TemplateHeadingSubheading::where('heading_id', $heading_id)->delete();
            } else {
                $subheadIds = Subheading::whereIn('name', $removeSubHeadings)->pluck('id')->all();
                //remove subheading from heading
                TemplateHeadingSubheading::where('heading_id', $heading_id)
                                            ->whereIn('subheading_id', $subheadIds)->delete();
            }
            //delete subheadings
            $subheadExist = DB::table('template_heading_subheading')
                                    ->where('heading_id','<>',$heading_id)
                                    ->whereIn('subheading_id', $subheadIds)
                                    ->whereNull('deleted_at')
                                    ->pluck('subheading_id')->all();

            $subheadToDelete = array_diff($subheadIds, $subheadExist);

            Subheading::whereIn('id', $subheadToDelete)->delete();
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }

    /**
     * Update level data into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $level
     * @param  int  $heading_id
     * @param  int  $tempId
     * @param  int  $loginId
     * @return boolean
     */
    private function updateLevel (array $level, $heading_id, $tempId, $loginId) {
        //check level is empty or have data.
        $headLevel = DB::table('template_heading_level as tempHeadLvl')->where('tempHeadLvl.heading_id', $heading_id)->whereNull('tempHeadLvl.deleted_at');
        $checkHeadLvl = $headLevel->exists();
        //to check each function operation
        $result = array();

        if ($level['level_cat'] != config('LEVEL_NONE')) {
            //Request has level data, if curr heading has lvl data, update. if no, add lvl data to heading.
            if ($checkHeadLvl) {
                //update level to heading
                //level_cat change, level_data change
                $headLevelJoin = $headLevel->join('levels','levels.id','=','tempHeadLvl.level_id');

                $checkLvlCat = $headLevelJoin->where('levels.level_category_id', $level['level_cat'])->exists();

                if ($checkLvlCat) {
                    # same level category case
                    //update levels of category '1' or '2' into DB
                    if ( $level['level_cat'] == config('LEVEL_CATEGORY_ALPHABET') ||
                        $level['level_cat'] == config('LEVEL_CATEGORY_NUMBER')) {
                        //get old level data from levels table.
                        $oldLevels = $headLevelJoin->pluck('levels.level')->all();

                        //seperate new and removed level with array_diff
                        $newLevels['level_cat'] = $level['level_cat'];
                        $newLevels['level_data'] = array_udiff($level['level_data'], $oldLevels, 'strcasecmp');

                        $removeLevels = array_udiff($oldLevels, $level['level_data'], 'strcasecmp');

                        #Insert new levels
                        if (!empty($newLevels['level_data']))
                            $result[] = $this->addLevels($newLevels, $heading_id, $tempId, $loginId);
                        #Delete removed levels
                        if (!empty($removeLevels))
                            $result[] = $this->deleteLevels($removeLevels, $heading_id);
                    }
                } else {
                    # different level category case
                    //delete heading_level records
                    $result[] = $this->deleteLevels(null, $heading_id, true);
                    //add all levels from request
                    $result[] = $this->addLevels($level, $heading_id, $tempId, $loginId);
                }
            } else {
                //add new level to heading
                $result[] = $this->addLevels($level, $heading_id, $tempId, $loginId);
            }
        } else {
            //level data none case
            //if heading has level data in DB, delete all levels in table.
            if ($checkHeadLvl)
                $result[] = $this->deleteLevels(null, $heading_id, true);
        }
        return collect($result)->contains(false) ? false : true;
    }

    /**
     * Insert new level data into DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $newLevels
     * @param  int  $heading_id
     * @param  int  $tempId
     * @param  int  $loginId
     * @return boolean
     */
    private function addLevels ($newLevels, $heading_id, $tempId, $loginId) {
        $tempHeadLvlInserts = array();
        $lvlInserts = array();
        $levelIds = array();

        $tempHeadLvlInsert = [
            'template_id' => $tempId,
            'heading_id' => $heading_id,
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $lvlInsert = [
            'level_category_id' => $newLevels['level_cat'],
            'created_emp' => $loginId,
            'updated_emp' => $loginId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            if ($newLevels['level_cat'] != config('LEVEL_CATEGORY_NUMBER')) {
                $levelQuery = Level::where('level_category_id', $newLevels['level_cat']);
                //level ids for level category alphabet
                if ($newLevels['level_cat'] == config('LEVEL_CATEGORY_ALPHABET'))
                    $levelQuery->whereIn('level', $newLevels['level_data']);  

                $levelIds = $levelQuery->pluck('id')->all();
            } else {
                $levelExist = Level::where('level_category_id', $newLevels['level_cat'])
                                        ->whereIn('level', $newLevels['level_data'])
                                        ->select('id','level')->get();

                $levelNew = array_udiff($newLevels['level_data'], $levelExist->pluck('level')->all(), 'strcasecmp');

                //add existing level to template_heading_level table
                $levelIds = array_merge($levelIds, $levelExist->pluck('id')->all());
                
                //add new level to levels table
                if (!empty($levelNew)) {
                    foreach ($levelNew as $lvl) {
                        $lvlInsert['level'] = $lvl;
                        $lvlInserts[] = $lvlInsert;
                    }
                    Level::insert($lvlInserts);
                    //add new level ids to template_heading_level table
                    $newLevelIds = Level::whereIn('level',$levelNew)
                                        ->where('level_category_id', $newLevels['level_cat'])
                                        ->pluck('id')->all();
                    $levelIds = array_merge($levelIds, $newLevelIds);
                }
            }
            //insert tempHeadLvl data
            foreach ($levelIds as $levelId) {
                $tempHeadLvlInsert['level_id'] = $levelId;
                $tempHeadLvlInserts[] = $tempHeadLvlInsert;
            }
            if (!empty($tempHeadLvlInserts))
                TemplateHeadingLevel::insert($tempHeadLvlInserts);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }

    /**
     * Delete removed or all levels from DB.
     *
     * @author yannaingaung
     * @create 27/06/2022
     * @param  array  $removeLevels
     * @param  int  $heading_id
     * @param  boolean  $deleteAll
     * @return boolean
     */
    private function deleteLevels ($removeLevels = array(), $heading_id, $deleteAll = false) {
        $lvlCategory = DB::table('template_heading_level as tempHeadLvl')->where('tempHeadLvl.heading_id', $heading_id)
                            ->join('levels', 'levels.id', '=', 'tempHeadLvl.level_id')
                            ->whereNull('tempHeadLvl.deleted_at')
                            ->value('levels.level_category_id');
        try {
            if ($deleteAll) {
                $levelIds = TemplateHeadingLevel::where('heading_id', $heading_id)->pluck('level_id')->all();
                //delete all levels of heading
                TemplateHeadingLevel::where('heading_id', $heading_id)->delete();
            } else {
                $levelIds = Level::whereIn('level', $removeLevels)->pluck('id')->all();
                //delete specific levels of heading
                TemplateHeadingLevel::where('heading_id', $heading_id)
                                        ->whereIn('level_id', $levelIds)->delete();
            }
            //records still remain for LEVEL_CATEGORY_TEXT
            if ($lvlCategory == config('LEVEL_CATEGORY_NUMBER')) {
                $levelExist = DB::table('template_heading_level')
                                    ->where('heading_id', '<>', $heading_id)
                                    ->whereIn('level_id', $levelIds)
                                    ->whereNull('deleted_at')
                                    ->pluck('level_id')->all();
                //delete levels are not used by any headings.
                $levelToDelete = array_diff($levelIds, $levelExist);

                Level::whereIn('id', $levelToDelete)->delete();
            }
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Process Error: '.$e->getMessage().' in file '.__FILE__.' at line '.__LINE__.' at method '.__METHOD__.'()');
            return false;
        }
    }
}
