<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChapterQuestion extends Model
{
    protected $fillable = [
        'chapter_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'order_index',
    ];

    protected $casts = [
        'points' => 'integer',
        'order_index' => 'integer',
    ];

    public function chapter()
    {
        return $this->belongsTo(CourseChapter::class, 'chapter_id');
    }
}
