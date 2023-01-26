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
        'pivot'
    ];

    public function consultants()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

}
