<?php

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsLog extends Model
{
    use HasFactory;
    protected $table = 'events_log';
}
