<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'label',
        'linkedin_id',
        'link',
        'safeId',
        'ownerId',
        'created_at',
        'updated_at'
    ];
}
