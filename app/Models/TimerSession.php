<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimerSession extends Model
{
    protected $fillable = [
        'user_id',
        'course_timer_id',
        'chapter_id',
        'started_at',
        'ended_at',
        'completed_at',
        'time_spent_seconds',
        'duration_minutes',
        'session_token',
        'is_active',
        'is_completed',
        'bypassed_by_admin'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'bypassed_by_admin' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timer()
    {
        return $this->belongsTo(CourseTimer::class, 'course_timer_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
