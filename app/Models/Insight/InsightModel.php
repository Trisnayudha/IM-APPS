<?php

namespace App\Models\Insight;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsightModel extends Model
{
    use HasFactory;
    protected $table = 'insight';
    protected $fillable = [
        'users_id',
        'email',
        'text',
    ];
}
