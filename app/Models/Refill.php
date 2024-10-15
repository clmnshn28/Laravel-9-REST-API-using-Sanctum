<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refill extends Model
{
    use HasFactory;

    protected $table = 'refill';

    protected $fillable = [
        'customer_id',
        'admin_id',
        'status',
    ];

    public function refill_details() {
        return $this->hasMany(RefillDetails::class, 'refill_gallon_id', 'id');
    }


}
