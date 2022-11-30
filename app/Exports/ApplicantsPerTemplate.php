<?php

namespace App\Exports;

use Exception;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Interfaces\Applicant\ApplicantRepositoryInterface;

    /**
     *  Applicant list multiple sheet export Class
     * @author  Saw Joseph Wah
     * @create  23/06/2022
     */
class ApplicantsPerTemplate implements FromCollection,WithTitle,WithHeadings,ShouldAutoSize, WithEvents
{
     /**
     * Applicant list multiple sheet export Class constructor
     * @author  Saw Joseph Wah
     * @create  23/06/2022
     */
    private $template_id;
    private $applicant_data; 
    private Request $request;
    public function __construct($applicant_data,$template_id)
    {
        $this->applicant_data = $applicant_data;
        $this->template_id = $template_id;
    }

    /**
    * Export export 
    * @author  Saw Joseph Wah
    * @create  23/06/2022
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->applicant_data;
    }
    

    /**
    * Excel Sheet title 
    * @author  Saw Joseph Wah
    * @create  23/06/2022
    * @return string
    */
    public function title(): string
    {
        $title = Template::where('id',$this->template_id)->first()->name;
        return $title;
    }

    /**
    * Excel Sheet headings 
    * @author  Saw Joseph Wah
    * @create  23/06/2022
    * @return array
    */
    public function headings(): array
    {
        $headings = [' No ',' Date ',' Status '];
        $headings_with_info = Template::
        join('template_heading','templates.id','template_heading.template_id')
        ->join('headings','headings.id','template_heading.heading_id')
        ->join('heading_info','heading_info.id','headings.heading_info_id')
        ->where('templates.id',$this->template_id)
        ->whereNotIn('headings.type_id',[7,8])
        ->whereNull('headings.deleted_at')
        ->orderBy('headings.id')
        ->pluck('heading_info.name')
        ->toArray();

        return array_merge($headings,$headings_with_info);
    }

    /**
    * Excel Sheet headings styling
    * @author  Saw Joseph Wah
    * @create  23/06/2022
    * @return array
    */
    public function registerEvents(): array
    {
        #for dynamic styling headings cell
        $headings_counts = DB::table('templates')
        ->join('template_heading','templates.id','template_heading.template_id')
        ->join('headings','headings.id','template_heading.heading_id')
        ->join('heading_info','heading_info.id','headings.heading_info_id')
        ->where('templates.id',$this->template_id)
        ->whereNull('headings.deleted_at')
        ->whereNotIn('headings.type_id',[7,8])
        ->count();
        $alphabet = range('A', 'Z');
        $last_heading_cell = $alphabet[2+$headings_counts];
        $data_count = count($this->applicant_data);
        $styleArray = [ #set excel style
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]; 
        return [
            AfterSheet::class => function(AfterSheet $event) use($last_heading_cell,$styleArray,$data_count)  {
                $event->sheet->getDelegate()->getStyle('A1:'.$last_heading_cell.'1')#set headings color
                                ->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('99ff66');
                $event->sheet->getDelegate()->getStyle('A1:'.$last_heading_cell.'1')#set headings bold
                                ->getFont()
                                ->setBold(true);
                $event->sheet->getDelegate()->getStyle('A1:'.$last_heading_cell.'1')#centering headings
                                ->getAlignment()
                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1:'.$last_heading_cell.(++$data_count))->applyFromArray($styleArray);
            },
        ];
    }
}
