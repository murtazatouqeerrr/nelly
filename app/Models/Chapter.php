<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $fillable = [
        'course_id',
        'course_table',
        'title',
        'content',
        'video_url',
        'order_index',
        'duration',
        'required_min_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        // Dynamically determine which course table to use
        if ($this->course_table === 'florida_courses') {
            return $this->belongsTo(FloridaCourse::class, 'course_id');
        }
        
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the course regardless of which table it's in
     */
    public function getCourseAttribute()
    {
        if ($this->course_table === 'florida_courses') {
            return FloridaCourse::find($this->course_id);
        }
        
        return Course::find($this->course_id);
    }
}
