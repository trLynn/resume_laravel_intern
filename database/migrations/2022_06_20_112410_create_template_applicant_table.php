<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateApplicantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_applicant', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id');
            $table->integer('applicant_id');
            $table->tinyInteger('status')->comment("1=>pending,2=>reject")->default(1);
            $table->string('applicant_template_link',255)->nullable();
            $table->softDeletes('deleted_at');
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
        Schema::dropIfExists('template_applicant');
    }
}
