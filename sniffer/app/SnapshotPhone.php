<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapshotPhone extends Model
{
    protected $fillable = [
        'snapshot_id',
        'phone',
        'type',
        'created_at', 
        'updated_at'
    ];
}
