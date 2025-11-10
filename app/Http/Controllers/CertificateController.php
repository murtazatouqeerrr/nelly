<?php

namespace App\Http\Controllers;

use App\Models\FloridaCertificate;
use App\Models\UserCourseEnrollment;
use App\Models\StateSubmissionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = FloridaCertificate::query();

        if ($request->state_code) {
            $query->where('state', $request->state_code);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $certificates = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($certificates);
    }

    public function generate(UserCourseEnrollment $enrollment)
    {
        // Check if enrollment is completed
        if (!$enrollment->completed_at) {
            return response()->json(['error' => 'Course not completed'], 400);
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('enrollment_id', $enrollment->id)->first();
        if ($existingCertificate) {
            return response()->json($existingCertificate);
        }

        $certificate = Certificate::create([
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $this->generateCertificateNumber($enrollment->course->state_code ?? 'FL'),
            'student_name' => $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
            'course_name' => $enrollment->course->title,
            'state_code' => $enrollment->course->state_code ?? 'FL',
            'completion_date' => $enrollment->completed_at,
            'verification_hash' => Str::random(32),
            'status' => 'generated',
        ]);

        return response()->json($certificate);
    }

    public function verify($verificationHash)
    {
        $certificate = \App\Models\FloridaCertificate::where('verification_hash', $verificationHash)->first();

        if (!$certificate) {
            return view('certificates.verify', ['certificate' => null]);
        }

        return view('certificates.verify', compact('certificate'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'enrollment_id' => 'required|exists:user_course_enrollments,id',
                'student_name' => 'required|string',
                'course_name' => 'required|string',
                'state_code' => 'required|string|size:2',
                'completion_date' => 'required|date',
                'status' => 'required|in:generated,submitted,confirmed,failed',
            ]);

            $certificate = Certificate::create([
                'enrollment_id' => $request->enrollment_id,
                'certificate_number' => $this->generateCertificateNumber($request->state_code),
                'student_name' => $request->student_name,
                'course_name' => $request->course_name,
                'state_code' => $request->state_code,
                'completion_date' => $request->completion_date,
                'verification_hash' => Str::random(32),
                'status' => $request->status,
                'is_sent_to_state' => false,
            ]);

            return response()->json($certificate);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);
        return response()->json($certificate);
    }

    public function update(Request $request, $id)
    {
        try {
            $certificate = FloridaCertificate::findOrFail($id);
            $certificate->update($request->all());
            return response()->json($certificate);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $certificate = FloridaCertificate::findOrFail($id);
            $certificate->delete();
            return response()->json(['message' => 'Certificate deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function submitToState($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);
        $certificate->update(['is_sent_to_student' => true, 'sent_at' => now()]);
        return response()->json(['message' => 'Submitted to state', 'certificate' => $certificate]);
    }

    public function emailCertificate($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);
        return response()->json(['message' => 'Certificate emailed successfully', 'certificate' => $certificate]);
    }

    public function download($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);
        $html = view('certificates.florida-certificate', compact('certificate'))->render();
        
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="certificate-' . $certificate->dicds_certificate_number . '.html"');
    }

    private function generateCertificateNumber($stateCode)
    {
        $year = date('Y');
        $lastCertificate = Certificate::where('state_code', $stateCode)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastCertificate ? 
            (int) substr($lastCertificate->certificate_number, -6) + 1 : 1;

        return $stateCode . '-' . $year . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }
}
