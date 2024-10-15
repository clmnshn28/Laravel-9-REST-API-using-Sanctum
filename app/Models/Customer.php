<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'password',
        'fname',
        'lname',
        'email',
        'contact_number',
        'house_number',
        'street',
        'barangay',
        'municipality_city',
        'province',
        'postal_code',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function feedbacks () {
        return $this->hasMany(Feedback::class, 'customer_id', 'id');
    }

    public function borrowed_gallons(){
        return $this->hasMany(Borrow::class, 'customer_id', 'id');
    }

    public function gallons(){
        return $this->hasManyThrough( BorrowDetails::class, Borrow::class, 'customer_id', 'borrowed_gallon_id')->with('borrow'); 
    }

    // public function inactive_gallons(){
    //     return $this->hasMany(Borrow::class, 'customer_id', 'id')->where('status', '=', 'Completed')->with('borrow_details');
    // }

    public function inactive_gallons(){
        return $this->hasMany(Borrow::class, 'customer_id', 'id')->where('status', '=', 'completed')->with('borrow_details');
    }

}
