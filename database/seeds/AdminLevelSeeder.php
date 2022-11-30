<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminLevel = array(
                array(
                    "admin_level_name" => 'SUPERADMIN',
                    "dashboard_permission" => '1',
                    "view_permission" => '1',
                    "deleted_at"  => null,
                    "created_emp" => '10001',
                    "updated_emp" => '10001',
                    "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                    "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
                )
            );

        //get adminLevel data
        foreach($adminLevel as $adminLevel) {

            $chk = DB::table('admin_levels')
                    ->where('admin_level_name', $adminLevel['admin_level_name'])
                    ->first();

            //check chk is null or not
            if (empty($chk)) {
                DB::table('admin_levels')->insert($adminLevel);                    
            }
        } 
    }
}
