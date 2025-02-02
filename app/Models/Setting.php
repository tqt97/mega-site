<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'facebook_url',
        'instagram_url',
        'twitter_url',
    ];

    public const VERSION = '1.0';

    // protected static function booted()
    // {
    //     static::saved(function () {
    //         Cache::tags(['settings'])->flush();
    //     });
    // }
}
