<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'question',
        'question_details',
        'answer',
        'status',
        'created_by',
        'answered_by',
        'updated_by',
        'case_codes',
        'case_ids',
        'question_code',
        'consultant_id'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class,'service_id','id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function answeredBy()
    {
        return $this->belongsTo(User::class, 'answered_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function remoteConsultant()
    {
        return $this->belongsTo(User::class, 'consultant_id', 'id');
    }
}
