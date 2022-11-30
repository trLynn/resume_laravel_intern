<?php

namespace App\Http\Controllers\API\Applicant;

use App\Classes\MessageHelperUtil;
use App\DBTransactions\ApplicantInfo\SaveApplicantInfo as ApplicantInfoSaveApplicantInfo;
use App\DBTransactions\ApplicantInfo\UpdateApplicantInfo;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplicantInfoSaveRequest;
use App\Http\Requests\ApplicantInfoUpdateRequest;
use App\Models\TemplateApplicant;
use App\Traits\DownloadHeaderTrait;
use App\Traits\GoogleCloudStorageTrait;
use App\Traits\LogTrait;
use App\Logic\ApplicantInfoSaveLogic;
use App\Logic\ApplicantInfoUpdateLogic;
use Illuminate\Support\Facades\Storage;
use App\Models\Applicant;

/**
 * Applicant Create Controller after that will commit into ApplicantController.
 * @author Thu Rein Lynn
 * @created_at 22/06/2022
 */
class ApplicantCreateController extends Controller
{
    use GoogleCloudStorageTrait;
    use DownloadHeaderTrait, LogTrait;

    private MessageHelperUtil $msgHelpUtil;
    private ApplicantInfoSaveLogic $logicSave;
    private ApplicantInfoUpdateLogic $logicUpdate;
    /**
     * Applicant Create Controller after that will commit into ApplicantController.
     * @author kyaw zin htet
     * @created_at 22/06/2022
     */
    function __construct()
    {
        $this->msgHelpUtil = new MessageHelperUtil();
    }
    /**
     * Applicant Create Controller after that will commit into ApplicantController.
     * @author Thu Rein Lynn
     * @created_at 22/06/2022
     * @param ApplicantInfoSaveRequest $request
     * @return Response response
     */
    function create(ApplicantInfoSaveRequest $request)
    {
        $data = [];
        if (!empty($request->applicant_email)) { # applciant_email is not empty
            $applicant = TemplateApplicant::join('applicants', 'applicants.id', "template_applicant.applicant_id")->where("applicants.email", trim($request->applicant_email))->where("template_applicant.template_id", $request->template_id)->first();
            if (!empty($applicant)) { # applicant exists returns already filled error
                return $this->msgHelpUtil->errorMessage('errorMessage.SE131', config('HTTP_CODE_200'));
            }
        }
        $this->logicSave = new ApplicantInfoSaveLogic($request);
        $bool = $this->logicSave->logicProcess();
        if (is_array($bool)) { # if the validation returns the array error message
            if ($bool['status'] === false && isset($bool['errorMessage'])) {
                return $this->msgHelpUtil->errorMessage($bool['errorMessage'], config('HTTP_CODE_200'));
            }
            if ($bool['status'] === false && isset($bool['heading_name'])) { # if the status is flase and heading_name is set
                return $this->msgHelpUtil->errorMessage('errorMessage.SE118', config("HTTP_CODE_200"), $bool['heading_name']);
            }
            elseif ($bool['status'] === false) {
                return $this->msgHelpUtil->errorMessage('errorMessage.SE115', config('HTTP_CODE_200'));
            }
            elseif ($bool['status'] === true) {
                $data = $bool['data'];
            }
        }
        elseif (!$bool) { # if the validation is failed
            return $this->msgHelpUtil->errorMessage('errorMessage.SE113', config('HTTP_CODE_200'));
        }
        $save = new ApplicantInfoSaveApplicantInfo($data);
        $bool = $save->executeProcess();
        if (!$bool) {
            Applicant::where('id', $data['applicant'])->forceDelete();
            $disk = Storage::disk('public');
            if (!empty($data['dataPicture'])) {
                foreach ($data['dataPicture'] as $picture) { # failed and dataPicture exists
                    $this->deleteObject($picture);
                }
            }
            if ($data['tobedeleted'] != "") { # folder Name and tobedeleted
                $this->deleteObject($data['folderName'] . $data['tobedeleted']);
                $disk->delete($data['tobedeleted']);
            }
            return $this->msgHelpUtil->errorMessage('errorMessage.SE010', config('HTTP_CODE_200'));
        }
        $description = "Inserted Applicant Form Data";
        $login_id = "0001";
        $this->writeCRUDLog($login_id, $description, "Applicant Info", config('SAVE'));
        if ($request->checked) { # if user check the download
            $templateApplicant = TemplateApplicant::join('applicants', 'applicants.id', "template_applicant.applicant_id")->where("applicants.email", $request->applicant_email)->where("template_applicant.template_id", $request->template_id)->first();
            if ($templateApplicant) { # applicant filled the template
                $tmp = $this->downloadMultipleObjects([['attach_files' => $templateApplicant->applicant_template_link]]);
                $header = $this->getDownloadHeader($tmp);
                return response()->download(storage_path() . "/app/" . $tmp, $tmp, $header)->deleteFileAfterSend(true);
            }
            return $this->msgHelpUtil->errorMessage('errorMessage.SE114', config('HTTP_CODE_200'));
        }
        return $this->msgHelpUtil->successMessage('successMessage.SS113', config("HTTP_CODE_200"));
    }
    /**s
     * Applicant Create Controller after that will commit into ApplicantController.
     * @author Thu Rein Lynn
     * @created_at 22/06/2022
     * @param ApplicantInfoUpdateRequest $request
     * @return Response response
     */
    function update(ApplicantInfoUpdateRequest $request)
    {
        $data = [];
        $applicant = TemplateApplicant::where("template_applicant.applicant_id", $request->applicant_id)->where("template_applicant.template_id", $request->template_id)->first();
        if (!empty($applicant)) {
            if ($applicant->status != config("ONE")) { # applicant exists returns already filled error
                return $this->msgHelpUtil->errorMessage('errorMessage.SE121', config('HTTP_CODE_200'));
            }
        }
        else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE120', config('HTTP_CODE_200'));
        }
        $this->logicUpdate = new ApplicantInfoUpdateLogic(null, $request);
        $bool = $this->logicUpdate->logicProcess();
        if (is_array($bool)) { # if the validation returns the array error message
            if ($bool['status'] === false && isset($bool['errorMessage'])) {
                return $this->msgHelpUtil->errorMessage($bool['errorMessage'], config('HTTP_CODE_200'));
            }
            if ($bool['status'] === false && isset($bool['heading_name'])) { # if the status is flase and heading_name is set
                return $this->msgHelpUtil->errorMessage('errorMessage.SE118', config("HTTP_CODE_200"), $bool['heading_name']);
            }
            elseif ($bool['status'] === false) {
                return $this->msgHelpUtil->errorMessage('errorMessage.SE115', config('HTTP_CODE_200'));
            }
            elseif ($bool['status'] === true) {
                $data = $bool['data'];
            }
        }
        //check process is ok.
        if (!$bool) { # if the validation is failed
            return $this->msgHelpUtil->errorMessage('errorMessage.SE113', config('HTTP_CODE_200'));
        }
        $save = new UpdateApplicantInfo($data);
        $bool = $save->executeProcess();
        if (!$bool) { # check the update failed
            $disk = Storage::disk('public');
            if (!empty($data['dataPicture'])) {
                foreach ($data['dataPicture'] as $picture) { # failed and dataPicture exists
                    $this->deleteObject($picture);
                }
            }
            if ($data['tobedeleted'] != "") { # tobedeleted exists
                $this->deleteObject($data['folderName'] . $data['tobedeleted']);
                $disk->delete($data['tobedeleted']);
            }
            return $this->msgHelpUtil->errorMessage('errorMessage.SE017', config('HTTP_CODE_200'));
        }
        $description = "Updated Applicant Form Data";
        $login_id = "0001";
        $this->writeCRUDLog($login_id, $description, "applicant Info", config('UPDATE'));
        if ($request->checked) { # if user check the download
            $templateApplicant = TemplateApplicant::join('applicants', 'applicants.id', "template_applicant.applicant_id")->where("applicants.id", $request->applicant_id)->where("template_applicant.template_id", $request->template_id)->first();
            if ($templateApplicant) { # applicant filled the template
                $tmp = $this->downloadMultipleObjects([['attach_files' => $templateApplicant->applicant_template_link]]);
                $header = $this->getDownloadHeader($tmp);
                return response()->download(storage_path() . "/app/" . $tmp, $tmp, $header)->deleteFileAfterSend(true);
            }
            return $this->msgHelpUtil->errorMessage('errorMessage.SE114', config('HTTP_CODE_200'));
        }

        return $this->msgHelpUtil->successMessage('successMessage.SS002', config('HTTP_CODE_200'));
    }
}
