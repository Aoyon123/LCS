<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_name',
        'designation',
        'department',
        'start_date',
        'end_date',
        'current_working',
        'user_id'
    ];
}
