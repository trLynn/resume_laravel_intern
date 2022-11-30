<?php

namespace App\Exports;

use Exception;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Interfaces\Applicant\ApplicantRepositoryInterface;

    /**
     *  Applicant list excel export Class
     * @author  Saw Joseph Wah
     * @create  23/06/2022
     */
class ApplicantsExport implements WithMultipleSheets
{
    /**
     * ApplicantsAllExport constructor
     * @author  Saw Joseph Wah
     * @create  23/06/2022
     */
    private ApplicantRepositoryInterface $applicantRepository;
    private Request $request;
    public function __construct(ApplicantRepositoryInterface $applicantRepository,Request $request)
    {
        $this->applicantRepository = $applicantRepository;
        $this->request = $request;
    }
  
    /**
     * Applicant list excel export
     * @author  Saw Joseph Wah
     * @param   Request $request
     * @return array
     * @create  23/06/2022
     */
    public function sheets(): array
    {
        $sheets = [];
        if($this->request->template_id == 0){#All
            $template_ids = Template::pluck('id')->toArray();
            foreach($template_ids as $key => $template_id){
                    $this->request->template_id = $template_id;
                    $applicants_data = $this->applicantRepository->applicantSearch($this->request);
                    if(count($applicants_data) > 0){
                        $sheets [$key]=  new ApplicantsPerTemplate($applicants_data,$template_id);
                    }
            }
        }else{  #template selected
            $template = Template::find($this->request->template_id);
            if($template){
                $this->request->template_id = $template->id;
                $applicants_data = $this->applicantRepository->applicantSearch($this->request);
                if($applicants_data->isNotEmpty()){
                    $sheets []=  new ApplicantsPerTemplate($applicants_data,$template->id);
                }
            }
        }
        return $sheets;
    }
}
