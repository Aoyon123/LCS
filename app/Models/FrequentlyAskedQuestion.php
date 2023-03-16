<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrequentlyAskedQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'question',
        'answer',
        'answer_image',
        'status',
    ];

    public function scopeActiveFrequentlyAskedQuestion($query)
    {
        return $query->where(['status' => 1]);
    }
    public function scopeCitizenCategory($query)
    {
        return $query->where(['category_name' => 'citizen']);
    }

    public function scopeConsultantCategory($query)
    {
        return $query->where(['category_name' => 'consultant']);
    }
}
