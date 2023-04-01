<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class verifMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;
    public $wording;
    public $subject;

    public function __construct($user, $otp, $wording, $subject)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->wording = $wording;
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->from(env('EMAIL_SENDER'), env('EMAIL_NAME'))
            ->subject($this->subject)
            ->view('email.tokenverify', [
                'name' => $this->user->name,
                'wording' => $this->wording,
                'otp' => $this->otp,
            ]);
    }
}
