<?php
namespace App\Interfaces\Template;

use Illuminate\Http\Request;

interface TemplateRepositoryInterface
{
    #get template information
    public function getTemplateData ($templateId);
    #update template data into DB.
    public function updateTemplate (Request $tempData);
    #update template active status.
    public function updateTemplateActiveFlag (array $tempData);
    #get all types
    public function getAllTypes();
    #get all templates to show
    public function getAllTemplates();
    #view template
    public function viewTemplate($tempId);
    public function templateAll(); //get all template name
    public function search($request); //search by template_name
    public function delete($template_id); //delete template
    #to show dashboard
    public function dashboard();
}

