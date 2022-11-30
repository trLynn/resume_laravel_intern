<?php
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        DB::table('employees')->truncate();
        DB::table('employees')->insert(array(            
            array(
                "employee_id" => "20001",  
                "code" => "20001",  
                "name_eng" => "BCMM", 
                "name" => "BCMM", 
                "email" => "wms@brycenmyanamr.com.mm",  
                "password" => "$2y$10$5auDRloDAaE4V3zC9Y061Owwp.gnJXqehTK2JKHnz9tkESd.zzZmG",  //12345
                "active" => "1", 
                "employee_type" => "1",      
                "role_id" => "1", 
                "avatar" => "",                             
                "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
            ),
            array(
                "employee_id" => "20002",  
                "code" => "20002",  
                "name_eng" => "BCMM_2", 
                "name" => "BCMM_2", 
                "email" => "wms@brycenmyanamr.com.mm",  
                "password" => "$2y$10$5auDRloDAaE4V3zC9Y061Owwp.gnJXqehTK2JKHnz9tkESd.zzZmG",  //12345
                "active" => "1", 
                "employee_type" => "1",      
                "role_id" => "1", 
                "avatar" => "",                             
                "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
            ),
            array(
                "employee_id" => "20003",  
                "code" => "20003",  
                "name_eng" => "BCMM_3", 
                "name" => "BCMM_3", 
                "email" => "wms@brycenmyanamr.com.mm",  
                "password" => "$2y$10$5auDRloDAaE4V3zC9Y061Owwp.gnJXqehTK2JKHnz9tkESd.zzZmG",  //12345
                "active" => "1", 
                "employee_type" => "1",      
                "role_id" => "1", 
                "avatar" => "",                             
                "created_at" => Carbon::now()->format('Y-m-d H:m:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:m:s')
            ),
        ));
        
    }
}
