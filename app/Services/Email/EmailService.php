<?php

namespace App\Services\Email;

use App\Mail\BenefitMail;
use App\Mail\ContactMail;
use App\Mail\OtpForgotMail;
use App\Mail\OtpMail;
use App\Mail\OtpRegisterMail;
use App\Mail\ReceiveMail;
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

    public function sendContactUs($user, $category, $subject, $message)
    {
        $mail = new ContactMail($user, $category, $subject, $message);
        Mail::to('yudha@indonesiaminer.com')->send($mail);
    }

    public function sendBenefit($type, $find)
    {
        $mail = new BenefitMail($type, $find);
        Mail::to('damun@indonesiaminer.com')->send($mail);
    }

    public function receiveBenefit($type, $find)
    {
        $mail = new ReceiveMail($type, $find);
        Mail::to($find->email)->send($mail);
    }
}
