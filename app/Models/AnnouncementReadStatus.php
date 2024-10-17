<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementReadStatus extends Model
{
    use HasFactory;

    protected $table = 'announcement_read_status';
    
    protected $fillable = [
        'announcement_id',
        'admin_id' , 
        'customer_id', 
        'is_read'
    ];

    public function announcement(){
        return $this->belongsTo(Announcement::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function admin(){
        return $this->belongsTo(User::class);
    }
}
