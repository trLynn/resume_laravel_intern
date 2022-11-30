<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->mailData = $data;
    }

    /**
     * Build the message.
     * @author  ZAR NI WIN
     * @create  [10.05.2021]
     *
     * @return $this
     */
    public function build()
    {
        if(!empty($this->mailData['attach_type'])){
            $attach_type = $this->mailData['attach_type'];
        }else{
            $attach_type = config('TWO');//default:: storage attachment
        }
        $mail = $this->view('email.'.$this->mailData['template']);//view template
        if(!empty($this->mailData['subject'])){//subject
            $mail->subject($this->mailData['subject']);
        }
        if(!empty($this->mailData['attachment'])){//attachment path
            $attachments = $this->mailData['attachment'];
            if(!is_array($attachments)){//change to array
               $attachments = array($attachments);
            }
            foreach($attachments as $attachment){
                if($attach_type == config('THREE')){ // from disk eg.s3
                    if(!empty($this->mailData['attach_disk'])){
                        $disk = $this->mailData['attach_disk'];
                    }else{
                        $disk = 's3';
                    }
                    $mail->attachFromStorageDisk($disk, $attachment);
                }else if($attach_type == config('ONE')){ //from local pc
                    $filename  = $attachment->getClientOriginalName();
                    $extension = $attachment->getClientOriginalExtension();
                    $mail->attach($attachment,[
                        'as' => $filename,
                        'mime' => 'application/'.$extension,
                    ]);
                }else{ // from storage
                    $path_parts = pathinfo($attachment);
                    $file       = $path_parts['basename'];
                    $extension  = $path_parts['extension'];
                    $mail->attachFromStorage($attachment, $file, [
                        'mime' => 'application/'.$extension
                    ]);
                }
            }
        }
        return $mail;

    }
}
