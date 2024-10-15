<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $table = 'borrow';

    protected $fillable = [
        'customer_id',
        'admin_id',
        'status', 
    ];


    public function borrow_details() {
        return $this->hasMany(BorrowDetails::class, 'borrowed_gallon_id', 'id');
    }


}
