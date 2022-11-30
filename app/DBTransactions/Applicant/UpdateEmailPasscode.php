<?php

namespace App\DBTransactions\Applicant;

use App\Classes\DBTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Email Passcode in email_passcode table
 *
 * @author  Thu Rein Lynn
 * @create  24/06/2022
 */
class UpdateEmailPasscode extends DBTransaction
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
	 * Update EmailPasscode
     *
     * @author  Thu Rein Lynn
     * @create  24/06/2022
     * @return  array
	 */
    public function process()
    {
        $id=$this->request['id'];
        $passcode=$this->request['passcode'];
        $update=DB::table('email_passcode')
              ->where('id', $id)
              ->update(['passcode' => $passcode]);
        if (!$update) { #this row is updated or not
            return ['status' => false, 'error' => 'Update Failed!'];
        }
        return ['status' => true, 'error' => ''];
    }
}
