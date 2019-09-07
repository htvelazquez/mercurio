<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name',
        'label',
        'linkedin_id',
        'link',
        'created_at',
        'updated_at'
    ];
}
