<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowDetails extends Model
{
    use HasFactory;

    protected $table = 'borrow_details'; 
    
    protected $fillable = [
        'shop_gallon_id',
        'borrowed_gallon_id',
        'quantity', 
    ];

    public function borrow () {
        return $this->hasOne(Borrow::class, 'id', 'borrowed_gallon_id');
    }

}
