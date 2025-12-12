<?php

namespace App\Services;

use App\Models\CourseTimer;
use App\Models\TimerSession;
use App\Models\User;

class CourseTimerService
{
    public function startTimer($userId, $chapterId, $chapterType = 'chapters')
    {
        $timer = CourseTimer::where('chapter_id', $chapterId)
            ->where('chapter_type', $chapterType)
            ->first();

        if (! $timer || ! $timer->is_enabled) {
            return ['success' => true, 'timer_required' => false];
        }

        $user = User::find($userId);
        if ($user && $user->role_id == 1 && $timer->bypass_for_admin) {
            return ['success' => true, 'timer_required' => false, 'bypassed' => true];
        }

        $existingSession = TimerSession::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->where('is_completed', false)
            ->first();

        if ($existingSession) {
            return [
                'success' => true,
                'session' => $existingSession,
                'required_time' => $timer->required_time_minutes * 60,
            ];
        }

        $session = TimerSession::create([
            'user_id' => $userId,
            'course_timer_id' => $timer->id,
            'chapter_id' => $chapterId,
            'started_at' => now(),
            'time_spent_seconds' => 0,
            'is_completed' => false,
        ]);

        return [
            'success' => true,
            'session' => $session,
            'required_time' => $timer->required_time_minutes * 60,
        ];
    }

    public function updateTimer($sessionId, $timeSpent)
    {
        $session = TimerSession::find($sessionId);

        if (! $session) {
            return ['success' => false, 'error' => 'Session not found'];
        }

        $session->update(['time_spent_seconds' => $timeSpent]);

        $timer = $session->timer;
        $requiredSeconds = $timer->required_time_minutes * 60;

        if ($timeSpent >= $requiredSeconds) {
            $session->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            return ['success' => true, 'completed' => true];
        }

        return ['success' => true, 'completed' => false];
    }

    public function bypassTimer($userId, $chapterId, $adminId, $chapterType = 'chapters')
    {
        $session = TimerSession::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->first();

        if ($session) {
            $session->update([
                'is_completed' => true,
                'completed_at' => now(),
                'bypassed_by_admin' => true,
            ]);
        } else {
            $timer = CourseTimer::where('chapter_id', $chapterId)
                ->where('chapter_type', $chapterType)
                ->first();
            $session = TimerSession::create([
                'user_id' => $userId,
                'course_timer_id' => $timer->id,
                'chapter_id' => $chapterId,
                'started_at' => now(),
                'completed_at' => now(),
                'time_spent_seconds' => 0,
                'is_completed' => true,
                'bypassed_by_admin' => true,
            ]);
        }

        return ['success' => true, 'session' => $session];
    }

    public function isTimerCompleted($userId, $chapterId)
    {
        $session = TimerSession::where('user_id', $userId)
            ->where('chapter_id', $chapterId)
            ->where('is_completed', true)
            ->first();

        return $session !== null;
    }
}
