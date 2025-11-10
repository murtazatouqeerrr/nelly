<?php

namespace App\Http\Controllers;

use App\Models\UserCourseEnrollment;
use App\Models\UserCourseProgress;
use App\Models\Chapter;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function startChapter(Request $request, UserCourseEnrollment $enrollment, Chapter $chapter)
    {
        $progress = UserCourseProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'chapter_id' => $chapter->id
        ], [
            'started_at' => now(),
            'last_accessed_at' => now()
        ]);
        
        if (!$enrollment->started_at) {
            $enrollment->update(['started_at' => now()]);
        }
        
        return response()->json($progress);
    }

    public function completeChapter(Request $request, UserCourseEnrollment $enrollment, Chapter $chapter)
    {
        $validated = $request->validate([
            'time_spent' => 'required|integer|min:0'
        ]);
        
        $progress = UserCourseProgress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapter->id)
            ->first();
            
        if ($progress) {
            $progress->update([
                'completed_at' => now(),
                'is_completed' => true,
                'time_spent' => $validated['time_spent'],
                'last_accessed_at' => now()
            ]);
            
            $this->updateEnrollmentProgress($enrollment);
        }
        
        return response()->json($progress);
    }

    public function getProgress(UserCourseEnrollment $enrollment)
    {
        $progress = $enrollment->progress()->with('chapter')->get();
        
        return response()->json([
            'enrollment' => $enrollment,
            'chapters_progress' => $progress,
            'overall_progress' => $enrollment->progress_percentage
        ]);
    }

    private function updateEnrollmentProgress(UserCourseEnrollment $enrollment)
    {
        $totalChapters = \App\Models\CourseChapter::where('course_id', $enrollment->course_id)->count();
        $completedChapters = $enrollment->progress()->where('is_completed', true)->count();
        
        $progressPercentage = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 100 : 0;
        $totalTimeSpent = $enrollment->progress()->sum('time_spent');
        
        $wasCompleted = $enrollment->status === 'completed';
        
        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'total_time_spent' => $totalTimeSpent,
            'completed_at' => $progressPercentage == 100 ? now() : null,
            'status' => $progressPercentage == 100 ? 'completed' : 'active'
        ]);
        
        // Generate certificate if course just completed
        if ($progressPercentage == 100 && !$wasCompleted) {
            $this->generateCertificate($enrollment);
        }
    }
    
    private function generateCertificate(UserCourseEnrollment $enrollment)
    {
        try {
            // Check if certificate already exists
            $existingCertificate = \App\Models\FloridaCertificate::where('enrollment_id', $enrollment->id)->first();
            if ($existingCertificate) {
                return;
            }
            
            // Generate certificate number
            $year = date('Y');
            $lastCertificate = \App\Models\FloridaCertificate::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = $lastCertificate ? 
                (int) substr($lastCertificate->dicds_certificate_number, -6) + 1 : 1;
            
            $certificateNumber = 'FL' . $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            
            $courseName = $enrollment->floridaCourse->title ?? 'Florida Traffic School Course';
            
            \App\Models\FloridaCertificate::create([
                'enrollment_id' => $enrollment->id,
                'dicds_certificate_number' => $certificateNumber,
                'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                'course_name' => $courseName,
                'completion_date' => $enrollment->completed_at,
                'verification_hash' => \Illuminate\Support\Str::random(32),
                'status' => 'generated',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Certificate generation error: ' . $e->getMessage());
        }
    }
    
    public function completeChapterWeb(UserCourseEnrollment $enrollment, $chapterId)
    {
        // Ensure user can only access their own enrollments
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $chapter = \App\Models\CourseChapter::findOrFail($chapterId);
        
        $progress = UserCourseProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'chapter_id' => $chapter->id
            ],
            [
                'completed_at' => now(),
                'is_completed' => true,
                'time_spent' => $chapter->duration,
                'last_accessed_at' => now()
            ]
        );
        
        // Update enrollment progress
        $this->updateEnrollmentProgress($enrollment);
        
        return response()->json($progress);
    }
}
