<?php

namespace App\Traits;

trait QrCode
{
    public static function checkIsQuestioner($code_payment)
    {
        if (strpos($code_payment, 'QIM24-') !== false)
            return true;
        return false;
    }

    public static function checkIsPolling($code_payment)
    {
        if (strpos($code_payment, 'PIM24-') !== false)
            return true;
        return false;
    }
}
