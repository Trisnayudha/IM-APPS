<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsPollsVote extends Model
{
    use HasFactory;
    protected $table = 'events_polls_vote';
}
