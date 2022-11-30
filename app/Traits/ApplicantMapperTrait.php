<?php
namespace App\Traits;
use Illuminate\Support\Facades\Storage;
use App\Models\ApplicantInfo;
use Illuminate\Support\Facades\DB;
use Rabbit;
use App\Models\Subheading;
use App\Models\TemplateHeadingSubheading;
use Intervention\Image\Facades\Image;
use App\Models\TemplateHeadingLevel;

trait ApplicantMapperTrait
{
    use GoogleCloudStorageTrait;
    /**
     * This create Avater Savable Format associatve array.
     * @author kyaw zin htet
     * @return array|boolean
     * @created_at 22/6/2022
     */
    private function generateAvaterLinkSaveableFormatArray(array $data, string $folderName, int $applicantId, int $headingId, $now)
    {
        $disk = Storage::disk("public");
        $encodedImgString = base64_decode(explode(',', $data['value'], 2)[1]);
        $extension = explode('/', mime_content_type($data['value']))[1];
        $fileName = "IMG" . date("Ymdhis") . "." . 'png';
        if (!in_array($extension, ["jpg", "png", "jpeg"])) { # check required type block and
            return ["status"=>false,"errorMessage"=>"Extension error"];
        }
        $image = Image::make($encodedImgString)->resize(170, 170)->sharpen();
        $image->save(storage_path() . "/app/public/" . $fileName, 100, 'png');
        $name = $fileName;
        $k = $disk->get($name);
        $bytes=$disk->size($fileName);
        $megaBytes=number_format($bytes / 1048576, 2);
        if($megaBytes>config('MAX_IMAGE_SIZE')){
            return ["status"=>false,"errorMessage"=>"File Size Error"];
        }
        $this->uploadObject($k, $folderName . $name);
        $applicantLink = [
            'applicant_id' => $applicantId,
            "link" => $folderName . $name,
            "type" => 1
        ];
        $applicantInfo = [
            'heading_id' => $headingId,
            'applicant_info' => $folderName . $name,
            'created_at' => $now,
            'level_id' => null
        ];
        return ["status"=>true,"applicant_link" => $applicantLink, "tobedeleted" => $name, 'applicant_info' => $applicantInfo];
    }
    /**
     * This create Attachment Savable Format associatve array.
     * @author kyaw zin htet
     * @return array|boolean
     * @created_at 22/6/2022
     */
    private function generateAttachmentLinkSaveableFormatArray($data, string $folderName, int $applicantId, int $headingId, $now)
    {
        $disk = Storage::disk("public");
        $encodedImgString = base64_decode(explode(',', $data['data'], 2)[1]);
        $extension = mime_content_type($data['data']);
        $fileName = $data['name']; #fix soon in docs in new version.
        if (!in_array($extension, ["application/vnd.ms-excel.sheet.macroenabled.12","application/vnd.ms-excel","application/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application/vnd.openxmlformats-officedocument.spreadsheetml.template","application/pdf"])) {
            return ["status"=>false,"errorMessage"=>"Extension error"];
        }
        $name = $disk->put("$fileName", $encodedImgString);
        $name = $fileName;
        $bytes=$disk->size($fileName);
        $megaBytes=number_format($bytes / 1048576, 2);
        if($megaBytes>config('MAX_ATTACHMENT_SIZE')){
            return ["status"=>false,"errorMessage"=>"File Size Error"];
        }
        $k = $disk->get($name);
        $this->uploadObject($k, $folderName . $name);
        $disk->delete($name);
        $applicantLink = [
            'applicant_id' => $applicantId,
            "link" => $folderName . $name,
            "type" => 2
        ];
        $applicantInfo = [
            'heading_id' => $headingId,
            'applicant_info' => $folderName . $name,
            'created_at' => $now,
            'level_id' => null
        ];
        return ["status"=> true,'applicant_link' => $applicantLink, 'applicant_info' => $applicantInfo];
    }
    /**
     * This function generate the savable formatted Array for single and multiple choices.
     * @author kyaw zin htet
     * @create_at 2020/6/28
     * @param array $obj (This is array obj array from request)
     * @param string $now (This is datetime created at now)
     * @param int  $heading (This is heading_id on the request)
     * @param ?bool array (if it is array true, or not false)
     * @return array|bool
     */
    private function getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $heading = null,$templateId=null, bool $array = false)
    {
        if ($array) { # check is array is true
            if(!empty(TemplateHeadingSubheading::where('subheading_id',$obj['subheading_id'])->where('template_id',$templateId)->where('heading_id',$heading)->first())){
                $sub = Subheading::where("id", $obj['subheading_id'])->first();
                if (isset($obj['level_id'])) { #check multichoice is checked.
                    if(!empty(TemplateHeadingLevel::where("heading_id",$heading)->where('template_id',$templateId)->where('level_id',$obj['level_id'])->first())){
                        $o = [
                            'heading_id' => $heading,
                            'applicant_info' => $sub->name,
                            'created_at' => $now,
                            'level_id' => $obj['level_id']
                        ];
                        return ["o" => $o, "subName" => Rabbit::uni2zg($sub->name)];
                    }
                    else{
                        return false;
                    }
                }
                else {
                    $o = [
                        'heading_id' => $heading,
                        'applicant_info' => $sub->name,
                        'created_at' => $now,
                        'level_id' => null
                    ];
                    return ["o" => $o, "subName" => Rabbit::uni2zg($sub->name)];
                }
            }
            else{
                return false;
            }
        }
        else {
            if(!empty(TemplateHeadingSubheading::join('subheadings','subheadings.id','template_heading_subheading.subheading_id')->where("subheadings.name",$obj["value"])->where('template_id',$templateId)->where('heading_id',$heading)->first())){
                $o = [
                    'heading_id' => $obj['heading_id'],
                    'applicant_info' => $obj['value'],
                    'created_at' => $now,
                    'level_id' => null
                ];
                return ["o" => $o, "subName" => Rabbit::uni2zg($obj['value'])];
            }
            else{
                return false;
            }
        }
    }
}
?>
