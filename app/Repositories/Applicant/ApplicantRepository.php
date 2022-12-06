<?php
namespace App\Repositories\Applicant;

use File;
use config;
use ZipArchive;
use App\Models\Heading;
use App\Models\Template;
use App\Models\Applicant;
use App\Traits\MailTrait;
use Illuminate\Http\Request;
use App\Models\ApplicantInfo;
use App\Models\ApplicantLink;
use App\Models\TemplateHeading;
use App\Models\TemplateApplicant;
use App\Classes\MessageHelperUtil;
use Illuminate\Support\Facades\DB;
use App\Traits\DownloadHeaderTrait;
use Illuminate\Support\Facades\Log;
use App\Models\TemplateHeadingLevel;
use Illuminate\Support\Facades\Mail;
use App\Traits\GoogleCloudStorageTrait;
use App\Models\TemplateHeadingSubheading;
use App\Models\TemplateApplicantApplicantInfo;
use App\Repositories\Template\TemplateRepository;
use App\DBTransactions\Applicant\SaveEmailPasscode;
use App\DBTransactions\Applicant\DeleteEmailPasscode;
use App\DBTransactions\Applicant\UpdateEmailPasscode;
use App\Interfaces\Applicant\ApplicantRepositoryInterface;

class ApplicantRepository implements ApplicantRepositoryInterface
{
    use DownloadHeaderTrait;
    use GoogleCloudStorageTrait;
    use MailTrait;
    public $templateRepository;
    public function __construct()
    {
        $this->templateRepository=new TemplateRepository;
        $this->msgHelpUtil = new MessageHelperUtil;
    }

    /**
     * Send Email Passcode
     *
     * @author Thu Rein Lynn
     * @create  24/06/2022
     *
     * @param  Request  $request
     * @return Response object
    */
    public function sendEmail($request)
    {
        $toEmail=$request->email;
        $templateId=(int)request()->template_id;
        $otp=rand(100000,999999);
        $mailData = [];
        $mailData['mail_to_receipts'] = $toEmail;
        $mailData['subject'] = 'Send resume passcode';
        $mailData['title'] = 'Mr/Ms';
        $mailData['body_message'] = $otp;
        $mailData['template'] = 'mail_template';
        $mailData['form'] = 'resume applicant login form';
        $mailData['op_flag'] = config("SAVE");
        $mailData['device_flag'] = config("WEB");

        $template=Template::where('id',$templateId)->where('active_flag',config('ONE'))->exists();

        if($template){ #Template active,valid exits or not
            //call mail send Method
            $message = $this->sendMail($mailData);
            $mailResArr = json_decode($message->getContent(), true);
            //send mail is error have or not
            if($mailResArr['status'] == "NG"){
                $mailError = $mailResArr['message'];
                return ['status'=>false,'data'=>$mailError];
            } else {
                $emailPasscode=[
                    'email'=>$toEmail,
                    'passcode'=>$otp
                ];
                $emailPasscodeId=DB::table('email_passcode')
                    ->where('email','=',$toEmail)
                    ->value('id');

                if(!isset($emailPasscodeId)){ #email_passcode save or update
                    $saveEmailPasscode = new SaveEmailPasscode($emailPasscode);
                    $saveEmailPasscode->executeProcess();
                }else{
                    $updatePasscode=[
                        'id'=>$emailPasscodeId,
                        'passcode'=>$otp
                    ];
                    $updateEmailPasscode = new UpdateEmailPasscode($updatePasscode);
                    $updateEmailPasscode->executeProcess();
                }
                return ['status'=>true];
            }
        }else{
            return ['status'=>"NoTemplate"];
        }
    }

    /**
     * Check email and passcode
     *
     * @author Thu Rein Lynn
     * @create  24/06/2022
     *
     * @param  Request  $request
     * @return Response object
    */
    public function applicantCheckOtp($request)
    {
        $otp=$request->passcode;
        $email=request()->email;
        $emailPasscode=DB::table('email_passcode') #check email_passcode in `email_passcode` in table
            ->where('email','=',$email)
            ->where('passcode',$otp)
            ->exists();
        if($emailPasscode){ #check email and passcode
            $emailPasscode_id=DB::table('email_passcode')->where('email','=',$email)->value('id');
            $deleteEmailPasscode = new DeleteEmailPasscode($emailPasscode_id);
            $deleteEmailPasscode->executeProcess();
            return ['status'=>true];
        }else{
            return  ['status'=>false];
        }
    }

    /**
     * Applicant Form Load
     *
     * @author Thu Rein Lynn
     * @create  21/06/2022
     *
     * @return Response object
    */
    public function applicantFormLoad()
    {
        $templateId=(int)request()->template_id;
        $email=request()->email;

        $template=Template::where('id',$templateId)->where('active_flag',config('ONE'))->exists();

        #execute TemplateApplicant data join with applicants
        $applicantId=TemplateApplicant::join('applicants','applicants.id','template_applicant.applicant_id')
            ->where('applicants.email',$email)
            ->where('template_applicant.template_id',$templateId)
            ->select('template_applicant.applicant_id')
            ->first();

        if($template){ #if template not exists, data not found
            if($applicantId){ #if template and applicant email exists,execute applicant data and template data
                $applicantId=$applicantId->applicant_id;
                #execute applicant data with associate applicantId and templateId
                $applicants = Applicant::leftJoin('template_applicant','template_applicant.applicant_id','applicants.id')
                    ->leftJoin('templates','templates.id','template_applicant.template_id')
                    ->select('applicants.id as applicant_id',
                    'templates.layout_id',
                    'templates.id as template_id',
                    'templates.name as template_name',
                    'template_applicant.status',
                    'template_applicant.applicant_template_link')
                    ->where('applicants.id',$applicantId)
                    ->where('templates.id',$templateId)
                    ->whereNull('template_applicant.deleted_at')
                    ->first();

                $heading=TemplateHeading::leftJoin('headings','template_heading.heading_id','headings.id')
                    ->leftJoin('heading_info','headings.heading_info_id','heading_info.id')
                    ->leftJoin('types','headings.type_id','types.id')
                    ->where('template_heading.template_id',$templateId)
                    ->select('headings.id as heading_id','heading_info.name as heading_name','headings.require_flag', 'headings.type_id','types.name as type_name')
                    ->get();
                $count=$heading->count();

                #subheading, level, applicantInfo with heading loop
                for ($i=config('ZERO'); $i < $count; $i++) {
                    $heading[$i]->subheadings=[];
                    $heading[$i]->levels=[];
                    #check typeId 1,2,3 execute with loop for subheading
                    if($heading[$i]->type_id == config('ONE') || $heading[$i]->type_id == config('TWO') || $heading[$i]->type_id == config('THREE')){
                        #execute subheading(single choice,multichoice and Datalist)
                        $subheading=TemplateHeadingSubheading::leftJoin('subheadings','template_heading_subheading.subheading_id','subheadings.id')
                            ->where('template_heading_subheading.heading_id',$heading[$i]->heading_id)
                            ->select('subheadings.id as subheading_id','subheadings.name as subheading_name')
                            ->get();
                        $heading[$i]->subheadings=$subheading;
                        #check typeId 2 execute with loop for level
                        if($heading[$i]->type_id == config('TWO')){
                            #execute level data for typeId 2
                            $level=TemplateHeadingLevel::leftJoin('levels','template_heading_level.level_id','levels.id')
                                ->leftJoin('level_categories','levels.level_category_id','level_categories.id')
                                ->where('template_heading_level.heading_id',$heading[$i]->heading_id)
                                ->select('levels.id as level_id','levels.level as level_name')
                                ->get();
                            $heading[$i]->levels=$level;
                        }
                    }
                    #execute applicantInfo Data
                    $applicantInfo=ApplicantInfo::leftJoin('template_applicant_applicant_info','applicant_infos.id','template_applicant_applicant_info.applicant_info_id')
                        ->leftJoin('levels','applicant_infos.level_id','levels.id')
                        ->where('template_applicant_applicant_info.template_id',$templateId)
                        ->where('template_applicant_applicant_info.applicant_id',$applicantId)
                        ->where('applicant_infos.heading_id',$heading[$i]->heading_id)
                        ->whereNull('template_applicant_applicant_info.deleted_at')
                        ->select('applicant_infos.id as applicant_info_id','applicant_infos.applicant_info as applicant_info_name','levels.level as applicant_info_level')
                        ->get();
                        $heading[$i]->applicant_info=$applicantInfo;

                        if($heading[$i]->type_id == config('SEVEN')){ #for cloud attach file
                           $applicantAttach=count($applicantInfo);
                           for($j=config('ZERO');$j<$applicantAttach;$j++){
                                #create attach file cloud Link
                                $imageCloudLink = $this->objectV4SignedUrl($applicantInfo[$j]->applicant_info_name);
                                $heading[$i]->applicant_info[$j]->applicant_cloud_file= $imageCloudLink;
                                #explode attach file name
                                $imageLink=explode('/',$applicantInfo[$j]->applicant_info_name)[count(explode('/',$applicantInfo[$j]->applicant_info_name))-1];
                                $heading[$i]->applicant_info[$j]->applicant_file= $imageLink;
                            }
                        }
                        if($heading[$i]->type_id == config('EIGHT')){ #for cloud image
                            $applicantImage=count($applicantInfo);
                            if($applicantImage>config('ZERO')){
                                $fileCloudLink = $this->objectV4SignedUrl($applicantInfo[config('ZERO')]->applicant_info_name);
                                $heading[$i]->applicant_info[config('ZERO')]->applicant_cloud_file= $fileCloudLink;
                            }
                        }
                }
                    $applicants->headings=$heading;

                if(isset($applicants)){ #return execute applicants data
                    return ['status'=>true,'data'=>$applicants];
                }else{
                    return  ['status'=>false];
                }
            }else{
                #call template view method
                $result=$this->templateRepository->viewTemplate($templateId);
                if (!$result['status']) return ['status'=>false];

                $template = $result['data'];
                $headingCount=$template->headings->count();
                for($i=0;$i<$headingCount;$i++){
                    $template->headings[$i]->applicant_info=[];
                }
                $template->email=$email;
                $template->applicant_id=null;
                $template->status=null;
                $template->applicant_template_link=null;
                 #return execute applicants data
                return ['status'=>true,'data'=>$template];
                
            }
        }else{
            return ['status'=>false];
        }
    }


    /**
     * Function to show an applicant resume pdf.
     * @create [21.6.2022]
     * @author Thu Rein Lynn
     * @param $id
     * @return mixed
     */
    public function applicantView($id)
    {
        #get pdf link from database
        $objectName = TemplateApplicant::where('applicant_id',$id)->value('applicant_template_link');
        // $objectName = '1009/testing/test_image.png';
        if($objectName){
            $view = $this->objectV4SignedUrl($objectName);
            return $view;
        } else {
           return false;
        }
    }

    /**
     * Download file from cloud to storage
     * @create [21.6.2022]
     * @author Thu Rein Lynn
     * @param  $id
     * @return $tmp temp file name
     */
    public function applicantStorageDownload($id)
    {
        #check if template link exists
        $pdf = TemplateApplicant::where('applicant_id',$id)->first();
        if($pdf){
            $pdfLink[] = ['attach_files' => $pdf->applicant_template_link];
            $attach = ApplicantLink::where('applicant_id',$id)->where('type',2)->get();
            if($attach->isNotEmpty()){
                foreach ($attach as $object){
                    #get all attach links
                    $attachLink = $object->link;
                    $links[] = ['attach_files' =>$attachLink];
                }
                $fileData = array_merge($pdfLink,$links);
            } else {
                $fileData = $pdfLink;
            }
            $tmp = $this->downloadMultipleObjects($fileData);
            return $tmp;
        } else {
            return false;
        }
    }

    /**
     * Download file from storage
     * @create [21.6.2022]
     * @author Thu Rein Lynn
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function applicantDownload($id)
    {
        #get tmp name from storage
        $tmp =$this->applicantStorageDownload($id);
        // dd($tmp);
        if($tmp){
            #check if attach file exists
            $attach = ApplicantLink::where('applicant_id',$id)->where('type',2)->first();
            if($attach){
                #make zip file
                $zip = new ZipArchive;
                #zip file name
                $zipFileName = 'applicantFile_'.date('YmdHis'). '.zip';
                if ($zip->open(storage_path($zipFileName), ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
                {
                    $files = File::files(storage_path('app/'.$tmp));
                    foreach ($files as $value) {
                        $relativeNameInZipFile = basename($value);
                        $zip->addFile($value, $relativeNameInZipFile);
                    }
                    $zip->close();
                }
                #delete tmp directory
                File::deleteDirectory(storage_path('app/'.$tmp));
                $fileName = 'Applicant_'.date('YmdHis').'.zip';
                $headers = $this->getDownloadHeader($fileName);
                #delete zip file after download
                return response()->download(storage_path($zipFileName),$fileName, $headers)->deleteFileAfterSend($zipFileName);
            } else {
                #pdf only download
                $pathToFile = storage_path().'/app/'.$tmp;
                $headers = $this->getDownloadHeader($tmp);
                #delete file after download
                return response()->download($pathToFile, $tmp, $headers)->deleteFileAfterSend(true);
               }
        } else {
            return false;
        }
    }

    /**
    *  Applicant search
    * @author  Thu Rein Lynn
    * @param   Request $request
    * @return  Response array
    * @create  21/06/2022
    */
    public function applicantSearch($request,$search = null)
    {
       # 1************ searching applicants ids
         #get applicants to search only applicants ids
         $applicantsHeadingsApplicantInfo = TemplateApplicant::
         leftJoin('template_applicant_applicant_info', function ($join) {
            $join->on('template_applicant_applicant_info.applicant_id', '=', 'template_applicant.applicant_id')->whereNull('template_applicant_applicant_info.deleted_at');
        })
         ->leftJoin('templates','templates.id','template_applicant_applicant_info.template_id')
         
         ->leftJoin('applicant_infos', function ($join) {
            $join->on('applicant_infos.id', '=', 'template_applicant_applicant_info.applicant_info_id')->whereNull('applicant_infos.deleted_at');;
            })
         ->leftJoin('headings','headings.id','applicant_infos.heading_id')
         ->leftJoin('heading_info','heading_info.id','headings.heading_info_id')
         ->leftJoin('levels','levels.id','applicant_infos.level_id')
          ->select('template_applicant.template_id as template_id','template_applicant.applicant_id as applicant_id','template_applicant.status as status','headings.id as heading_id','applicant_infos.applicant_info as applicant_info','applicant_infos.level_id as level_id','template_applicant_applicant_info.id as temp_app_info_id')
          ->whereNull('template_applicant.deleted_at');
         #check tamplate id
         if(isset($request->template_id) && $request->template_id != config('ZERO')){
             $applicantsHeadingsApplicantInfo= $applicantsHeadingsApplicantInfo->where('template_applicant.template_id',$request->template_id);
         #check headings id
         if(isset($request->heading_id) && $request->heading_id != config('ZERO')){
             $applicantsHeadingsApplicantInfo->where('headings.id',$request->heading_id);
         }
         #check type and heading value
         if(isset($request->heading_type) && isset($request->heading_type) != config('ZERO')){

             #check headings value
             if(($request->heading_type == config('THREE')) || ($request->heading_type == config('ONE')) || $request->heading_type == config('TWO')){#if radio button or dropdown or check-box
                if(isset($request->heading_value)){
                    $applicantsHeadingsApplicantInfo->where('applicant_infos.applicant_info',$request->heading_value);
                }

             }else{
                $applicantsHeadingsApplicantInfo->where('applicant_infos.applicant_info','like','%'.$request->heading_value.'%');
             }
         }
         #check level
         if(isset($request->level) && $request->level != config('ZERO')){
             $applicantsHeadingsApplicantInfo->where('applicant_infos.level_id',$request->level);
         }
         }
         #check status
         if(isset($request->status) && $request->status != config('ZERO')){
             $applicantsHeadingsApplicantInfo
             ->where('template_applicant.status',$request->status);
         }
         #get ids **** applicantsIds
        $applicantsIds = array_unique($applicantsHeadingsApplicantInfo->pluck('applicant_id')->unique()->toArray());

         #in search , you can return false if no applicants , but in excel you have to return empty array if no applicants
         if($search && empty($applicantsIds)){
            return false;
         }

             # 2************** getting applicants info
                 #get applicants

             $applicants = Applicant::
             leftJoin('template_applicant','template_applicant.applicant_id','applicants.id')
             ->leftJoin('templates','templates.id','template_applicant.template_id')
             ->select('templates.id as template_id','templates.name as template_name','applicants.id as applicant_id',DB::raw('DATE_FORMAT(applicants.created_at,  "%Y-%m-%d") as date'),'template_applicant.status as status', DB::raw('(CASE
             WHEN template_applicant.status = "1" THEN "pending"WHEN template_applicant.status = "2" THEN "reject"
             WHEN template_applicant.status = "3" THEN "processing"WHEN template_applicant.status = "4" THEN "pass"
             ELSE "fail" END) AS status'),'template_applicant.status as status_id')
             ->whereIn('applicants.id', $applicantsIds)
             ->whereNull('template_applicant.deleted_at')
             ->orderBy('applicants.created_at','desc');
             if($search){#if search
                $applicants = $applicants->paginate(config('PAGING_LIMIT_20'));
                $applicantsIds = $applicants #get applicants ids (only in paginate)
                ->getCollection()->map(function($applicant,$key){
                    return $applicant->applicant_id;
                });
                $applicants->getCollection();
             }else{#if excel download
                $applicants = collect($applicants->get());
             }

             #get links
             $links = ApplicantLink::
             leftJoin('applicants','applicant_links.applicant_id','applicants.id')
             ->select('applicant_links.applicant_id as applicant_id','applicant_links.link as link','applicant_links.type as type')
             ->whereIn('applicants.id', $applicantsIds)
             ->get();
             #headings and applicant info
             $headingsApplicantInfos = Heading::
             leftJoin('heading_info','heading_info.id','headings.heading_info_id')
             ->leftJoin('applicant_infos','applicant_infos.heading_id','headings.id')
             ->leftJoin('levels','levels.id','applicant_infos.level_id')
             ->leftJoin('template_applicant_applicant_info','template_applicant_applicant_info.applicant_info_id','applicant_infos.id')
             ->leftJoin('applicants','applicants.id','template_applicant_applicant_info.applicant_id')
             ->leftJoin('templates','templates.id','template_applicant_applicant_info.template_id')
             ->select('headings.id as heading_id','heading_info.name as heading_name','applicant_infos.applicant_info as applicant_info',
             'applicants.id as applicant_id','levels.level as level','headings.type_id as type')
             ->whereNull('template_applicant_applicant_info.deleted_at')
             ->whereIn('applicants.id', $applicantsIds)
             ->whereNotIn('headings.type_id',[config('SEVEN'),config('EIGHT')])#ignore profile image and attach files
             ->orderBy('headings.id')
             ->get();
             #conbine applicants and their corresponds headings and infos
             $applicantsData = $applicants->map(function($applicant,$key) use ($headingsApplicantInfos,$search,$links){
                $applicantsIdsInApplicantsInfos = $headingsApplicantInfos->pluck('applicant_id')->unique();
                #check applicant exists but have ONLY profile and attach file
                if(in_array($applicant->applicant_id,$applicantsIdsInApplicantsInfos->toArray())){
                    foreach($headingsApplicantInfos as $headingsApplicantInfo){
                            if($headingsApplicantInfo->applicant_id == $applicant->applicant_id){
                                if($search){#  for search
                                    if(strtolower($headingsApplicantInfo->heading_name) == 'name' && $headingsApplicantInfo->type == config('FOUR')){
                                        $applicant->name = $headingsApplicantInfo->applicant_info;
                                    }else if(strtolower($headingsApplicantInfo->heading_name) == 'email' && $headingsApplicantInfo->type == config('FOUR')){
                                        $applicant->email  = $headingsApplicantInfo->applicant_info;
                                    }else if(strtolower($headingsApplicantInfo->heading_name) == "job position" && $headingsApplicantInfo->type == config('FOUR')){
                                        $applicant->job_position = $headingsApplicantInfo->applicant_info;
                                    }elseif(__("resume_headings.$headingsApplicantInfo->heading_name") == 'name' && $headingsApplicantInfo->type == config('FOUR')){
                                        $applicant->name = $headingsApplicantInfo->applicant_info;
                                    }elseif(__("resume_headings.$headingsApplicantInfo->heading_name") == 'email' && $headingsApplicantInfo->type == config('FOUR')){
                                        $applicant->email  = $headingsApplicantInfo->applicant_info;
                                    }elseif(__("resume_headings.$headingsApplicantInfo->heading_name") == 'job position' && $headingsApplicantInfo->type == config('FOUR')){
                                        $applicant->job_position  = $headingsApplicantInfo->applicant_info;
                                    }

                                    if(!isset($applicant->name)){ #if name is not exsits
                                        $applicant->name = '';
                                    }
                                    if(!isset($applicant->email)){ #if email is not exsits
                                        $applicant->email = '';
                                    }
                                    if(!isset($applicant->job_position)){ #if job description is not exsits
                                        $applicant->job_position = '';
                                    }
                                    if($links->isNotEmpty()){
                                       foreach($links as $link){#for avatar link
                                           if($link->applicant_id == $applicant->applicant_id){
                                               if($link->type == config('ONE')){# profile image
                                                  $image_cloud_link = $this->objectV4SignedUrl($link->link);
                                                   $applicant->image_link = $image_cloud_link;
                                               }else{#not profile image
                                                   if(!isset( $applicant->image_link)){#both attach file and profile exists and attach is under profile in db********
                                                       $applicant->image_link = '';
                                                   }
                                               }
                                           }else{
                                               if(!isset( $applicant->image_link)){#both attach file and profile do not exists
                                                   $applicant->image_link = '';
                                               }
                                           }
                                       }
                                    }else{#if links is empty array
                                       $applicant->image_link = '';
                                    }

                                    $applicant->isChecked = false;
                                }else{# for excel
                                    unset($applicant->template_id); #unset some attribute that are not require for excel
                                    unset($applicant->template_name);
                                    unset($applicant->status_id);
                                    if($headingsApplicantInfo->type == config('TWO')){#if applicant info type is check box

                                        if($headingsApplicantInfo->applicant_info !=''){ #if data exist

                                           if(isset($applicant->{$headingsApplicantInfo->heading_name})){#if attribute exists , insert coma (for decorate multiple choice)
                                               $applicant->{$headingsApplicantInfo->heading_name} .= ' , ';
                                           }
                                            $applicant->{$headingsApplicantInfo->heading_name} .= $headingsApplicantInfo->applicant_info;

                                            if($headingsApplicantInfo->level){#if level exist
                                                $applicant->{$headingsApplicantInfo->heading_name} .= ' - '.$headingsApplicantInfo->level;
                                            }
                                        }else{ #if data is blank (to appropriate with excel download)
                                                $applicant->{$headingsApplicantInfo->heading_name} .= $headingsApplicantInfo->applicant_info;
                                        }

                                    }else{
                                        $applicant->{$headingsApplicantInfo->heading_name} = $headingsApplicantInfo->applicant_info;
                                    }
                                }
                            }
                     }
                }else{# applicant exists but have ONLY profile and attach file
                    if($search){#search
                        $applicant->name = '';
                        $applicant->email = '';
                        $applicant->job_position = '';
                        if($links->isNotEmpty()){
                            foreach($links as $link){#for avatar link
                                if($link->applicant_id == $applicant->applicant_id){
                                    if($link->type == config('ONE')){# profile image
                                    $image_cloud_link = $this->objectV4SignedUrl($link->link);
                                        $applicant->image_link = $image_cloud_link;
                                    }else{#not profile image
                                        if(!isset( $applicant->image_link)){#both attach file and profile exists and attach is under profile in db********
                                            $applicant->image_link = '';
                                        }
                                    }
                                }else{
                                    if(!isset( $applicant->image_link)){#both attach file and profile do not exists
                                        $applicant->image_link = '';
                                    }
                                }
                            }
                        }else{#if links is empty array
                            $applicant->image_link = '';
                        }

                    }else{#excel
                        unset($applicant->template_id); #unset some attribute that are not require for excel
                        unset($applicant->template_name);
                        unset($applicant->status_id);
                    }

                }

               if(!$search){#excel
                $applicant->applicant_id= ($key+config("ONE")); #replace applicant id with NO (in excel show NO instead of applicant id)
               }
             return $applicant;
         });

         if($search){#search
            $applicants->setCollection($applicantsData);
            return $applicants;
         }else{#excel
            return $applicants;
         }
    }

    /**
     * Get all headings with template_id
     * @author Thu Rein Lynn
     * @param  Array $templateId
     * @return $templateHeading
     * @create 22/06/2022
     */
    public function getHeadingswithTemplateId($templateId)
    {
        try{
            DB::beginTransaction();
            $templateHeading = TemplateHeading::where('template_heading.template_id', $templateId)
            ->where('template_heading.template_id',$templateId)
            ->whereNull('template_heading.deleted_at')
            ->whereNotIn("headings.type_id",[config('SEVEN'),config('EIGHT')]) #if heading type is 7 or 8 won't show any data
            ->join('templates','templates.id', '=', 'template_heading.template_id') #join id from templates table with template_id from template_heading table
            ->join('headings','headings.id', '=', 'template_heading.heading_id') #join id from headings table with heading_id from template_heading table
            ->join('heading_info','heading_info.id', '=', 'headings.heading_info_id') #join id from heading_info table with heading_info_id from headings table
            ->select('headings.id as heading_id',
                     'heading_info.name as heading_name',
                     'headings.type_id as heading_type_id') #select heading datas from templates and headings tables
            ->get();
                return $templateHeading;
                }catch (\Exception $e) {
                Log::info($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
                return false;
                }
    }
    /**
     * Status change
     * @author Thu Rein Lynn
     * @param  string $status, Array $applicantId
     * @return boolean
     * @create 20/06/2022
     */
    public function update($status,$applicantId)
    {
        try{
            DB::beginTransaction();
            $applicantsStatus = TemplateApplicant::whereIn('template_applicant.applicant_id',$applicantId)#get applicant from table
                                                    ->whereNull('template_applicant.deleted_at')->pluck('status')->toArray(); #get stauts that is notNull

            // dd($applicants_status);
            if(!empty($applicantsStatus)){ #Reject status cannot be changed to any status
                if($status == config('TWO')){
                    if(in_array(config('TWO'),$applicantsStatus) || in_array(config('THREE'),$applicantsStatus) || in_array(config('FIVE'),$applicantsStatus)){
                        return [false,'change_reject_error']; #Pending and Success can be changed to Reject status
                    }
                }
                elseif($status == config('THREE')){ #Processing status can be changed to success and fail status
                    if(in_array(config('TWO'),$applicantsStatus) || in_array(config('THREE'),$applicantsStatus) || in_array(config('FOUR'),$applicantsStatus) || in_array(config('FIVE'),$applicantsStatus)){
                        return [false,'change_processing_error']; #Pending can only be changed to Processing status
                    }
                }
                elseif($status == config('FOUR')){ #Success status can only be changed to reject status
                    if(in_array(config('ONE'),$applicantsStatus) || in_array(config('TWO'),$applicantsStatus) || in_array(config('FOUR'),$applicantsStatus)){
                        return [false,'change_success_error']; #Processing and Fail can be changed to Success status
                    }   
                }
                elseif($status == config('FIVE')){ #Fail status can only be changed to success status
                    if(in_array(config('ONE'),$applicantsStatus) || in_array(config('TWO'),$applicantsStatus) || in_array(config('FOUR'),$applicantsStatus) || in_array(config('FIVE'),$applicantsStatus)){
                        return [false,'change_fail_error']; #Processing can only be changed to Fail status
                    }
                }
                foreach($applicantId as $applicantIds){
                    $applicantEmail = Applicant::where('id',$applicantIds)->value('email');
                $jobTitle = TemplateApplicant::leftJoin('templates','templates.id','template_applicant.template_id')
                    ->where('template_applicant.applicant_id',$applicantIds)
                    ->value('templates.name');
                    Log::info($applicantIds);
                    Log::info($status);
                if($status == config('FOUR') || $status == config('FIVE')){
                    
                    $mailData = [];
                    $mailData['mail_to_receipts'] = $applicantEmail;
                    $mailData['subject'] = 'Send Job Result';
                    $mailData['title'] = 'Mr/Ms';
                    $mailData['job_title'] = $jobTitle;
                    if($status == config('FOUR')){
                        $mailData['template'] = 'pass';
                    }
                    if($status == config('FIVE')){
                        $mailData['template'] = 'fail';
                    }
                    $mailData['form'] = 'pass or fail result form';
                    $mailData['op_flag'] = config("SAVE");
                    $mailData['device_flag'] = config("WEB");
                    $message = $this->sendMail($mailData);
                    $mailResArr = json_decode($message->getContent(), true);
                    //send mail is error have or not
                    if($mailResArr['status'] == "NG"){
                        $mailError = $mailResArr['message'];
                        return ['status'=>false,'data'=>$mailError];
                    }
                }
                }
                
                TemplateApplicant::
                whereIn("template_applicant.applicant_id",$applicantId)
                ->update(["status"=> $status]);
                DB::commit();
                    return [true,$status];
            }else{ #if applicant is already deleted,cannot change status and show error message
                return [false,'change_deleted_at'];
            }
            }catch(\Exception $e){
                // dd('here');
            Log::channel('debuglog')->debug($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
            DB::rollBack();
            return [false , 'exception_error'];
            }
    }
     /**
     * Applicant delete
     * @author Thu Rein Lynn
     * @param Array $applicantId
     * @return boolean
     * @create 20/06/2022
     */
    public function delete($applicantId)
    {
       DB::beginTransaction();
        try{
            #template applicant delete
            $templateApplicant = TemplateApplicant::whereIn("applicant_id",$applicantId)
            ->whereNull('deleted_at'); #cannot delete if applicant is already deleted
            if($templateApplicant->count() != count($applicantId)){ #to show message for applicant that is already deleted
                return [false , 'already_deleted'];
            }
            $template_applicant = TemplateApplicant::whereIn("applicant_id",$applicantId)
            ->whereNotIn("status",[config('FOUR'),config('THREE')]); #cannot delete if status is success or processing
            if($template_applicant->count() != count($applicantId)){ #to show message for applicant that is success or processing status
                return [false , 'status_denied'];
            }
            $templateApplicant->delete();
            $templateApplicantForLocalDelete =$templateApplicant->get(); #delete data from cloud storage
            foreach ($templateApplicantForLocalDelete as $templateApplicant){
                $this->deleteObject($templateApplicant->applicant_template_link);
            }

            #template applicant applicant info delete
            $templateApplicantApplicantInfo=TemplateApplicantApplicantInfo::join('template_applicant','template_applicant.applicant_id', '=','template_applicant_applicant_info.applicant_id')
            ->whereIn("template_applicant_applicant_info.applicant_id",$applicantId)
            ->whereNotIn("template_applicant.status",[config('THREE'),config('FOUR')]); #cannot delete if status is success or processing
            $templateApplicantApplicantInfo->delete();

            #applicant info delete
            $applicantInfo=ApplicantInfo::join('template_applicant_applicant_info','template_applicant_applicant_info.applicant_info_id','applicant_infos.id')
            ->join('template_applicant','template_applicant.applicant_id','template_applicant_applicant_info.applicant_id')
            ->whereIn('template_applicant.applicant_id',$applicantId)
            ->whereNotIn("template_applicant.status",[config('THREE'),config('FOUR')]); #cannot delete if status is success or processing
            $applicantInfo->delete();

            #link delete
            $applicantLink = ApplicantLink::join('template_applicant','template_applicant.applicant_id','applicant_links.applicant_id')
            ->whereIn('template_applicant.applicant_id',$applicantId)
            ->whereNotIn("template_applicant.status",[config('THREE'),config('FOUR')]); #cannot delete if status is success or processing
            $applicantLink->delete();
            $applicantLinkForLocalDelete =$applicantLink->get(); #delete data from cloud storage
            foreach ($applicantLinkForLocalDelete as $applicantLink){
                $this->deleteObject($applicantLink->link);
            }

            #applicant delete
            $applicant = Applicant::join('template_applicant','template_applicant.applicant_id','applicants.id')
            ->whereIn('applicants.id',$applicantId)
            ->whereNotIn("template_applicant.status",[config('THREE'),config('FOUR')]); #cannot delete if status is success or processing
            $applicant->delete();
            $applicantForLocalDelete= $applicant->get(); #delete data from cloud storage
            foreach ($applicantForLocalDelete as $applicant){
                $this->deleteObject($applicant->link);
            }
            DB::commit();
            return [true , 1];
        }catch(\Exception $e) {
            Log::channel('debuglog')->debug($e->getMessage() . ' in file ' .__FILE__. ' at line ' .__LINE__. ' within the class ' . get_class());
            DB::rollback();
            return [false , 'exception_error'];
        }
    }

    /**
     * get levels with heading type
     * @author Thu Rein Lynn
     * @param Array $templateId,$headingId
     * @return AssociativeArray
     * @create 22/06/2022
     */
        public function getLevelsWithHeadingType($templateId,$headingId)
    {
        try{
            DB::beginTransaction();
            $templateHeadingSubheading = DB::table('template_heading_subheading')->where('template_heading_subheading.template_id', $templateId)
            ->where('template_heading_subheading.heading_id', $headingId)
            ->whereNull('template_heading_subheading.deleted_at')
            ->join('templates','templates.id', '=','template_heading_subheading.template_id') #join id from templates table with template_id from template_heading_subheading table
            ->join('headings','headings.id', '=','template_heading_subheading.heading_id') #join id form headings table with heading_id from template_heading_subheading table
            ->join('subheadings','subheadings.id', '=','template_heading_subheading.subheading_id') #join id from subheadings table with subheading_id from template_heading_subheading table
            ->select('subheadings.name as subheading_name',
                     'subheadings.id as subheading_id') #select subheading datas that is related with template_id and heading_id
            ->get();
            $templateHeadingLevel = TemplateHeadingLevel::where('template_heading_level.template_id',$templateId)
            ->where('template_heading_level.heading_id',$headingId)
            ->whereNull('template_heading_level.deleted_at')
            ->join('levels','levels.id', '=', 'template_heading_level.level_id') #join id from levels table with level_id from template_heading_level table
            ->select('levels.id as level_id','levels.level as level') #select level datas
            ->get();
            $data['sub_headings'] = $templateHeadingSubheading;
            $data['levels'] = $templateHeadingLevel;
                return[
                        'status' => 'OK',
                        'data' => $data
                    ];
            DB::commit();
        }catch (\Exception $e) {
            Log::channel('debuglog')->debug($e->getMessage() . ' in file ' . __FILE__ . ' at line ' . __LINE__ . ' within the class ' . get_class());
            DB::rollback();
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'));
            }
    }
}
