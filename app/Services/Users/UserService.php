<?php

namespace App\Services\Users;

use App\Models\Auth\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserService implements UserRepositoryInterface
{
    public function getUserById($id)
    {
        return User::leftjoin('ms_phone_code', 'ms_phone_code.ms_country_id', 'users.ms_prefix_call_id')
            ->select('users.*', 'ms_phone_code.code')
            ->where('users.id', $id)
            ->where('is_register', '1')->first();
    }

    public function getUserByEmail($email)
    {
        return User::leftjoin('ms_phone_code', 'ms_phone_code.ms_country_id', 'users.ms_prefix_call_id')
            ->select('users.*', 'ms_phone_code.code')
            ->where('email', $email)->first();
    }

    public function getUserByEmailActive($email)
    {
        return User::where('email', $email)->where('is_register', '1')->select('otp', 'is_register', 'email', 'name', 'created_at', 'updated_at', 'created_at', 'id')->first();
    }

    public function getUserByEmailDeactive($email)
    {
        return User::where('email', $email)->where('is_register', '0')->select('otp', 'is_register', 'email', 'name', 'created_at', 'updated_at', 'created_at', 'id')->first();
    }

    public function createUsers()
    {
        return new User();
    }

    public function getUserByPhone($phone)
    {
        return User::where('phone', $phone)->first();
    }

    public function deleteAccount($id)
    {
        User::where('id', $id)->delete();
        DB::table('payment')->where('users_id', $id)->delete();
        DB::table('users_delegate')->where('users_id', $id)->delete();
        DB::table('users_log')->where('users_id', $id)->delete();
        DB::table('users_notification')->where('users_id', $id)->delete();
        DB::table('project_bookmark')->where('users_id', $id)->delete();
        DB::table('product_bookmark')->where('users_id', $id)->delete();
        DB::table('news_bookmark')->where('users_id', $id)->delete();
        DB::table('media_bookmark')->where('users_id', $id)->delete();

        return true;
    }
}
