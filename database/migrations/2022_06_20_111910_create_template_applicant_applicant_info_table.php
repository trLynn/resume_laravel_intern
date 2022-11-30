<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateApplicantApplicantInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_applicant_applicant_info', function (Blueprint $table) {
            $table->id();
            $table->integer('template_id');
            $table->integer('applicant_id');
            $table->integer('applicant_info_id');
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
        Schema::dropIfExists('template_applicant_applicant_info');
    }
}
