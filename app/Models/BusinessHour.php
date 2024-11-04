<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;


    protected $fillable = [
        'day',
        'is_open',
    ];

    public function timeSlots(){
        return $this->hasMany(TimeSlot::class);
    }
}
