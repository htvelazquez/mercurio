<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientCap extends Model
{
    protected $fillable = [
        'client_id', 
        'cap', 
        'created_at', 
        'updated_at'
    ];
}
