<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_hour_id',
        'start',
        'end',
    ];

    public function businessHour(){
        return $this->belongsTo(BusinessHour::class);
    }

}
