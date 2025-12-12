<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'state',
        'duration',
        'price',
        'passing_score',
        'is_active',
        'course_type',
        'delivery_type',
        'certificate_type',
    ];

    const COURSE_TYPES = ['BDI', 'ADI', 'TLSAE'];

    const DELIVERY_TYPES = ['In Person', 'Internet', 'CD-Rom', 'Video', 'DVD'];

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function schoolCourses()
    {
        return $this->hasMany(SchoolCourse::class);
    }

    public function instructorCourses()
    {
        return $this->hasMany(InstructorCourse::class);
    }
}

class SchoolCourse extends Model
{
    protected $table = 'dicds_school_courses';

    protected $fillable = ['school_id', 'course_id', 'status', 'status_date'];

    protected $casts = ['status_date' => 'date'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

class Certificate extends Model
{
    protected $table = 'dicds_certificates';

    protected $fillable = [
        'order_number', 'course_id', 'school_id', 'provider_id',
        'certificate_count', 'status', 'total_amount', 'student_name',
        'certificate_number', 'issued_at',
    ];

    protected $casts = ['issued_at' => 'datetime'];

    const STATUSES = ['Pending', 'Active', 'Issued'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function provider()
    {
        return $this->belongsTo(DicdsUser::class, 'provider_id');
    }
}
