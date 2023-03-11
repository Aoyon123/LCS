<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LcsCase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'citizen_id',
        'consultant_id',
        'title',
        'description',
        'status',
        'document_file',
        'rating',
        'document_link',
        'case_initial_date',
        'case_status_date',
        'consultant_review_comment',
        'citizen_review_comment',
        'case_code',
        // 'deleted_at'
    ];

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id', 'id');
    }
    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id', 'id');
    }

    public function scopeCompleted($query)
    {
        return $query->where(['status' => 2]);
    }

}
