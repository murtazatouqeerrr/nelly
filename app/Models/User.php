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
        'status'
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
