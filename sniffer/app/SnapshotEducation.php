<?php

namespace App;

//use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SnapshotEducation extends Pivot
{
    protected $table = 'snapshot_educations';

    protected $fillable = [
        'snapshot_id',
        'school_id',
        'study_field_id',
        'degree',
        'from',
        'to',
    ];

    public function school() {
        return $this->belongsTo('App\School');
    }

    public function studyField() {
        return $this->belongsTo('App\StudyField');
    }
}
