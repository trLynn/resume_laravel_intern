<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('menu_id')->comment('FK from menus');
            $table->string('sub_menu',255);
            $table->tinyinteger('menu_flag')->default('1')->comment('1:show,0:not show');
            $table->string('display_name',255);
            $table->enum('display_status',[1,0]);
            $table->string('controller',255);
            $table->string('method',255);
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
        Schema::dropIfExists('permissions');
    }
}
