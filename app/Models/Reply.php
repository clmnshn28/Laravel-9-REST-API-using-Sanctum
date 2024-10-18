<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $table = 'concerns_replies';
    
    protected $fillable = [
        'concern_id',
        'customer_id',
        'admin_id',
        'content',
    ];

    public function concern()
    {
        return $this->belongsTo(Concern::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id'); 
    }
}
