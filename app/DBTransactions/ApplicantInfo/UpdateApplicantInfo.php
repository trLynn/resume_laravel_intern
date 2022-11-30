<?php

namespace App\DBTransactions\ApplicantInfo;

use App\Models\ApplicantInfo;
use App\Models\ApplicantLink;
use App\Classes\DBTransaction;
use App\Http\Requests\ApplicantInfoUpdateRequest;
use App\Models\Subheading;
use App\Models\Template;
use App\Models\TemplateApplicant;
use App\Traits\ApplicantMapperTrait;
use App\Traits\GoogleCloudStorageTrait;
use App\Traits\PdfFormatGeneratorTrait;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Rabbit;
use Intervention\Image\Facades\Image;


/**
 * To save new department in `departments` table
 *
 * @author  kyaw zin htet
 * @create  2022/06/06
 */
class UpdateApplicantInfo extends DBTransaction
{
    use GoogleCloudStorageTrait, ApplicantMapperTrait;
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
     * Save Department
     *
     * @author  kyaw zin htet
     * @create  2022/06/06
     * @return  array
     */
    public function process()
    {
        $tobedeleted = $this->request['tobedeleted'];
        $templateId = request()->only('template_id')['template_id'];
        $applicantID = request()->only('applicant_id')['applicant_id'];
        $data = $this->request['data'];
        $arr = $this->request['arr'];
        $dataPicture = $this->request['dataPicture'];
        $now = $this->request['now'];
        $folderName = $this->request['folderName'];
        $oldArray = $this->request['oldArray'];
        # check for old photos
        if (!empty($oldArray)) { # check for old photos
            $link = ApplicantLink::whereNotIn('link', $oldArray)->where('applicant_id', $applicantID)->get();
            ApplicantLink::whereNotIn('link', $oldArray)->where('applicant_id', $applicantID)->delete();
            if ($link->isNotEmpty()) {
                foreach ($link as $i) { # loop over the all links
                    $this->deleteObject($i->link);
                }
            }
        }
        else {
            $link = ApplicantLink::where('applicant_id', $applicantID)->get();
            ApplicantLink::where('applicant_id', $applicantID)->delete();
            if ($link->isNotEmpty()) {
                foreach ($link as $i) { # loop over the all links
                    $this->deleteObject($i->link);
                }
            }
        }
        // if (!empty($arr)) { #check new items exists.
        //     ApplicantInfo::insert($arr);
        //     $applicantInfoIds = ApplicantInfo::where('created_at', $now)->get()->pluck('id');
        //     $applicantApplicantInfos = [];
        //     for ($i = 0; $i < count($applicantInfoIds); $i++) {
        //         $o = [
        //             'template_id' => $templateId,
        //             'applicant_id' => $applicantID,
        //             'applicant_info_id' => $applicantInfoIds[$i]
        //         ];
        //         array_push($applicantApplicantInfos, $o);
        //     }
        //     DB::table('template_applicant_applicant_info')->insert($applicantApplicantInfos);
        // }
        if (!empty($dataPicture)) # check for new photos
            ApplicantLink::insert($dataPicture);
        $public = Storage::disk('public');

        $fileName = '';

        $template = Template::where('id', $templateId)->where('deleted_at', null)->first();

        $fileName = $this->generate($template->layout_id, $data, empty($tobedeleted) ? null : $tobedeleted);
        $public = Storage::disk('public');
        $public->delete($tobedeleted);
        $bool=$this->uploadObject(file_get_contents(storage_path("app/" . $fileName)), $folderName . $fileName);
        if(!$bool){
            throw new \Exception("Connection Error");
        }
        unlink(storage_path("app/" . $fileName));
        $oldpdf = TemplateApplicant::where('applicant_id', $applicantID)->where('template_id', $templateId)->first();
        $this->deleteObject($oldpdf->applicant_template_link);
        TemplateApplicant::where('applicant_id', $applicantID)->where('template_id', $templateId)->update([
            'applicant_template_link' => $folderName . $fileName
        ]);
        $applicant = TemplateApplicant::where("template_applicant.applicant_id", $applicantID)->where("template_applicant.template_id", $templateId)->first();
        if (empty($template) && $applicant->status != config("ONE")) {
            if (!empty($dataPicture)) {
                foreach ($dataPicture as $i) {
                    $this->deleteObject($i['link']);
                }
            }
            $this->deleteObject($folderName . $fileName);
            return ['status' => false, 'error' => "Template Does Not Exists"];
        }
        return ['status' => true, 'error' => ""];
    }
}
