<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;


    protected $fillable = [
        'customer_id',
        'admin_id',
        'type',
        'subject',
        'description',
        'is_admin',
    ];
}
