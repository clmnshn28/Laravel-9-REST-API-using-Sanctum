<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returned extends Model
{
    use HasFactory;

    protected $table = 'returned';

    protected $fillable = [
        'customer_id',
        'admin_id',
        'status',
    ];

    
    public function returned_details() {
        return $this->hasMany(ReturnedDetails::class, 'returned_gallon_id', 'id');
    }

}
