<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceUser extends Model
{
    use HasFactory, SoftDeletes;

    public function consultant()
    {
        return $this->belongsTo(User::class, 'user_id', 'service_id');
    }


}
