<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'fname',
        'lname',
        'email',
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
