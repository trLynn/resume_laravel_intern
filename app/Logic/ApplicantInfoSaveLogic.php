<?php

namespace App\Logic;

use App\Http\Requests\ApplicantInfoSaveRequest;

use App\Models\Applicant;
use App\Models\TemplateHeading;
use App\Models\Template;
use App\Traits\ApplicantMapperTrait;
use App\Traits\GoogleCloudStorageTrait;
use Rabbit;
use \Exception;
use App\Models\TemplateHeadingLevel;

/**
 * This class will check the required fields are not null.
 * @author Thu Rein Lynn
 * @created_at 22/6/2022
 */
class ApplicantInfoSaveLogic
{
    use GoogleCloudStorageTrait, ApplicantMapperTrait;
    /**
     * This is constructor of ApplicantInfoSaveLogic
     * @param ApplicantTemplateRequest $request
     * @author Thu Rein Lynn
     * @created_at 22/6/2022
     * @return void
     */
    public function __construct(ApplicantInfoSaveRequest $request = null)
    {
        if (!empty($request))
            $this->request = $request;
    }
    private $request;
    /**
     * This is implementation for check value exists in required fields.
     * @author Thu Rein Lynn
     * @create_at 22/6/2022
     */
    public function logicProcess()
    {
        $templateId = request()->only('template_id')['template_id'];
        $data = request()->only("fields")['fields'];
        $now = now();
        $arr = [];
        $request=["status"=>false];
        $applicantEmail = request()->only('applicant_email')['applicant_email'];
        $applicantId = Applicant::create([
            "email" => $applicantEmail
        ]);
        $dataPicture = [];
        $tobedeleted = "";
        $applicantId = $applicantId->id;
        $folderName = "1010/" . date('Y') . "/" . date("m") . "/" . date("H_i_sa") . '/' . $applicantId . '/';
        try {
            $template = Template::where('id', $templateId)->where('templates.active_flag', config("ONE"))->first();
            if (!empty($template)) { # template exists
                $heading = TemplateHeading::leftJoin('headings', 'template_heading.heading_id', 'headings.id')
                    ->leftJoin('heading_info', 'headings.heading_info_id', 'heading_info.id')
                    ->leftJoin('templates', 'templates.id', 'template_heading.template_id')
                    ->leftJoin('types', 'headings.type_id', 'types.id')
                    ->where('templates.active_flag', config("ONE"))
                    ->where('template_heading.template_id', $templateId)
                    ->select(['headings.id as heading_id', 'types.id as type_id', 'heading_info.name as heading_name'])
                    ->get();
                if ($heading->isNotEmpty()) { # if template changes everything
                    if (count($data) == $heading->count()) { # if user changes headings
                        for ($i = config("ZERO"); $i < count($data); $i++) { # shape data in loop
                            if ($heading[$i]->heading_id == $data[$i]["heading_id"] && $heading[$i]->type_id == $data[$i]["type_id"] && $heading[$i]->heading_name == $data[$i]['heading_name'] && $template->name === $this->request->template_name) {
                                #applicant type id is two
                                if ($data[$i]['type_id'] == config("TWO")) {
                                    $local = config("ZERO");
                                    $count = config("ZERO");
                                    if (!empty($data[$i]['value'])) {
                                        $connected=TemplateHeadingLevel::where("heading_id",$data[$i]['heading_id'])->where('template_id',$templateId)->get();
                                        $flagLevel=false;
                                        if($connected->isNotEmpty()){ # if heading has level.
                                            $flagLevel=true;
                                        }
                                        foreach ($data[$i]['value'] as $obj) { # loop all the data for type 2
                                            if ($obj['check']) {
                                                if (!empty($obj['subheading_id']) && !empty($obj['level_id']) && $flagLevel) { #check subheading id is not empty and level id is not empty
                                                    $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $data[$i]['heading_id'], $templateId, true);
                                                    if ($o) { # if the multiple choice has value
                                                        if (!empty($o["subName"])) {
                                                            $data[$i]['value'][$local]["subName"] = $o["subName"];
                                                        }
                                                        array_push($arr, $o['o']);
                                                        $count++;
                                                    }
                                                    else { # or is false boolean
                                                        $request = ["status" => false, "error" => "Please Refresh Again"];
                                                        throw new \Exception();
                                                    }
                                                }
                                                elseif (!empty($obj['subheading_id']) && !isset($obj['level_id']) && !$flagLevel) { #check subheading id is not empty and level id is does not exists
                                                    $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($obj, $now, $data[$i]['heading_id'], $templateId, true);
                                                    if ($o) {
                                                        if (!empty($o["subName"])) {
                                                            $data[$i]['value'][$local]["subName"] = $o["subName"];
                                                        }
                                                        array_push($arr, $o['o']);
                                                        $count++;
                                                    }
                                                    else {
                                                        $request = ["status" => false, "error" => "Please Refresh Again"];
                                                        throw new \Exception();
                                                    }
                                                }else{
                                                    $request=['status'=>false];
                                                    throw new \Exception();
                                                }
                                            }
                                            $local++;
                                        }
                                    }
                                    if ($count == config("ZERO") && $data[$i]['required_flag'] == config("ONE")) { # check if user check nothing and required field
                                        $request = false;
                                        throw new \Exception();
                                    }
                                    #check nothing
                                    elseif ($count == config("ZERO")) { # check if user check nothing
                                        $o = [
                                            'heading_id' => $data[$i]['heading_id'],
                                            'applicant_info' => "",
                                            'created_at' => $now,
                                            'level_id' => null
                                        ];
                                        // $data[$i]['value'][$local]["subName"] = $o["applicant_info"];
                                        array_push($arr, $o);
                                        $local++;
                                    }
                                }
                                #check type is three = radio button
                                elseif ($data[$i]['type_id'] == config("THREE", 3)) {
                                    if (!empty($data[$i]['value'])) {
                                        $o = $this->getFormattedArrayForMultiChoiceAndSingleChoice($data[$i], $now,$data[$i]['heading_id'],$templateId);
                                        if ($o) {
                                            array_push($arr, $o['o']);
                                        }
                                        else {
                                            $request = ["status" => false, "error" => "Please Refresh Again"];
                                            throw new \Exception();
                                        }
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
                                #check type is seven
                                elseif ((int)$data[$i]['type_id'] < config("SEVEN", 7)) {
                                    if (empty($data[$i]['value']) && $data[$i]['required_flag'] == config("ONE")) { # check is required and not null
                                        $request = false;
                                        throw new \Exception();
                                    }
                                    elseif (strlen($data[$i]['value']) > config('MAX_SINGLE_VALUE_TEXT_LENGTH') && $data[$i]['type_id'] != config("FIVE")) { # check comment box text length
                                        $request = ['status' => false, 'errorMessage' => "errorMessage.SE129"];
                                        throw new \Exception();
                                    }
                                    elseif (strlen($data[$i]['value']) > config('MAX_COMMENT_LENGTH') && $data[$i]['type_id'] == config("FIVE")) { # check comment box text length
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
                                    if (strlen($o['applicant_info']) > config('MAX_SINGLE_VALUE_TEXT_LENGTH') && $data[$i]['type_id'] != config("FIVE")) { # check single value mapptext
                                        $request = ['status' => false, 'errorMessage' => "errorMessage.SE129"];
                                        throw new \Exception();
                                    }
                                }
                                #check user filled the value
                                elseif (!empty($data[$i]['value'])) {
                                    #check data is array (or) attachment file
                                    if (is_array($data[$i]['value'])) {
                                        $localArray = [];
                                        if (count($data[$i]['value']) <= config("THIRTY") ) {
                                            foreach ($data[$i]['value'] as $k) { # loop through the attachment files
                                                if (strlen($k['name']) > config("MAX_ATTACHMENT_FILENAME_LENGTH")) { # if name is less than 200
                                                    $request = ['status' => false, "heading_name" => $data[$i]['heading_name']];
                                                    throw new \Exception();
                                                }
                                                if (!empty($k['data'])) { # data is not empty
                                                    $array = $this->generateAttachmentLinkSaveableFormatArray($k, $folderName, $applicantId, $data[$i]['heading_id'], $now);
                                                    if ($array['status'] == false && isset($array['errorMessage'])) { # if the status is false and get errorMessage
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
                                            }
                                            $data[$i]['value'] = $localArray;
                                        }else{
                                            $request=['status'=>false, 'errorMessage'=> "errorMessage.SE130"];
                                            throw new \Exception();
                                        }
                                    }
                                    #check data is profile and new picture
                                    elseif ($data[$i]['type_id'] == config("EIGHT", 8)) { # config is profile picture
                                        if (empty($data[$i]['value']) && $data[$i]['required_flag'] == config("ONE")) {
                                            $request = false;
                                            throw new \Exception;
                                        }
                                        if (!empty($data[$i]['value'])) { # if user added picture
                                            $array = $this->generateAvaterLinkSaveableFormatArray($data[$i], $folderName, $applicantId, $data[$i]['heading_id'], $now);
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
                                            $tobedeleted = $array["tobedeleted"];
                                            $data[$i]['value'] = $array['applicant_link']['link'];
                                        }

                                    }
                                }
                            }
                            else { # if user change something
                                $request = ["status" => false, "error" => "Please Refresh Again"];
                                throw new \Exception();
                            }
                        }
                    }
                    else { # if user changes something
                        $request = ['status' => false, "error" => "Please Refresh Again"];
                        throw new \Exception();
                    }
                }
                else { # template not avalialbe
                    $request = ['status' => false, 'errorMessage' => "errorMessage.SE119"];
                    throw new \Exception();
                }
            }
            else { # template not not exists
                $request = ['status' => false, 'errorMessage' => "errorMessage.SE119"];
                throw new \Exception();
            }

            return ['status' => true, 'data' => [
                    'applicant' => $applicantId,
                    'arr' => $arr,
                    'dataPicture' => $dataPicture,
                    'tobedeleted' => $tobedeleted,
                    'data' => $data,
                    'now' => $now,
                    'folderName' => $folderName,
                ]];
        #shape the data into the loop.
        }
        catch (\Exception $e) { # if user false some kind of format
            Applicant::where('id', $applicantId)->forceDelete();
            if (!empty($dataPicture)) {
                foreach ($dataPicture as $picture) {
                    $this->deleteObject($picture);
                }
            }
            if ($tobedeleted != "") {
                $this->deleteObject($folderName . $tobedeleted);
            }
            return $request;
        }
    }
}
