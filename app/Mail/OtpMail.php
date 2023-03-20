<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
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
            ->subject('OTP Login Indonesia Miner')
            ->view('email.tokenverify', [
                'name' => $this->user->name,
                'wording' => 'We received a request to login your account. To login, please use this code:',
                'otp' => $this->otp,
            ]);
    }
}
