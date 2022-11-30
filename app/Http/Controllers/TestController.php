<?php

namespace App\Http\Controllers;
use App\Traits\LogTrait;
use App\Traits\MailTrait;
use Illuminate\Http\Request;
use App\Classes\MessageHelperUtil;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    use LogTrait, MailTrait;

    public function __construct()
    {
        $this->msgHelpUtil = new MessageHelperUtil;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        //return $this->msgHelpUtil->errorMessage('errorMessage.SE003',config('HTTP_CODE_200'),['attribute'=>'test']);
        return $this->msgHelpUtil->successMessage('successMessage.SS002',config('HTTP_CODE_200'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        # write insertion log to crud_logs table
        $description = "Download after overtime amount";
        $form = "After Overtime Request List";
        $this->writeCRUDLog($request->login_id, $description, $form, config('DOWNLOAD'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $mailData = [];
        $mailData['mail_to_receipts'] = 'bcmm.intern08@brycenmyanmar.com.mm';
        $mailData['subject'] = 'Send resume passcode';
        $mailData['title'] = 'Resume Passcode';
        $body_message = "You are receiving this email because we received a resume passcode to use resume form.";
        $body_message .= "<br>Please do not share this credentials to others.";
        $body_message .= "<br><br> Passcode : 11111111";
        $mailData['body_message'] = $body_message;
        $mailData['template'] = 'text_template';
        $mailData['form'] = 'aaaaa';
        $mailData['op_flag'] = config("SAVE");
        $mailData['device_flag'] = config("WEB");
        //dd($mailData['op_flag']);

        //call mail send Method
        $message = $this->sendMail($mailData);

        $mailResArr = json_decode($message->getContent(), true);
        //send mail is error have or not
        if($mailResArr['status'] == "NG"){
            $mailError = $mailResArr['message'];
        } else {
            $mailError = "";
        }

         //check mailError is null or not
         if(empty($mailError)) {
            return $this->msgHelpUtil->successMessage('successMessage.SS004',config('HTTP_CODE_200'),'Mail');
        } else {
            $msg = $mailError;
            return response()->json(["status" => "OK", "message" => $msg], config('HTTP_CODE_200'));
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
