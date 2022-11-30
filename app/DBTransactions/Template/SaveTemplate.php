<?php

namespace App\DBTransactions\Template;
use App\Models\Heading;
use App\Models\Template;
use App\Models\Subheading;
use App\Models\HeadingInfo;
use App\Classes\DBTransaction;
use App\Models\Level;
use App\Models\LevelCategories;
use App\Models\TemplateHeading;
use App\Models\TemplateHeadingLevel;
use App\Models\TemplateHeadingSubheading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * To save new template in `templates` table
 *
 * @author  Thuta
 * @create  21/06/2022
 */
class SaveTemplate extends DBTransaction
{
    private $request;

    /**
     * Constructor to assign interface to variable
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
	 * Save Template
     *
     * @author  Thu Ta
     * @create  21/06/2022
     * @return  array
	 */
    public function process()
    {
        $request = $this->request;
        $tempName = $request->title;
        $layoutId = $request->layout_id;
        $loginId  = $request->login_id;
        $link = config('Create_Link'); 
        $tempData = [
            "name"          =>      $tempName,
            "layout_id"     =>      $layoutId,
            "link"          =>      $link,
            "active_flag"   =>      config('ONE'),
            "created_emp"   =>      $loginId,
            "updated_emp"   =>      $loginId
        ];
        $tempId = Template::create($tempData)->id;
        Template::where('id', $tempId)->update(['link' => $link.$tempId]);
        $headingData = $request->headings;
        if (!empty($headingData)){                               //if the headings array is true
            foreach ($headingData as $data) {                   //loop the headings data
                $headingInfoCounter = HeadingInfo::get()->count();
                if ($headingInfoCounter > config('ZERO')) {                 //if there is no data in headings table 
                    $headingInfoNameExist = HeadingInfo::where('name',$data["heading_name"])->exists();
                    if ($headingInfoNameExist) {               //if there have heading'name in heading_info table
                        $headingInfoId = HeadingInfo::where('name',$data["heading_name"])->value('id');
                        $headingInsert = [ 
                            "type_id"           =>      $data["type_id"],
                            "heading_info_id"   =>      $headingInfoId,
                            "require_flag"      =>      $data["require_flag"],
                            "created_emp"       =>      $loginId,
                            "updated_emp"       =>      $loginId
                        ];
                        $headingId = Heading::create($headingInsert)->max('id');
                        $tempHeadInsert = [
                            "template_id"   =>      $tempId,
                            "heading_id"    =>      $headingId,
                            "created_emp"   =>      $loginId,
                            "updated_emp"   =>      $loginId
                        ];
                        TemplateHeading::create($tempHeadInsert);
                        $subheadingData = $data["subheadings"];
                        if (!empty($subheadingData)) {              //if sudheadingdata array is not empty
                            foreach ($subheadingData as $subheadNameValue) {                //loop the sudheadingdata array
                                $subheadingNameExist = Subheading::where('name',$subheadNameValue)->exists();
                                if ($subheadingNameExist) {             //if the subheading name is exist
                                    $subheadingId = Subheading::where('name',$subheadNameValue)->value('id');
                                    $tempHeadSubheadInsert = [
                                        "template_id"   =>      $tempId,
                                        "heading_id"    =>      $headingId,
                                        "subheading_id" =>      $subheadingId,
                                        "created_emp"   =>      $loginId,
                                        "updated_emp"   =>      $loginId
                                    ];
                                    TemplateHeadingSubheading::create($tempHeadSubheadInsert);      
                                } else {
                                    $subheadingInsert = [
                                        "name"          =>      $subheadNameValue,
                                        "created_emp"   =>      $loginId,
                                        "updated_emp"   =>      $loginId
                                    ];
                                    $subheadingId = Subheading::create($subheadingInsert)->max('id');
                                    $tempHeadSubheadInsert = [
                                        "template_id"   =>      $tempId,
                                        "heading_id"    =>      $headingId,
                                        "subheading_id" =>      $subheadingId,
                                        "created_emp"   =>      $loginId,
                                        "updated_emp"   =>      $loginId
                                    ];
                                    TemplateHeadingSubheading::create($tempHeadSubheadInsert);
                                }    
                            }
                        }
                        $levelData = $data["level"];
                        if (!empty($levelData)) {                       //if the leveldata array is not empty
                            $lvlCatId = LevelCategories::where('id',$levelData["level_cat"])->value('id');
                            $level = Level::where('level_category_id',$lvlCatId)->pluck('level')->all();
                            $resulDiff = array_diff($levelData["level_data"],$level);
                            if (!empty($resulDiff)) {          //if the resultIntersect array is not empty
                                foreach ($resulDiff as $diffValue) {             //loop the resultIntersect array
                                    $levelInsert = [
                                        "level_category_id"    =>       $lvlCatId,
                                        "level"                =>       $diffValue,
                                        "created_emp"          =>       $loginId,
                                        "updated_emp"          =>       $loginId 
                                    ];
                                    Level::create($levelInsert);
                                }
                                    $levelId = Level::where('level_category_id',$lvlCatId)->pluck('id')->all();
                                    foreach ($levelId as $lvlId) {
                                        $tempHeadLvl = [
                                            "template_id"       =>       $tempId,
                                            "heading_id"        =>       $headingId,
                                            "level_id"          =>       $lvlId,
                                            "created_emp"       =>       $loginId,
                                            "updated_emp"       =>       $loginId 
                                        ];
                                        TemplateHeadingLevel::create($tempHeadLvl);   
                                    }                            
                            } else {
                                $resultIntersect = array_intersect($level,$levelData["level_data"]);
                                foreach ($resultIntersect as $intersectValue) {             //loop the resultIntersect array
                                    $levelId = Level::where('level',$intersectValue)->value('id');
                                    $tempHeadLvl = [
                                        "template_id"       =>       $tempId,
                                        "heading_id"        =>       $headingId,
                                        "level_id"          =>       $levelId,
                                        "created_emp"       =>       $loginId,
                                        "updated_emp"       =>       $loginId 
                                    ];
                                    TemplateHeadingLevel::create($tempHeadLvl);    
                                } 
                            }
                        }
                    } else {                                    
                        $headingInfoInsert = [
                            "name"          =>      $data["heading_name"],
                            "created_emp"   =>      $loginId,
                            "updated_emp"   =>      $loginId
                        ];
                        $headingInfoId = HeadingInfo::create($headingInfoInsert)->max('id');
                        $headingInsert = [ 
                            "type_id"           =>      $data["type_id"],
                            "heading_info_id"   =>      $headingInfoId,
                            "require_flag"      =>      $data["require_flag"],
                            "created_emp"       =>      $loginId,
                            "updated_emp"       =>      $loginId
                        ];
                        $headingId = Heading::create($headingInsert)->max('id');
                        $tempHeadInsert = [
                            "template_id"   =>      $tempId,
                            "heading_id"    =>      $headingId,
                            "created_emp"   =>      $loginId,
                            "updated_emp"   =>      $loginId
                        ];
                        TemplateHeading::create($tempHeadInsert);
                        $subheadingData = $data["subheadings"];
                        if (!empty($subheadingData)) {                  //if the subheadingData is not empty
                            foreach ($subheadingData as $value) {       //loop the subheadingData array
                                $subheadingNameExist = Subheading::where('name',$value)->exists();
                                if ($subheadingNameExist) {             //if the subheadingName is exist
                                    $subheadingId = Subheading::where('name',$value)->value('id');
                                    $tempHeadSubheadInsert = [
                                        "template_id"   =>      $tempId,
                                        "heading_id"    =>      $headingId,
                                        "subheading_id" =>      $subheadingId,
                                        "created_emp"   =>      $loginId,
                                        "updated_emp"   =>      $loginId
                                    ];
                                    TemplateHeadingSubheading::create($tempHeadSubheadInsert);      
                                } else {
                                    $subheadingInsert = [
                                        "name"          =>      $value,
                                        "created_emp"   =>      $loginId,
                                        "updated_emp"   =>      $loginId
                                    ];
                                    $subheadingId = Subheading::create($subheadingInsert)->max('id');
                                    $tempHeadSubheadInsert = [
                                        "template_id"   =>      $tempId,
                                        "heading_id"    =>      $headingId,
                                        "subheading_id" =>      $subheadingId,
                                        "created_emp"   =>      $loginId,
                                        "updated_emp"   =>      $loginId
                                    ];
                                    TemplateHeadingSubheading::create($tempHeadSubheadInsert);
                                }    
                            }
                        }
                        $levelData = $data["level"];
                        if (!empty($levelData)) {                       //if the leveldata array is not empty
                            $lvlCatId = LevelCategories::where('id',$levelData["level_cat"])->value('id');
                            $level = Level::where('level_category_id',$lvlCatId)->pluck('level')->all();
                            $resulDiff = array_diff($levelData["level_data"],$level);
                            if (!empty($resulDiff)) {          //if the resultIntersect array is not empty
                                foreach ($resulDiff as $diffValue) {             //loop the resultIntersect array
                                    $levelInsert = [
                                        "level_category_id"    =>       $lvlCatId,
                                        "level"                =>       $diffValue,
                                        "created_emp"          =>       $loginId,
                                        "updated_emp"          =>       $loginId 
                                    ];
                                    Level::create($levelInsert);
                                }
                                    $levelId = Level::where('level_category_id',$lvlCatId)->pluck('id')->all();
                                    foreach ($levelId as $lvlId) {
                                        $tempHeadLvl = [
                                            "template_id"       =>       $tempId,
                                            "heading_id"        =>       $headingId,
                                            "level_id"          =>       $lvlId,
                                            "created_emp"       =>       $loginId,
                                            "updated_emp"       =>       $loginId 
                                        ];
                                        TemplateHeadingLevel::create($tempHeadLvl);   
                                    }                            
                            } else {
                                $resultIntersect = array_intersect($level,$levelData["level_data"]);
                                foreach ($resultIntersect as $intersectValue) {             //loop the resultIntersect array
                                    $levelId = Level::where('level',$intersectValue)->value('id');
                                    $tempHeadLvl = [
                                        "template_id"       =>       $tempId,
                                        "heading_id"        =>       $headingId,
                                        "level_id"          =>       $levelId,
                                        "created_emp"       =>       $loginId,
                                        "updated_emp"       =>       $loginId 
                                    ];
                                    TemplateHeadingLevel::create($tempHeadLvl);    
                                } 
                            }
                        }        
                    }
                } else {
                    $headingInfoInsert = [
                        "name"          =>     $data["heading_name"],
                        "created_emp"   =>      $loginId,
                        "updated_emp"   =>      $loginId
                    ];
                    $headingInfoId = HeadingInfo::create($headingInfoInsert)->max('id');
                    $headingInsert = [ 
                        "type_id"           =>      $data["type_id"],
                        "heading_info_id"   =>      $headingInfoId,
                        "require_flag"      =>      $data["require_flag"],
                        "created_emp"       =>      $loginId,
                        "updated_emp"       =>      $loginId
                    ];
                    $headingId = Heading::create($headingInsert)->max('id');
                    $tempHeadInsert = [
                        "template_id"   =>      $tempId,
                        "heading_id"    =>      $headingId,
                        "created_emp"   =>      $loginId,
                        "updated_emp"   =>      $loginId
                    ];
                    TemplateHeading::create($tempHeadInsert);
                    $subheadingData = $data["subheadings"];
                    if (!empty($subheadingData)) {              //if suheadingData is not empty
                        foreach ($subheadingData as $value) {           //loop subheadingData array
                            $subheadingInsert = [
                                "name"          =>      $value,
                                "created_emp"   =>      $loginId,
                                "updated_emp"   =>      $loginId
                            ];
                            $subheadingId = Subheading::create($subheadingInsert)->max('id');                    
                            $tempHeadSubheadInsert = [
                                "template_id"   =>      $tempId,
                                "heading_id"    =>      $headingId,
                                "subheading_id" =>      $subheadingId,
                                "created_emp"   =>      $loginId,
                                "updated_emp"   =>      $loginId
                            ];
                            TemplateHeadingSubheading::create($tempHeadSubheadInsert);
                        }
                    }
                    $levelData = $data["level"];
                    if (!empty($levelData)) {                   //if levelData array is not empty
                        $lvlCatId = LevelCategories::where('id',$levelData["level_cat"])->value('id');
                        foreach ($levelData["level_data"] as $lvlDataValue) {               //loop levelData array
                            $levelInsert = [
                                "level_category_id"     =>      $lvlCatId,
                                "level"                 =>      $lvlDataValue,
                                "created_emp"           =>      $loginId,
                                "updated_emp"           =>      $loginId
                            ];
                            $levelId = Level::create($levelInsert)->max('id');
                            $tempHeadLevel = [
                                "template_id"   =>      $tempId,
                                "heading_id"    =>      $headingId,
                                "level_id"      =>      $levelId,
                                "created_emp"   =>      $loginId,
                                "updated_emp"   =>      $loginId
                            ];
                            TemplateHeadingLevel::create($tempHeadLevel);
                        }          
                    }         
                }
            }
        }
        return ['status' => true, 'error' => ''];
    }
}