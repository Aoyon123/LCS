<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralAskingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'phone',
        'question',
        'question_answer',
        'email',
        'status',
        'registration_status'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class,'service_id','id');
    }

   
}
