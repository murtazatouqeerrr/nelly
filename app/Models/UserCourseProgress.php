<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCourseProgress extends Model
{
    protected $fillable = [
        'enrollment_id',
        'chapter_id',
        'started_at',
        'completed_at',
        'time_spent',
        'is_completed',
        'last_accessed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'is_completed' => 'boolean'
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function chapter(): BelongsTo
    {
        // Try CourseChapter first, then Chapter
        $courseChapter = CourseChapter::find($this->chapter_id);
        if ($courseChapter) {
            return $this->belongsTo(CourseChapter::class, 'chapter_id');
        }
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
    
    public function courseChapter(): BelongsTo
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id');
    }
}
