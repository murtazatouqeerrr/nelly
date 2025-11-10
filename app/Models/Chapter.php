<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    protected $fillable = [
        'course_id',
        'title',
        'content',
        'video_url',
        'order_index',
        'duration',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(FloridaCourse::class, 'course_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
