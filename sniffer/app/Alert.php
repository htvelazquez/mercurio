<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    const STATUS_NEW        = 0;
    const STATUS_DONE  = 1;

    protected $fillable = [
        'before_snapshot_id',
        'after_snapshot_id',
        'status',
        'checked_by_solver',
        'case',
        'created_at',
        'updated_at'
    ];

    public function after_metadata() {
        return $this->hasOne('App\SnapshotMetadata', 'snapshot_id', 'after_snapshot_id')->with(['location']);
    }

    public function snapshot() {
        return $this->hasOne('App\Snapshot', 'id', 'after_snapshot_id')->with(['contact']);
    }

    public function email() {
        return $this->hasOne('App\SnapshotEmail', 'snapshot_id', 'after_snapshot_id');
    }

    public function before_experience() {
        return $this->hasMany('App\SnapshotExperience', 'snapshot_id', 'before_snapshot_id')->orderBy('from', 'desc')->with(['company']);
    }

    public function after_experience() {
        return $this->hasMany('App\SnapshotExperience', 'snapshot_id', 'after_snapshot_id')->orderBy('from', 'desc')->with(['company']);
    }
}
