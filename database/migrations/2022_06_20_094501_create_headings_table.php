<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('headings', function (Blueprint $table) {
            $table->id();
            $table->integer('type_id')->nullable(false);
            $table->string('name',255)->nullable(false);
            $table->tinyInteger('require_flag', false, true)->comment('1: Yes, 2: No')->nullable(true)->default('2');
            $table->softDeletes();
            $table->integer('created_emp')->nullable(false);
            $table->integer('updated_emp')->nullable(false);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
            $table->timestamp('updated_at')->nullable(false)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('headings');
    }
}
