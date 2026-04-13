<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsScheduleReserve extends Model
{
    use HasFactory;
    protected $table = 'events_schedule_reserve';

    protected $fillable = [
        'users_id',
        'events_schedule_id',
    ];
}
