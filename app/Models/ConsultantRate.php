<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'consultant_id',
        'rate',
        'against_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
