<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedDetails extends Model
{
    use HasFactory;

    protected $table = 'returned_details'; 
    
    protected $fillable = [
        'shop_gallon_id',
        'returned_gallon_id',
        'quantity', 
    ];

   
    public function returned() {
        return $this->hasMany(Returned::class, 'id', 'returned_gallon_id');
    }
}
