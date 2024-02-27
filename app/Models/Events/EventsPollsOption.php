<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsPollsOption extends Model
{
    use HasFactory;
    protected $table = 'events_polls_option';

    public function question()
    {
        return $this->belongsTo(EventsPollsQuestion::class, 'events_polls_question_id', 'id');
    }
}
