<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->truncate();
        DB::table('menus')->insert(array(
            array(
                "company_id" => 1,
                "menu_name" => 'Applicant List',
                "display_name" => 'Applicant List',
                "rank" => '1',
                "created_emp" => '10001',
                "updated_emp" => '10001',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ),
            array(
                "company_id" => 1,
                "menu_name" => 'Template List',
                "display_name" => 'Template List',
                "rank" => '1',
                "created_emp" => '10001',
                "updated_emp" => '10001',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ),
        ));
    }
}
