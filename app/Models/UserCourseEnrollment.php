<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCourseEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'payment_status',
        'amount_paid',
        'payment_method',
        'payment_id',
        'citation_number',
        'court_date',
        'enrolled_at',
        'started_at',
        'completed_at',
        'progress_percentage',
        'total_time_spent',
        'status',
        'access_revoked',
        'access_revoked_at'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'court_date' => 'date',
        'amount_paid' => 'decimal:2',
        'payment_status' => 'string',
        'status' => 'string'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }
    
    public function floridaCourse(): BelongsTo
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }
    
    public function legacyCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(UserCourseProgress::class, 'enrollment_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'enrollment_id');
    }
    
    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'enrollment_id');
    }
    
    public function floridaCertificate()
    {
        return $this->hasOne(FloridaCertificate::class, 'enrollment_id');
    }
}
