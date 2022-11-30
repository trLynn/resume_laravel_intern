<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubheadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subheadings', function (Blueprint $table) {
            $table->id();
            $table->string('name',255)->nullable(false);
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
        Schema::dropIfExists('subheadings');
    }
}
