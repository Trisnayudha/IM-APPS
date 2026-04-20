<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;

class UsersDelegate extends Model
{
    protected $table = 'users_delegate';

    protected $fillable = [
        'payment_id',
        'users_id',
        'events_id',
        'image',
        'date_day1',
        'date_day2',
        'date_day3',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
