<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaResource extends Model
{
    use HasFactory;
    protected $table = 'media_resource';
    protected $fillable = [
        'views',
        'download'
    ];
}
