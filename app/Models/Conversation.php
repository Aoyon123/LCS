<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'purpose_id',
        'purpose_type',
        'time',
        'seen_status',
        'status',
        'is_delete',
        'attachment'
    ];

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function caseInfo()
    {
        return $this->belongsTo(LcsCase::class, 'purpose_id', 'id');
    }


}
