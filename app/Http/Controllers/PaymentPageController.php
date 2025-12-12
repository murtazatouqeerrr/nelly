<?php

namespace App\Http\Controllers;

use App\Events\PaymentApproved;
use App\Events\UserEnrolled;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\Payment;
use App\Models\UserCourseEnrollment;
use Illuminate\Http\Request;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentPageController extends Controller
{
    public function create(Request $request)
    {
        $courseId = $request->course_id;
        $table = $request->table ?? 'courses';

        // Prefix the course ID with table name for disambiguation
        if ($table === 'florida_courses') {
            $courseId = 'florida_'.$courseId;
        } else {
            $courseId = 'courses_'.$courseId;
        }

        $course = $this->findCourse($courseId);

        if (! $course) {
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
            'status' => 'active',
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
            'zipcode' => 'required|string',
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
                    'user_id' => auth()->id(),
                ],
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $this->completePayment($enrollment, 'stripe', $paymentIntent->id, $request->only(['address', 'city', 'state', 'country', 'zipcode']));

                return response()->json(['success' => true, 'redirect' => route('payment.success')]);
            }

            return response()->json(['error' => 'Payment failed'], 400);

        } catch (\Exception $e) {
            \Log::error('Stripe payment error: '.$e->getMessage());

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
            'zipcode' => 'required|string',
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
            \Log::error('PayPal payment error: '.$e->getMessage());

            return response()->json(['error' => 'Payment processing failed'], 500);
        }
    }

    public function processAuthorizenet(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'card_number' => 'required|string',
            'expiry_month' => 'required|string',
            'expiry_year' => 'required|string',
            'cvv' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'zipcode' => 'required|string',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Set the transaction's reference ID
            $refId = 'ref'.time();

            // Get credentials from database or .env fallback
            $gatewayService = app(\App\Services\PaymentGatewayConfigService::class);
            $config = $gatewayService->getAuthorizeNetConfig();

            $loginId = $config['api_login_id'];
            $transactionKey = $config['transaction_key'];
            $environment = $config['environment'];

            // Debug logging
            \Log::info('Authorize.Net Credentials Check', [
                'login_id' => $loginId ? 'Present' : 'MISSING',
                'transaction_key' => $transactionKey ? 'Present' : 'MISSING',
                'environment' => $environment,
                'source' => $config['source'],
            ]);

            // Create a merchant authentication instance
            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType;
            $merchantAuthentication->setName($loginId);
            $merchantAuthentication->setTransactionKey($transactionKey);

            // Create the payment data for a credit card
            $creditCard = new AnetAPI\CreditCardType;
            $creditCard->setCardNumber($request->card_number);
            $creditCard->setExpirationDate($request->expiry_year.'-'.$request->expiry_month);
            $creditCard->setCardCode($request->cvv);

            // Add the payment data to a paymentType object
            $paymentOne = new AnetAPI\PaymentType;
            $paymentOne->setCreditCard($creditCard);

            // Create order information
            $order = new AnetAPI\OrderType;
            $order->setInvoiceNumber('INV-'.$enrollment->id);
            $order->setDescription('Course Enrollment Payment');

            // Set the customer's Bill To address
            $customerAddress = new AnetAPI\CustomerAddressType;
            $customerAddress->setFirstName(auth()->user()->first_name);
            $customerAddress->setLastName(auth()->user()->last_name);
            $customerAddress->setAddress($request->address);
            $customerAddress->setCity($request->city);
            $customerAddress->setState($request->state);
            $customerAddress->setZip($request->zipcode);
            $customerAddress->setCountry($request->country);

            // Set the customer's identifying information
            $customerData = new AnetAPI\CustomerDataType;
            $customerData->setType('individual');
            $customerData->setId(auth()->id());
            $customerData->setEmail(auth()->user()->email);

            // Create a transaction
            $transactionRequestType = new AnetAPI\TransactionRequestType;
            $transactionRequestType->setTransactionType('authCaptureTransaction');
            $transactionRequestType->setAmount($enrollment->amount_paid);
            $transactionRequestType->setOrder($order);
            $transactionRequestType->setPayment($paymentOne);
            $transactionRequestType->setBillTo($customerAddress);
            $transactionRequestType->setCustomer($customerData);

            // Assemble the complete transaction request
            $requestApi = new AnetAPI\CreateTransactionRequest;
            $requestApi->setMerchantAuthentication($merchantAuthentication);
            $requestApi->setRefId($refId);
            $requestApi->setTransactionRequest($transactionRequestType);

            // Create the controller and get the response
            $controller = new AnetController\CreateTransactionController($requestApi);

            // Set endpoint based on mode
            if ($environment === 'production') {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
            } else {
                $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
            }

            if ($response != null) {
                if ($response->getMessages()->getResultCode() == 'Ok') {
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getMessages() != null) {
                        $transactionId = $tresponse->getTransId();

                        $this->completePayment(
                            $enrollment,
                            'authorizenet',
                            $transactionId,
                            $request->only(['address', 'city', 'state', 'country', 'zipcode']),
                            'authorizenet'
                        );

                        return response()->json([
                            'success' => true,
                            'redirect' => route('payment.success'),
                            'transaction_id' => $transactionId,
                        ]);
                    } else {
                        $errorMessage = 'Transaction Failed';
                        if ($tresponse->getErrors() != null) {
                            $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                        }
                        \Log::error('Authorize.Net transaction error: '.$errorMessage);

                        return response()->json(['error' => $errorMessage], 400);
                    }
                } else {
                    $errorMessage = 'Transaction Failed';
                    $tresponse = $response->getTransactionResponse();

                    if ($tresponse != null && $tresponse->getErrors() != null) {
                        $errorMessage = $tresponse->getErrors()[0]->getErrorText();
                    } else {
                        $errorMessage = $response->getMessages()->getMessage()[0]->getText();
                    }

                    \Log::error('Authorize.Net error: '.$errorMessage);

                    return response()->json(['error' => $errorMessage], 400);
                }
            } else {
                \Log::error('Authorize.Net: No response returned');

                return response()->json(['error' => 'No response from payment gateway'], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Authorize.Net payment error: '.$e->getMessage());

            return response()->json(['error' => 'Payment processing failed: '.$e->getMessage()], 500);
        }
    }

    public function processDummy(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $enrollment = UserCourseEnrollment::findOrFail($request->enrollment_id);

        if ($enrollment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Use 'stripe' as gateway since enum doesn't include 'dummy' yet
            $this->completePayment($enrollment, 'stripe', 'dummy_'.time().'_'.auth()->id(), [], 'dummy');

            return response()->json(['success' => true, 'redirect' => route('payment.success')]);

        } catch (\Exception $e) {
            \Log::error('Dummy payment error: '.$e->getMessage());

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
            'payment_id' => $gatewayPaymentId,
        ]);

        // Create payment record
        $payment = Payment::create([
            'user_id' => $enrollment->user_id,
            'enrollment_id' => $enrollment->id,
            'amount' => $enrollment->amount_paid,
            'payment_method' => $paymentMethod ?? $gateway,
            'gateway' => $gateway,
            'gateway_payment_id' => $gatewayPaymentId,
            'billing_name' => auth()->user()->first_name.' '.auth()->user()->last_name,
            'billing_email' => auth()->user()->email,
            'address' => $addressData['address'] ?? null,
            'city' => $addressData['city'] ?? null,
            'state' => $addressData['state'] ?? null,
            'country' => $addressData['country'] ?? null,
            'zipcode' => $addressData['zipcode'] ?? null,
            'status' => 'completed',
        ]);

        // Generate and send payment receipt via email
        try {
            $course = $this->findCourse($enrollment->course_id);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payments.receipt', compact('payment'));

            \Mail::send('emails.payment-receipt', compact('payment', 'course'), function ($message) use ($payment, $pdf) {
                $message->to($payment->billing_email)
                    ->subject('Payment Receipt #'.$payment->id.' - Traffic School')
                    ->attachData($pdf->output(), 'payment-receipt-'.$payment->id.'.pdf');
            });

            \Log::info('Payment receipt sent', ['payment_id' => $payment->id, 'email' => $payment->billing_email]);
        } catch (\Exception $e) {
            \Log::error('Payment receipt email error: '.$e->getMessage());
        }

        // Send enrollment confirmation email
        try {
            $course = $this->findCourse($enrollment->course_id);
            \Mail::to(auth()->user()->email)->send(new \App\Mail\EnrollmentConfirmation(
                auth()->user(),
                $course,
                $enrollment
            ));
        } catch (\Exception $e) {
            \Log::error('Enrollment email error: '.$e->getMessage());
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
