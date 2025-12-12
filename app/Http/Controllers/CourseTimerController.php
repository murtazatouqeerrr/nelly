<?php

namespace App\Http\Controllers;

use App\Models\CourseTimer;
use App\Services\CourseTimerService;
use Illuminate\Http\Request;

class CourseTimerController extends Controller
{
    protected $timerService;

    public function __construct(CourseTimerService $timerService)
    {
        $this->timerService = $timerService;
    }

    public function startTimer(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|integer',
            'chapter_type' => 'nullable|string|in:chapters,course_chapters',
        ]);

        $chapterType = $request->chapter_type ?? 'chapters';
        $result = $this->timerService->startTimer(auth()->id(), $request->chapter_id, $chapterType);

        return response()->json($result);
    }

    public function updateTimer(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:timer_sessions,id',
            'time_spent' => 'required|integer|min:0',
        ]);

        $result = $this->timerService->updateTimer($request->session_id, $request->time_spent);

        return response()->json($result);
    }

    public function bypassTimer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        // Only admins can bypass
        if (auth()->user()->role_id != 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->timerService->bypassTimer(
            $request->user_id,
            $request->chapter_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function checkTimerStatus(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $isCompleted = $this->timerService->isTimerCompleted(auth()->id(), $request->chapter_id);

        return response()->json(['completed' => $isCompleted]);
    }

    public function configureTimer(Request $request)
    {
        try {
            \Log::info('=== Configure Timer Request START ===');
            \Log::info('Request data:', $request->all());
            \Log::info('Request headers:', $request->headers->all());

            $request->validate([
                'chapter_id' => 'required|integer',
                'chapter_type' => 'nullable|string|in:chapters,course_chapters',
                'required_time_minutes' => 'required|integer|min:1',
                'is_enabled' => 'boolean',
                'allow_pause' => 'boolean',
                'bypass_for_admin' => 'boolean',
            ]);

            \Log::info('Validation passed');

            $data = [
                'chapter_id' => $request->chapter_id,
                'chapter_type' => $request->chapter_type ?? 'chapters',
                'required_time_minutes' => $request->required_time_minutes,
                'is_enabled' => $request->is_enabled ?? true,
                'allow_pause' => $request->allow_pause ?? true,
                'bypass_for_admin' => $request->bypass_for_admin ?? true,
            ];

            \Log::info('Data to save:', $data);

            $timer = CourseTimer::updateOrCreate(
                [
                    'chapter_id' => $data['chapter_id'],
                    'chapter_type' => $data['chapter_type'],
                ],
                [
                    'required_time_minutes' => $data['required_time_minutes'],
                    'is_enabled' => $data['is_enabled'],
                    'allow_pause' => $data['allow_pause'],
                    'bypass_for_admin' => $data['bypass_for_admin'],
                ]
            );

            \Log::info('Timer saved successfully:', $timer->toArray());
            \Log::info('=== Configure Timer Request END ===');

            return response()->json(['success' => true, 'timer' => $timer]);
        } catch (\Exception $e) {
            \Log::error('=== Configure Timer ERROR ===');
            \Log::error('Error message: '.$e->getMessage());
            \Log::error('Error file: '.$e->getFile().':'.$e->getLine());
            \Log::error('Stack trace: '.$e->getTraceAsString());
            \Log::error('=== Configure Timer ERROR END ===');

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
