<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_id');
            $table->string('code',50)->nullable();
            $table->string('name_eng',200);
            $table->string('name',200)->nullable();
            $table->string('email');
            $table->string('nrc_number',40)->nullable(); 
            $table->string('passport_number',40)->nullable(); 
            $table->date('date_of_birth')->nullable();            
            $table->string('password');            
            $table->rememberToken();
            $table->string('avatar',255)->nullable();
            $table->integer('active')->default(0)->comment('0:not active 1:active 2:delete 3:Locked');
            $table->integer('employee_type')->default(1)->comment('1:Permanent 2:Part time 3:Contract 4:Indirect/Driect');
            $table->string('activation_token')->nullable();
            $table->bigInteger('role_id');
            $table->softDeletes();//deleted_at
            $table->timestamps();
        });
      
    }    
   
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
        
    }
}
