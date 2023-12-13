<?php

namespace App\Models;

use App\Models\AcademicQualification;
use App\Models\Conversation;
use App\Models\District;
use App\Models\Division;
use App\Models\Experience;
use App\Models\LcsCase;
use App\Models\Service;
use App\Models\Union;
use App\Models\Upazila;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    // protected $guarded = [];
    protected $fillable = ['name', 'phone', 'email', 'dob', 'division_id', 'district_id', 'upazila_id', 'union_id', 'type',
        'code', 'password', 'terms_conditions', 'approval', 'approved_by', 'otp_code',
        'nid', 'general_info', 'gender', 'profile_image', 'status', 'address', 'is_phone_verified',
        'years_of_experience', 'current_profession', 'nid_front', 'nid_back', 'rates', 'totalRating',
        'approval', 'approved_by', 'schedule', 'active_status', 'terms_conditions', 'cv_attachment',
        'device_log', 'serialize', 'fee', 'device_token','otp_update_date','otp_count', 'client_ip'
    ];
    protected $hidden = [
        'password',
        // 'otp_code',
        // 'a_password',
        'remember_token',
        'pivot',
    ];
    // protected $with = ['upazilas'];
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
    protected $casts = [
        'created_at' => "datetime:Y-m-d",
    ];

    public function getFullAddressAttribute()
    {
        // Remove " বিভাগ" from the division name_bn using str_replace
        $divisionName = str_replace(' বিভাগ', '', $this->division->name_bn);

        // Concatenate the modified division name_bn and other address components with spaces
        return "{$this->address}, {$this->unions->name_bn}, {$this->upazilas->name_bn}, {$this->districts->name_bn}, {$divisionName}।";
    }
    public function experiances()
    {
        return $this->hasMany(Experience::class, 'user_id');
    }

    // public function experianceLatest()
    // {
    //     // return $this->hasOne(Experience::class)->latest();
    // }
    public function experianceLatest()
    {
        return $this->hasOne(Experience::class)
            ->where('current_working', 1)
            ->latest();
        // ->orWhere(function ($query) {
        //     $query->where('current_working', 0)
        //         ->latest();
        // });
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
        return $this->belongsToMany(Service::class)->withTimestamps()->orderBy('id', 'asc');
    }

    public function citizenCases()
    {

        return $this->hasMany(LcsCase::class, 'citizen_id', 'id');
        // ->where('status', 2);
    }

    public function citizenCompleteCases()
    {
        return $this->hasMany(LcsCase::class, 'citizen_id', 'id')
            ->where('status', 2);
    }

    public function consultation()
    {
        return $this->belongsTo(LcsCase::class, 'id', 'consultant_id')->where('status', 2);
    }

    public function initialConsultation()
    {
        return $this->hasMany(LcsCase::class, 'consultant_id')->where('status', 0);
    }

    public function inprogressConsultation()
    {
        return $this->hasMany(LcsCase::class, 'consultant_id')->where('status', 1);
    }

    public function newMessageCount()
    {
        return $this->hasMany(Conversation::class, 'receiver_id')
            ->where('seen_status', 0)
            ->distinct('purpose_id');
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
        return $query->where(['approval' => 2]);
    }

    public function scopeDeactivated($query)
    {
        return $query->where(['approval' => 3]);
    }

    public function scopeApproval($query)
    {
        return $query->where(['approval' => 1]);
    }
    public function scopeCitizen($query)
    {
        return $query->where(['type' => 'citizen']);
    }

    public function scopePhoneVerified($query)
    {
        return $query->where('is_phone_verified', 1);
    }

    public function scopeConsultant($query)
    {
        return $query->where(['type' => 'consultant']);
    }

    public function scopeServiceList($query)
    {
        return $this->belongsToMany(Service::class)->where(['status' => 1]);
    }

    public function emptyInstituteName()
    {
        return $this->hasMany(Experience::class, 'user_id')->where('institute_name', '!=', "");
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function districts()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function upazilas()
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function unions()
    {
        return $this->belongsTo(Union::class, 'union_id');
    }
    public static function boot()
    {
        parent::boot();

        // if (request()->password) {
        self::creating(function (User $user) {
            // $user->a_password = request()->password;
            $encryptedPassword = crypt::encryptString(request()->password);
            $user->a_password = $encryptedPassword;

        });
        //}
        self::updating(function (User $user) {
            if (request()->password) {
                // $user->a_password = request()->password;
                $encryptedPassword = crypt::encryptString(request()->password);
                $user->a_password = $encryptedPassword;
            }

            if (request()->new_password) {
                // $user->a_password = request()->new_password;
                $encryptedPassword = crypt::encryptString(request()->new_password);
                $user->a_password = $encryptedPassword;
            }
        });
    }

}
