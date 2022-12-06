<?php

namespace App\Http\Controllers\API\Applicant;

use App\Models\Template;
use App\Traits\LogTrait;
use App\Traits\MailTrait;
use Illuminate\Http\Request;
use App\Exports\ApplicantsExport;
use App\Models\TemplateApplicant;
use App\Classes\MessageHelperUtil;
use Illuminate\Support\Facades\DB;
use App\Traits\DownloadHeaderTrait;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Traits\ApplicantSearchTrait;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\ApplicantRequest;
use App\Traits\GoogleCloudStorageTrait;
use App\Http\Requests\Applicant\ApplicantOtpRequest;
use App\Http\Requests\Applicant\ApplicantEmailRequest;
use App\Interfaces\Applicant\ApplicantRepositoryInterface;

    class ApplicantController extends Controller
{
    use GoogleCloudStorageTrait, LogTrait;
     /**
     * Create constructor.
     * @create [21.6.2022]
     * @author Thu Rein Lynn
     */
    private ApplicantRepositoryInterface $applicantRepository;
    use LogTrait, MailTrait , ApplicantSearchTrait;

    public function __construct(ApplicantRepositoryInterface $applicantRepository)
    {
        $this->applicantRepository = $applicantRepository;
        $this->msgHelpUtil = new MessageHelperUtil;
    }

     /**
     * Get all headings with template_id
     * @author Thu Rein Lynn
     * @param  integer $templateId
     * @return Array
     * @create 22/06/2022
     **/
    public function showAllHeadings($template_id)
    {
        $result = $this->applicantRepository->getHeadingswithTemplateId($template_id);
        if($result){
            return response()->json([
                'status' => 'OK',
                'data' => $result
            ],config('HTTP_CODE_200'));
        }else{
            return $this->msgHelpUtil->errorMessage('errorMessage.SE001',config('HTTP_CODE_200'));
        }
    }

    /**
     * Status change
     * @author Thu Rein Lynn
     * @param Request $requset,$status
     * @return AssociativeArray
     * @create 20/06/2022
     */
    public function update(Request $request,$status)
    {
        #status cannot be changed more than 5states
        if($status > 5){
            return $this->msgHelpUtil->errorMessage('errorMessage.SE015',config('HTTP_CODE_200'));
        }
        $result = $this->applicantRepository->update($status,$request->applicant_id);
        // dd($result);
        if($result[config('ZERO')]){
            $description = "Update applicant status";
            $form = "Applicant List";
            $this->writeCRUDLog($request->login_id, $description, $form, config("UPDATE"));
            $message=  null; #return success messages for each status changing
            if($result[config('ONE')] == config('TWO')){
                $message = 'Change status to REJECT Successfully !';
            }elseif($result[config('ONE')] == config('THREE')){
                $message = 'Change status to PROCESSING Successfully !';
            }elseif ($result[config('ONE')] == config('FOUR')) {
                $message = 'Change status to PASSED Successfully !';
            }else{
                $message = 'Change status to FAIL Successfully !';
            }
            return response()->json(['status'=>'OK','message' =>$message]);
            // return $this->msgHelpUtil->successMessage('successMessage.SS030',config('HTTP_CODE_200'));
        }else{
            $error_message_code = null; #return error messages for each status changing
            if($result[config('ONE')] == 'change_reject_error'){
                $error_message_code = 'SE031';
            }elseif($result[config('ONE')] == 'change_processing_error'){
                $error_message_code = 'SE032';
            }elseif($result[config('ONE')] == 'change_success_error'){
                $error_message_code = 'SE033';
            }elseif($result[config('ONE')] == 'change_fail_error'){
                $error_message_code = 'SE034';
            }elseif($result[config('ONE')] == 'change_deleted_at'){
                $error_message_code = 'SE018';
            }else{
                $error_message_code = 'SE012';
            }
            return $this->msgHelpUtil->errorMessage("errorMessage.$error_message_code",config('HTTP_CODE_200'));
        }
    }
    /**
     * Applicant delete
     * @author Thu Rein Lynn
     * @param Request $request
     * @return AssociativeArray
     * @create 20/06/2022
     */
    public function delete(Request $request)
    {
        $result = $this->applicantRepository->delete($request->applicant_id);
        if($result[config('ZERO')]){
            $description = "Delete applicant data";
            $form = "Applicant List";
            $this->writeCRUDLog($request->login_id, $description, $form, config("DELETE"));
            return $this->msgHelpUtil->successMessage('successMessage.SS003',config('HTTP_CODE_200'));
        }else{
            $error_message_code =null;
            if($result[config('ONE')] == "already_deleted"){ #return message for applicant that is already deleted
                $error_message_code = 'SE018';
            }elseif($result[config('ONE')] == "status_denied"){ #return messages for status that are SUCCESS and PROCESSING
                $error_message_code = 'SE016';
            }else{
                $error_message_code = 'SE039';
            }
            return $this->msgHelpUtil->errorMessage("errorMessage.$error_message_code",config('HTTP_CODE_200'));
        }
    }
        /**
     * get levels with heading type
     * @author Thu Rein Lynn
     * @param  Request $request
     * @return AssociativeArray
     * @create 22/06/2022
     */
    public function showLevels(Request $request)
    {
        $result = $this->applicantRepository->getLevelsWithHeadingType($request->template_id,$request->heading_id);
        return $result;
    }

    /**
     * Send OTP Code with Email
     *
     * @author Thu Rein Lynn
     * @create  20/06/2022
     * @param  Request  $request
     * @return Response object
    */
    public function sendEmailToUser(ApplicantEmailRequest $request)
    {
        $otp=$this->applicantRepository->sendEmail($request);
        if($otp['status'] === "NoTemplate"){ #Template Exits or not
            return $this->msgHelpUtil->errorMessage('errorMessage.SE020',config('HTTP_CODE_200'));
        }else{
            if($otp['status']){ #Email send or not
                return $this->msgHelpUtil->successMessage('successMessage.SS004',config('HTTP_CODE_200'),'Mail');
            }else{
                return response()->json(["status" => "NG", "message" => $otp['data']], config('HTTP_CODE_200'));
            }
        }
    }

    /**
     * Check OTP Code
     *
     * @author Thu Rein Lynn
     * @create  20/06/2022
     * @param  Request  $request
     * @return Response object
    */
    public function checkOtp(ApplicantOtpRequest $request)
    {
        $otp=$this->applicantRepository->applicantCheckOtp($request);
        if($otp['status']){ #valid passcode or not
            return $this->msgHelpUtil->successMessage('successMessage.SS013',config('HTTP_CODE_200'));
        }else{
            return $this->msgHelpUtil->errorMessage('errorMessage.'.$otp['message'],config('HTTP_CODE_200'));
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
    public function formLoad()
    {
        $applicant=$this->applicantRepository->applicantFormLoad();
        if($applicant['status']){ #return applicant data
            return response()->json(['status'=>'OK','data'=>$applicant['data']],200);
        }else{
            return $this->msgHelpUtil->errorMessage('errorMessage.SE020',config('HTTP_CODE_200'));
        }
    }

    /**
     * Function to show an applicant resume pdf.
     * @create [21.6.2022]
     * @author Thu Rein Lynn
     * @param  $applicant_id
     * @return \Illuminate\Http\Response
     */
    public function view($applicant_id)
    {
        $result = $this->applicantRepository->applicantView($applicant_id);
        if(!$result){
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'));
        } else {
           return response()->json([
            'status' => 'OK',
            'data'  => $result,
           ],config('HTTP_CODE_200'));
        }
    }

    /**
     * Function to download applicant files.
     * @create [21.6.2022]
     * @author Thu Rein Lynn
     * @param  $applicant_id
     * @return mixed
     */
    public function download(Request $request,$applicant_id)
	{
		$result = $this->applicantRepository->applicantDownload($applicant_id);

        if(!$result){
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'));
        } else {
            # write insertion log to crud_logs table
            $description = "Download applicant pdf or zip file.";
            $form = "Applicant List";
            $this->writeCRUDLog($request->login_id, $description, $form, config('DOWNLOAD'));
            return $result;
        }
	}

     /**
     *  Applicant search
     * @author  Thu Rein Lynn
     * @param   Request $request
     * @return  Response array
     * @create  21/06/2022
     */
    public function search(Request $request)
    {
        #check applicants exist
        $message = $this->checkApplicantsExist($request);
        if($message != 'applicants_exist'){
            return $message;
        }
        #search
        $applicants = $this->applicantRepository->applicantSearch($request,true);
        if($applicants == false){#no search data
                return $this->msgHelpUtil->errorMessage('errorMessage.SE116',config('HTTP_CODE_200')); 
        }
        return response()->json([
                'status' => 'OK',
                'data'=>  $applicants
            ],config('HTTP_CODE_200'));
    }

    /**
     *  Applicant list excel download
     * @author  Thu Rein Lynn
     * @param   Request $request
     * @return  Response object
     * @create  22/06/2022
     */
    public function excelDownload(Request $request)
    {
        #check applicants exist
        $message = $this->checkApplicantsExist($request);
        if($message != 'applicants_exist'){
            return $message;
        }
        
        DB::beginTransaction();
          try {
            # write excel download log to crud_logs table
            $description = "Excel download for applicants lists.";
            $form = "After Overtime Request List";
            $this->writeCRUDLog($request->login_id, $description, $form, config('DOWNLOAD'));
            $excel = Excel::download(new ApplicantsExport($this->applicantRepository,$request),'Applicantslist.xlsx');
            DB::commit();
            return $excel;
          } catch (\Exception $e) {# no data
            Log::debug($e->getMessage());
            DB::rollBack();
            return $this->msgHelpUtil->errorMessage('errorMessage.SE117',config('HTTP_CODE_200'));
          }
    }
}
