<?php

namespace App\Services\Email;

use App\Mail\OtpMail;
use App\Repositories\EmailServiceInterface;
use Illuminate\Support\Facades\Mail;

class EmailService implements EmailServiceInterface
{
    public function sendOtpEmail($user, $otp)
    {
        $mail = new OtpMail($user, $otp);
        Mail::to($user->email)->send($mail);
    }
}
