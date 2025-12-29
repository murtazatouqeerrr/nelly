<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\UserCourseEnrollment;
use App\Models\UserCourseProgress;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function startChapter(Request $request, UserCourseEnrollment $enrollment, Chapter $chapter)
    {
        $progress = UserCourseProgress::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'chapter_id' => $chapter->id,
        ], [
            'started_at' => now(),
            'last_accessed_at' => now(),
        ]);

        if (! $enrollment->started_at) {
            $enrollment->update(['started_at' => now()]);
        }

        return response()->json($progress);
    }

    public function completeChapter(Request $request, UserCourseEnrollment $enrollment, Chapter $chapter)
    {
        $validated = $request->validate([
            'time_spent' => 'required|integer|min:0',
        ]);

        $progress = UserCourseProgress::where('enrollment_id', $enrollment->id)
            ->where('chapter_id', $chapter->id)
            ->first();

        if ($progress) {
            $progress->update([
                'completed_at' => now(),
                'is_completed' => true,
                'time_spent' => $validated['time_spent'],
                'last_accessed_at' => now(),
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
            'overall_progress' => $enrollment->progress_percentage,
        ]);
    }

    private function updateEnrollmentProgress(UserCourseEnrollment $enrollment)
    {
        // Get total chapters from chapters table (primary source)
        $totalChapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)
            ->where('is_active', true)
            ->count();

        // If no chapters found, this might be an issue with the course setup
        if ($totalChapters == 0) {
            \Log::warning("No chapters found for course_id: {$enrollment->course_id}");
        }

        // Count unique completed chapters (avoid duplicates)
        $completedChapters = \DB::table('user_course_progress')
            ->where('enrollment_id', $enrollment->id)
            ->where('is_completed', true)
            ->distinct()
            ->count('chapter_id');

        // Cap progress at 100%
        $progressPercentage = $totalChapters > 0 ? min(($completedChapters / $totalChapters) * 100, 100) : 0;
        $totalTimeSpent = $enrollment->progress()->sum('time_spent');

        $wasCompleted = $enrollment->status === 'completed';

        \Log::info("Progress update - Total: {$totalChapters}, Completed: {$completedChapters}, Percentage: {$progressPercentage}");

        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'total_time_spent' => $totalTimeSpent,
            'completed_at' => $progressPercentage >= 100 ? now() : null,
            'status' => $progressPercentage >= 100 ? 'completed' : 'active',
        ]);

        // Generate certificate and fire event if course just completed
        if ($progressPercentage >= 100 && ! $wasCompleted) {
            $this->generateCertificate($enrollment);

            // Fire CourseCompleted event for state transmissions and notifications
            event(new \App\Events\CourseCompleted($enrollment));
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

            $certificateNumber = 'FL'.$year.str_pad($sequence, 6, '0', STR_PAD_LEFT);

            $courseName = $enrollment->floridaCourse->title ?? 'Florida Traffic School Course';

            \App\Models\FloridaCertificate::create([
                'enrollment_id' => $enrollment->id,
                'dicds_certificate_number' => $certificateNumber,
                'student_name' => $enrollment->user->first_name.' '.$enrollment->user->last_name,
                'course_name' => $courseName,
                'completion_date' => $enrollment->completed_at,
                'verification_hash' => \Illuminate\Support\Str::random(32),
                'status' => 'generated',
            ]);

        } catch (\Exception $e) {
            \Log::error('Certificate generation error: '.$e->getMessage());
        }
    }

    public function completeChapterWeb(UserCourseEnrollment $enrollment, $chapter)
    {
        // Ensure user can only access their own enrollments
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Get chapter from chapters table
            $chapterModel = \App\Models\Chapter::find($chapter);
            
            if (!$chapterModel) {
                return response()->json(['error' => 'Chapter not found'], 404);
            }

            // Check if already completed
            $existingProgress = UserCourseProgress::where('enrollment_id', $enrollment->id)
                ->where('chapter_id', $chapterModel->id)
                ->where('is_completed', true)
                ->first();

            if ($existingProgress) {
                $enrollment->refresh();
                return response()->json([
                    'success' => true,
                    'progress' => $existingProgress,
                    'progress_percentage' => round($enrollment->progress_percentage, 2),
                    'enrollment_completed' => $enrollment->status === 'completed',
                    'message' => 'Chapter already completed',
                ]);
            }

            // Mark chapter as complete in progress table
            $progress = UserCourseProgress::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'chapter_id' => $chapterModel->id,
                ],
                [
                    'completed_at' => now(),
                    'is_completed' => true,
                    'time_spent' => $chapterModel->duration ?? 60,
                    'last_accessed_at' => now(),
                ]
            );

            // Update enrollment progress
            $this->updateEnrollmentProgress($enrollment);

            // Refresh enrollment to get updated progress_percentage
            $enrollment->refresh();

            return response()->json([
                'success' => true,
                'progress' => $progress,
                'progress_percentage' => round($enrollment->progress_percentage, 2),
                'enrollment_completed' => $enrollment->status === 'completed',
                'message' => 'Chapter completed successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Chapter completion error: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to complete chapter',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
