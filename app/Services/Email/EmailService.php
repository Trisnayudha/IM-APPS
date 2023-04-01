<?php

namespace App\Services\Email;

use App\Mail\OtpForgotMail;
use App\Mail\OtpMail;
use App\Mail\OtpRegisterMail;
use App\Mail\SuggestMeetMail;
use App\Mail\verifMail;
use App\Repositories\EmailServiceInterface;
use Illuminate\Support\Facades\Mail;

class EmailService implements EmailServiceInterface
{
    public function sendOtpEmail($user, $otp)
    {
        $mail = new OtpMail($user, $otp);
        Mail::to($user->email)->send($mail);
    }

    public function sendOtpRegisterEmail($user, $otp)
    {
        $mail = new OtpRegisterMail($user, $otp);
        Mail::to($user->email)->send($mail);
    }

    public function sendOtpForgotPassword($user, $otp)
    {
        $mail = new OtpForgotMail($user, $otp);
        Mail::to($user->email)->send($mail);
    }

    public function sendSuggestMeet($name, $users_name, $category_name, $message, $email)
    {
        //
        $mail = new SuggestMeetMail($name, $users_name, $category_name, $message);
        Mail::to($email)->send($mail);
    }

    public function sendOtpVerify($user, $otp, $wording, $subject, $email)
    {
        $mail  = new verifMail($user, $otp, $wording, $subject);
        Mail::to($email)->send($mail);
    }
}
