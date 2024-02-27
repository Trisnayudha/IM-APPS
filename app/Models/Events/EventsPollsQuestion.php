<?php

namespace App\Models\Events;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventsPollsQuestion extends Model
{
    use HasFactory;
    protected $table = 'events_polls_question';

    public function options()
    {
        return $this->hasMany(EventsPollsOption::class, 'events_polls_question_id', 'id');
    }
}
