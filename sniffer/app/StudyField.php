<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudyField extends Model
{
    protected $fillable = [
        'name',
        'label', 
        'created_at',
        'updated_at'
    ];
}
