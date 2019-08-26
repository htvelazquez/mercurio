<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapshotExperience extends Model
{
    protected $fillable = [
        'snapshot_id',
        'company_id', 
        'jobTitle', 
        'from', 
        'to',
        'before_diff',
        'after_diff',
        'created_at', 
        'updated_at'
    ];

    public function snapshot() {
        return $this->belongsTo('App\Snapshot');
    }

    public function company() {
        return $this->belongsTo('App\Company');
    }
}