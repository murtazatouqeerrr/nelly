<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('creator');
        
        if ($request->state_code) {
            $query->where('state_code', $request->state_code);
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'state_code' => 'required|string|size:2',
            'min_pass_score' => 'required|integer|min:0|max:100',
            'total_duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'certificate_template' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $validated['created_by'] = auth()->id();
        
        return response()->json(Course::create($validated), 201);
    }

    public function show(Course $course)
    {
        return response()->json($course->load(['chapters', 'creator']));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'state_code' => 'required|string|size:2',
            'min_pass_score' => 'required|integer|min:0|max:100',
            'total_duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'certificate_template' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $course->update($validated);
        
        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();
        
        return response()->json(['message' => 'Course deleted successfully']);
    }
    
    // Web-specific methods for session authentication
    public function storeWeb(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'state_code' => 'required|string|size:2',
                'min_pass_score' => 'required|integer|min:0|max:100',
                'total_duration' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'certificate_template' => 'nullable|string',
                'is_active' => 'boolean'
            ]);
            
            $validated['course_type'] = 'BDI';
            $validated['delivery_type'] = 'internet';
            $validated['dicds_course_id'] = 'BDI-' . time();
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['copyright_protected'] = true;
            
            $course = \App\Models\FloridaCourse::create($validated);
            
            if ($request->wantsJson()) {
                return response()->json($course, 201);
            }
            
            return redirect('/courses')->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            \Log::error('Course storeWeb error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function indexWeb(Request $request)
    {
        try {
            \Log::info('CourseController indexWeb called', ['request' => $request->all()]);
            
            // Fetch from both tables
            $floridaCourses = DB::table('florida_courses')
                ->when($request->state_code, function($query, $state) {
                    return $query->where('state', $state);
                })
                ->when($request->has('is_active'), function($query) use ($request) {
                    return $query->where('is_active', $request->is_active);
                })
                ->when($request->search, function($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%');
                })
                ->get();

            $regularCourses = DB::table('courses')
                ->when($request->state_code, function($query, $state) {
                    return $query->where('state', $state);
                })
                ->when($request->has('is_active'), function($query) use ($request) {
                    return $query->where('is_active', $request->is_active);
                })
                ->when($request->search, function($query, $search) {
                    return $query->where('title', 'like', '%' . $search . '%');
                })
                ->get();

            // Combine and format courses
            $allCourses = collect();
            
            foreach ($floridaCourses as $course) {
                $allCourses->push([
                    'id' => $course->id, // Use numeric ID only
                    'real_id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'state_code' => $course->state,
                    'total_duration' => $course->duration,
                    'price' => $course->price,
                    'passing_score' => $course->passing_score,
                    'is_active' => $course->is_active,
                    'course_type' => $course->course_type,
                    'certificate_type' => $course->certificate_type,
                    'table' => 'florida_courses'
                ]);
            }

            foreach ($regularCourses as $course) {
                $allCourses->push([
                    'id' => $course->id, // Use numeric ID only
                    'real_id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'state_code' => $course->state,
                    'total_duration' => $course->duration,
                    'price' => $course->price,
                    'passing_score' => $course->passing_score,
                    'is_active' => $course->is_active,
                    'course_type' => $course->course_type,
                    'certificate_type' => $course->certificate_type,
                    'table' => 'courses'
                ]);
            }

            \Log::info('CourseController indexWeb success', ['courses_count' => $allCourses->count()]);
            return response()->json($allCourses);
        } catch (\Exception $e) {
            \Log::error('Course indexWeb error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function updateWeb(Request $request, $id)
    {
        try {
            $course = \App\Models\FloridaCourse::findOrFail($id);
            
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'state_code' => 'sometimes|string|size:2',
                'min_pass_score' => 'sometimes|integer|min:0|max:100',
                'total_duration' => 'sometimes|integer|min:1',
                'price' => 'sometimes|numeric|min:0',
                'certificate_template' => 'nullable|string',
                'is_active' => 'sometimes|boolean'
            ]);
            
            $course->update($validated);
            
            return response()->json($course);
        } catch (\Exception $e) {
            \Log::error('Course updateWeb error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
