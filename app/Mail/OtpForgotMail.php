<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpForgotMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;

    public function __construct($user, $otp)
    {
        $this->user = $user;
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->from(env('EMAIL_SENDER'), env('EMAIL_NAME'))
            ->subject('OTP Forgot Password Indonesia Miner')
            ->view('email.tokenverify', [
                'name' => $this->user->name,
                'wording' => 'We received a request to reset the password for your account. To reset the password, please use this
                code:',
                'otp' => $this->otp,
            ]);
    }
}
