<?php

namespace App\Http\Controllers;

use App\Models\UserCourseEnrollment;
use App\Models\Course;
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
            'enrolled_at' => now()
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
            'court_date' => 'nullable|date'
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
            
            if (!$course) {
                $course = DB::table('florida_courses')->where('id', $courseId)->first();
            }
        }
        
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }
        
        // Check if user is already enrolled
        $existingEnrollment = UserCourseEnrollment::where('user_id', auth()->id())
            ->where('course_id', $realCourseId)
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
            'amount_paid' => $course->price ?? 0,
            'payment_status' => 'pending',
            'citation_number' => $request->citation_number ?? null,
            'court_date' => $request->court_date ?? null,
            'enrolled_at' => now()
        ]);
        
        if ($request->wantsJson()) {
            return response()->json($enrollment, 201);
        }
        
        return redirect('/my-enrollments')->with('success', 'Successfully enrolled in course!');
    }
    
    public function myEnrollmentsWeb()
    {
        $enrollments = UserCourseEnrollment::where('user_id', auth()->id())->get();
        
        $enrollmentsWithCourses = $enrollments->map(function($enrollment) {
            // First try courses table
            $course = DB::table('courses')->where('id', $enrollment->course_id)->first();
            
            // If not found or if it's a generic course, try florida_courses
            if (!$course) {
                $course = DB::table('florida_courses')->where('id', $enrollment->course_id)->first();
            }
            
            // If we found a course in courses table, prefer it over florida_courses
            // Check if there's a Missouri course in courses table
            if ($course && isset($course->state) && $course->state === 'Missouri') {
                // This is likely the Missouri course from courses table
                $course = DB::table('courses')->where('id', $enrollment->course_id)->first();
            }
            
            $enrollmentData = $enrollment->toArray();
            $enrollmentData['course'] = $course;
            
            return $enrollmentData;
        });
            
        return response()->json($enrollmentsWithCourses);
    }
    
    public function showWeb(UserCourseEnrollment $enrollment)
    {
        // Ensure user can only access their own enrollments
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Load course from florida_courses table
        $course = DB::table('florida_courses')->where('id', $enrollment->course_id)->first();
        $enrollmentData = $enrollment->toArray();
        $enrollmentData['course'] = $course;
        
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
}
