<?php

namespace  App\Repositories;

interface EmailServiceInterface
{
    public function sendOtpEmail($user, $otp);
}
