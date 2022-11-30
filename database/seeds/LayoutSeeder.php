<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author Thu Ta
     * @create  20/06/2022 
     * @return void
     */
    public function run()
    {
        DB::table("layouts")->truncate();
        DB::table("layouts")->insert([
            [
                'name'          =>'TemplateOne',
                'link'          => 'templateImage\Template 1.png',
                'created_emp'   =>'1001',
                'updated_emp'   =>'1001',
                "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
            ],
            [
                'name'          =>'TemplateTwo',
                'link'          => 'templateImage\Template 2.png',
                'created_emp'   =>'1001',
                'updated_emp'   =>'1001',
                "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
            ],
            [
                'name'          =>'TemplateThree',
                'link'          => 'templateImage\Template 3.png',
                'created_emp'   =>'1001',
                'updated_emp'   =>'1001',
                "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
            ],
    ]);
    }
}
