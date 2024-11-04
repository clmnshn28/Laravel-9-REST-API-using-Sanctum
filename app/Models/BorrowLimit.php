<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowLimit extends Model
{
    use HasFactory;


    protected $fillable = [
        'customer_id',
        'slim_gallons',
        'round_gallons',
    ];


    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
