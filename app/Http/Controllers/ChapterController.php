<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChapterController extends Controller
{
    public function getAllChapters()
    {
        try {
            // Cache the result for 10 minutes
            $result = cache()->remember('all_chapters_with_courses', 600, function () {
                $chapters = Chapter::orderBy('course_id')->orderBy('order_index')->get();

                // Batch load courses to avoid N+1 queries
                $courseIds = $chapters->pluck('course_id')->unique();
                $regularCourses = \App\Models\Course::whereIn('id', $courseIds)->get()->keyBy('id');
                $floridaCourses = \App\Models\FloridaCourse::whereIn('id', $courseIds)->get()->keyBy('id');

                return $chapters->map(function ($chapter) use ($regularCourses, $floridaCourses) {
                    $course = $regularCourses->get($chapter->course_id);
                    $courseType = 'courses';

                    if (!$course) {
                        $course = $floridaCourses->get($chapter->course_id);
                        $courseType = 'florida_courses';
                    }

                    return [
                        'id' => $chapter->id,
                        'title' => $chapter->title,
                        'display_title' => $chapter->title,
                        'course_id' => $chapter->course_id,
                        'course_name' => $course ? $course->title : 'Unknown Course',
                        'type' => $courseType,
                        'order_index' => $chapter->order_index,
                        'course' => $course ? [
                            'id' => $course->id,
                            'title' => $course->title,
                        ] : null,
                    ];
                })->sortBy('course_name')->values();
            });

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error loading all chapters: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load chapters'], 500);
        }
    }

    public function index(Course $course)
    {
        $chapters = $course->chapters()->orderBy('order_index')->get();

        return response()->json($chapters);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'duration' => 'required|integer|min:1',
            'order_index' => 'required|integer',
        ]);

        $validated['course_id'] = $course->id;

        return response()->json(Chapter::create($validated), 201);
    }

    public function show(Chapter $chapter)
    {
        return response()->json($chapter->load('course'));
    }

    public function update(Request $request, Chapter $chapter)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'video_url' => 'nullable|url',
            'duration' => 'required|integer|min:1',
            'order_index' => 'required|integer',
        ]);

        $chapter->update($validated);

        return response()->json($chapter);
    }

    public function destroy(Chapter $chapter)
    {
        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted successfully']);
    }

    public function indexWeb($courseId)
    {
        try {
            // Determine course table by checking which table the course exists in
            $courseTable = 'courses';
            
            // Check if this is a Florida course request
            if (request()->is('api/florida-courses/*')) {
                $courseTable = 'florida_courses';
            } else {
                // For other requests (like course player), determine by checking the database
                $floridaCourseExists = DB::table('florida_courses')->where('id', $courseId)->exists();
                $regularCourseExists = DB::table('courses')->where('id', $courseId)->exists();
                
                if ($floridaCourseExists && !$regularCourseExists) {
                    $courseTable = 'florida_courses';
                } elseif ($regularCourseExists && !$floridaCourseExists) {
                    $courseTable = 'courses';
                } elseif ($floridaCourseExists && $regularCourseExists) {
                    // Both exist, prefer florida_courses if URL suggests it or check chapters
                    $floridaChapters = DB::table('chapters')->where('course_id', $courseId)->where('course_table', 'florida_courses')->count();
                    $regularChapters = DB::table('chapters')->where('course_id', $courseId)->where('course_table', 'courses')->count();
                    
                    $courseTable = $floridaChapters > $regularChapters ? 'florida_courses' : 'courses';
                }
            }

            \Log::info("ChapterController: Determined course_table = {$courseTable} for course_id = {$courseId}");

            // First try to get chapters with the correct course_table
            $chapters = \App\Models\Chapter::where('course_id', $courseId)
                ->where('course_table', $courseTable)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();

            \Log::info("ChapterController: Found {$chapters->count()} chapters with course_table = {$courseTable}");

            // If no chapters found with the correct course_table, try without the course_table filter
            // This handles cases where chapters were created with wrong course_table value
            if ($chapters->isEmpty()) {
                \Log::info("ChapterController: No chapters found with course_table filter, trying without filter");
                
                $chapters = \App\Models\Chapter::where('course_id', $courseId)
                    ->where('is_active', true)
                    ->orderBy('order_index')
                    ->get();
                    
                \Log::info("ChapterController: Found {$chapters->count()} chapters without course_table filter");
                
                // If we found chapters but they have the wrong course_table, update them
                if ($chapters->isNotEmpty()) {
                    \Log::info("ChapterController: Updating course_table for {$chapters->count()} chapters to {$courseTable}");
                    
                    \App\Models\Chapter::where('course_id', $courseId)
                        ->where('is_active', true)
                        ->update(['course_table' => $courseTable]);
                        
                    // Reload chapters to get updated data
                    $chapters = \App\Models\Chapter::where('course_id', $courseId)
                        ->where('course_table', $courseTable)
                        ->where('is_active', true)
                        ->orderBy('order_index')
                        ->get();
                }
            }

            \Log::info("ChapterController: Final result: {$chapters->count()} chapters for course_id: {$courseId}, table: {$courseTable}");

            // Get enrollment ID from request to check completion status
            $enrollmentId = request('enrollmentId');

            if ($enrollmentId) {
                // Check completion status for each chapter
                foreach ($chapters as $chapter) {
                    // Check progress using chapter id from chapters table
                    $progress = \App\Models\UserCourseProgress::where('enrollment_id', $enrollmentId)
                        ->where('chapter_id', $chapter->id)
                        ->where('is_completed', true)
                        ->first();
                    
                    $chapter->is_completed = $progress ? true : false;
                    $chapter->chapter_type = 'chapters';
                }
            } else {
                foreach ($chapters as $chapter) {
                    $chapter->is_completed = false;
                    $chapter->chapter_type = 'chapters';
                }
            }

            // Add dynamic "Final Exam" chapter at the end for course player
            // Always add final exam for course player, but not for admin chapter builder
            $isAdminChapterBuilder = request()->is('api/florida-courses/*/chapters') || 
                                     request()->is('api/courses/*/chapters') ||
                                     request()->is('api/*/chapters');
            
            if (!$isAdminChapterBuilder) {
                $finalExamChapter = (object) [
                    'id' => 'final-exam',
                    'title' => 'Final Exam',
                    'course_id' => $courseId,
                    'content' => 'Complete the final exam to finish the course.',
                    'video_url' => null,
                    'order_index' => $chapters->count() + 1,
                    'duration' => 60, // 60 minutes for final exam
                    'is_active' => true,
                    'is_completed' => false,
                    'chapter_type' => 'final_exam',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $chapters->push($finalExamChapter);
            }

            return response()->json($chapters);
        } catch (\Exception $e) {
            \Log::error('Chapter indexWeb error: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function storeWeb(Request $request, $courseId)
    {
        try {
            \Log::info('Chapter store request received', [
                'course_id' => $courseId,
                'data' => $request->all(),
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'duration' => 'required|integer|min:1',
                'required_min_time' => 'nullable|integer|min:0',
                'order_index' => 'nullable|integer|min:0',
                'video_url' => 'nullable|string',
                'media.*' => 'nullable|file|max:51200',
            ]);

            $validated['course_id'] = $courseId;
            $validated['required_min_time'] = $validated['required_min_time'] ?? $validated['duration'];

            // Determine course table based on the request URL
            if (request()->is('api/florida-courses/*')) {
                $validated['course_table'] = 'florida_courses';
            } else {
                $validated['course_table'] = 'courses';
            }

            // Auto-generate order_index if not provided
            if (! isset($validated['order_index'])) {
                $maxOrder = Chapter::where('course_id', $courseId)
                    ->where('course_table', $validated['course_table'])
                    ->max('order_index') ?? 0;
                $validated['order_index'] = $maxOrder + 1;
            }

            // Handle file upload if present
            if ($request->hasFile('media')) {
                $files = $request->file('media');

                // Handle single file or array of files
                if (! is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

                    if (! file_exists(storage_path('app/public/course-media'))) {
                        mkdir(storage_path('app/public/course-media'), 0755, true);
                    }

                    $path = $file->storeAs('course-media', $filename, 'public');
                    $mimeType = $file->getClientMimeType();
                    $fileUrl = '/storage/course-media/'.$filename;

                    // Handle videos
                    if (in_array($mimeType, ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm'])) {
                        if (! isset($validated['video_url']) || ! $validated['video_url']) {
                            $validated['video_url'] = $fileUrl;
                        }
                    }
                    // Handle images - add to content
                    elseif (in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                        $validated['content'] .= "\n\n<div class='chapter-media'><img src='{$fileUrl}' alt='{$originalName}' class='img-fluid' style='max-width: 100%;'></div>";
                    }
                    // Handle other files (PDFs, docs) - add download link
                    else {
                        $validated['content'] .= "\n\n<div class='chapter-media'><a href='{$fileUrl}' target='_blank' class='btn btn-outline-primary'><i class='fas fa-download'></i> Download {$originalName}</a></div>";
                    }

                    // Only process first file for now
                    break;
                }
            }

            unset($validated['media']);

            $chapter = \App\Models\Chapter::create($validated);

            \Log::info('Chapter created successfully', ['chapter' => $chapter]);

            return response()->json($chapter, 201);
        } catch (\Exception $e) {
            \Log::error('Chapter store error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroyWeb(Chapter $chapter)
    {
        $chapter->delete();

        return response()->json(['message' => 'Chapter deleted successfully']);
    }

    public function updateWeb(Request $request, $id)
    {
        try {
            $chapter = \App\Models\Chapter::findOrFail($id);

            \Log::info('Chapter update request received', [
                'chapter_id' => $chapter->id,
                'data' => $request->all(),
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'duration' => 'required|integer|min:1',
                'required_min_time' => 'nullable|integer|min:0',
                'order_index' => 'nullable|integer|min:0',
                'video_url' => 'nullable|string|max:500',
                'is_active' => 'nullable|boolean',
                'media' => 'nullable|array',
                'media.*' => 'file|max:51200',
            ]);

            if (isset($validated['required_min_time'])) {
                $validated['required_min_time'] = $validated['required_min_time'] ?? $validated['duration'];
            }

            // Handle multiple file uploads if present
            if ($request->hasFile('media')) {
                $files = $request->file('media');
                if (! is_array($files)) {
                    $files = [$files]; // Convert single file to array
                }

                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time().'_'.uniqid().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

                    if (! file_exists(storage_path('app/public/course-media'))) {
                        mkdir(storage_path('app/public/course-media'), 0755, true);
                    }

                    $path = $file->storeAs('course-media', $filename, 'public');
                    $mimeType = $file->getClientMimeType();
                    $fileUrl = '/files/'.$filename;

                    if (in_array($mimeType, ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm'])) {
                        // For videos, add to content as embedded video (don't touch video_url field)
                        $validated['content'] .= "\n\n<div class='chapter-media'><video src='{$fileUrl}' controls width='100%' style='max-height: 400px;'></video></div>";
                    } elseif (in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                        // For images, add to content as embedded image
                        $validated['content'] .= "\n\n<div class='chapter-media'><img src='{$fileUrl}' alt='{$originalName}' class='img-fluid' style='max-width: 100%; height: auto;'></div>";
                    } else {
                        // For other files (PDF, docs), add as download link
                        $validated['content'] .= "\n\n<div class='chapter-media'><a href='{$fileUrl}' target='_blank' class='btn btn-outline-primary'><i class='fas fa-download'></i> Download {$originalName}</a></div>";
                    }
                }
            }

            unset($validated['media']);

            $chapter->update($validated);

            \Log::info('Chapter updated successfully', ['chapter_id' => $chapter->id]);

            return response()->json($chapter);
        } catch (\Exception $e) {
            \Log::error('Chapter update failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to update chapter: '.$e->getMessage()], 500);
        }
    }

    public function saveQuizResults(Request $request)
    {
        try {
            $validated = $request->validate([
                'chapter_id' => 'required|integer',
                'enrollment_id' => 'required|integer',
                'total_questions' => 'required|integer',
                'correct_answers' => 'required|integer',
                'wrong_answers' => 'required|integer',
                'percentage' => 'required|numeric',
                'answers' => 'required|array',
            ]);

            // Get the enrollment to find the course
            $enrollment = \App\Models\UserCourseEnrollment::find($validated['enrollment_id']);
            $course = $enrollment ? \DB::table('florida_courses')->where('id', $enrollment->course_id)->first() : null;
            
            // Handle Delaware quiz rotation logic
            if ($course && $course->state_code === 'DE') {
                return $this->handleDelawareQuizRotation($validated, $enrollment);
            }

            // Save or update quiz result (allow retakes)
            $quizResult = \App\Models\ChapterQuizResult::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'chapter_id' => $validated['chapter_id'],
                ],
                [
                    'total_questions' => $validated['total_questions'],
                    'correct_answers' => $validated['correct_answers'],
                    'wrong_answers' => $validated['wrong_answers'],
                    'percentage' => $validated['percentage'],
                    'answers' => $validated['answers'],
                ]
            );

            if ($enrollment) {
                // Calculate new quiz average for this user and course
                $quizAverage = \App\Models\ChapterQuizResult::calculateUserQuizAverage(
                    auth()->id(), 
                    $enrollment->course_id
                );
                
                // Update the enrollment with the new quiz average
                $enrollment->update(['quiz_average' => $quizAverage]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Quiz results saved',
                    'quiz_average' => $quizAverage,
                    'chapter_score' => $validated['percentage']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Quiz results saved']);
        } catch (\Exception $e) {
            \Log::error('Failed to save quiz results: '.$e->getMessage());

            return response()->json(['error' => 'Failed to save results'], 500);
        }
    }

    public function getQuizResult($chapterId)
    {
        try {
            $quizResult = \App\Models\ChapterQuizResult::where('user_id', auth()->id())
                ->where('chapter_id', $chapterId)
                ->first();

            return response()->json([
                'quiz_result' => $quizResult
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get quiz result: '.$e->getMessage());
            return response()->json(['error' => 'Failed to get quiz result'], 500);
        }
    }

    /**
     * Handle Delaware quiz rotation logic
     * If student fails Quiz Set 1, show Quiz Set 2
     */
    private function handleDelawareQuizRotation($validated, $enrollment)
    {
        $chapterId = $validated['chapter_id'];
        $userId = auth()->id();
        $passed = $validated['percentage'] >= 70; // Assuming 70% is passing
        
        // Get or create progress record for this chapter
        $progress = \DB::table('user_course_progress')
            ->where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapterId)
            ->first();
            
        if (!$progress) {
            // Create new progress record
            \DB::table('user_course_progress')->insert([
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapterId,
                'current_quiz_set' => 1,
                'quiz_set_1_attempts' => 1,
                'quiz_set_2_attempts' => 0,
                'quiz_score' => $validated['percentage'],
                'is_completed' => $passed,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Update existing progress
            $currentQuizSet = $progress->current_quiz_set ?? 1;
            $set1Attempts = $progress->quiz_set_1_attempts ?? 0;
            $set2Attempts = $progress->quiz_set_2_attempts ?? 0;
            
            if ($currentQuizSet == 1) {
                $set1Attempts++;
                
                // If failed Quiz Set 1, switch to Quiz Set 2
                if (!$passed) {
                    $currentQuizSet = 2;
                }
            } else {
                $set2Attempts++;
            }
            
            \DB::table('user_course_progress')
                ->where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterId)
                ->update([
                    'current_quiz_set' => $currentQuizSet,
                    'quiz_set_1_attempts' => $set1Attempts,
                    'quiz_set_2_attempts' => $set2Attempts,
                    'quiz_score' => $validated['percentage'],
                    'is_completed' => $passed,
                    'updated_at' => now(),
                ]);
        }
        
        // Save quiz result
        $quizResult = \App\Models\ChapterQuizResult::updateOrCreate(
            [
                'user_id' => $userId,
                'chapter_id' => $chapterId,
            ],
            [
                'total_questions' => $validated['total_questions'],
                'correct_answers' => $validated['correct_answers'],
                'wrong_answers' => $validated['wrong_answers'],
                'percentage' => $validated['percentage'],
                'answers' => $validated['answers'],
            ]
        );
        
        // Calculate and update quiz average
        $quizAverage = \App\Models\ChapterQuizResult::calculateUserQuizAverage(
            $userId, 
            $enrollment->course_id
        );
        
        $enrollment->update(['quiz_average' => $quizAverage]);
        
        // Return response with quiz set information
        $response = [
            'success' => true,
            'message' => $passed ? 'Quiz passed!' : 'Quiz failed.',
            'passed' => $passed,
            'delaware_quiz_rotation' => true,
        ];
        
        // If failed Quiz Set 1, inform frontend to load Quiz Set 2
        if (!$passed && ($progress->current_quiz_set ?? 1) == 2) {
            $response['switch_to_quiz_set'] = 2;
            $response['message'] = 'Quiz Set 1 failed. You will now see Quiz Set 2 questions.';
        }
        
        return response()->json($response);
    }
}
