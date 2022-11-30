<?php

namespace App\Logic;

use App\Http\Requests\ApplicantInfoSaveRequest;
use App\Http\Requests\ApplicantInfoUpdateRequest;
use App\Models\Applicant;
use App\Models\TemplateHeading;
use App\Models\Template;
use App\Traits\ApplicantMapperTrait;
use App\Models\ApplicantInfo;
use App\Models\ApplicantLink;
use App\Models\TemplateApplicant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rabbit;
use Illuminate\Support\Facades\Storage;


/**
 * This class will check the required fields are not null.
 * @author Thu Ta
 * @created_at 22/6/2022
 */
class ApplicantInfoUpdateLogic
{
    use ApplicantMapperTrait;
    /**
     * This is constructor of ApplicantInfoSaveLogic
     * @param ApplicantTemplateRequest $request
     * @author Thu Ta
     * @created_at 22/6/2022
     * @$request =  void
     */
    public function __construct(ApplicantInfoSaveRequest $request = null, ApplicantInfoUpdateRequest $request1 = null)
    {
        if (!empty($request))
            $this->request = $request;
        else
            $this->request = $request1;
    }
    private $request;
    /**
     * This is implementation for check value exists in required fields.
     * @author Thu Ta
     * @create_at 22/6/2022
     */
    public function logicProcess()
    {
        $tobedeleted = "";
        $templateId = $this->request->only('template_id')['template_id'];
        $applicantID = $this->request->only('applicant_id')['applicant_id'];
        $data = $this->request->only("fields")['fields'];
        $arr = [];
        $dataPicture = [];
        $now = now();
        $folderName = "1010/" . date('Y') . "/" . date("m") . "/" . date("H_i_sa") . '/' . $applicantID . '/';
        $oldArray = [];
        $stillInRange = true;
        $oldHeadingId = [];
        $template = Template::where('id', $templateId)->where('templates.active_flag', config("ONE"))->first();
        $request = ["status" => false];
        DB::beginTransaction();
        try {
            #shape the data into the loop.
            if (!empty($template)) {
                $heading = TemplateHeading::leftJoin('headings', 'template_heading.heading_id', 'headings.id')
                    ->leftJoin('heading_info', 'headings.heading_info_id', 'heading_info.id')
                    ->leftJoin('templates', 'templates.id', 'template_heading.template_id')
                    ->leftJoin('types', 'headings.type_id', 'types.id')
                    ->where('templates.active_flag', config("ONE"))
                    ->where('template_heading.template_id', $templateId)
                    ->select(['headings.id as heading_id', 'types.id as type_id', 'heading_info.name as heading_name'])
                    ->get();
                if ($heading->isNotEmpty()) {
                    if (count($data) == $heading->count()) {
                        for ($i = config("ZERO"); $i < count($data); $i++) {
                            if ($heading[$i]->heading_id == $data[$i]["heading_id"] && $heading[$i]->type_id == $data[$i]["type_id"] && $heading[$i]->heading_name == $data[$i]['heading_name'] && $template->name === $this->request->template_name) {
                                if ($data[$i]['type_id'] < config("SEVEN", 7) && isset($data[$i]['applicant_info_id'])) {
                                    if (!empty($data[$i]['applicant_info_id']) && isset($data[$i]['applicant_info_id'])) {
                                        if (empty($data[$i]['value']) && $data[$i]['required_flag'] == 1) {
                                            $request = false;
                                            throw new \Exception();
                                        }
                                        elseif (strlen($data[$i]['value']) > config('MAX_SINGLE_VALUE_TEXT_LENGTH') && $data[$i]['type_id'] != config("FIVE")) {
                                            $request = ['status' => false, 'errorMessage' => "errorMessage.SE129"];
                                            throw new \Exception();
                                        }
                                        elseif (strlen($data[$i]['value']) > config('MAX_COMMENT_LENGTH') && $data[$i]['type_id'] == config("FIVE")) {
                                            $request = ['status' => false, 'errorMessage' => "errorMessage.SE128"];
                                            throw new \Exception();
                                        }
                                        $object = ApplicantInfo::where('id', $data[$i]['applicant_info_id'])->first();
                                        if ($object) {
                                            if ($object->applicant_info != $data[$i]['value']) {
                                                if (strlen($data[$i]['value']) > config('MAX_SINGLE_VALUE_TEXT_LENGTH') && $data[$i]['type_id'] != config("FIVE")) {
                                                    $request = ['status' => false, 'errorMessage' => "errorMessage.SE129"];
                                                    throw new \Exception();
                                                }
                                                $object->applicant_info = empty($data[$i]['value']) ? "" : $data[$i]['value'];
                                                $data[$i]['value'] = empty($data[$i]['value']) ? "" : $data[$i]['value'];
                                                $object->save();
                                            }
                                        }
                                    }
                                    elseif ($data[$i]['type_id'] == config("TWO")) {
                                        $applicantIDs = ApplicantInfo::where('heading_id', $data[$i]['heading_id'])->join("template_applicant_applicant_info", "template_applicant_applicant_info.applicant_info_id", "applicant_infos.id")->where("template_applicant_applicant_info.applicant_id", $applicantID)->where("applicant_infos.heading_id", $data[$i]['heading_id'])->where("template_applicant_applicant_info.template_id", $templateId)->select('applicant_infos.id')->pluck('id');
                                        ApplicantInfo::whereIn('id', $applicantIDs)->delete();
                                        DB::table("template_applicant_applicant_info")->whereIn("template_applicant_applicant_info.applicant_info_id", $applicantIDs)->update(["deleted_at" => now()]);
                                        $local = 0;
                                        foreach ($data[$i]['value'] as $obj) {
                                            if ($obj['checked']) {
                                                if (!empty($obj['subheading_id']) && !empty($obj['level_id'])) {
                                                    $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $data[$i]['heading_id'], true);
                                                    if ($o) {
                                                        if (!empty($o['subName'])) {
                                                            $data[$i]['value'][$local]["subName"] = $o["subName"];
                                                        }
                                                        array_push($arr, $o['o']);
                                                    }
                                                }
                                                elseif (!empty($obj['subheading_id']) && !isset($obj['level_id'])) {
                                                    $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $data[$i]['heading_id'], true);
                                                    if ($o) {
                                                        if (!empty($o['subName'])) {
                                                            $data[$i]['value'][$local]["subName"] = $o["subName"];
                                                        }
                                                        array_push($arr, $o['o']);
                                                    }
                                                }
                                                $local++;
                                            }
                                        }
                                        if ($local == 0 && $data[$i]['required_flag'] == 1) {
                                            $request = false;
                                            throw new \Exception();
                                        }
                                        elseif ($local == 0) {
                                            $o = [
                                                'heading_id' => $data[$i]['heading_id'],
                                                'applicant_info' => "",
                                                'created_at' => $now,
                                                'level_id' => null
                                            ];
                                            $data[$i]['value'][$local]["subName"] = $o["applicant_info"];
                                            array_push($arr, $o);
                                            $local++;
                                        }
                                    }
                                }
                                elseif ($data[$i]['type_id'] == config("THREE", 3)) {
                                    $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($data[$i], $now, $data[$i]['heading_id'], $templateId);
                                    if (empty($data[$i]['value']) && $data[$i]['required_flag'] == 1) {
                                        $request = false;
                                        throw new \Exception();
                                    }
                                    elseif (!empty($data[$i]["value"])) {
                                        $data[$i]["subName"] = $o["subName"];
                                        array_push($arr, $o['o']);
                                        continue;
                                    }
                                    $data[$i]['value'] = "";
                                    array_push($arr, $o = [
                                        'heading_id' => $data[$i]['heading_id'],
                                        'applicant_info' => "",
                                        'created_at' => $now,
                                        'level_id' => null
                                    ]);
                                }
                                elseif ((int)$data[$i]['type_id'] < config("SEVEN") and isset($data[$i]['value']) && $data[$i]['type_id'] != config("TWO")) {
                                    if (empty($data[$i]['value']) && $data[$i]['required_field'] == 1) {
                                        $request = false;
                                        throw new \Exception();
                                    }
                                    elseif (strlen($data[$i]['value']) > config('MAX_SINGLE_VALUE_TEXT_LENGTH') && $data[$i]['type_id'] != config("FIVE")) {
                                        $request = ['status' => false, 'errorMessage' => "errorMessage.SE129"];
                                        throw new \Exception();
                                    }
                                    elseif (strlen($data[$i]['value']) > config('MAX_COMMENT_LENGTH') && $data[$i]['type_id'] == config("FIVE")) {
                                        $request = ['status' => false, 'errorMessage' => "errorMessage.SE128"];
                                        throw new \Exception();
                                    }

                                    $o = [
                                        'heading_id' => $data[$i]['heading_id'],
                                        'applicant_info' => empty($data[$i]['value']) ? "" : $data[$i]['value'],
                                        'created_at' => $now,
                                        'level_id' => null
                                    ];
                                    $data[$i]['value'] = empty($data[$i]['value']) ? "" : $data[$i]['value'];
                                    array_push($arr, $o);
                                }
                                elseif ($data[$i]['type_id'] == config("TWO")) { # if type_id is checkbox
                                    $applicantIDs = ApplicantInfo::where('heading_id', $data[$i]['heading_id'])->join("template_applicant_applicant_info", "template_applicant_applicant_info.applicant_info_id", "applicant_infos.id")->where("template_applicant_applicant_info.applicant_id", $applicantID)->where("applicant_infos.heading_id", $data[$i]['heading_id'])->where("template_applicant_applicant_info.template_id", $templateId)->select('applicant_infos.id')->pluck('id');
                                    ApplicantInfo::whereIn('id', $applicantIDs)->delete();
                                    DB::table("template_applicant_applicant_info")->whereIn("template_applicant_applicant_info.applicant_info_id", $applicantIDs)->update(["deleted_at" => now()]);
                                    $local = config("ZERO");
                                    $count = config("ZERO");
                                    foreach ($data[$i]['value'] as $obj) { # loop through the multiple choice
                                        if ($obj['check']) { # if user is check the checkbox
                                            if (!empty($obj['subheading_id']) && !empty($obj['level_id'])) {
                                                $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $data[$i]['heading_id'], $templateId, true);
                                                if (!empty($o)) {
                                                    if (!empty($o['subName'])) {
                                                        $data[$i]['value'][$local]["subName"] = $o["subName"];
                                                    }
                                                    array_push($arr, $o['o']);
                                                    $count++;
                                                }
                                            }
                                            elseif (!empty($obj['subheading_id']) && !isset($obj['level_id'])) { # $subheading_id and but level id is none
                                                $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $data[$i]['heading_id'], $templateId, true);
                                                if (!empty($o)) {
                                                    if (!empty($o['subName'])) {
                                                        $data[$i]['value'][$local]["subName"] = $o["subName"];
                                                    }
                                                    array_push($arr, $o['o']);
                                                    $count++;
                                                }
                                            }
                                        }
                                        $local++;
                                    }
                                    if ($count == 0 && $data[$i]['required_flag'] == 1) {
                                        $request = false;
                                        throw new \Exception();
                                    }
                                    if ($count == 0) { # not checked
                                        $o = [
                                            'heading_id' => $data[$i]['heading_id'],
                                            'applicant_info' => "",
                                            'created_at' => $now,
                                            'level_id' => null
                                        ];
                                        $data[$i]['value'][$local]["subName"] = $o["applicant_info"];
                                        array_push($arr, $o);
                                        $local++;
                                    }
                                }
                                elseif ($data[$i]['type_id'] == config("SEVEN") or $data[$i]['type_id'] == config("EIGHT")) { # if the profile_picture and  attachment files
                                    $applicantIDs = ApplicantInfo::where('heading_id', $data[$i]['heading_id'])->join("template_applicant_applicant_info", "template_applicant_applicant_info.applicant_info_id", "applicant_infos.id")->where("template_applicant_applicant_info.applicant_id", $applicantID)->where("applicant_infos.heading_id", $data[$i]['heading_id'])->where("template_applicant_applicant_info.template_id", $templateId)->select('applicant_infos.id')->pluck('id');
                                    ApplicantInfo::whereIn('id', $applicantIDs)->delete();
                                    DB::table("template_applicant_applicant_info")->whereIn("template_applicant_applicant_info.applicant_info_id", $applicantIDs)->update(["deleted_at" => now()]);
                                    $localArray = [];
                                    if (empty($data[$i]['value']) && $data[$i]['required_flag'] == 1) {
                                        $request = false;
                                        throw new \Exception();
                                    }
                                    if (isset($data[$i]['value'])) { # is value is set
                                        if (is_array($data[$i]['value'])) { # value is array
                                            if (count($data[$i]['value']) <= config("THIRTY")) {
                                                foreach ($data[$i]['value'] as $r) { # loop through the value
                                                    if (is_array($r)) { # if value is array
                                                        if (!empty($r['data'])) {
                                                            if (str_starts_with($r['data'], "data:")) {
                                                                $array = $this->generateAttachmentLinkSaveableFormatArray($r, $folderName, $applicantID, $data[$i]['heading_id'], $now);
                                                                if (strlen($r['name']) > config('MAX_ATTACHMENT_FILENAME_LENGTH')) {
                                                                    $request = ['status' => false, "heading_name" => $data[$i]['heading_name']];
                                                                    throw new \Exception();
                                                                }
                                                                if ($array['status'] == false && isset($array['errorMessage'])) {
                                                                    if ($array['errorMessage'] == 'File Size Error') {
                                                                        $request = ['status' => false, 'errorMessage' => "errorMessage.SE127"];
                                                                        throw new \Exception();
                                                                    }
                                                                    $request = ['status' => false, 'errorMessage' => "errorMessage.SE123"];
                                                                    throw new \Exception();
                                                                }
                                                                array_push($dataPicture, $array['applicant_link']);
                                                                array_push($arr, $array['applicant_info']);
                                                                array_push($localArray, Rabbit::uni2zg($array['applicant_link']['link']));
                                                            }
                                                            else {
                                                                array_push($oldArray, $r['data']);
                                                                array_push($oldHeadingId, $data[$i]['heading_id']);
                                                                $applicantLink = [
                                                                    'applicant_id' => $applicantID,
                                                                    "link" => $r['data'],
                                                                    "type" => 2
                                                                ];
                                                                $applicantInfo = [
                                                                    'heading_id' => $data[$i]['heading_id'],
                                                                    'applicant_info' => $r['data'],
                                                                    'created_at' => $now,
                                                                    'level_id' => null
                                                                ];
                                                                array_push($arr, $applicantInfo);
                                                                array_push($localArray, Rabbit::uni2zg($r['data']));
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            else {
                                                $request = ['status' => false, 'errorMessage' => "errorMessage.SE130"];
                                                throw new \Exception();
                                            }
                                            $data[$i]['value'] = $localArray;
                                        }
                                        elseif (!str_starts_with($data[$i]['value'], "data:")) { # if data is new
                                            $data[$i]["value"] = explode("https://storage.googleapis.com/intern01-testing/", $data[$i]['value'])[1];
                                            $data[$i]['value'] = explode("?", $data[$i]['value'])[0];
                                            $tmp = $this->downloadMultipleObjects([["attach_files" => $data[$i]['value']]]);
                                            $applicantInfo = [
                                                'heading_id' => $data[$i]['heading_id'],
                                                'applicant_info' => $data[$i]['value'],
                                                'created_at' => $now,
                                                'level_id' => null
                                            ];
                                            array_push($arr, $applicantInfo);
                                            array_push($oldArray, $data[$i]['value']);
                                            if (!Storage::exists("/app/public/$tmp"))
                                                Storage::move("$tmp", "public/$tmp");
                                            $tobedeleted = $tmp;
                                        }
                                        else { # if avater link is new
                                            $array = $this->generateAvaterLinkSaveableFormatArray($data[$i], $folderName, $applicantID, $data[$i]['heading_id'], $now);
                                            if ($array['status'] == false && isset($array['errorMessage'])) { # if the status is false and get errorMessage
                                                if ($array['errorMessage'] == 'File Size Error') {
                                                    $request = ['status' => false, 'errorMessage' => "errorMessage.SE132"];
                                                    throw new \Exception();
                                                }
                                                $request = ['status' => false, 'errorMessage' => "errorMessage.SE122"];
                                                throw new \Exception();
                                            }
                                            array_push($dataPicture, $array["applicant_link"]);
                                            array_push($arr, $array['applicant_info']);
                                            $data[$i]['value'] = $array['applicant_link']['link'];
                                            $tobedeleted = $array["tobedeleted"];
                                            $data[$i]['value'] = $array['applicant_link']['link'];
                                        }
                                    }
                                }
                            }
                            else { # if user something wrong
                                $request = ["status" => false, "error" => "Please Refresh Again"];
                                throw new \Exception();
                            }
                        }
                    }
                    else { # if user add something
                        $request = ['status' => false, "error" => "Please Refresh Again"];
                        throw new \Exception();
                    }
                }
                else { # if template does not exists
                    $request = ['status' => false, 'errorMessage' => "errorMessage.SE119"];
                    throw new \Exception();
                }
            }
            else {
                $request = ['status' => false, 'errorMessage' => "errorMessage.SE119"];
                throw new \Exception();
            }
            $applicant = TemplateApplicant::where("template_applicant.applicant_id", $this->request->applicant_id)->where("template_applicant.template_id", $this->request->template_id)->first();
            if (!empty($applicant)) {
                if ($applicant->status != config("ONE")) { # applicant exists returns already filled error
                    $request = ['status' => false, 'errorMessage' => "errorMessage.SE121"];
                    throw new \Exception();
                }
            }
            else {
                $request = ['status' => false, 'errorMessage' => "errorMessage.SE120"];
                throw new \Exception();
            }
            if (!empty($arr)) { #check new items exists.
                ApplicantInfo::insert($arr);
                $applicantInfoIds = ApplicantInfo::where('created_at', $now)->get()->pluck('id');
                $applicantApplicantInfos = [];
                for ($i = 0; $i < count($applicantInfoIds); $i++) {
                    $o = [
                        'template_id' => $templateId,
                        'applicant_id' => $applicantID,
                        'applicant_info_id' => $applicantInfoIds[$i]
                    ];
                    array_push($applicantApplicantInfos, $o);
                }
                DB::table('template_applicant_applicant_info')->insert($applicantApplicantInfos);
            }
            DB::commit();
            return ['status' => true, 'data' => [
                    'arr' => $arr,
                    'dataPicture' => $dataPicture,
                    'tobedeleted' => $tobedeleted,
                    'data' => $data,
                    'now' => $now,
                    'folderName' => $folderName,
                    'oldArray' => $oldArray,
                    'oldHeadingId' => $oldHeadingId
                ]];
        }
        catch (\Exception $e) {
            DB::rollBack();
            Log::debug($e->getMessage() . " at line number " . __LINE__ . "in Applicant Update Info...... Please check those lines some are not user error.");
            if (!empty($dataPicture)) {
                foreach ($dataPicture as $picture) {
                    $this->deleteObject($picture);
                }
            }
            if ($tobedeleted != "") {
                $disk = Storage::disk('public');
                $disk->delete($tobedeleted);
            }
            return $request;
        }
    }
}
