<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillDetails extends Model
{
    use HasFactory;

    protected $table = 'refill_details';

    protected $fillable = [
        'shop_gallon_id',
        'refill_gallon_id',
        'quantity',
    ];

    public function refill() {
        return $this->hasMany(Refill::class, 'id', 'refill_gallon_id');
    }
}
