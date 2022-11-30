<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailPasscodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_passcode', function (Blueprint $table) {
            $table->id();
            $table->string('email',255);
            $table->integer('passcode')->length(6);
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
        Schema::dropIfExists('email_passcode');
    }
}
