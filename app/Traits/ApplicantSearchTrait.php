<?php

namespace App\Traits;

use App\Models\Template;
use App\Models\TemplateApplicant;
use App\Classes\MessageHelperUtil;

/**
 * Applicant Exists Check Trait
 *
 * @author Thu Rein Lynn
 * @create  2022/07/25
 */
trait ApplicantSearchTrait
{
    public function __construct()
    {
        $this->msgHelpUtil = new MessageHelperUtil;
    }
    /**
     * Return message array or string
     *
     * @author  Thu Rein Lynn
     * @create  2022/06/21
     * @param   Request
     * @return  array
     */
    public function checkApplicantsExist($request)
    {
        if($request->template_id == 0){
            $all_applicants_count = TemplateApplicant::all()->count();
            if($all_applicants_count == 0){
                return $this->msgHelpUtil->errorMessage('errorMessage.SE124',config('HTTP_CODE_200'));
            }
        }else{
            $template = Template::where('id',$request->template_id)->first();
            if($template){
                $applicants_count = TemplateApplicant::where('template_id',$template->id)->count();
                if($applicants_count == 0){
                    return $this->msgHelpUtil->errorMessage('errorMessage.SE125',config('HTTP_CODE_200'),$template->name); 
                }
            }else{
                return $this->msgHelpUtil->errorMessage('errorMessage.SE126',config('HTTP_CODE_200')); 
            }
        }
        return 'applicants_exist';
    }
}
