<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'consultant_id',
        'case_message',
        'case_id',
        'time',
        'seen_status',
        'status',
        'is_delete'
    ];

    // public function receiver()
    // {
    //     return $this->belongsTo(User::class, 'receiver_id', 'id');
    // }
    // public function sender()
    // {
    //     return $this->belongsTo(User::class, 'sender_id', 'id');
    // }
}
