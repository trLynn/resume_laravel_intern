<?php

namespace App\DBTransactions\Applicant;

use Carbon\Carbon;
use App\Classes\DBTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * To save new Applicant in `applicants` table
 *
 * @author  Thu Rein Lynn
 * @create  24/06/2022
 */
class SaveEmailPasscode extends DBTransaction
{
    private $request;

    /**
     * Constructor to assign interface to variable
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
	 * Save Email Passcode in email_passcode table
     *
     * @author  Thu Rein Lynn
     * @create  24/06/2022
     * @return  array
	 */
    public function process()
    {
        $email=$this->request['email'];
        $passcode=$this->request['passcode'];
        DB::table('email_passcode')->insert(['email'=>$email,'passcode'=>$passcode,'passcode_duration'=>Carbon::now()->addMinutes(config('ONE'))]);
        return ['status' => true, 'error' => ''];
    }
}
