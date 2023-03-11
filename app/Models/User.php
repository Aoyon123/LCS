<?php

namespace App\Models;

use App\Models\LcsCase;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use illuminate\Database\Eloquent\SoftDeletes;
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
        'pivot',
        // 'otp_code'
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
        return $this->belongsToMany(Service::class)->where(['status' => 1])->select(array('services.title', 'services.id', 'services.created_at'))->latest();
    }

    public function countRating()
    {
        return $this->hasMany(LcsCase::class,'consultant_id')
                    ->where('status',2);
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeActive($query)
    {
        return $query->where(['active_status' => 1]);
    }

    public function scopeStatus($query)
    {
        return $query->where(['status' => 1]);
    }

    public function scopeApproval($query)
    {
        return $query->where(['approval' => 2]);
    }
    public function scopeCitizen($query)
    {
        return $query->where(['type' => 'citizen']);
    }

    public function scopeConsultant($query)
    {
        return $query->where(['type' => 'consultant']);
    }

    public function scopeServiceList($query)
    {
        return $this->belongsToMany(Service::class)->where(['status' => 1]);
    }

    // public function scopeCountRating($query)
    // {
    //     return $this->belongsToMany(LcsCase::class)->select(DB::raw('count(rating) as total'))->get();
    // }

// public function lcsCases()
// {
//     return $this->belongsToMany(LcsCase::class)->withTimestamps();
// }

}
