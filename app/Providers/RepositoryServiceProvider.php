<?php

namespace App\Providers;


use App\Services\Email\EmailService;
use App\Repositories\EmailServiceInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\Users\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserService::class);
        $this->app->bind(EmailServiceInterface::class, EmailService::class);
    }
}
