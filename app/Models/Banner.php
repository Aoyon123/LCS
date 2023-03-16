<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'page_location',
        'image',
    ];

    public function scopeActiveBanner($query)
    {
        return $query->where(['status' => 1]);
    }
}
