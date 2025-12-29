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
            $query->where('title', 'like', '%'.$request->search.'%');
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
}
