<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    const STATUS_NEW        = 0;
    const STATUS_PROCESSED  = 1;

    protected $fillable = [
        'contact_id',
        'priority',
        'status', 
        'created_at', 
        'updated_at'
    ];

    public function contact() {
        return $this->belongsTo('App\Contact');
    }

    public function email() {
        return $this->hasOne('App\SnapshotEmail');
    }

    public function language() {
        return $this->hasMany('App\SnapshotLanguage');
    }

    public function metadata() {
        return $this->hasOne('App\SnapshotMetadata');
    }

    public function phone() {
        return $this->hasOne('App\SnapshotPhone');
    }

    public function skill() {
        return $this->hasMany('App\SnapshotSkill');
    }

    public function current_experience() {
        $instance = $this->hasMany('App\SnapshotExperience');
        $instance->where('to','=', null);
        return $instance;
    }
}
