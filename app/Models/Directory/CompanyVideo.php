<?php

namespace App\Models\Directory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyVideo extends Model
{
    use HasFactory;
    protected $table = 'company_video';
    protected $fillable = [
        'views',
        'download'
    ];
}
