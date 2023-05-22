<?php

namespace App\Models;

use App\Models\LcsCase;
use App\Models\Service;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

   // protected $guarded = [];
    protected $fillable =['id','name','phone','email','dob','district_id','type',
            'code','password','terms_conditions','approval','approved_by','otp_code',
           'nid','general_info','gender','profile_image','status','address','is_phone_verified',
           'years_of_experience','current_profession','nid_front','nid_back','rates','totalRating',
           'approval','approved_by','schedule','active_status','terms_conditions'
];
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
    ];

    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
    protected $casts = [
        'created_at' => "datetime:Y-m-d",
    ];

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

    public function ratingList()
    {
        return $this->hasMany(LcsCase::class, 'consultant_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withTimestamps();
    }

    public function serviceLatest()
    {
        return $this->belongsToMany(Service::class)->where(['status' => 1])
            ->select(array('services.title', 'services.id', 'services.created_at'))->latest();
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

    public function scopeInitial($query)
    {
        return $query->where(['approval' => 0]);
    }

    public function scopeRejected($query)
    {
        return $query->where(['approval' =>  2]);
    }

    public function scopeApproval($query)
    {
        return $query->where(['approval' => 1]);
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

    public function emptyInstituteName(){
        return $this->hasMany(Experience::class, 'user_id')->where('institute_name', '!=', "");
    }

    // public function scopeCitizen($query)
    // {
    //     return $query->where(['type' => 'citizen']);
    // }

}
