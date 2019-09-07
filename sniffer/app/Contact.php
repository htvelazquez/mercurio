<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Contact extends Model
{
    use Notifiable;

    protected $fillable = [
        'linkedin_id',
        'created_at',
        'updated_at'
    ];

    public function routeNotificationForSlack()
    {
        return config('services.slack.webhook');
    }
}
