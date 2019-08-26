<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    const STATUS_RUNNING   = 0;
    const STATUS_DONE      = 1;
    const STATUS_FAILED    = 2;

    protected $fillable = [
        'name', 
        'detail', 
        'status', 
        'created_at', 
        'updated_at'
    ];
}