<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        try {
            \Log::info('PaymentController index called');
            $payments = Payment::with(['user', 'enrollment.course'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            \Log::info('Payments loaded successfully', ['count' => $payments->count()]);
            return response()->json($payments);
        } catch (\Exception $e) {
            \Log::error('Error loading payments: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function myPaymentsWeb()
    {
        $payments = Payment::with(['enrollment.course', 'invoice'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        try {
            \Log::info('PaymentController store called', ['request_data' => $request->all()]);
            
            $request->validate([
                'user_email' => 'required|email|exists:users,email',
                'course_id' => 'required|exists:florida_courses,id',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|string',
                'status' => 'required|in:completed,pending,failed',
            ]);
            
            \Log::info('Validation passed');

            $user = \App\Models\User::where('email', $request->user_email)->first();
            \Log::info('User found', ['user_id' => $user->id, 'user_name' => $user->first_name . ' ' . $user->last_name]);
            
            // Create or get enrollment
            $enrollment = \App\Models\UserCourseEnrollment::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $request->course_id,
            ]);
            \Log::info('Enrollment created/found', ['enrollment_id' => $enrollment->id]);

            $paymentData = [
                'user_id' => $user->id,
                'enrollment_id' => $enrollment->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'gateway' => 'stripe',
                'gateway_payment_id' => 'manual_' . time() . '_' . $user->id,
                'billing_name' => $user->first_name . ' ' . $user->last_name,
                'billing_email' => $user->email,
                'status' => $request->status,
            ];
            
            \Log::info('Payment data prepared', ['payment_data' => $paymentData]);

            $payment = Payment::create($paymentData);
            \Log::info('Payment created successfully', ['payment_id' => $payment->id]);

            // Invoice will be created automatically by PaymentObserver

            return response()->json($payment->load(['user', 'enrollment.course', 'invoice']));
        } catch (\Exception $e) {
            \Log::error('Error creating payment: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Payment $payment)
    {
        try {
            $request->validate([
                'amount' => 'sometimes|numeric|min:0',
                'payment_method' => 'sometimes|string',
                'status' => 'sometimes|in:completed,pending,failed',
            ]);

            $payment->update($request->only(['amount', 'payment_method', 'status']));
            return response()->json($payment->load(['user', 'enrollment.course']));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Payment $payment)
    {
        try {
            $payment->delete();
            return response()->json(['message' => 'Payment deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function downloadPDF(Payment $payment)
    {
        $payment->load(['user', 'enrollment.course']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.receipt', compact('payment'));
        
        return $pdf->download('payment-receipt-' . $payment->id . '.pdf');
    }

    public function emailReceipt(Payment $payment)
    {
        $payment->load(['user', 'enrollment.course']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.receipt', compact('payment'));
        
        \Mail::send('emails.payment-receipt', compact('payment'), function ($message) use ($payment, $pdf) {
            $message->to($payment->user->email)
                    ->subject('Payment Receipt #' . $payment->id)
                    ->attachData($pdf->output(), 'payment-receipt-' . $payment->id . '.pdf');
        });

        return response()->json(['message' => 'Receipt sent successfully']);
    }

    public function show(Payment $payment)
    {
        $payment->load(['user', 'enrollment.course', 'invoice', 'refunds']);
        return response()->json($payment);
    }

    public function refund(Request $request, Payment $payment)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:' . $payment->amount,
            'reason' => 'required|string|max:255',
        ]);

        $refund = Refund::create([
            'payment_id' => $payment->id,
            'amount' => $request->amount,
            'reason' => $request->reason,
            'status' => 'completed',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Update payment status based on refund amount
        $totalRefunded = $payment->refunds()->sum('amount');
        
        if ($totalRefunded >= $payment->amount) {
            $payment->update(['status' => 'refunded']);
        } elseif ($totalRefunded > 0) {
            $payment->update(['status' => 'partially_refunded']);
        }

        return response()->json([
            'refund' => $refund,
            'payment' => $payment->fresh(),
            'total_refunded' => $totalRefunded,
            'remaining_amount' => $payment->amount - $totalRefunded
        ]);
    }

    public function showPayment(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue');
        }

        $courseId = $request->course_id;
        $table = $request->input('table', 'florida_courses');
        $user = auth()->user();
        
        // Validate table parameter
        if (!in_array($table, ['courses', 'florida_courses'])) {
            return redirect()->back()->with('error', 'Invalid course table specified');
        }
        
        // Determine which model to use based on table parameter
        try {
            if ($table === 'courses') {
                $course = \App\Models\Course::findOrFail($courseId);
            } else {
                $course = \App\Models\FloridaCourse::findOrFail($courseId);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Course not found');
        }
        
        // Create or get enrollment (required for checkout page)
        $enrollment = \App\Models\UserCourseEnrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], [
            'amount_paid' => $course->price ?? 0,
            'payment_status' => 'pending',
            'enrolled_at' => now(),
            'status' => 'active', // Valid values: active, completed, expired, cancelled
        ]);
        
        return view('payment.checkout', compact('course', 'enrollment'));
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'table' => 'required|in:courses,florida_courses',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $table = $request->table;
        
        // Get course from appropriate table
        if ($table === 'courses') {
            $course = \App\Models\Course::findOrFail($request->course_id);
        } else {
            $course = \App\Models\FloridaCourse::findOrFail($request->course_id);
        }

        // Create enrollment
        $enrollment = \App\Models\UserCourseEnrollment::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ], [
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        // Create payment (invoice will be created automatically by PaymentObserver)
        $payment = Payment::create([
            'user_id' => $user->id,
            'enrollment_id' => $enrollment->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'gateway' => $request->payment_method === 'stripe' ? 'stripe' : 'manual',
            'gateway_payment_id' => 'pay_' . time() . '_' . $user->id,
            'billing_name' => $user->first_name . ' ' . $user->last_name,
            'billing_email' => $user->email,
            'status' => 'completed',
        ]);

        // Clear pending enrollment session
        session()->forget('pending_course_enrollment');

        return redirect()->route('course-player', ['enrollmentId' => $enrollment->id])
            ->with('success', 'Payment successful! You can now access the course.');
    }
}
