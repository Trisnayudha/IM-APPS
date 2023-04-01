<?php

namespace  App\Repositories;

interface EmailServiceInterface
{
    public function sendOtpEmail($user, $otp);
    public function sendOtpRegisterEmail($user, $otp);
    public function sendOtpForgotPassword($user, $otp);
    public function sendSuggestMeet($name, $users_name, $category_name, $message, $email);
    public function sendOtpVerify($user, $otp, $wording, $subject, $email);
}
