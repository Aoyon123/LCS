<?php

namespace App\Models;

use App\Models\Service;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'pivot'
    ];

    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];

    public function experiances()
    {
        return $this->hasMany(Experience::class, 'user_id');
    }
    public function experianceLatest()
    {
        return $this->hasOne(Experience::class)->latest();
    }
    public function academics()
    {
        return $this->hasMany(AcademicQualification::class);
    }
    public function academicLatest()
    {
        return $this->hasOne(AcademicQualification::class)->latest();
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }

    public function serviceLatest()
    {
        // return $this->services()->take(1);
        return $this->belongsToMany(Service::class)->select(array('services.title', 'services.id', 'services.created_at'))->latest();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeActive($query)
    {
        return $query->where(['type' => 'consultant', 'status' => 1, 'approval' => 2]);
    }

}
