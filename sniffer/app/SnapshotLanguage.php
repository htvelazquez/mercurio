<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SnapshotLanguage extends Model
{
    protected $fillable = [
        'snapshot_id',
        'language_id',
        'created_at', 
        'updated_at'
    ];

    public function language() {
        return $this->belongsTo('App\Language');
    }
}
