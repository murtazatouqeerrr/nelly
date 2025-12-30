<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        \Log::info('=== Courses API START ===');
        
        // Try florida_courses first since it has data
        $floridaCount = DB::table('florida_courses')->count();
        $coursesCount = DB::table('courses')->count();
        
        \Log::info('florida_courses count: ' . $floridaCount);
        \Log::info('courses count: ' . $coursesCount);
        
        if ($floridaCount > 0) {
            \Log::info('Using florida_courses table');
            // Explicitly select all columns including strict_duration_enabled
            $query = DB::table('florida_courses')->select(
                'id', 'title', 'description', 'state_code', 'min_pass_score', 
                'total_duration', 'price', 'certificate_template', 'is_active',
                'strict_duration_enabled', 'created_at', 'updated_at'
            );
        } else {
            \Log::info('Using courses table');
            $query = Course::query();
        }

        if ($request->state_code) {
            $query->where('state_code', $request->state_code);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $result = $query->get();
        \Log::info('Courses loaded: ' . $result->count());
        
        // Log first course data
        if ($result->count() > 0) {
            \Log::info('First course data: ' . json_encode($result[0]));
            \Log::info('strict_duration_enabled value: ' . ($result[0]->strict_duration_enabled ?? 'NULL'));
        }
        
        \Log::info('=== Courses API END ===');
        
        return response()->json($result);
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
            'is_active' => 'boolean',
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
            'is_active' => 'boolean',
        ]);

        $course->update($validated);

        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    // Public course listing for all authenticated users
    public function publicIndex(Request $request)
    {
        try {
            // Fetch only active courses from both tables
            $floridaCourses = DB::table('florida_courses')
                ->where('is_active', true)
                ->when($request->state_code, function ($query, $state) {
                    return $query->where('state', $state);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', '%'.$search.'%');
                })
                ->get();

            $regularCourses = DB::table('courses')
                ->where('is_active', true)
                ->when($request->state_code, function ($query, $state) {
                    return $query->where('state_code', $state);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', '%'.$search.'%');
                })
                ->get();

            // Combine and format courses for public view
            $allCourses = collect();

            foreach ($floridaCourses as $course) {
                $allCourses->push([
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'state_code' => $course->state,
                    'total_duration' => $course->duration,
                    'price' => $course->price,
                    'course_type' => $course->course_type ?? 'BDI',
                    'table' => 'florida_courses',
                ]);
            }

            foreach ($regularCourses as $course) {
                $allCourses->push([
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'state_code' => $course->state_code,
                    'total_duration' => $course->total_duration,
                    'price' => $course->price,
                    'course_type' => 'Regular',
                    'table' => 'courses',
                ]);
            }

            return response()->json($allCourses);
        } catch (\Exception $e) {
            \Log::error('Course publicIndex error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load courses'], 500);
        }
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
                'is_active' => 'boolean',
            ]);

            // Map form fields to actual database columns
            $courseData = [
                'title' => $validated['title'],
                'description' => $validated['description'],
                'state_code' => $validated['state_code'],
                'passing_score' => $validated['min_pass_score'],
                'duration' => $validated['total_duration'],
                'price' => $validated['price'],
                'certificate_type' => $validated['certificate_template'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'course_type' => 'BDI',
            ];

            $course = \App\Models\FloridaCourse::create($courseData);

            if ($request->wantsJson()) {
                return response()->json($course, 201);
            }

            return redirect('/courses')->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            \Log::error('Course storeWeb error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function indexWeb(Request $request)
    {
        try {
            \Log::info('CourseController indexWeb called', ['request' => $request->all()]);

            // Fetch from both tables
            $floridaCourses = DB::table('florida_courses')
                ->when($request->state_code, function ($query, $state) {
                    return $query->where(function ($q) use ($state) {
                        $q->where('state_code', $state)->orWhere('state', $state);
                    });
                })
                ->when($request->has('is_active'), function ($query) use ($request) {
                    return $query->where('is_active', $request->is_active);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', '%'.$search.'%');
                })
                ->get();

            $regularCourses = DB::table('courses')
                ->when($request->state_code, function ($query, $state) {
                    return $query->where(function ($q) use ($state) {
                        $q->where('state_code', $state)->orWhere('state', $state);
                    });
                })
                ->when($request->has('is_active'), function ($query) use ($request) {
                    return $query->where('is_active', $request->is_active);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', '%'.$search.'%');
                })
                ->get();

            // Combine and format courses
            $allCourses = collect();

            foreach ($floridaCourses as $course) {
                $allCourses->push([
                    'id' => $course->id,
                    'real_id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description ?? '',
                    'state_code' => $course->state_code ?? $course->state ?? 'FL',
                    'total_duration' => $course->total_duration ?? $course->duration ?? 0,
                    'duration' => $course->duration ?? 0,
                    'price' => $course->price ?? 0,
                    'passing_score' => $course->min_pass_score ?? $course->passing_score ?? 80,
                    'is_active' => $course->is_active ?? true,
                    'course_type' => $course->course_type ?? 'BDI',
                    'certificate_type' => $course->certificate_template ?? $course->certificate_type ?? null,
                    'table' => 'florida_courses',
                ]);
            }

            foreach ($regularCourses as $course) {
                $allCourses->push([
                    'id' => $course->id,
                    'real_id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description ?? '',
                    'state_code' => $course->state ?? 'FL',
                    'total_duration' => $course->duration ?? 0,
                    'duration' => $course->duration ?? 0,
                    'price' => $course->price ?? 0,
                    'passing_score' => $course->passing_score ?? 80,
                    'is_active' => $course->is_active ?? true,
                    'course_type' => $course->course_type ?? 'Regular',
                    'certificate_type' => $course->certificate_type ?? null,
                    'table' => 'courses',
                ]);
            }

            \Log::info('CourseController indexWeb success', ['courses_count' => $allCourses->count()]);

            return response()->json($allCourses);
        } catch (\Exception $e) {
            \Log::error('Course indexWeb error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateWeb(Request $request, $id)
    {
        try {
            // First try to find in florida_courses
            $course = \App\Models\FloridaCourse::find($id);
            $isFloridaCourse = true;
            
            // If not found, try regular courses table
            if (!$course) {
                $course = \App\Models\Course::find($id);
                $isFloridaCourse = false;
            }
            
            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'state_code' => 'sometimes|string|max:50',
                'min_pass_score' => 'sometimes|integer|min:0|max:100',
                'total_duration' => 'sometimes|integer|min:1',
                'price' => 'sometimes|numeric|min:0',
                'certificate_template' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            // Map fields for regular courses table if needed
            if (!$isFloridaCourse) {
                if (isset($validated['min_pass_score'])) {
                    $validated['passing_score'] = $validated['min_pass_score'];
                    unset($validated['min_pass_score']);
                }
                if (isset($validated['total_duration'])) {
                    $validated['duration'] = $validated['total_duration'];
                    unset($validated['total_duration']);
                }
                // Also update the state field for backward compatibility
                if (isset($validated['state_code'])) {
                    $validated['state'] = $validated['state_code'];
                }
            }

            $course->update($validated);

            return response()->json($course);
        } catch (\Exception $e) {
            \Log::error('Course updateWeb error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyWeb($id)
    {
        try {
            // First try to find in florida_courses
            $course = \App\Models\FloridaCourse::find($id);
            $isFloridaCourse = true;
            
            // If not found, try regular courses table
            if (!$course) {
                $course = \App\Models\Course::find($id);
                $isFloridaCourse = false;
            }
            
            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }

            // Delete related data first
            \DB::table('questions')->where('course_id', $id)->delete();
            \DB::table('chapters')->where('course_id', $id)->delete();
            
            // Delete the course
            $course->delete();

            return response()->json(['message' => 'Course deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Course destroyWeb error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function showDetails($table, $courseId)
    {
        try {
            \Log::info('Course showDetails called', ['table' => $table, 'courseId' => $courseId]);
            
            if ($table === 'courses') {
                $course = Course::find($courseId);
                \Log::info('Looking for course in courses table', ['found' => $course ? 'yes' : 'no']);
            } elseif ($table === 'florida_courses') {
                $course = \App\Models\FloridaCourse::find($courseId);
                \Log::info('Looking for course in florida_courses table', ['found' => $course ? 'yes' : 'no']);
            } else {
                \Log::error('Invalid course table', ['table' => $table]);
                abort(404, 'Invalid course type');
            }

            if (! $course) {
                \Log::error('Course not found', ['table' => $table, 'courseId' => $courseId]);
                abort(404, 'Course not found');
            }

            \Log::info('Course found', ['course' => $course->toArray()]);

            // Normalize course data to ensure consistent field names
            $normalizedCourse = (object) [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'state_code' => $course->state_code ?? $course->state ?? 'FL',
                'total_duration' => $course->total_duration ?? $course->duration ?? 240,
                'duration' => $course->duration ?? $course->total_duration ?? 240,
                'price' => $course->price ?? 0,
                'min_pass_score' => $course->min_pass_score ?? $course->passing_score ?? 80,
                'passing_score' => $course->passing_score ?? $course->min_pass_score ?? 80,
                'course_type' => $course->course_type ?? 'BDI',
                'is_active' => $course->is_active ?? true,
                'table' => $table,
            ];

            \Log::info('Normalized course data', ['normalizedCourse' => (array) $normalizedCourse]);

            // Fetch reviews normally
            $reviews = \App\Models\Review::with('user')->where('course_name', $course->title)->get();

            return view('course-details', [
                'course' => $normalizedCourse,
                'reviews' => $reviews,
                'avgRating' => round($reviews->avg('rating') ?? 0, 1),
                'totalReviews' => $reviews->count(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Course showDetails error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            abort(404, 'Course not found');
        }
    }

    public function copy(Request $request)
    {
        try {
            $validated = $request->validate([
                'source_course_id' => 'required|integer',
                'source_table' => 'required|string|in:courses,florida_courses',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'state_code' => 'required|string|size:2',
                'min_pass_score' => 'required|integer|min:0|max:100',
                'total_duration' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'certificate_template' => 'nullable|string',
                'is_active' => 'boolean',
                'copy_options' => 'required|array',
                'copy_options.chapters' => 'boolean',
                'copy_options.questions' => 'boolean',
                'copy_options.final_exam' => 'boolean',
            ]);

            DB::beginTransaction();

            // Get source course from appropriate table
            if ($validated['source_table'] === 'florida_courses') {
                $sourceCourse = DB::table('florida_courses')->where('id', $validated['source_course_id'])->first();
                if (!$sourceCourse) {
                    throw new \Exception('Source course not found');
                }
            } else {
                $sourceCourse = Course::findOrFail($validated['source_course_id']);
            }

            // Create new course in regular courses table
            $newCourse = Course::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'state_code' => $validated['state_code'],
                'min_pass_score' => $validated['min_pass_score'],
                'total_duration' => $validated['total_duration'],
                'price' => $validated['price'],
                'certificate_template' => $validated['certificate_template'],
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            // Copy chapters if requested
            if ($validated['copy_options']['chapters']) {
                $sourceChapters = DB::table('chapters')
                    ->where('course_id', $sourceCourse->id)
                    ->orderBy('order_index')
                    ->get();

                foreach ($sourceChapters as $chapter) {
                    $newChapterId = DB::table('chapters')->insertGetId([
                        'course_id' => $newCourse->id,
                        'title' => $chapter->title,
                        'content' => $chapter->content,
                        'duration' => $chapter->duration,
                        'order_index' => $chapter->order_index,
                        'video_url' => $chapter->video_url,
                        'course_table' => 'courses', // Set the course table for the new chapter
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Copy chapter questions if requested
                    if ($validated['copy_options']['questions']) {
                        $chapterQuestions = DB::table('questions')
                            ->where('chapter_id', $chapter->id)
                            ->get();

                        foreach ($chapterQuestions as $question) {
                            DB::table('questions')->insert([
                                'chapter_id' => $newChapterId,
                                'course_id' => $newCourse->id,
                                'question_text' => $question->question_text,
                                'question_type' => $question->question_type,
                                'options' => $question->options,
                                'correct_answer' => $question->correct_answer,
                                'explanation' => $question->explanation,
                                'points' => $question->points,
                                'order_index' => $question->order_index,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // Copy final exam questions if requested
            if ($validated['copy_options']['final_exam']) {
                $finalExamQuestions = DB::table('final_exam_questions')
                    ->where('course_id', $sourceCourse->id)
                    ->get();

                foreach ($finalExamQuestions as $question) {
                    DB::table('final_exam_questions')->insert([
                        'course_id' => $newCourse->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'options' => $question->options,
                        'correct_answer' => $question->correct_answer,
                        'explanation' => $question->explanation,
                        'points' => $question->points,
                        'order_index' => $question->order_index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Course copied successfully',
                'course' => $newCourse
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Course copy error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to copy course: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStrictDuration(Request $request)
    {
        try {
            \Log::info('╔════════════════════════════════════════╗');
            \Log::info('║ toggleStrictDuration START             ║');
            \Log::info('╚════════════════════════════════════════╝');
            
            \Log::info('STEP 1: Received request');
            \Log::info('Request method: ' . $request->method());
            \Log::info('Request URL: ' . $request->fullUrl());
            \Log::info('Request data: ' . json_encode($request->all()));
            \Log::info('Request headers: ' . json_encode($request->headers->all()));
            
            \Log::info('STEP 2: Validating input');
            $validated = $request->validate([
                'strict_duration_enabled' => 'required|boolean'
            ]);
            \Log::info('Validation passed');
            \Log::info('Validated value: ' . ($validated['strict_duration_enabled'] ? 'TRUE' : 'FALSE'));
            
            \Log::info('STEP 3: Checking database connection');
            try {
                $connection = DB::connection()->getPdo();
                \Log::info('Database connection: OK');
            } catch (\Exception $e) {
                \Log::error('Database connection FAILED: ' . $e->getMessage());
                throw $e;
            }
            
            \Log::info('STEP 4: Checking table existence');
            $coursesTableExists = DB::getSchemaBuilder()->hasTable('courses');
            $floridaTableExists = DB::getSchemaBuilder()->hasTable('florida_courses');
            \Log::info('courses table exists: ' . ($coursesTableExists ? 'YES' : 'NO'));
            \Log::info('florida_courses table exists: ' . ($floridaTableExists ? 'YES' : 'NO'));
            
            \Log::info('STEP 5: Checking column existence');
            $coursesHasColumn = DB::getSchemaBuilder()->hasColumn('courses', 'strict_duration_enabled');
            $floridaHasColumn = DB::getSchemaBuilder()->hasColumn('florida_courses', 'strict_duration_enabled');
            \Log::info('courses.strict_duration_enabled exists: ' . ($coursesHasColumn ? 'YES' : 'NO'));
            \Log::info('florida_courses.strict_duration_enabled exists: ' . ($floridaHasColumn ? 'YES' : 'NO'));
            
            \Log::info('STEP 6: Counting rows in tables');
            $coursesCount = DB::table('courses')->count();
            $floridaCount = DB::table('florida_courses')->count();
            \Log::info('courses table row count: ' . $coursesCount);
            \Log::info('florida_courses table row count: ' . $floridaCount);
            
            \Log::info('STEP 7: Updating courses table');
            if ($coursesTableExists && $coursesHasColumn && $coursesCount > 0) {
                \Log::info('Attempting to update courses table...');
                $coursesUpdated = DB::table('courses')->update(['strict_duration_enabled' => $validated['strict_duration_enabled']]);
                \Log::info('Courses updated: ' . $coursesUpdated . ' rows');
                
                $coursesVerify = DB::table('courses')->where('strict_duration_enabled', $validated['strict_duration_enabled'])->count();
                \Log::info('Verification - courses with new value: ' . $coursesVerify);
            } else {
                \Log::warning('Skipping courses table update - exists: ' . ($coursesTableExists ? 'Y' : 'N') . ', hasColumn: ' . ($coursesHasColumn ? 'Y' : 'N') . ', count: ' . $coursesCount);
            }
            
            \Log::info('STEP 8: Updating florida_courses table');
            if ($floridaTableExists && $floridaHasColumn && $floridaCount > 0) {
                \Log::info('Attempting to update florida_courses table...');
                
                // Check current values before update
                $currentTrue = DB::table('florida_courses')->where('strict_duration_enabled', true)->count();
                $currentFalse = DB::table('florida_courses')->where('strict_duration_enabled', false)->count();
                $currentNull = DB::table('florida_courses')->whereNull('strict_duration_enabled')->count();
                
                \Log::info('Current values - TRUE: ' . $currentTrue . ', FALSE: ' . $currentFalse . ', NULL: ' . $currentNull);
                
                $floridaUpdated = DB::table('florida_courses')->update(['strict_duration_enabled' => $validated['strict_duration_enabled']]);
                \Log::info('Florida courses updated: ' . $floridaUpdated . ' rows');
                
                $floridaVerify = DB::table('florida_courses')->where('strict_duration_enabled', $validated['strict_duration_enabled'])->count();
                \Log::info('Verification - florida_courses with new value: ' . $floridaVerify);
            } else {
                \Log::warning('Skipping florida_courses table update - exists: ' . ($floridaTableExists ? 'Y' : 'N') . ', hasColumn: ' . ($floridaHasColumn ? 'Y' : 'N') . ', count: ' . $floridaCount);
            }
            
            \Log::info('STEP 9: Final verification');
            $finalCoursesCount = DB::table('courses')->where('strict_duration_enabled', $validated['strict_duration_enabled'])->count();
            $finalFloridaCount = DB::table('florida_courses')->where('strict_duration_enabled', $validated['strict_duration_enabled'])->count();
            \Log::info('Final - courses with setting: ' . $finalCoursesCount);
            \Log::info('Final - florida_courses with setting: ' . $finalFloridaCount);
            
            \Log::info('╔════════════════════════════════════════╗');
            \Log::info('║ toggleStrictDuration SUCCESS           ║');
            \Log::info('╚════════════════════════════════════════╝');
            
            return response()->json([
                'success' => true,
                'message' => 'Strict duration enforcement ' . ($validated['strict_duration_enabled'] ? 'ENABLED' : 'DISABLED') . ' for all courses',
                'strict_duration_enabled' => $validated['strict_duration_enabled']
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('╔════════════════════════════════════════╗');
            \Log::error('║ VALIDATION ERROR                       ║');
            \Log::error('╚════════════════════════════════════════╝');
            \Log::error('Errors: ' . json_encode($e->errors()));
            throw $e;
            
        } catch (\Exception $e) {
            \Log::error('╔════════════════════════════════════════╗');
            \Log::error('║ toggleStrictDuration ERROR             ║');
            \Log::error('╚════════════════════════════════════════╝');
            \Log::error('Exception class: ' . get_class($e));
            \Log::error('Error message: ' . $e->getMessage());
            \Log::error('Error file: ' . $e->getFile());
            \Log::error('Error line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to update strict duration setting',
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ], 500);
        }
    }
}
