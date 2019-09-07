<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'priority',
        'url',
        'external_id',
        'time_pulled',
        'crawled_at',
        'created_at',
        'updated_at'
    ];
}
