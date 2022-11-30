<?php

namespace App\DBTransactions\Applicant;

use App\Classes\DBTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * delete row in email_passcode table
 *
 * @author  Thu Rein Lynn
 * @create  24/06/2022
 */
class DeleteEmailPasscode extends DBTransaction
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
	 * Delete Email Passcode
     *
     * @author  Thu Rein Lynn
     * @create  24/06/2022
     * @return  array
	 */
    public function process()
    {
        $id=$this->request;
        $delete=DB::table('email_passcode')->where('id',$id)->delete();
        if (!$delete) { #this row is delete or not
            return ['status' => false, 'error' => 'Delete Failed!'];
        }
        return ['status' => true, 'error' => ''];
    }
}