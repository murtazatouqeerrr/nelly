<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
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
            'order_index' => 'required|integer'
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
            'order_index' => 'required|integer'
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
            // Use Chapter model (chapters table) instead of CourseChapter
            $chapters = \App\Models\Chapter::where('course_id', $courseId)
                ->where('is_active', true)
                ->orderBy('order_index')
                ->get();
            
            \Log::info("ChapterController: Found {$chapters->count()} chapters for course_id: {$courseId}");
            
            // Get enrollment ID from request to check completion status
            $enrollmentId = request('enrollmentId');
            
            if ($enrollmentId) {
                // Get user_id from enrollment
                $enrollment = \App\Models\UserCourseEnrollment::find($enrollmentId);
                $userId = $enrollment ? $enrollment->user_id : auth()->id();
                
                foreach ($chapters as $chapter) {
                    $progress = \App\Models\ChapterProgress::where('user_id', $userId)
                        ->where('chapter_id', $chapter->id)
                        ->first();
                    
                    $chapter->is_completed = $progress ? ($progress->status === 'completed') : false;
                    $chapter->chapter_type = 'chapters';
                }
            } else {
                foreach ($chapters as $chapter) {
                    $chapter->chapter_type = 'chapters';
                }
            }
            
            return response()->json($chapters);
        } catch (\Exception $e) {
            \Log::error('Chapter indexWeb error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function storeWeb(Request $request, $courseId)
    {
        try {
            \Log::info('Chapter store request received', [
                'course_id' => $courseId,
                'data' => $request->all()
            ]);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'duration' => 'required|integer|min:1',
                'required_min_time' => 'nullable|integer|min:0',
                'order_index' => 'required|integer|min:0',
                'video_url' => 'nullable|string',
                'media' => 'nullable|file|max:51200'
            ]);
            
            $validated['course_id'] = $courseId;
            $validated['required_min_time'] = $validated['required_min_time'] ?? $validated['duration'];
            
            // Handle file upload if present
            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                
                if (!file_exists(storage_path('app/public/course-media'))) {
                    mkdir(storage_path('app/public/course-media'), 0755, true);
                }
                
                $path = $file->storeAs('course-media', $filename, 'public');
                $mimeType = $file->getClientMimeType();
                
                if (in_array($mimeType, ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/webm'])) {
                    if (!$validated['video_url']) {
                        $validated['video_url'] = '/files/' . $filename;
                    }
                } else {
                    $fileUrl = '/files/' . $filename;
                    if (in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                        $validated['content'] .= "\n\n<div class='chapter-media'><img src='{$fileUrl}' alt='{$originalName}' class='img-fluid'></div>";
                    } else {
                        $validated['content'] .= "\n\n<div class='chapter-media'><a href='{$fileUrl}' target='_blank' class='btn btn-outline-primary'><i class='fas fa-download'></i> Download {$originalName}</a></div>";
                    }
                }
            }
            
            unset($validated['media']);
            
            $chapter = \App\Models\CourseChapter::create($validated);
            
            \Log::info('Chapter created successfully', ['chapter' => $chapter]);
            
            return response()->json($chapter, 201);
        } catch (\Exception $e) {
            \Log::error('Chapter store error: ' . $e->getMessage());
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
            $chapter = \App\Models\CourseChapter::findOrFail($id);
            
            \Log::info('Chapter update request received', [
                'chapter_id' => $chapter->id,
                'data' => $request->all()
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
                'media.*' => 'file|max:51200'
            ]);
            
            if (isset($validated['required_min_time'])) {
                $validated['required_min_time'] = $validated['required_min_time'] ?? $validated['duration'];
            }
            
            // Handle multiple file uploads if present
            if ($request->hasFile('media')) {
                $files = $request->file('media');
                if (!is_array($files)) {
                    $files = [$files]; // Convert single file to array
                }
                
                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                    
                    if (!file_exists(storage_path('app/public/course-media'))) {
                        mkdir(storage_path('app/public/course-media'), 0755, true);
                    }
                    
                    $path = $file->storeAs('course-media', $filename, 'public');
                    $mimeType = $file->getClientMimeType();
                    $fileUrl = '/files/' . $filename;
                    
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
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to update chapter: ' . $e->getMessage()], 500);
        }
    }
}
