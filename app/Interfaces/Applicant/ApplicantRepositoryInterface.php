<?php
namespace App\Interfaces\Applicant;

use App\Http\Requests\ApplicantEmailRequest;

interface ApplicantRepositoryInterface
{
    public function sendEmail($request); #send Email
    public function applicantCheckOtp($request); #check email and passcode
    public function applicantFormLoad(); #applicand form load
    public function applicantView($id); //applicant pdf view
    public function applicantStorageDownload($id); //applicant file(s) download to storage
    public function applicantDownload($id); //applicant file(s) download
    public function update($status,$applicant_ids); #status change
    public function delete(array $applicantId); #delete applicant
    public function getHeadingsWithTemplateId($templateId); #headings with template_id
    public function getLevelsWithHeadingType($subheading_id,$level_id); #levels according to heading_type
    public function applicantSearch($request,$search = null);        #pass true for search
}

