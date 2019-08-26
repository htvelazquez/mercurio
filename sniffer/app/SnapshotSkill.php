<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapshotSkill extends Model
{
    protected $fillable = [
        'snapshot_id',
        'skill_id',
    ];

    public function skill() {
        return $this->belongsTo('App\Skill');
    }
}
