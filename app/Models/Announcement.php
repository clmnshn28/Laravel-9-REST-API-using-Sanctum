<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable =[
        'title',
        'content',
    ];

    public function readStatus(){
        return $this->hasMany(AnnouncementReadStatus::class,  'announcement_id')->with('admin');
    }

    

}
