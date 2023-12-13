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
        'device_log',
        'case_complete_date',
    ];

    protected $casts = [
        'created_at' => "datetime:Y-m-d",
    ];

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id', 'id');
    }
    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id', 'id');
    }

    public function scopeInitial($query)
    {
        return $query->where(['status' => 0]);
    }
    public function scopeCompleted($query)
    {
        return $query->where(['status' => 2]);
    }

    public function scopeInProgress($query)
    {
        return $query->where(['status' => 1]);
    }

    public function scopeCancel($query)
    {
        return $query->where(['status' => 3]);
    }

    public function scopeAccepted($query)
    {
        return $query->where(['status' => 4]);
    }

    // public function services()
    // {
    //     return $this->belongsTo(User::class, 'consultant_id', 'id');
    // }

    public function service()
    {
        return $this->belongsTo(Service::class,'service_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, $this->type . '_id', 'id');
    }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'citizen_id', 'id');
    // }
}
