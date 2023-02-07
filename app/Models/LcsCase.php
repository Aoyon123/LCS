<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LcsCase extends Model
{
    use HasFactory;

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
        'case_code'
    ];
}
