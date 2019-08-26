<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapshotEmail extends Model
{
    protected $fillable = [
        'snapshot_id',
        'email',
        'type',
        'created_at', 
        'updated_at'
    ];
}
