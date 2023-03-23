<?php

namespace App\Services\MsPrefix;

use App\Repositories\MsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MsService implements MsRepositoryInterface
{
    public function getMsPrefixPhone()
    {
        return DB::table('ms_phone_code')
            ->select('ms_phone_code.id', 'ms_phone_code.code')
            ->orderby('ms_phone_code.code', 'asc')
            ->get();
    }
    public function getMsPrefixPhoneDetail($code)
    {
        return DB::table('ms_phone_code')
            ->where('code', $code)->first();
    }
}
