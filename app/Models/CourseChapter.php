<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseChapter extends Model
{
    protected $fillable = [
        'course_id',
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

    public function course()
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }
}
