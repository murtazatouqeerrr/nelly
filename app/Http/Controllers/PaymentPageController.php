<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FloridaCourse;
use App\Models\Course;
use App\Models\UserCourseEnrollment;
use App\Models\Payment;
use App\Events\UserEnrolled;
use App\Events\PaymentApproved;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaymentPageController extends Controller
{
    public function create(Request $request)
    {
        $courseId = $request->course_id;
        $table = $request->table ?? 'courses';
        
        // Prefix the course ID with table name for disambiguation
        if ($table === 'florida_courses') {
            $courseId = 'florida_' . $courseId;
        } else {
            $courseId = 'courses_' . $courseId;
        }
        
        $course = $this->findCourse($courseId);
        
        if (!$course) {
            return redirect('/courses')->with('error', 'Course not found');
        }

        // Create pending enrollment
        $enrollment = UserCourseEnrollment::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $this->getRealCourseId($courseId),
        ], [
            'amount_paid' => $course->price,
            'payment_status' => 'pending',
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'enrolled_at' => now(),
            'status' => 'active'
        ]);

        return view('payment.checkout', compact('course', 'enrollment'));
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        if ($enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        $course = $this->findCourse($enrollment->course_id);
        return view('payment.checkout', compact('course', 'enrollment'));
    }

    public function processStripe(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'payment_method_id' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string'
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);
        
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $enrollment->amount_paid * 100, // Convert to cents
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'return_url' => route('payment.success'),
                'metadata' => [
                    'enrollment_id' => $enrollment->id,
                    'user_id' => auth()->id()
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $this->completePayment($enrollment, 'stripe', $paymentIntent->id, $request->only(['address', 'city', 'state', 'country', 'zipcode']));
                return response()->json(['success' => true, 'redirect' => route('payment.success')]);
            }

            return response()->json(['error' => 'Payment failed'], 400);

        } catch (\Exception $e) {
            \Log::error('Stripe payment error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    public function processPaypal(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'paypal_order_id' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string'
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);
        
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Verify PayPal payment (simplified - implement full PayPal SDK integration)
            $this->completePayment($enrollment, 'paypal', $request->paypal_order_id, $request->only(['address', 'city', 'state', 'country', 'zipcode']));
            return response()->json(['success' => true, 'redirect' => route('payment.success')]);

        } catch (\Exception $e) {
            \Log::error('PayPal payment error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    public function processDummy(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'amount' => 'required|numeric|min:0.01'
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);
        
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Use 'stripe' as gateway since enum doesn't include 'dummy' yet
            $this->completePayment($enrollment, 'stripe', 'dummy_' . time() . '_' . auth()->id(), [], 'dummy');
            return response()->json(['success' => true, 'redirect' => route('payment.success')]);

        } catch (\Exception $e) {
            \Log::error('Dummy payment error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    public function success()
    {
        return view('payment.success');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }

    private function completePayment($enrollment, $gateway, $gatewayPaymentId, $addressData = [], $paymentMethod = null)
    {
        // Update enrollment
        $enrollment->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod ?? $gateway,
            'payment_id' => $gatewayPaymentId
        ]);

        // Create payment record
        $payment = Payment::create([
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'amount' => $enrollment->amount_paid,
            'payment_method' => $paymentMethod ?? $gateway,
            'gateway' => $gateway,
            'gateway_payment_id' => $gatewayPaymentId,
            'billing_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'billing_email' => auth()->user()->email,
            'address' => $addressData['address'] ?? null,
            'city' => $addressData['city'] ?? null,
            'state' => $addressData['state'] ?? null,
            'country' => $addressData['country'] ?? null,
            'zipcode' => $addressData['zipcode'] ?? null,
            'status' => 'completed'
        ]);

        // Send enrollment confirmation email
        try {
            $course = $this->findCourse($enrollment->course_id);
            \Mail::to(auth()->user()->email)->send(new \App\Mail\EnrollmentConfirmation(
                auth()->user(),
                $course,
                $enrollment
            ));
        } catch (\Exception $e) {
            \Log::error('Enrollment email error: ' . $e->getMessage());
        }

        // Dispatch events
        event(new UserEnrolled($enrollment));
        event(new PaymentApproved($payment));
    }

    private function findCourse($courseId)
    {
        if (is_numeric($courseId)) {
            return Course::find($courseId) ?? FloridaCourse::find($courseId);
        }
        
        if (str_starts_with($courseId, 'florida_')) {
            $realId = str_replace('florida_', '', $courseId);
            return FloridaCourse::find($realId);
        } elseif (str_starts_with($courseId, 'courses_')) {
            $realId = str_replace('courses_', '', $courseId);
            return Course::find($realId);
        }
        
        return null;
    }

    private function getRealCourseId($courseId)
    {
        if (str_starts_with($courseId, 'florida_')) {
            return str_replace('florida_', '', $courseId);
        } elseif (str_starts_with($courseId, 'courses_')) {
            return str_replace('courses_', '', $courseId);
        }
        return $courseId;
    }
}
