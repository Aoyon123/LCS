<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'remark',
    ];

    protected $hidden = [
        'pivot',
        'created_at'
    ];

    public function consultants()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function scopeActiveServiceList($query)
    {
        return $query->where(['status' => 1]);
    }

}
