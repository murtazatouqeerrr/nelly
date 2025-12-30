<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'course']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $course = Course::findOrFail($request->course_id);

        // Check if user is already enrolled
        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingEnrollment) {
            return response()->json(['error' => 'Already enrolled in this course'], 400);
        }

        $enrollment = UserCourseEnrollment::create([
            'user_id' => auth()->id(),
            'course_id' => $request->course_id,
            'amount_paid' => $course->price,
            'payment_status' => 'pending',
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'enrolled_at' => now(),
        ]);

        return response()->json($enrollment->load('course'), 201);
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        return response()->json($enrollment->load(['user', 'course', 'progress', 'quizAttempts']));
    }

    public function update(Request $request, UserCourseEnrollment $enrollment)
    {
        $validated = $request->validate([
            'payment_status' => 'in:pending,paid,failed,refunded',
            'status' => 'in:active,completed,expired,cancelled',
            'court_date' => 'nullable|date',
        ]);

        $enrollment->update($validated);

        return response()->json($enrollment);
    }

    public function destroy(UserCourseEnrollment $enrollment)
    {
        $enrollment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Enrollment cancelled successfully']);
    }

    public function myEnrollments()
    {
        $enrollments = UserCourseEnrollment::with(['course', 'progress'])
            ->where('user_id', auth()->id())
            ->get();

        return response()->json($enrollments);
    }

    // Web-specific methods for session authentication
    public function storeWeb(Request $request)
    {
        $courseId = $request->course_id;
        $course = null;
        $realCourseId = null;

        // Handle prefixed course IDs
        if (str_starts_with($courseId, 'florida_')) {
            $realCourseId = str_replace('florida_', '', $courseId);
            $course = DB::table('florida_courses')->where('id', $realCourseId)->first();
        } elseif (str_starts_with($courseId, 'courses_')) {
            $realCourseId = str_replace('courses_', '', $courseId);
            $course = DB::table('courses')->where('id', $realCourseId)->first();
        } else {
            // Fallback for numeric IDs - try courses table first
            $course = DB::table('courses')->where('id', $courseId)->first();
            $realCourseId = $courseId;

            if (! $course) {
                $course = DB::table('florida_courses')->where('id', $courseId)->first();
            }
        }

        if (! $course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        // Determine course table
        $courseTable = 'courses';
        if (str_starts_with($courseId, 'florida_') || DB::table('florida_courses')->where('id', $realCourseId)->exists()) {
            $courseTable = 'florida_courses';
        }

        // Check if user is already enrolled
        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $realCourseId)
            ->where('course_table', $courseTable)
            ->first();

        if ($existingEnrollment) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Already enrolled in this course'], 400);
            }

            return redirect()->back()->with('error', 'Already enrolled in this course');
        }

        $enrollment = UserCourseEnrollment::create([
            'user_id' => auth()->id(),
            'course_id' => $realCourseId,
            'course_table' => $courseTable,
            'amount_paid' => $course->price ?? 0,
            'payment_status' => 'pending',
            'citation_number' => $request->citation_number ?? null,
            'court_date' => $request->court_date ?? null,
            'enrolled_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json($enrollment, 201);
        }

        return redirect('/my-enrollments')->with('success', 'Successfully enrolled in course!');
    }

    public function myEnrollmentsWeb()
    {
        try {
            $enrollments = UserCourseEnrollment::where('user_id', auth()->id())
                ->where('status', '!=', 'cancelled') // Don't show cancelled enrollments
                ->orderBy('created_at', 'desc')
                ->get();

            $enrollmentsWithCourses = $enrollments->map(function ($enrollment) {
                // Use course_table column to determine which table to fetch from
                $courseTable = $enrollment->course_table ?? 'florida_courses';
                
                $course = DB::table($courseTable)->where('id', $enrollment->course_id)->first();

                // Fallback to other table if not found
                if (! $course) {
                    $fallbackTable = $courseTable === 'courses' ? 'florida_courses' : 'courses';
                    $course = DB::table($fallbackTable)->where('id', $enrollment->course_id)->first();
                    
                    // Update the course_table if we found it in the fallback table
                    if ($course) {
                        $enrollment->update(['course_table' => $fallbackTable]);
                    }
                }

                $enrollmentData = $enrollment->toArray();
                $enrollmentData['course'] = $course;

                // Add computed fields for better UI
                $enrollmentData['can_access_course'] = $enrollment->payment_status === 'paid';
                $enrollmentData['needs_payment'] = in_array($enrollment->payment_status, ['pending', 'failed']);
                $enrollmentData['is_completed'] = $enrollment->status === 'completed';
                $enrollmentData['progress_percentage'] = $enrollment->progress_percentage ?? 0;

                return $enrollmentData;
            });

            return response()->json($enrollmentsWithCourses);
            
        } catch (\Exception $e) {
            \Log::error('Error in myEnrollmentsWeb: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Unable to load enrollments'], 500);
        }
    }

    public function checkEnrollment(Request $request)
    {
        $request->validate([
            'course_id' => 'required|integer',
            'table' => 'required|in:courses,florida_courses'
        ]);

        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $request->course_id)
            ->where('course_table', $request->table)
            ->first();

        return response()->json([
            'already_enrolled' => $existingEnrollment !== null,
            'enrollment_id' => $existingEnrollment ? $existingEnrollment->id : null
        ]);
    }

    public function showWeb(UserCourseEnrollment $enrollment)
    {
        // Ensure user can only access their own enrollments
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Cache course data for 10 minutes to improve performance
        $cacheKey = "enrollment_course_data_{$enrollment->id}_{$enrollment->course_id}_{$enrollment->course_table}";
        
        $courseData = cache()->remember($cacheKey, 600, function () use ($enrollment) {
            $courseTable = $enrollment->course_table ?? 'courses';
            
            // Use Eloquent for better performance and relationships
            if ($courseTable === 'florida_courses') {
                $course = \App\Models\FloridaCourse::with(['chapters' => function($query) {
                    $query->orderBy('chapter_order')->select('id', 'course_id', 'title', 'chapter_order', 'content');
                }])->find($enrollment->course_id);
            } else {
                $course = \App\Models\Course::with(['chapters' => function($query) {
                    $query->orderBy('chapter_order')->select('id', 'course_id', 'title', 'chapter_order', 'content');
                }])->find($enrollment->course_id);
            }
            
            return $course;
        });
        
        if (!$courseData) {
            return response()->json(['error' => 'Course not found'], 404);
        }
        
        $enrollmentData = $enrollment->toArray();
        $enrollmentData['course'] = $courseData->toArray();
        // Ensure strict_duration_enabled is included
        if (!isset($enrollmentData['course']['strict_duration_enabled'])) {
            $enrollmentData['course']['strict_duration_enabled'] = $courseData->strict_duration_enabled ?? false;
        }

        return response()->json($enrollmentData);
    }

    public function indexWeb(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'course']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return response()->json($query->get());
    }

    public function cancelEnrollmentWeb(UserCourseEnrollment $enrollment)
    {
        try {
            // Ensure user can only cancel their own enrollments
            if ($enrollment->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Only allow cancellation of pending payments
            if ($enrollment->payment_status === 'paid') {
                return response()->json(['error' => 'Cannot cancel paid enrollments'], 400);
            }

            // Update enrollment status to cancelled (keep payment_status as is since 'cancelled' is not valid)
            $enrollment->update([
                'status' => 'cancelled'
                // Don't update payment_status to 'cancelled' as it's not a valid enum value
            ]);

            return response()->json(['message' => 'Enrollment cancelled successfully']);
            
        } catch (\Exception $e) {
            \Log::error('Error cancelling enrollment: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'enrollment_id' => $enrollment->id
            ]);
            
            return response()->json(['error' => 'Unable to cancel enrollment'], 500);
        }
    }
}
