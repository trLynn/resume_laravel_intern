<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeAdminLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @author thiri win htwe
     * @create  2021-09-15
     * @return void
    */
    public function run()
    {
        DB::table('employee_admin_levels')->truncate();
        DB::table('employee_admin_levels')->insert(array(
            array(
                "employee_id" => '20001',
                "admin_level_id" => '1',
                "created_emp" => '10001',
                "updated_emp" => '10001',
                "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
            ),
            array(
                "employee_id" => '20002',
                "admin_level_id" => '1',
                "created_emp" => '10001',
                "updated_emp" => '10001',
                "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
            ),
            array(
                "employee_id" => '20003',
                "admin_level_id" => '1',
                "created_emp" => '10001',
                "updated_emp" => '10001',
                "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
            )
        ));

    }
}
