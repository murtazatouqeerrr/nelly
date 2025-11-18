<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'driver_license',
        'dicds_user_id',
        'dicds_password',
        'status',
        'mailing_address',
        'city',
        'state',
        'zip',
        'phone_1',
        'phone_2',
        'phone_3',
        'gender',
        'birth_month',
        'birth_day',
        'birth_year',
        'license_state',
        'license_class',
        'court_selected',
        'citation_number',
        'due_month',
        'due_day',
        'due_year',
        'security_q1',
        'security_q2',
        'security_q3',
        'security_q4',
        'security_q5',
        'security_q6',
        'security_q7',
        'security_q8',
        'security_q9',
        'security_q10',
        'agreement_name',
        'terms_agreement',
        'registration_completed_at',
        'account_locked',
        'lock_reason',
        'locked_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'dicds_password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function certificates()
    {
        return $this->hasManyThrough(
            FloridaCertificate::class,
            UserCourseEnrollment::class,
            'user_id',
            'enrollment_id',
            'id',
            'id'
        );
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function enrollments()
    {
        return $this->hasMany(UserCourseEnrollment::class);
    }
    
    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by');
    }
    
    public function scopeNotLocked($query)
    {
        return $query->where('account_locked', false);
    }
    
    // JWT methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
