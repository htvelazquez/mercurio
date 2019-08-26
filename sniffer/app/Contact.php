<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    use Notifiable;

    protected $fillable = [
        'external_id',
        'has_worked_at_client',
        'created_at',
        'updated_at',
        'salesforce_id'
    ];

    public function routeNotificationForSlack()
    {
        return config('services.slack.webhook');
    }
}
