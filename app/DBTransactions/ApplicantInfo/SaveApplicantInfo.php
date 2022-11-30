<?php

namespace App\DBTransactions\ApplicantInfo;


use App\Models\ApplicantInfo;
use App\Models\ApplicantLink;
use App\Classes\DBTransaction;
use App\Http\Requests\ApplicantInfoSaveRequest;
use App\Models\Applicant;
use App\Models\Template;
use App\Models\TemplateApplicant;
use App\Traits\GoogleCloudStorageTrait;
use App\Traits\PdfFormatGeneratorTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApplicantMapperTrait;
use Exception;

/**
 * To delete department in `Applciant` table
 *
 * @author  Kyaw Zin Htet
 * @create  2022/06/06
 */
class SaveApplicantInfo extends DBTransaction
{
    use GoogleCloudStorageTrait,ApplicantMapperTrait;
    use PdfFormatGeneratorTrait;
    private array $request;

    /**
     * Constructor to assign interface to variable
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * Delete Department
     *
     * @author  kyaw zin htet
     * @create  2022/06/06
     * @return  array
     */
    public function process()
    {
        $templateId = request()->only('template_id')['template_id'];
        $applicantId = $this->request['applicant'];
        $data=$this->request['data'];
        $now = $this->request['now'];
        $arr = $this->request['arr'];
        $tobedeleted = $this->request['tobedeleted'];
        $dataPicture = $this->request['dataPicture'];
        $folderName = $this->request['folderName'];

        ApplicantInfo::insert($arr);
        $applicantInfoIds = ApplicantInfo::where('created_at', $now)->get()->pluck('id');
        $applicantApplicantInfos = [];
        for ($i = 0; $i < count($applicantInfoIds); $i++) { # shape the templicantapplicantInfos saveable format
            $o = [
                'template_id' => $templateId,
                'applicant_id' => $applicantId,
                'applicant_info_id' => $applicantInfoIds[$i]
            ];
            array_push($applicantApplicantInfos, $o);
        }
        DB::table('template_applicant_applicant_info')->insert($applicantApplicantInfos);

        ApplicantLink::insert($dataPicture);
        $template = Template::where('id', $templateId)->first();
        $fileName = $this->generate($template->layout_id, $data, empty($tobedeleted) ? null : $tobedeleted);
        $public = Storage::disk('public');
        $public->delete($tobedeleted);
        $bool=$this->uploadObject(file_get_contents(storage_path("app/" . $fileName)), $folderName . $fileName);
        if(!$bool){
            throw new Exception("Upload Fail");
        }
        unlink(storage_path("app/" . $fileName));
        if (!empty(request()->applicant_email)) { # applciant_email is not empty
            $applicant = TemplateApplicant::join('applicants', 'applicants.id', "template_applicant.applicant_id")->where("applicants.email", trim(request()->applicant_email))->where("template_applicant.template_id", request()->template_id)->first();
            if(!empty($applicant)) { # applicant exists returns already filled error
                return ['status' => false, 'error' => "Already Filled"];
            }
        }
        TemplateApplicant::insert([
            'applicant_id' => $applicantId,
            'template_id' => $templateId,
            'created_at' => now(),
            "applicant_template_link" => $folderName . $fileName
        ]);
        if (empty(Template::where("id", $templateId)->where('deleted_at', null)->first())) {
            return ['status' => false, 'error' => "Template Does Not Exists"];
        }
        return ['status' => true];
    }
}
