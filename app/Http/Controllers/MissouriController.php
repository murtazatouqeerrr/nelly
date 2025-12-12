<?php

namespace App\Http\Controllers;

use App\Models\MissouriForm4444;
use App\Models\MissouriSubmissionTracker;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;

class MissouriController extends Controller
{
    public function generateForm4444(Request $request)
    {
        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        // Create Form 4444 record
        $form = MissouriForm4444::create([
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'form_number' => 'MO-4444-'.time(),
            'completion_date' => now(),
            'submission_deadline' => now()->addDays(15),
            'submission_method' => $request->submission_method,
            'court_signature_required' => $request->submission_method === 'point_reduction',
            'status' => 'ready_for_submission',
        ]);

        // Create submission tracker
        MissouriSubmissionTracker::create([
            'form_4444_id' => $form->id,
            'user_id' => $enrollment->user_id,
            'completion_date' => now(),
            'submission_deadline' => now()->addDays(15),
            'days_remaining' => 15,
        ]);

        return response()->json([
            'success' => true,
            'form' => $form,
            'message' => 'Form 4444 generated successfully',
        ]);
    }

    public function getSubmissionStatus($userId)
    {
        $trackers = MissouriSubmissionTracker::where('user_id', $userId)
            ->with(['form4444', 'user'])
            ->get()
            ->map(function ($tracker) {
                $tracker->days_remaining = $tracker->calculateDaysRemaining();
                $tracker->is_expired = $tracker->isExpired();

                return $tracker;
            });

        return response()->json($trackers);
    }

    public function submitToDOR(Request $request, $formId)
    {
        $form = MissouriForm4444::findOrFail($formId);

        $form->update([
            'submitted_to_dor' => true,
            'dor_submission_date' => now(),
            'status' => 'submitted_to_dor',
        ]);

        // Update tracker
        $tracker = MissouriSubmissionTracker::where('form_4444_id', $formId)->first();
        if ($tracker) {
            $tracker->update(['status' => 'submitted']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Form submitted to Missouri DOR successfully',
        ]);
    }

    public function getExpiringForms()
    {
        $expiringSoon = MissouriSubmissionTracker::whereRaw('DATEDIFF(submission_deadline, NOW()) <= 3')
            ->where('status', 'active')
            ->with(['form4444', 'user'])
            ->get();

        return response()->json($expiringSoon);
    }
}
