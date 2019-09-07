<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapshotMetadata extends Model
{
    protected $fillable = [
        'snapshot_id',
        'location_id',
        'summary',
        'totalConnections',
        'twitter',
        'birthday',
        'name',
        'firstName',
        'lastName',
        'created_at',
        'updated_at'
    ];

    public function location() {
        return $this->belongsTo('App\Location');
    }
}
