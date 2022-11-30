<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_levels', function (Blueprint $table) {
            $table->id();
            $table->string('admin_level_name',100);
			$table->tinyInteger('dashboard_permission')->default('1')->comment('1:all;0:only me;2:my data and who requests send to me');
			$table->tinyInteger('view_permission')->default('1')->comment('1:all;0:only me;2:my data and my members');
            $table->softDeletes(); // deleted_at
            $table->integer('created_emp');
            $table->integer('updated_emp');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_levels');
    }
}
