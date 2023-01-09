<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicQualification extends Model
{
    use HasFactory;
    protected $fillable = [
        'education_level',
        'institute_name',
        'passing_year',
        'certification_copy',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
