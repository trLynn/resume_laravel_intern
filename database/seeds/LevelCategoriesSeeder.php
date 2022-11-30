<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author Thu Ta
     * @create  20/06/2022 
     * @return void
     */
    public function run()
    {
        DB::table("level_categories")->truncate();
        DB::table("level_categories")->insert([
                [
                    'name'          =>'A ~ Z',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'name'          =>'1 ~ 10',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
                [
                    'name'          =>'Good, Average, Poor',
                    'created_emp'   =>'1001',
                    'updated_emp'   =>'1001',
                    "created_at"    =>  Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_at"    =>  Carbon::now()->format("Y-m-d H:i:s")
                ],
        ]);
    }
}
