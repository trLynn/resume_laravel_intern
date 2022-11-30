<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrudLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crud_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address',45)->nullable();
            $table->string('browser',45)->nullable();
            $table->integer('employee_id');
            $table->text('description')->nullable();
            $table->string('form',255)->nullable();
            $table->tinyInteger('op_flag')->default(1)->comment('1:save,2:update,3:delete,4:download,5:upload,6:request');
            $table->tinyInteger('device_flag')->default(1)->comment('1:web,2:android,3:ios');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crud_logs');
    }
}
