<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatsWeb()
    {
        $stats = [
            'total_students' => User::where('role_id', '!=', 1)->count(),
            'total_courses' => Course::count(),
            'total_enrollments' => UserCourseEnrollment::count(),
            'completed_courses' => UserCourseEnrollment::whereNotNull('completed_at')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'certificates_issued' => Certificate::count(),
            'pending_certificates' => Certificate::where('status', 'generated')->count(),
            'state_submissions' => Certificate::where('is_sent_to_state', true)->count(),
        ];

        // Monthly enrollment data for chart
        $monthlyEnrollments = UserCourseEnrollment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months with 0
        $enrollmentChart = [];
        for ($i = 1; $i <= 12; $i++) {
            $enrollmentChart[] = $monthlyEnrollments[$i] ?? 0;
        }

        // Revenue by month
        $monthlyRevenue = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->where('status', 'completed')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $revenueChart = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueChart[] = $monthlyRevenue[$i] ?? 0;
        }

        // Course completion rates
        $courseStats = Course::select('courses.*')
            ->withCount([
                'enrollments',
                'enrollments as completed_count' => function ($query) {
                    $query->whereNotNull('completed_at');
                },
            ])
            ->get()
            ->map(function ($course) {
                $course->completion_rate = $course->enrollments_count > 0
                    ? round(($course->completed_count / $course->enrollments_count) * 100, 2)
                    : 0;

                return $course;
            });

        // Recent activities
        $recentEnrollments = UserCourseEnrollment::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPayments = Payment::with(['user', 'enrollment.course'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'charts' => [
                'enrollments' => $enrollmentChart,
                'revenue' => $revenueChart,
            ],
            'course_stats' => $courseStats,
            'recent_activities' => [
                'enrollments' => $recentEnrollments,
                'payments' => $recentPayments,
            ],
        ]);
    }

    public function getUserStats()
    {
        $user = auth()->user();

        $stats = [
            'total_enrollments' => $user->enrollments()->count(),
            'completed_courses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            'in_progress' => $user->enrollments()->whereNull('completed_at')->count(),
            'certificates_earned' => Certificate::whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'total_spent' => Payment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        // Progress data
        $enrollments = $user->enrollments()->with(['course', 'progress'])->get();

        $progressData = $enrollments->map(function ($enrollment) {
            $totalChapters = $enrollment->course->chapters()->count();
            $completedChapters = $enrollment->progress()->count();

            return [
                'course_title' => $enrollment->course->title,
                'progress_percentage' => $totalChapters > 0 ? round(($completedChapters / $totalChapters) * 100, 2) : 0,
                'completed_chapters' => $completedChapters,
                'total_chapters' => $totalChapters,
                'enrollment_date' => $enrollment->created_at->format('M d, Y'),
                'completion_date' => $enrollment->completed_at ? $enrollment->completed_at->format('M d, Y') : null,
            ];
        });

        return response()->json([
            'stats' => $stats,
            'progress_data' => $progressData,
        ]);
    }
}
