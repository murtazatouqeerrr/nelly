<?php

namespace App\Http\Controllers;

use App\Events\CertificateGenerated;
use App\Models\FloridaCertificate;
use App\Models\UserCourseEnrollment;
use App\Services\CertificateAccessService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function generate(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/login')->with('error', 'Please login to generate certificate');
        }

        // Use URL parameters if provided, otherwise fallback to database
        $enrollment = null;
        if ($request->enrollment_id) {
            $enrollment = UserCourseEnrollment::with(['course', 'user'])
                ->where('user_id', $user->id)
                ->where('id', $request->enrollment_id)
                ->first();

            // Check if review exists for this enrollment
            $review = \App\Models\Review::where('user_id', $user->id)
                ->where('enrollment_id', $request->enrollment_id)
                ->first();

            if (! $review) {
                // Redirect to review page if no review exists
                return redirect('/review-course?'.http_build_query([
                    'enrollment_id' => $request->enrollment_id,
                    'course_name' => $enrollment && $enrollment->course ? $enrollment->course->title : 'Course',
                    'completion_date' => $request->completion_date ?: ($enrollment && $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : date('m/d/Y')),
                    'score' => $request->score ?: '95%',
                ]));
            }
        }

        // Build student address
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(', ', $addressParts);

        // Build phone number
        $phone_parts = array_filter([$user->phone_1, $user->phone_2, $user->phone_3]);
        $phone = implode('-', $phone_parts);

        // Build birth date
        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
        }

        // Build due date
        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
        }

        // Get state stamp if available
        $stateStamp = null;
        if ($enrollment && $enrollment->course) {
            $stateCode = $enrollment->course->state ?? $enrollment->course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }

        $data = [
            'student_name' => $request->student_name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'student_address' => $student_address ?: null,
            'completion_date' => $request->completion_date ?: ($enrollment && $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : date('m/d/Y')),
            'course_type' => $request->course_name ?: ($enrollment && $enrollment->course ? $enrollment->course->title : 'Course'),
            'score' => $request->score ?: ($enrollment && $enrollment->final_exam_score ? $enrollment->final_exam_score.'%' : 'N/A'),
            'license_number' => $user->driver_license ?? null,
            'birth_date' => $birth_date,
            'citation_number' => $user->citation_number ?? null,
            'due_date' => $due_date,
            'court' => $user->court_selected ?? null,
            'county' => $user->state ?? null,
            'certificate_number' => $this->generateCertificateNumber(),
            'phone' => $phone ?: null,
            'city' => $user->city ?? null,
            'state' => $user->state ?? null,
            'zip' => $user->zip ?? null,
            'state_stamp' => $stateStamp,
        ];

        return view('certificate', $data);
    }

    public function downloadPdf(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect('/login')->with('error', 'Please login to download certificate');
        }

        // Use URL parameters if provided, otherwise fallback to database
        $enrollment = null;
        if ($request->enrollment_id) {
            $enrollment = UserCourseEnrollment::with(['course', 'user'])
                ->where('user_id', $user->id)
                ->where('id', $request->enrollment_id)
                ->first();
        }

        // Build student address
        $addressParts = array_filter([
            $user->mailing_address,
            $user->city,
            $user->state,
            $user->zip,
        ]);
        $student_address = implode(', ', $addressParts);

        // Build phone number
        $phone_parts = array_filter([$user->phone_1, $user->phone_2, $user->phone_3]);
        $phone = implode('-', $phone_parts);

        // Build birth date
        $birth_date = null;
        if ($user->birth_month && $user->birth_day && $user->birth_year) {
            $birth_date = $user->birth_month.'/'.$user->birth_day.'/'.$user->birth_year;
        }

        // Build due date
        $due_date = null;
        if ($user->due_month && $user->due_day && $user->due_year) {
            $due_date = $user->due_month.'/'.$user->due_day.'/'.$user->due_year;
        }

        $certificateNumber = $this->generateCertificateNumber();

        $data = [
            'student_name' => $request->student_name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'student_address' => $student_address ?: null,
            'completion_date' => $request->completion_date ?: ($enrollment && $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : date('m/d/Y')),
            'course_type' => $request->course_name ?: ($enrollment && $enrollment->course ? $enrollment->course->title : 'Course'),
            'score' => $request->score ?: ($enrollment && $enrollment->final_exam_score ? $enrollment->final_exam_score.'%' : 'N/A'),
            'license_number' => $user->driver_license ?? null,
            'birth_date' => $birth_date,
            'citation_number' => $user->citation_number ?? null,
            'due_date' => $due_date,
            'court' => $user->court_selected ?? null,
            'county' => $user->state ?? null,
            'certificate_number' => $certificateNumber,
            'phone' => $phone ?: null,
        ];

        // Get state stamp if available
        $stateStamp = null;
        if ($enrollment && $enrollment->course) {
            $stateCode = $enrollment->course->state ?? $enrollment->course->state_code ?? null;
            if ($stateCode) {
                $stateStamp = \App\Models\StateStamp::where('state_code', strtoupper($stateCode))
                    ->where('is_active', true)
                    ->first();
            }
        }
        $data['state_stamp'] = $stateStamp;

        // Check if PDF package is available
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificate-pdf', $data);
            $filename = 'certificate-'.($data['student_name'] ? str_replace(' ', '-', $data['student_name']) : 'user').'-'.date('Y-m-d').'.pdf';

            // Send certificate email with PDF
            try {
                $course = $enrollment ? $enrollment->course : null;
                \Mail::to($user->email)->send(new \App\Mail\CertificateGenerated(
                    $user,
                    $course,
                    $certificateNumber,
                    $pdf->output()
                ));
            } catch (\Exception $e) {
                \Log::error('Certificate email error: '.$e->getMessage());
            }

            // Handle access revocation after download
            $accessService = new CertificateAccessService;

            \Log::info('Certificate download', [
                'user_id' => $user->id,
                'enrollment_id' => $request->enrollment_id,
                'has_enrollment_id' => $request->has('enrollment_id'),
            ]);

            $result = $accessService->handleCertificateDownload($user, $request->enrollment_id);

            \Log::info('Access service result', $result);

            if ($result['status'] === 'account_locked') {
                auth()->logout();

                return redirect('/login')->with('error', $result['message']);
            }

            return $pdf->download($filename);
        }

        // Fallback: return HTML view that can be printed as PDF by browser
        return response()->view('certificate-pdf', $data)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="certificate.html"');
    }

    public function generateEnrollment(UserCourseEnrollment $enrollment)
    {
        // Check if enrollment is completed
        if (! $enrollment->completed_at) {
            return response()->json(['error' => 'Course not completed'], 400);
        }

        // Check if certificate already exists
        $existingCertificate = FloridaCertificate::where('enrollment_id', $enrollment->id)->first();
        if ($existingCertificate) {
            return view('certificates.florida-certificate', ['certificate' => $existingCertificate]);
        }

        $certificate = FloridaCertificate::create([
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $this->generateCertificateNumber($enrollment->course->state_code ?? 'FL'),
            'student_name' => $enrollment->user->first_name.' '.$enrollment->user->last_name,
            'course_name' => $enrollment->course->title,
            'state_code' => $enrollment->course->state_code ?? 'FL',
            'completion_date' => $enrollment->completed_at,
            'verification_hash' => Str::random(32),
            'status' => 'generated',
        ]);

        // Dispatch certificate generated event
        event(new CertificateGenerated($certificate));

        return view('certificates.florida-certificate', ['certificate' => $certificate]);
    }

    public function verify($verificationHash)
    {
        $certificate = \App\Models\FloridaCertificate::where('verification_hash', $verificationHash)->first();

        if (! $certificate) {
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
        $certificate = FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
            ->findOrFail($id);

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

    public function emailCertificate(Request $request, $id)
    {
        try {
            \Log::info('Starting certificate email process', ['certificate_id' => $id]);

            $certificate = FloridaCertificate::with(['enrollment.user', 'enrollment.course'])
                ->findOrFail($id);

            \Log::info('Certificate loaded', [
                'certificate_number' => $certificate->dicds_certificate_number,
                'student_name' => $certificate->student_name,
            ]);

            // Get email from request or use student's email
            $recipientEmail = $request->input('email');

            if (! $recipientEmail && $certificate->enrollment && $certificate->enrollment->user) {
                $recipientEmail = $certificate->enrollment->user->email;
            }

            if (! $recipientEmail) {
                \Log::error('No recipient email found', ['certificate_id' => $id]);

                return response()->json(['error' => 'No recipient email address found'], 400);
            }

            \Log::info('Recipient email determined', ['email' => $recipientEmail]);

            // Generate PDF
            \Log::info('Generating certificate PDF');
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.florida-certificate', compact('certificate'));
            $pdfOutput = $pdf->output();
            \Log::info('PDF generated successfully', ['size' => strlen($pdfOutput).' bytes']);

            // Prepare email data
            $emailData = [
                'certificate' => $certificate,
                'student_name' => $certificate->student_name,
                'certificate_number' => $certificate->dicds_certificate_number,
                'course_name' => $certificate->course_name,
                'completion_date' => $certificate->completion_date->format('F d, Y'),
            ];

            \Log::info('Sending email', [
                'to' => $recipientEmail,
                'from' => config('mail.from.address'),
                'subject' => 'Your Course Completion Certificate',
            ]);

            // Send email
            \Mail::send('emails.certificate', $emailData, function ($message) use ($recipientEmail, $certificate, $pdfOutput) {
                $message->to($recipientEmail)
                    ->subject('Your Course Completion Certificate - '.$certificate->dicds_certificate_number)
                    ->attachData($pdfOutput, 'certificate-'.$certificate->dicds_certificate_number.'.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            \Log::info('Email sent successfully', ['to' => $recipientEmail]);

            // Update certificate sent status
            $certificate->update([
                'is_sent_to_student' => true,
                'sent_at' => now(),
            ]);

            \Log::info('Certificate status updated', ['certificate_id' => $id]);

            return response()->json([
                'message' => 'Certificate emailed successfully',
                'sent_to' => $recipientEmail,
                'certificate_number' => $certificate->dicds_certificate_number,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to email certificate', [
                'certificate_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to send certificate email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function download($id)
    {
        $certificate = FloridaCertificate::findOrFail($id);
        $html = view('certificates.florida-certificate', compact('certificate'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificate->dicds_certificate_number.'.html"');
    }

    private function generateCertificateNumber($stateCode = null)
    {
        if ($stateCode) {
            $year = date('Y');
            $lastCertificate = Certificate::where('state_code', $stateCode)
                ->whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastCertificate ?
                (int) substr($lastCertificate->certificate_number, -6) + 1 : 1;

            return $stateCode.'-'.$year.'-'.str_pad($sequence, 6, '0', STR_PAD_LEFT);
        }

        // Default certificate number for /certificate route
        return 'CERT-'.date('Y').'-'.str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
}
