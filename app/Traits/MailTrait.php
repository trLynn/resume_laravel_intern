<?php
namespace App\Traits;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Jobs\SendMail;
use App\Traits\LogTrait;
use App\Mail\MailTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Classes\MessageHelperUtil;

trait MailTrait
{
    use LogTrait;
    /**
     * Mail Sending
     * @author  ZAR NI WIN
     * @create  [10.05.2021]
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function sendMail($data)
    {
        $msgHelpUtil = new MessageHelperUtil;
        try{
            if(!empty($data['data'])){//check mail data include data or not
                $table_data = $data['data'];
                if(!array_key_exists(config('ZERO'),$table_data)){//check data is array of array eg. data = [[],[]]
                    $data['data'] = [];
                    $data['data'][config('ZERO')] = $table_data;
                }
            }
            $mail = Mail::to($data['mail_to_receipts']);
            if(!empty($data['mail_cc_receipts'])){//cc receipets
                $mail->cc($data['mail_cc_receipts']);
            }
            if(!empty($data['mail_bb_receipts'])){//bb receipets
                $mail->bcc($data['mail_bb_receipts']);
            }
            $mail->send(new MailTemplate($data));
            return $msgHelpUtil->successMessage('successMessage.SS004',config('HTTP_CODE_200'),'Mail');//success
        }catch(Exception $e){
            if (count(Mail::failures()) > config('ZERO')) {//check mail fail errors               
                return $msgHelpUtil->errorMessage('errorMessage.SE008',config('HTTP_CODE_200'),'Mail');//Mail Fail
            }else{//Internal Server Error
                Log::debug($e);//error log
                return $msgHelpUtil->errorMessage('errorMessage.SE008',config('HTTP_CODE_500'),'Mail');
            }
        }
    }
}
