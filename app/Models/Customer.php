<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'fname',
        'lname',
        'contact_number',
        'house_number',
        'street',
        'barangay',
        'municipality_city',
        'province',
        'postal_code',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

}
