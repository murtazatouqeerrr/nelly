<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaCourse extends Model
{
    protected $fillable = [
        'course_type',
        'delivery_type',
        'title',
        'description',
        'state_code',
        'min_pass_score',
        'total_duration',
        'price',
        'dicds_course_id',
        'certificate_template',
        'is_active',
        'copyright_protected',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'copyright_protected' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(UserCourseEnrollment::class, 'course_id');
    }
}
