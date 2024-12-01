<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnregisteredCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'fname',
        'lname',
        'contact_number',
        'house_number',
        'street',
        'barangay',
        'municipality_city',
        'province',
        'postal_code',
    ];

    
}
