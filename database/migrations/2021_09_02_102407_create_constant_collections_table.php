<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConstantCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('constant_collections', function (Blueprint $table) {
            $table->id();
            $table->string('constant_name',255);
            $table->string('constant_definition',255);
            $table->string('remark',255);
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
        Schema::dropIfExists('constant_collections');
    }
}
