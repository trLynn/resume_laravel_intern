<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasscodeDurationToEmailPasscodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_passcode', function (Blueprint $table) {
            $table->timestamp('passcode_duration')->after('passcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('email_passcode', function (Blueprint $table) {
            $table->dropColumn('passcode_duration');
        });
    }
}
