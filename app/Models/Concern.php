<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'customer_id',
        'subject',
        'concern_type',
        'content',
        'images',
    ];

    protected $casts = [
        'images' => 'array', 
    ];


    public function admin(){
        return $this->belongsTo(User::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function replies(){
        return $this->hasMany(Reply::class);
    }

}
