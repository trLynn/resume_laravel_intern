<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionPairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission_pairs', function (Blueprint $table) {
            $table->id();
            $table->integer('main_permission_id')->comment('FK from permissions');
            $table->integer('related_permission_id')->comment('FK from permissions');
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
        Schema::dropIfExists('permission_pairs');
    }
}
