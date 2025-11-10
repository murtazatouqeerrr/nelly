<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\UserCourseEnrollment;
use App\Models\Payment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function indexWeb()
    {
        return response()->json([
            'available_reports' => [
                'enrollment_report' => 'Student Enrollment Report',
                'completion_report' => 'Course Completion Report',
                'revenue_report' => 'Revenue Report',
                'certificate_report' => 'Certificate Report',
                'state_compliance_report' => 'State Compliance Report',
            ]
        ]);
    }

    public function generateWeb(Request $request)
    {
        $reportType = $request->input('report_type');
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $stateCode = $request->input('state_code');

        switch ($reportType) {
            case 'enrollment':
            case 'enrollment_report':
                return $this->generateEnrollmentReport($startDate, $endDate, $stateCode);
            case 'completion':
            case 'completion_report':
                return $this->generateCompletionReport($startDate, $endDate, $stateCode);
            case 'revenue':
            case 'revenue_report':
                return $this->generateRevenueReport($startDate, $endDate, $stateCode);
            case 'certificate':
            case 'certificate_report':
                return $this->generateCertificateReport($startDate, $endDate, $stateCode);
            case 'compliance':
            case 'state_compliance':
            case 'state_compliance_report':
                return $this->generateStateComplianceReport($startDate, $endDate, $stateCode);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    private function generateEnrollmentReport($startDate, $endDate, $stateCode = null)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->whereHas('floridaCourse', function ($q) use ($stateCode) {
                $q->where('state_code', $stateCode);
            });
        }

        $enrollments = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_enrollments' => $enrollments->count(),
            'unique_students' => $enrollments->pluck('user_id')->unique()->count(),
            'courses_enrolled' => $enrollments->pluck('course_id')->unique()->count(),
            'completed_enrollments' => $enrollments->where('completed_at', '!=', null)->count(),
        ];

        // Group by course
        $courseBreakdown = $enrollments->groupBy('floridaCourse.title')->map(function ($group) {
            return [
                'course_title' => $group->first()->floridaCourse->title,
                'total_enrollments' => $group->count(),
                'completed' => $group->where('completed_at', '!=', null)->count(),
                'in_progress' => $group->where('completed_at', null)->count(),
            ];
        })->values();

        return response()->json([
            'report_type' => 'Enrollment Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'course_breakdown' => $courseBreakdown,
            'detailed_data' => $enrollments->map(function ($enrollment) {
                return [
                    'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                    'student_email' => $enrollment->user->email,
                    'course_title' => $enrollment->floridaCourse->title ?? 'N/A',
                    'enrollment_date' => $enrollment->created_at->format('Y-m-d H:i:s'),
                    'completion_date' => $enrollment->completed_at ? $enrollment->completed_at->format('Y-m-d H:i:s') : 'In Progress',
                    'status' => $enrollment->completed_at ? 'Completed' : 'In Progress',
                ];
            })
        ]);
    }

    private function generateCompletionReport($startDate, $endDate, $stateCode = null)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->whereHas('floridaCourse', function ($q) use ($stateCode) {
                $q->where('state_code', $stateCode);
            });
        }

        $completions = $query->orderBy('completed_at', 'desc')->get();

        $summary = [
            'total_completions' => $completions->count(),
            'unique_students' => $completions->pluck('user_id')->unique()->count(),
            'courses_completed' => $completions->pluck('course_id')->unique()->count(),
            'average_completion_time' => $this->calculateAverageCompletionTime($completions),
        ];

        return response()->json([
            'report_type' => 'Course Completion Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'detailed_data' => $completions->map(function ($completion) {
                return [
                    'student_name' => $completion->user->first_name . ' ' . $completion->user->last_name,
                    'student_email' => $completion->user->email,
                    'course_title' => $completion->floridaCourse->title ?? 'N/A',
                    'enrollment_date' => $completion->created_at->format('Y-m-d'),
                    'completion_date' => $completion->completed_at->format('Y-m-d'),
                    'completion_time_days' => $completion->created_at->diffInDays($completion->completed_at),
                ];
            })
        ]);
    }

    private function generateRevenueReport($startDate, $endDate, $stateCode = null)
    {
        $query = Payment::with(['user', 'enrollment.floridaCourse'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->whereHas('enrollment.floridaCourse', function ($q) use ($stateCode) {
                $q->where('state_code', $stateCode);
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_revenue' => $payments->sum('amount'),
            'total_transactions' => $payments->count(),
            'average_transaction' => $payments->count() > 0 ? round($payments->sum('amount') / $payments->count(), 2) : 0,
            'unique_customers' => $payments->pluck('user_id')->unique()->count(),
        ];

        // Revenue by payment method
        $paymentMethodBreakdown = $payments->groupBy('payment_method')->map(function ($group, $method) {
            return [
                'method' => $method,
                'total_amount' => $group->sum('amount'),
                'transaction_count' => $group->count(),
            ];
        })->values();

        return response()->json([
            'report_type' => 'Revenue Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'payment_method_breakdown' => $paymentMethodBreakdown,
            'detailed_data' => $payments->map(function ($payment) {
                return [
                    'transaction_id' => $payment->id,
                    'customer_name' => $payment->user->first_name . ' ' . $payment->user->last_name,
                    'customer_email' => $payment->user->email,
                    'course_title' => $payment->enrollment->floridaCourse->title ?? 'N/A',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'gateway' => $payment->gateway,
                    'transaction_date' => $payment->created_at->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    private function generateCertificateReport($startDate, $endDate, $stateCode = null)
    {
        $query = Certificate::with(['enrollment.user', 'enrollment.course'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state_code', $stateCode);
        }

        $certificates = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_certificates' => $certificates->count(),
            'sent_to_state' => $certificates->where('is_sent_to_state', true)->count(),
            'pending_state_submission' => $certificates->where('is_sent_to_state', false)->count(),
            'confirmed_by_state' => $certificates->where('status', 'confirmed')->count(),
        ];

        // Status breakdown
        $statusBreakdown = $certificates->groupBy('status')->map(function ($group, $status) {
            return [
                'status' => $status,
                'count' => $group->count(),
            ];
        })->values();

        return response()->json([
            'report_type' => 'Certificate Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'status_breakdown' => $statusBreakdown,
            'detailed_data' => $certificates->map(function ($certificate) {
                return [
                    'certificate_number' => $certificate->certificate_number,
                    'student_name' => $certificate->student_name,
                    'course_name' => $certificate->course_name,
                    'state_code' => $certificate->state_code,
                    'completion_date' => $certificate->completion_date,
                    'issued_date' => $certificate->created_at->format('Y-m-d'),
                    'status' => $certificate->status,
                    'sent_to_state' => $certificate->is_sent_to_state ? 'Yes' : 'No',
                ];
            })
        ]);
    }

    private function generateStateComplianceReport($startDate, $endDate, $stateCode = null)
    {
        $query = \App\Models\FloridaCertificate::with(['enrollment.user'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state_code', $stateCode);
        }

        $certificates = $query->get();

        $summary = [
            'total_certificates' => $certificates->count(),
            'submitted_to_state' => $certificates->where('is_sent_to_state', true)->count(),
            'confirmed_by_state' => $certificates->where('status', 'confirmed')->count(),
            'rejected_by_state' => $certificates->where('status', 'rejected')->count(),
            'compliance_rate' => $certificates->count() > 0 
                ? round(($certificates->where('status', 'confirmed')->count() / $certificates->count()) * 100, 2)
                : 0,
        ];

        // State breakdown
        $stateBreakdown = $certificates->groupBy('state_code')->map(function ($group, $state) {
            return [
                'state_code' => $state,
                'total_certificates' => $group->count(),
                'submitted' => $group->where('is_sent_to_state', true)->count(),
                'confirmed' => $group->where('status', 'confirmed')->count(),
                'compliance_rate' => $group->count() > 0 
                    ? round(($group->where('status', 'confirmed')->count() / $group->count()) * 100, 2)
                    : 0,
            ];
        })->values();

        return response()->json([
            'report_type' => 'State Compliance Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'state_breakdown' => $stateBreakdown,
            'detailed_data' => $certificates->map(function ($certificate) {
                return [
                    'certificate_number' => $certificate->certificate_number,
                    'student_name' => $certificate->student_name,
                    'state_code' => $certificate->state_code,
                    'completion_date' => $certificate->completion_date,
                    'submission_date' => $certificate->is_sent_to_state 
                        ? $certificate->stateSubmissionLogs->first()?->submitted_at?->format('Y-m-d') ?? 'N/A'
                        : 'Not Submitted',
                    'status' => $certificate->status,
                    'compliance_status' => $certificate->status === 'confirmed' ? 'Compliant' : 'Pending',
                ];
            })
        ]);
    }

    private function calculateAverageCompletionTime($completions)
    {
        if ($completions->isEmpty()) {
            return 0;
        }

        $totalDays = $completions->sum(function ($completion) {
            return $completion->created_at->diffInDays($completion->completed_at);
        });

        return round($totalDays / $completions->count(), 2);
    }
}
