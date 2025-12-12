<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FloridaSoapProxyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Florida SOAP Proxy - allows requests from any IP to reach Florida's server
Route::post('/api/florida-soap-proxy', [FloridaSoapProxyController::class, 'proxy']);

// File serving route with different pattern
Route::get('files/{filename}', function ($filename) {
    $path = storage_path('app/public/course-media/'.$filename);

    if (! file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

// Direct file serving route
Route::get('storage/course-media/1761175955_indian-man-7061278_640__1_.jpg', function () {
    $path = storage_path('app/public/course-media/1761175955_indian-man-7061278_640__1_.jpg');

    return response()->file($path);
});

// Debug route to test file access
Route::get('test-file', function () {
    $filename = '1761175955_indian-man-7061278_640__1_.jpg';
    $path = storage_path('app/public/course-media/'.$filename);

    return response()->json([
        'filename' => $filename,
        'path' => $path,
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size' => file_exists($path) ? filesize($path) : 0,
        'storage_path' => storage_path('app/public/course-media/'),
        'files_in_dir' => scandir(storage_path('app/public/course-media/')),
    ]);
});

// Storage route for serving files
Route::get('storage/course-media/{filename}', function ($filename) {
    $path = storage_path('app/public/course-media/'.$filename);

    \Log::info('Storage route hit', ['filename' => $filename, 'path' => $path, 'exists' => file_exists($path)]);

    if (! file_exists($path)) {
        \Log::error('File not found', ['path' => $path]);
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

Route::get('/', function () {
    return redirect('/dashboard');
});

// Include DICDS routes
Route::prefix('dicds')->group(base_path('routes/dicds.php'));

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Test route to check authentication status
Route::get('/auth-test', function () {
    \Log::info('Auth test route accessed', [
        'is_authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session_token' => session('jwt_token'),
    ]);

    return response()->json([
        'is_authenticated' => auth()->check(),
        'user' => auth()->user(),
        'session_token' => session('jwt_token'),
        'session_id' => session()->getId(),
    ]);
});

// Test route with built-in auth middleware
Route::get('/auth-test-middleware', function () {
    return response()->json([
        'message' => 'You are authenticated!',
        'user' => auth()->user(),
    ]);
})->middleware('auth');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth');

Route::get('/courses', function () {
    return view('courses');
})->middleware('auth');

Route::get('/course-details/{table}/{courseId}', [App\Http\Controllers\CourseController::class, 'showDetails'])->middleware('auth');

Route::get('/api/courses/public', [App\Http\Controllers\CourseController::class, 'publicIndex'])->middleware('auth');

Route::get('/certificates', function () {
    return view('certificates');
})->middleware('auth');

Route::get('/generate-certificates', function () {
    $user = auth()->user();
    $enrollments = \App\Models\UserCourseEnrollment::where('user_id', $user->id)
        ->with('course')
        ->get();

    return view('certificates.select', compact('enrollments'));
})->middleware('auth');

Route::get('/generate-certificate/{enrollment_id}', function ($enrollment_id) {
    $user = auth()->user();
    $enrollment = \App\Models\UserCourseEnrollment::where('user_id', $user->id)
        ->where('id', $enrollment_id)
        ->with('course')
        ->firstOrFail();

    // Check if survey is required and completed
    $surveyService = app(\App\Services\SurveyService::class);
    if (! $surveyService->hasCompletedRequiredSurvey($enrollment)) {
        return redirect()->route('survey.show', $enrollment->id)
            ->with('message', 'Please complete the survey before receiving your certificate.');
    }

    $params = [
        'student_name' => $user->name,
        'completion_date' => $enrollment->completed_at ? $enrollment->completed_at->format('m/d/Y') : now()->format('m/d/Y'),
        'score' => '95%',
        'course_name' => $enrollment->course->title,
        'enrollment_id' => $enrollment->id,
    ];

    $review = \App\Models\Review::where('user_id', $user->id)
        ->where('enrollment_id', $enrollment_id)
        ->first();

    if ($review) {
        return redirect('/certificate?'.http_build_query($params));
    }

    return redirect('/review-course?'.http_build_query($params));
})->middleware('auth');

Route::get('/review-course', [App\Http\Controllers\ReviewController::class, 'show'])->middleware('auth')->name('review-course');
Route::post('/submit-review', [App\Http\Controllers\ReviewController::class, 'store'])->middleware('auth')->name('submit-review');

Route::get('/certificates/verify/{hash}', function ($hash) {
    $certificate = \App\Models\FloridaCertificate::where('verification_hash', $hash)->first();

    if (! $certificate) {
        abort(404, 'Certificate not found');
    }

    return view('certificates.verify', compact('certificate'));
});

// Serve storage files
Route::get('/files/{path}', function ($path) {
    $filePath = storage_path('app/public/'.$path);

    if (! file_exists($filePath)) {
        // Try course-media subdirectory
        $filePath = storage_path('app/public/course-media/'.$path);
        if (! file_exists($filePath)) {
            abort(404);
        }
    }

    return response()->file($filePath);
})->where('path', '.*');

Route::get('/my-certificates', function () {
    return view('my-certificates');
})->middleware('auth')->name('my-certificates');

Route::get('/open-ticket', function () {
    return view('open-ticket');
})->middleware('auth')->name('open-ticket');

Route::get('/my-enrollments', function () {
    return view('my-enrollments');
})->middleware('auth')->name('my-enrollments');

Route::get('/course-player/{enrollmentId}', function ($enrollmentId) {
    $enrollment = \App\Models\UserCourseEnrollment::where('id', $enrollmentId)
        ->where('user_id', auth()->id())
        ->first();

    if (! $enrollment) {
        return redirect('/dashboard')->with('error', 'Enrollment not found');
    }

    if ($enrollment->access_revoked) {
        return redirect('/dashboard')->with('error', 'Access to this course has been revoked after certificate download');
    }

    return view('course-player');
})->middleware('auth');

Route::get('/course-player', function () {
    $enrollmentId = request()->get('enrollmentId');

    if ($enrollmentId) {
        $enrollment = \App\Models\UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $enrollment) {
            return redirect('/dashboard')->with('error', 'Enrollment not found');
        }

        if ($enrollment->access_revoked) {
            return redirect('/dashboard')->with('error', 'Access to this course has been revoked after certificate download');
        }
    }

    return view('course-player');
})->middleware('auth');

Route::get('/profile', function () {
    return view('profile');
})->middleware('auth');

Route::get('/my-payments', function () {
    return view('my-payments');
})->middleware('auth');

// Public invoice routes for users
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'showPublic'])->name('invoice.show');
    Route::get('/invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'downloadPublic'])->name('invoice.download');
});

// Booklet routes for users
Route::middleware(['auth'])->prefix('booklets')->name('booklets.')->group(function () {
    Route::get('/', [App\Http\Controllers\BookletOrderController::class, 'index'])->name('index');
    Route::get('/order/{enrollment}', [App\Http\Controllers\BookletOrderController::class, 'create'])->name('create');
    Route::post('/order/{enrollment}', [App\Http\Controllers\BookletOrderController::class, 'store'])->name('store');
    Route::get('/{order}', [App\Http\Controllers\BookletOrderController::class, 'show'])->name('show');
    Route::get('/{order}/download', [App\Http\Controllers\BookletOrderController::class, 'download'])->name('download');
});

Route::get('/create-course', function () {
    return view('create-course');
})->middleware('auth', 'role:super-admin,admin');

// Web routes for course operations (using session auth)
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::post('/web/courses', [App\Http\Controllers\CourseController::class, 'storeWeb']);
    Route::match(['PUT', 'POST'], '/web/courses/{course}', [App\Http\Controllers\CourseController::class, 'updateWeb']);
    Route::post('/web/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
    Route::match(['PUT', 'POST'], '/web/chapters/{chapter}', [App\Http\Controllers\ChapterController::class, 'updateWeb']);
    Route::delete('/web/chapters/{chapter}', [App\Http\Controllers\ChapterController::class, 'destroyWeb']);
});

// Payment routes
Route::middleware('auth')->group(function () {
    Route::get('/payment', [App\Http\Controllers\PaymentPageController::class, 'create'])->name('payment.create');
    Route::post('/payment/stripe', [App\Http\Controllers\PaymentPageController::class, 'processStripe'])->name('payment.stripe');
    Route::post('/payment/authorizenet', [App\Http\Controllers\PaymentPageController::class, 'processAuthorizenet'])->name('payment.authorizenet');
    Route::post('/payment/paypal', [App\Http\Controllers\PaymentPageController::class, 'processPaypal'])->name('payment.paypal');
    Route::post('/payment/dummy', [App\Http\Controllers\PaymentPageController::class, 'processDummy'])->name('payment.dummy');
    Route::get('/payment/success', [App\Http\Controllers\PaymentPageController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [App\Http\Controllers\PaymentPageController::class, 'cancel'])->name('payment.cancel');
});

// Web routes for enrollments (using session auth)
Route::middleware('auth')->group(function () {
    Route::post('/web/enrollments', [App\Http\Controllers\EnrollmentController::class, 'storeWeb']);
    Route::get('/web/my-enrollments', [App\Http\Controllers\EnrollmentController::class, 'myEnrollmentsWeb']);
});

// Web routes for user profile (using session auth)
Route::middleware('auth')->group(function () {
    Route::get('/web/user', [App\Http\Controllers\AuthController::class, 'userWeb']);
    Route::put('/web/user', [App\Http\Controllers\AuthController::class, 'updateProfileWeb']);
    Route::get('/web/enrollments/{enrollment}', [App\Http\Controllers\EnrollmentController::class, 'showWeb']);
    Route::get('/web/courses', [App\Http\Controllers\CourseController::class, 'indexWeb']);
    Route::get('/web/courses/{course}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::match(['GET', 'POST'], '/web/enrollments/{enrollment}/complete-chapter/{chapter}', [App\Http\Controllers\ProgressController::class, 'completeChapterWeb']);
    Route::get('/web/my-payments', [App\Http\Controllers\PaymentController::class, 'myPaymentsWeb']);
});

// Web routes for admin (using session auth)
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/web/users', [App\Http\Controllers\UserController::class, 'indexWeb']);
    Route::post('/web/users', [App\Http\Controllers\UserController::class, 'storeWeb']);
    Route::put('/web/users/{user}', [App\Http\Controllers\UserController::class, 'updateWeb']);
    Route::delete('/web/users/{user}', [App\Http\Controllers\UserController::class, 'destroyWeb']);
    Route::get('/web/enrollments', [App\Http\Controllers\EnrollmentController::class, 'indexWeb']);
    Route::get('/web/admin/reports', [App\Http\Controllers\ReportController::class, 'indexWeb']);
    Route::get('/web/admin/reports/generate', [App\Http\Controllers\ReportController::class, 'generateWeb']);
    Route::get('/web/admin/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'getStatsWeb']);

    // Admin payments CRUD
    Route::get('/web/admin/payments', [App\Http\Controllers\PaymentController::class, 'index']);
    Route::post('/web/admin/payments', [App\Http\Controllers\PaymentController::class, 'store']);
    Route::get('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'show']);
    Route::put('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'update']);
    Route::delete('/web/admin/payments/{payment}', [App\Http\Controllers\PaymentController::class, 'destroy']);
    Route::post('/web/admin/payments/{payment}/refund', [App\Http\Controllers\PaymentController::class, 'refund']);
    Route::get('/web/admin/payments/{payment}/pdf', [App\Http\Controllers\PaymentController::class, 'downloadPDF']);
    Route::post('/web/admin/payments/{payment}/email', [App\Http\Controllers\PaymentController::class, 'emailReceipt']);

    // Admin invoices CRUD
    Route::get('/web/admin/invoices', [App\Http\Controllers\InvoiceController::class, 'index']);
    Route::post('/web/admin/invoices', [App\Http\Controllers\InvoiceController::class, 'store']);
    Route::get('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'show']);
    Route::put('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'update']);
    Route::delete('/web/admin/invoices/{invoice}', [App\Http\Controllers\InvoiceController::class, 'destroy']);
    Route::post('/web/admin/invoices/{invoice}/send', [App\Http\Controllers\InvoiceController::class, 'send']);
    Route::get('/web/admin/invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'download']);
    Route::post('/web/admin/invoices/{invoice}/email', [App\Http\Controllers\InvoiceController::class, 'emailInvoice']);

    // Admin certificates CRUD
    Route::get('/web/admin/certificates', [App\Http\Controllers\CertificateController::class, 'index']);
    Route::post('/web/admin/certificates', [App\Http\Controllers\CertificateController::class, 'store']);
    Route::get('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'show']);
    Route::put('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'update']);
    Route::delete('/web/admin/certificates/{certificate}', [App\Http\Controllers\CertificateController::class, 'destroy']);
    Route::post('/web/admin/certificates/{certificate}/submit-to-state', [App\Http\Controllers\CertificateController::class, 'submitToState']);
    Route::get('/web/admin/certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'download']);
    Route::post('/web/admin/certificates/{certificate}/email', [App\Http\Controllers\CertificateController::class, 'emailCertificate']);

    // State Integration Web Routes
    Route::get('/web/admin/state-configurations', [App\Http\Controllers\StateConfigurationController::class, 'index']);
    Route::post('/web/admin/state-configurations', [App\Http\Controllers\StateConfigurationController::class, 'store']);
    Route::get('/web/admin/state-configurations/{stateCode}/test-connection', [App\Http\Controllers\StateConfigurationController::class, 'testConnection']);
    Route::delete('/web/admin/state-configurations/{stateConfiguration}', [App\Http\Controllers\StateConfigurationController::class, 'destroy']);
    Route::get('/web/admin/courts/states', [App\Http\Controllers\CountyController::class, 'index']);
    Route::post('/web/admin/courts/states', [App\Http\Controllers\CountyController::class, 'storeState']);
    Route::delete('/web/admin/courts/states/{state}', [App\Http\Controllers\CountyController::class, 'deleteState']);
    Route::get('/web/admin/courts/{state}/counties', [App\Http\Controllers\CountyController::class, 'getCounties']);
    Route::post('/web/admin/courts/{state}/counties', [App\Http\Controllers\CountyController::class, 'storeCounty']);
    Route::delete('/web/admin/courts/{state}/counties/{county}', [App\Http\Controllers\CountyController::class, 'deleteCounty']);
    Route::get('/web/admin/courts/{state}/{county}', [App\Http\Controllers\CountyController::class, 'getCourts']);
    Route::post('/web/admin/courts', [App\Http\Controllers\CountyController::class, 'storeCourt']);
    Route::put('/web/admin/courts/{id}', [App\Http\Controllers\CountyController::class, 'updateCourt']);
    Route::delete('/web/admin/courts/{id}', [App\Http\Controllers\CountyController::class, 'deleteCourt']);

    Route::get('/web/admin/submission-queue/stats', [App\Http\Controllers\StateSubmissionController::class, 'stats']);
    Route::post('/web/admin/submission-queue/process-pending', [App\Http\Controllers\StateSubmissionController::class, 'processPending']);
    Route::get('/web/admin/submission-queue', [App\Http\Controllers\StateSubmissionController::class, 'index']);
    Route::post('/web/admin/submission-queue/{id}/retry', [App\Http\Controllers\StateSubmissionController::class, 'retry']);

    // Email Templates Web Routes
    Route::get('/web/admin/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'index']);
    Route::post('/web/admin/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'store']);
    Route::get('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'show']);
    Route::put('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'update']);
    Route::delete('/web/admin/email-templates/{emailTemplate}', [App\Http\Controllers\EmailTemplateController::class, 'destroy']);
    Route::post('/web/admin/email-templates/{emailTemplate}/test', [App\Http\Controllers\EmailTemplateController::class, 'test']);

    Route::get('/web/admin/email-logs', [App\Http\Controllers\EmailLogController::class, 'index']);
    Route::get('/web/admin/email-logs/stats', [App\Http\Controllers\EmailLogController::class, 'stats']);
});

// Public certificate verification
Route::get('/certificates/{verificationHash}/verify', [App\Http\Controllers\CertificateController::class, 'verify']);

Route::get('/admin/florida-courses', function () {
    return view('admin.florida-courses');
});

Route::get('/admin/florida-certificates', function () {
    return view('admin.florida-certificates');
});

Route::get('/admin/dicds-orders', function () {
    return view('admin.dicds-orders');
});

Route::get('/admin/florida-dashboard', function () {
    return view('admin.florida-dashboard');
});

Route::get('/admin/certificate-inventory', function () {
    return view('admin.certificate-inventory');
});

Route::get('/admin/compliance-reports', function () {
    return view('admin.compliance-reports');
});

Route::get('/admin/florida-payments', function () {
    return view('admin.florida-payments');
});

Route::get('/admin/fee-remittances', function () {
    return view('admin.fee-remittances');
});

Route::get('/admin/pricing-rules', function () {
    return view('admin.pricing-rules');
});

// New Modules Routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/flhsmv/submissions', [App\Http\Controllers\FlhsmvController::class, 'listSubmissions']);
    Route::get('/admin/payments/transactions', function () {
        return view('admin.payments.transactions');
    });
    Route::get('/admin/payments/stripe', function () {
        return view('admin.payments.stripe');
    });
    Route::get('/admin/payments/paypal', function () {
        return view('admin.payments.paypal');
    });
    Route::get('/admin/course-timers', function () {
        return view('admin.course-timers');
    });

    // State Stamps Admin routes
    Route::get('/admin/state-stamps', [App\Http\Controllers\StateStampController::class, 'index']);
    Route::post('/admin/state-stamps', [App\Http\Controllers\StateStampController::class, 'store']);
    Route::put('/admin/state-stamps/{id}', [App\Http\Controllers\StateStampController::class, 'update']);
    Route::delete('/admin/state-stamps/{id}', [App\Http\Controllers\StateStampController::class, 'destroy']);

    Route::get('/admin/support/tickets', [App\Http\Controllers\SupportTicketController::class, 'index']);
    Route::get('/admin/support/recipients', [App\Http\Controllers\TicketRecipientController::class, 'index'])->name('ticket-recipients.index');
    Route::post('/admin/support/recipients', [App\Http\Controllers\TicketRecipientController::class, 'store'])->name('ticket-recipients.store');
    Route::delete('/admin/support/recipients/{recipient}', [App\Http\Controllers\TicketRecipientController::class, 'destroy'])->name('ticket-recipients.destroy');
    Route::patch('/admin/support/recipients/{recipient}/toggle', [App\Http\Controllers\TicketRecipientController::class, 'toggle'])->name('ticket-recipients.toggle');
    Route::get('/admin/user-access', [App\Http\Controllers\UserAccessController::class, 'index'])->name('user-access.index');
    Route::patch('/admin/user-access/{user}/unlock', [App\Http\Controllers\UserAccessController::class, 'unlock'])->name('user-access.unlock');
    Route::get('/admin/faqs', [App\Http\Controllers\FaqController::class, 'index']);
    Route::get('/admin/counties', function () {
        return view('admin.counties');
    });
    Route::get('/admin/question-banks', function () {
        return view('admin.question-banks');
    });
});

Route::get('/admin/florida-email-templates', function () {
    return view('admin.florida-email-templates');
});

Route::get('/admin/dicds-submissions', function () {
    return view('admin.dicds-submissions');
});

Route::get('/admin/certificate-lookup', function () {
    return view('admin.certificate-lookup');
});

Route::get('/admin/school-activity', function () {
    return view('admin.school-activity');
});

Route::get('/admin/web-service-info', function () {
    return view('admin.web-service-info');
});

Route::get('/admin/legal-documents', function () {
    return view('admin.legal-documents');
});

Route::get('/admin/copyright-protection', function () {
    return view('admin.copyright-protection');
});

Route::get('/admin/user-consents', function () {
    return view('admin.user-consents');
});

// Web routes for DICDS order management
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/web/dicds-orders', [App\Http\Controllers\FloridaApprovalController::class, 'indexWeb']);
    Route::post('/web/dicds-orders', [App\Http\Controllers\DicdsOrderController::class, 'storeWeb']);
    Route::put('/web/dicds-orders/{id}/amend', [App\Http\Controllers\DicdsOrderAmendmentController::class, 'amendWeb']);
    Route::post('/web/dicds-orders/{id}/generate-receipt', [App\Http\Controllers\DicdsReceiptController::class, 'generateWeb']);
    Route::put('/web/dicds-orders/{id}/update-approval', [App\Http\Controllers\FloridaApprovalController::class, 'updateApprovalWeb']);
    Route::get('/web/florida-schools', [App\Http\Controllers\FloridaSchoolController::class, 'indexWeb']);
    Route::get('/web/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'indexWeb']);
    Route::get('/api/florida-courses', [App\Http\Controllers\FloridaCourseController::class, 'indexWeb']);
    Route::put('/api/florida-courses/{id}', [App\Http\Controllers\FloridaCourseController::class, 'updateWeb']);
    Route::get('/api/florida-certificates', function () {
        $certificates = \App\Models\FloridaCertificate::orderBy('created_at', 'desc')->get();

        return response()->json($certificates);
    });
    Route::get('/api/admin/florida-dashboard/stats', function () {
        $inventory = [
            ['course_type' => 'BDI', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
            ['course_type' => 'ADI', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
            ['course_type' => 'TLSAE', 'total_ordered' => 0, 'total_used' => 0, 'available_count' => 0],
        ];

        return response()->json([
            'available' => 0,
            'used_this_month' => \App\Models\FloridaCertificate::whereMonth('completion_date', now()->month)->count(),
            'pending' => 0,
            'failed' => 0,
            'inventory' => $inventory,
            'recent_submissions' => \App\Models\FloridaCertificate::orderBy('created_at', 'desc')->limit(5)->get(),
        ]);
    });

    Route::get('/api/admin/certificate-inventory', function () {
        return response()->json(\App\Models\CertificateInventory::all());
    });

    Route::get('/api/admin/florida-reports', function () {
        return response()->json(\App\Models\FloridaComplianceReport::with('generator')->orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/admin/florida-reports/generate', function (Illuminate\Http\Request $request) {
        $report = \App\Models\FloridaComplianceReport::create([
            'report_type' => $request->report_type,
            'report_date' => now(),
            'data_range_start' => $request->data_range_start,
            'data_range_end' => $request->data_range_end,
            'generated_by' => auth()->id(),
        ]);

        return response()->json($report);
    });

    Route::get('/api/admin/florida-reports/{id}/download', function ($id) {
        $report = \App\Models\FloridaComplianceReport::findOrFail($id);

        return response()->json(['message' => 'Download functionality to be implemented']);
    });

    Route::get('/api/florida-payments', function () {
        $payments = \App\Models\FloridaPayment::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'payments' => $payments,
            'total_revenue' => $payments->sum('total_amount'),
            'florida_fees' => $payments->sum('florida_assessment_fee'),
            'pending_remittance' => $payments->where('florida_fee_remitted', false)->sum('florida_assessment_fee'),
        ]);
    });

    Route::get('/api/florida-remittances', function () {
        return response()->json(\App\Models\FloridaFeeRemittance::with('submitter')->orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/florida-remittances', function (Illuminate\Http\Request $request) {
        $remittance = \App\Models\FloridaFeeRemittance::create([
            'remittance_date' => $request->remittance_date,
            'total_assessment_fees' => $request->total_assessment_fees,
            'total_courses' => $request->total_courses,
            'payment_method' => $request->payment_method,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return response()->json($remittance);
    });

    Route::post('/api/florida-remittances/{id}/submit', function (Illuminate\Http\Request $request, $id) {
        $remittance = \App\Models\FloridaFeeRemittance::findOrFail($id);
        $remittance->update([
            'florida_reference_number' => $request->florida_reference_number,
            'processed_by_florida' => true,
            'processed_at' => now(),
        ]);

        return response()->json($remittance);
    });

    Route::get('/api/pricing-rules', function () {
        return response()->json(\App\Models\FloridaPricingRule::where('is_active', true)->get());
    });

    Route::post('/api/pricing-rules', function (Illuminate\Http\Request $request) {
        $rule = \App\Models\FloridaPricingRule::create($request->all());

        return response()->json($rule);
    });

    Route::get('/api/florida-email-templates', function () {
        return response()->json(\App\Models\FloridaEmailTemplate::orderBy('created_at', 'desc')->get());
    });

    Route::post('/api/florida-email-templates', function (Illuminate\Http\Request $request) {
        $template = \App\Models\FloridaEmailTemplate::create(array_merge($request->all(), ['created_by' => auth()->id()]));

        return response()->json($template);
    });

    Route::post('/api/florida-email-templates/{id}/test', function (Illuminate\Http\Request $request, $id) {
        $template = \App\Models\FloridaEmailTemplate::findOrFail($id);
        \App\Services\FloridaMailService::send($request->email, $template->subject, $template->content);

        return response()->json(['message' => 'Test email sent']);
    });
    Route::get('/api/florida-certificates/{id}/view', function ($id) {
        $certificate = \App\Models\FloridaCertificate::findOrFail($id);

        return view('certificates.florida-certificate', compact('certificate'));
    });
    Route::get('/api/florida-certificates/{id}/download', function ($id) {
        $certificate = \App\Models\FloridaCertificate::findOrFail($id);
        $html = view('certificates.florida-certificate', compact('certificate'))->render();

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificate->dicds_certificate_number.'.html"');
    });
    Route::get('/api/email-logs-stats', function () {
        return response()->json([
            'total_sent' => 0,
            'total_failed' => 0,
            'total_pending' => 0,
        ]);
    });
    Route::get('/api/email-logs', function () {
        return response()->json([]);
    });
    Route::post('/api/notifications/send', function (Request $request) {
        $user = \App\Models\User::where('email', $request->user_email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Create notification record (if you have a notifications table)
        // For now, just return success
        return response()->json([
            'message' => 'Notification sent successfully',
            'user' => $user->name,
            'type' => $request->type,
        ]);
    });
    Route::get('/api/pwa/manifest', function (Request $request) {
        $theme = $request->cookie('theme', 'dark-blue');

        $themes = [
            'dark-blue' => [
                'background_color' => '#1e3a5f',
                'theme_color' => '#4a90e2',
            ],
            'dark' => [
                'background_color' => '#1a1a1a',
                'theme_color' => '#ffffff',
            ],
            'light' => [
                'background_color' => '#f0f8ff',
                'theme_color' => '#87ceeb',
            ],
        ];

        $colors = $themes[$theme] ?? $themes['dark-blue'];

        return response()->json([
            'name' => 'Traffic School',
            'short_name' => 'TrafficSchool',
            'description' => 'Online Traffic School Management System',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => $colors['background_color'],
            'theme_color' => $colors['theme_color'],
            'icons' => [
                [
                    'src' => '/favicon.ico',
                    'sizes' => '64x64',
                    'type' => 'image/x-icon',
                ],
            ],
        ]);
    });
    Route::get('/web/data-export/download', function (Request $request) {
        $type = $request->query('type');
        $format = $request->query('format', 'html');
        $user = auth()->user();

        $data = [
            'user' => $user,
            'type' => $type,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        // Generate HTML content
        $html = view('exports.data-export', $data)->render();

        if ($format === 'pdf') {
            // For PDF, return HTML with print-friendly CSS
            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="export-'.$type.'-'.time().'.html"');
        }

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="export-'.$type.'-'.time().'.html"');
    });
    Route::get('/api/chapters', [App\Http\Controllers\ChapterController::class, 'getAllChapters']);

    // Timer API routes
    Route::get('/api/timer/list', [App\Http\Controllers\TimerController::class, 'list']);
    Route::post('/api/timer/configure', [App\Http\Controllers\TimerController::class, 'configure']);
    Route::post('/api/timer/toggle/{id}', [App\Http\Controllers\TimerController::class, 'toggle']);
    Route::delete('/api/timer/delete/{id}', [App\Http\Controllers\TimerController::class, 'delete']);
    Route::get('/api/timer/chapter/{id}', [App\Http\Controllers\TimerController::class, 'getForChapter']);

    // State Stamps API routes
    Route::get('/api/state-stamps/{stateCode}', [App\Http\Controllers\StateStampController::class, 'getByStateCode']);

    Route::get('/api/florida-courses/{id}/chapters', [App\Http\Controllers\ChapterController::class, 'indexWeb']);
    Route::post('/api/florida-courses/{id}/chapters', [App\Http\Controllers\ChapterController::class, 'storeWeb']);
    Route::get('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'show']);
    Route::put('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'updateWeb']);
    Route::delete('/api/chapters/{id}', [App\Http\Controllers\ChapterController::class, 'destroyWeb']);
    Route::get('/api/chapters/{id}/questions', [App\Http\Controllers\QuestionController::class, 'index']);
    Route::post('/api/chapters/{id}/questions', [App\Http\Controllers\QuestionController::class, 'store']);
    Route::get('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'show']);
    Route::put('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'update']);
    Route::delete('/api/questions/{id}', [App\Http\Controllers\QuestionController::class, 'destroy']);

    // Chapter Quiz Results
    Route::post('/api/chapter-quiz-results', [App\Http\Controllers\ChapterController::class, 'saveQuizResults']);

    // Certificate Lookup
    Route::post('/web/certificate-lookup', [App\Http\Controllers\CertificateLookupController::class, 'search']);
    Route::post('/web/certificate-lookup/{id}/reprint', [App\Http\Controllers\CertificateLookupController::class, 'reprint']);

    // School Activity Reports
    Route::post('/web/school-activity-reports/generate', [App\Http\Controllers\SchoolActivityController::class, 'generate']);
    Route::get('/web/school-activity-reports', [App\Http\Controllers\SchoolActivityController::class, 'index']);

    // Web Service Info
    Route::get('/web/dicds-web-service-info', function () {
        return response()->json(App\Models\DicdsWebServiceInfo::with('school')->get());
    });

    // Legal Documents
    Route::get('/web/legal-documents', [App\Http\Controllers\LegalDocumentController::class, 'index']);
    Route::post('/web/legal-documents', [App\Http\Controllers\LegalDocumentController::class, 'store']);

    // Copyright Protection
    Route::get('/web/copyright-protection/stats', [App\Http\Controllers\CopyrightProtectionController::class, 'stats']);

    // User Consents
    Route::get('/web/user-consents', function () {
        return response()->json(App\Models\UserLegalConsent::with(['user', 'document'])->get());
    });
});

Route::get('/certificate-verification', function () {
    return view('certificate-verification');
});

// Admin routes
Route::middleware(['auth', 'role:super-admin,admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('dashboard');
    });
    Route::get('/admin/enrollments', function () {
        return view('admin.enrollments');
    });
    Route::get('/admin/enrollments/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'show'])->name('admin.enrollments.show');
    Route::post('/admin/enrollments/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'update'])->name('admin.enrollments.update');

    // Enrollment API actions
    Route::post('/api/resend-certificate/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'resendCertificate']);
    Route::post('/api/resend-transmission/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'resendTransmission']);
    Route::post('/api/email-receipt/{id}', [App\Http\Controllers\Admin\EnrollmentAdminController::class, 'emailReceipt']);

    // Announcements
    Route::resource('announcements', App\Http\Controllers\AnnouncementController::class);

    Route::get('/admin/users', function () {
        return view('admin.users');
    });
    Route::get('/admin/reports', function () {
        return view('admin.reports');
    });
    Route::get('/admin/payments', function () {
        return view('admin.payments');
    });
    Route::get('/admin/invoices', function () {
        return view('admin.invoices');
    });
    Route::get('/admin/certificates', function () {
        return view('admin.certificates');
    });
    Route::get('/admin/state-integration', function () {
        return view('admin.state-integration');
    });
    Route::get('/admin/manage-counties', function () {
        return view('admin.manage-counties');
    });
    Route::get('/admin/email-templates', function () {
        return view('admin.email-templates');
    });
    Route::get('/admin/notifications', function () {
        return view('admin.notifications');
    });
    Route::get('/admin/accessibility-settings', function () {
        return view('admin.accessibility-settings');
    });
    Route::get('/admin/mobile-optimization', function () {
        return view('admin.mobile-optimization');
    });
    Route::get('/admin/pwa-management', function () {
        return view('admin.pwa-management');
    });
    Route::get('/admin/security-dashboard', function () {
        return view('admin.security-dashboard');
    });
    Route::get('/admin/account-security', function () {
        return view('admin.account-security');
    });
    Route::get('/admin/data-export', function () {
        return view('admin.data-export');
    });

    // Florida Security & Audit Module Routes
    Route::get('/admin/florida-security', [App\Http\Controllers\FloridaSecurityWebController::class, 'securityDashboard']);
    Route::get('/admin/florida-audit', [App\Http\Controllers\Admin\FloridaAuditController::class, 'index'])->name('admin.florida-audit.index');
    Route::get('/admin/florida-audit/export', [App\Http\Controllers\Admin\FloridaAuditController::class, 'export'])->name('admin.florida-audit.export');
    Route::get('/admin/florida-compliance', [App\Http\Controllers\FloridaSecurityWebController::class, 'complianceManager']);
    Route::get('/admin/florida-data-export', [App\Http\Controllers\FloridaSecurityWebController::class, 'dataExportTool']);

    // Florida Mobile & Accessibility Module Routes
    Route::get('/admin/florida-mobile', function () {
        return view('admin.florida-mobile');
    });
    Route::get('/admin/florida-accessibility', function () {
        return view('admin.florida-accessibility');
    });

    // Florida DICDS UI & Workflow Module Routes
    Route::get('/dicds/welcome', function () {
        return view('dicds.welcome');
    });
    Route::get('/dicds/main-menu', function () {
        return view('dicds.main-menu');
    });
    Route::get('/admin/dicds-user-management', function () {
        return view('admin.dicds-user-management');
    });
    Route::get('/admin/dicds-access-requests', function () {
        return view('admin.dicds-access-requests');
    });

    Route::get('/admin/florida-courses/{courseId}/chapters', function () {
        return view('admin.chapter-builder');
    });
    Route::get('/admin/chapters/{chapterId}/questions', function ($chapterId) {
        return view('admin.question-manager', ['chapterId' => $chapterId]);
    });
    Route::get('/admin/courses/{courseId}/final-exam', function () {
        return view('admin.question-manager');
    });
    Route::get('/admin/courses/{courseId}/preview', function () {
        return view('admin.course-preview');
    });

    // Mobile-specific routes
    Route::get('/mobile/course/{id}', function ($id) {
        return view('mobile.course-player', ['course' => (object) ['id' => $id, 'title' => 'Sample Course']]);
    });

    // Web routes for accessibility system
    Route::get('/web/accessibility/preferences', [App\Http\Controllers\AccessibilityController::class, 'getPreferences']);
    Route::put('/web/accessibility/preferences', [App\Http\Controllers\AccessibilityController::class, 'updatePreferences']);
    Route::post('/web/accessibility/reset-preferences', [App\Http\Controllers\AccessibilityController::class, 'resetPreferences']);

    Route::get('/web/device-info', [App\Http\Controllers\MobileOptimizationController::class, 'getDeviceInfo']);
    Route::get('/web/mobile-optimized/{component}', [App\Http\Controllers\MobileOptimizationController::class, 'getMobileOptimizedComponent']);

    // Web routes for security system
    Route::get('/web/security/logs', [App\Http\Controllers\SecurityLogController::class, 'index']);
    Route::get('/web/account/security-settings', [App\Http\Controllers\AccountSecurityController::class, 'getSecuritySettings']);
    Route::put('/web/account/password', [App\Http\Controllers\AccountSecurityController::class, 'changePassword']);
    Route::get('/web/account/login-history', [App\Http\Controllers\AccountSecurityController::class, 'getLoginHistory']);
    Route::post('/web/data-export/request', [App\Http\Controllers\DataExportController::class, 'requestExport']);
    Route::get('/web/audit/dashboard', [App\Http\Controllers\AuditController::class, 'getDashboard']);

    // Web routes for certificates
    Route::get('/web/certificates/{certificate}/download', [App\Http\Controllers\CertificateController::class, 'downloadWeb']);

    // Florida State Transmission Management Routes
    Route::prefix('admin/fl-transmissions')->name('admin.fl-transmissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FlTransmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\FlTransmissionController::class, 'show'])->name('show');
        Route::post('/{id}/send', [App\Http\Controllers\Admin\FlTransmissionController::class, 'sendSingle'])->name('send');
        Route::post('/send-all', [App\Http\Controllers\Admin\FlTransmissionController::class, 'sendAll'])->name('send-all');
        Route::post('/{id}/retry', [App\Http\Controllers\Admin\FlTransmissionController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [App\Http\Controllers\Admin\FlTransmissionController::class, 'destroy'])->name('destroy');
    });

    // All State Transmissions Management (unified view)
    Route::prefix('admin/state-transmissions')->name('admin.state-transmissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StateTransmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\StateTransmissionController::class, 'show'])->name('show');
        Route::post('/{id}/send', [App\Http\Controllers\Admin\StateTransmissionController::class, 'send'])->name('send');
        Route::post('/{id}/retry', [App\Http\Controllers\Admin\StateTransmissionController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [App\Http\Controllers\Admin\StateTransmissionController::class, 'destroy'])->name('destroy');
    });

    // California State Transmission Management Routes
    Route::prefix('admin/ca-transmissions')->name('admin.ca-transmissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CaTransmissionController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\CaTransmissionController::class, 'show'])->name('show');
        Route::post('/send-all', [App\Http\Controllers\Admin\CaTransmissionController::class, 'sendAll'])->name('send-all');
        Route::post('/retry-all', [App\Http\Controllers\Admin\CaTransmissionController::class, 'retryAll'])->name('retry-all');
        Route::post('/{id}/retry', [App\Http\Controllers\Admin\CaTransmissionController::class, 'retry'])->name('retry');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CaTransmissionController::class, 'destroy'])->name('destroy');
    });

    // California CTSI Results Management Routes
    Route::prefix('admin/ctsi-results')->name('admin.ctsi-results.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CtsiResultController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\CtsiResultController::class, 'show'])->name('show');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CtsiResultController::class, 'destroy'])->name('destroy');
    });
});
// Certificate Management Routes
Route::middleware('auth')->group(function () {
    Route::get('/web/admin/certificates', function (Request $request) {
        $certificates = \App\Models\FloridaCertificate::query()
            ->when($request->state_code, fn ($q) => $q->where('state', $request->state_code))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($certificates);
    });

    Route::post('/web/admin/certificates', function (Request $request) {
        try {
            $validated = $request->validate([
                'enrollment_id' => 'nullable|integer',
                'student_name' => 'required|string',
                'course_name' => 'required|string',
                'state_code' => 'required|string',
                'completion_date' => 'required|date',
                'status' => 'nullable|string',
            ]);

            $certificate = \App\Models\FloridaCertificate::create([
                'enrollment_id' => $validated['enrollment_id'] ?? null,
                'student_name' => $validated['student_name'],
                'course_name' => $validated['course_name'],
                'state' => $validated['state_code'],
                'completion_date' => $validated['completion_date'],
                'final_exam_score' => 80.00,
                'driver_license_number' => 'A000000000000',
                'citation_number' => '0000000',
                'citation_county' => 'UNKNOWN',
                'traffic_school_due_date' => now()->addDays(30),
                'student_address' => 'N/A',
                'student_date_of_birth' => now()->subYears(25),
                'court_name' => 'N/A',
                'dicds_certificate_number' => 'CERT-'.time(),
                'verification_hash' => bin2hex(random_bytes(16)),
                'generated_at' => now(),
            ]);

            return response()->json($certificate, 201);
        } catch (\Exception $e) {
            \Log::error('Certificate creation error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('/api/enrollments', function () {
        $enrollments = \App\Models\UserCourseEnrollment::where('user_id', auth()->id())
            ->get()
            ->map(function ($enrollment) {
                $course = \App\Models\FloridaCourse::find($enrollment->course_id);

                $enrollmentArray = $enrollment->toArray();
                $enrollmentArray['course'] = $course ? $course->toArray() : null;

                return $enrollmentArray;
            });

        return response()->json($enrollments);
    });

    Route::get('/api/dicds-submissions', function () {
        $submissions = \App\Models\FloridaCertificate::where('is_sent_to_student', true)
            ->orderBy('sent_at', 'desc')
            ->get()
            ->map(function ($cert) {
                return [
                    'id' => $cert->id,
                    'student_name' => $cert->student_name,
                    'course_name' => $cert->course_name,
                    'certificate_number' => $cert->dicds_certificate_number,
                    'submitted_at' => $cert->sent_at,
                    'status' => 'success',
                ];
            });

        return response()->json($submissions);
    });
});

// Public Pages
Route::get('/register/{step?}', [App\Http\Controllers\RegistrationController::class, 'showStep'])->name('register.step');
Route::post('/register/{step}', [App\Http\Controllers\RegistrationController::class, 'processStep'])->name('register.process');

// Payment Routes - Course Enrollment
Route::middleware('auth')->group(function () {
    Route::get('/payment', [App\Http\Controllers\PaymentController::class, 'showPayment'])->name('payment.show');
});

Route::get('/certificate', [App\Http\Controllers\CertificateController::class, 'generate']);
Route::get('/certificate/download', [App\Http\Controllers\CertificateController::class, 'downloadPdf'])->middleware('auth');

Route::get('/faq', function () {
    return view('faq');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
});

Route::get('/terms-conditions', function () {
    return view('legal.terms-conditions');
});

Route::get('/refund-policy', function () {
    return view('legal.refund-policy');
});

// Survey Routes - User-facing
Route::middleware('auth')->group(function () {
    Route::get('/survey/{enrollment}', [App\Http\Controllers\SurveyController::class, 'show'])->name('survey.show');
    Route::post('/survey/{enrollment}', [App\Http\Controllers\SurveyController::class, 'submit'])->name('survey.submit');
    Route::get('/survey/{enrollment}/thank-you', [App\Http\Controllers\SurveyController::class, 'thankYou'])->name('survey.thank-you');
});

// Survey Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    // Survey CRUD
    Route::resource('surveys', App\Http\Controllers\Admin\SurveyController::class);
    Route::post('surveys/{survey}/duplicate', [App\Http\Controllers\Admin\SurveyController::class, 'duplicate'])->name('surveys.duplicate');
    Route::patch('surveys/{survey}/toggle-active', [App\Http\Controllers\Admin\SurveyController::class, 'toggleActive'])->name('surveys.toggle-active');
    Route::get('surveys/{survey}/responses', [App\Http\Controllers\Admin\SurveyController::class, 'responses'])->name('surveys.responses');
    Route::get('surveys/{survey}/export', [App\Http\Controllers\Admin\SurveyController::class, 'export'])->name('surveys.export');

    // Survey Questions
    Route::get('surveys/{survey}/questions', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'index'])->name('surveys.questions.index');
    Route::post('surveys/{survey}/questions', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'store'])->name('surveys.questions.store');
    Route::put('surveys/{survey}/questions/{question}', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'update'])->name('surveys.questions.update');
    Route::delete('surveys/{survey}/questions/{question}', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'destroy'])->name('surveys.questions.destroy');
    Route::post('surveys/{survey}/questions/reorder', [App\Http\Controllers\Admin\SurveyQuestionController::class, 'reorder'])->name('surveys.questions.reorder');

    // Survey Reports
    Route::get('survey-reports', [App\Http\Controllers\Admin\SurveyReportController::class, 'index'])->name('survey-reports.index');
    Route::get('survey-reports/by-survey/{survey}', [App\Http\Controllers\Admin\SurveyReportController::class, 'bySurvey'])->name('survey-reports.by-survey');
    Route::get('survey-reports/by-state/{stateCode}', [App\Http\Controllers\Admin\SurveyReportController::class, 'byState'])->name('survey-reports.by-state');
    Route::get('survey-reports/by-course/{course}', [App\Http\Controllers\Admin\SurveyReportController::class, 'byCourse'])->name('survey-reports.by-course');
    Route::get('survey-reports/by-date-range', [App\Http\Controllers\Admin\SurveyReportController::class, 'byDateRange'])->name('survey-reports.by-date-range');
    Route::get('survey-reports/print/{survey}', [App\Http\Controllers\Admin\SurveyReportController::class, 'print'])->name('survey-reports.print');
    Route::get('survey-reports/delaware', [App\Http\Controllers\Admin\SurveyReportController::class, 'delaware'])->name('survey-reports.delaware');
    Route::get('survey-reports/export/{survey}/{type}', [App\Http\Controllers\Admin\SurveyReportController::class, 'export'])->name('survey-reports.export');
});

// Newsletter Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/newsletter')->name('admin.newsletter.')->group(function () {
    // Subscribers
    Route::get('subscribers', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])->name('subscribers.index');
    Route::get('subscribers/create', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'create'])->name('subscribers.create');
    Route::post('subscribers', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'store'])->name('subscribers.store');
    Route::get('subscribers/{subscriber}/edit', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'edit'])->name('subscribers.edit');
    Route::put('subscribers/{subscriber}', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'update'])->name('subscribers.update');
    Route::delete('subscribers/{subscriber}', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])->name('subscribers.destroy');

    // Import/Export
    Route::get('subscribers/import', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'import'])->name('subscribers.import');
    Route::post('subscribers/import', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'import']);
    Route::get('subscribers/export', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'export'])->name('subscribers.export');

    // Bulk Actions
    Route::post('subscribers/bulk-action', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'bulkAction'])->name('subscribers.bulk-action');

    // Statistics
    Route::get('subscribers/statistics', [App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'statistics'])->name('subscribers.statistics');
});

// Revenue Reporting Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/revenue')->name('admin.revenue.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\RevenueReportController::class, 'dashboard'])->name('dashboard');
    Route::get('/by-state', [App\Http\Controllers\Admin\RevenueReportController::class, 'byState'])->name('by-state');
    Route::get('/by-course', [App\Http\Controllers\Admin\RevenueReportController::class, 'byCourse'])->name('by-course');
    Route::get('/export', [App\Http\Controllers\Admin\RevenueReportController::class, 'export'])->name('export');
});

// Customer Segmentation Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/customers')->name('admin.customers.')->group(function () {
    // Segment Dashboard
    Route::get('/segments', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'index'])->name('segments');

    // Pre-defined Segments (Legacy equivalents)
    Route::get('/completed-monthly', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'completedMonthly'])->name('completed-monthly');
    Route::get('/paid-incomplete', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'paidIncomplete'])->name('paid-incomplete');

    // Additional Segments
    Route::get('/in-progress', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'inProgress'])->name('in-progress');
    Route::get('/abandoned', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'abandoned'])->name('abandoned');
    Route::get('/expiring-soon', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'expiringSoon'])->name('expiring-soon');
    Route::get('/expired', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'expired'])->name('expired');
    Route::get('/never-started', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'neverStarted'])->name('never-started');
    Route::get('/struggling', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'struggling'])->name('struggling');

    // Custom Segments
    Route::get('/custom/{segment}', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'customSegment'])->name('custom-segment');
    Route::post('/segments', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'saveSegment'])->name('segments.save');
    Route::delete('/segments/{segment}', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'deleteSegment'])->name('segments.delete');

    // Bulk Actions
    Route::post('/bulk-remind', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'bulkRemind'])->name('bulk-remind');
    Route::post('/bulk-extend', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'bulkExtend'])->name('bulk-extend');
    Route::post('/bulk-export', [App\Http\Controllers\Admin\CustomerSegmentController::class, 'bulkExport'])->name('bulk-export');
});

// Booklet System Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/booklets')->name('admin.booklets.')->group(function () {
    // Booklet Management
    Route::get('/', [App\Http\Controllers\Admin\BookletController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\Admin\BookletController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\BookletController::class, 'store'])->name('store');
    Route::get('/{booklet}', [App\Http\Controllers\Admin\BookletController::class, 'show'])->name('show');
    Route::get('/{booklet}/edit', [App\Http\Controllers\Admin\BookletController::class, 'edit'])->name('edit');
    Route::put('/{booklet}', [App\Http\Controllers\Admin\BookletController::class, 'update'])->name('update');
    Route::delete('/{booklet}', [App\Http\Controllers\Admin\BookletController::class, 'destroy'])->name('destroy');
    Route::get('/{booklet}/preview', [App\Http\Controllers\Admin\BookletController::class, 'preview'])->name('preview');
    Route::get('/{booklet}/download', [App\Http\Controllers\Admin\BookletController::class, 'download'])->name('download');
    Route::post('/{booklet}/regenerate', [App\Http\Controllers\Admin\BookletController::class, 'regenerate'])->name('regenerate');

    // Orders
    Route::get('/orders/all', [App\Http\Controllers\Admin\BookletController::class, 'orders'])->name('orders');
    Route::get('/orders/pending', [App\Http\Controllers\Admin\BookletController::class, 'pendingOrders'])->name('orders.pending');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\BookletController::class, 'viewOrder'])->name('orders.view');
    Route::post('/orders/{order}/generate', [App\Http\Controllers\Admin\BookletController::class, 'generateOrder'])->name('orders.generate');
    Route::post('/orders/{order}/mark-printed', [App\Http\Controllers\Admin\BookletController::class, 'markPrinted'])->name('orders.mark-printed');
    Route::post('/orders/{order}/mark-shipped', [App\Http\Controllers\Admin\BookletController::class, 'markShipped'])->name('orders.mark-shipped');
    Route::post('/orders/bulk-generate', [App\Http\Controllers\Admin\BookletController::class, 'bulkGenerate'])->name('orders.bulk-generate');
    Route::post('/orders/bulk-print', [App\Http\Controllers\Admin\BookletController::class, 'bulkPrint'])->name('orders.bulk-print');

    // Templates
    Route::get('/templates/all', [App\Http\Controllers\Admin\BookletController::class, 'templates'])->name('templates');
    Route::get('/templates/{template}/edit', [App\Http\Controllers\Admin\BookletController::class, 'editTemplate'])->name('templates.edit');
    Route::put('/templates/{template}', [App\Http\Controllers\Admin\BookletController::class, 'updateTemplate'])->name('templates.update');
    Route::post('/templates/{template}/preview', [App\Http\Controllers\Admin\BookletController::class, 'previewTemplate'])->name('templates.preview');
});

// Court Mailing System Routes - Admin
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/mail-court')->name('admin.mail-court.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\CourtMailingController::class, 'index'])->name('index');
    Route::get('/pending', [App\Http\Controllers\Admin\CourtMailingController::class, 'pending'])->name('pending');
    Route::get('/printed', [App\Http\Controllers\Admin\CourtMailingController::class, 'printed'])->name('printed');
    Route::get('/mailed', [App\Http\Controllers\Admin\CourtMailingController::class, 'mailed'])->name('mailed');
    Route::get('/completed', [App\Http\Controllers\Admin\CourtMailingController::class, 'completed'])->name('completed');
    Route::get('/returned', [App\Http\Controllers\Admin\CourtMailingController::class, 'returned'])->name('returned');
    Route::get('/{mailing}', [App\Http\Controllers\Admin\CourtMailingController::class, 'show'])->name('show');
    Route::post('/{mailing}/print', [App\Http\Controllers\Admin\CourtMailingController::class, 'markPrinted'])->name('mark-printed');
    Route::post('/{mailing}/mail', [App\Http\Controllers\Admin\CourtMailingController::class, 'markMailed'])->name('mark-mailed');
    Route::post('/{mailing}/deliver', [App\Http\Controllers\Admin\CourtMailingController::class, 'markDelivered'])->name('mark-delivered');
    Route::post('/{mailing}/return', [App\Http\Controllers\Admin\CourtMailingController::class, 'markReturned'])->name('mark-returned');
    Route::get('/batches/list', [App\Http\Controllers\Admin\CourtMailingController::class, 'batches'])->name('batches');
    Route::post('/batches/create', [App\Http\Controllers\Admin\CourtMailingController::class, 'createBatch'])->name('batches.create');
    Route::get('/batches/{batch}', [App\Http\Controllers\Admin\CourtMailingController::class, 'viewBatch'])->name('batches.show');
    Route::post('/batches/{batch}/add', [App\Http\Controllers\Admin\CourtMailingController::class, 'addToBatch'])->name('batches.add');
    Route::post('/batches/{batch}/print', [App\Http\Controllers\Admin\CourtMailingController::class, 'printBatch'])->name('batches.print');
    Route::post('/batches/{batch}/mail', [App\Http\Controllers\Admin\CourtMailingController::class, 'mailBatch'])->name('batches.mail');
    Route::post('/batches/{batch}/close', [App\Http\Controllers\Admin\CourtMailingController::class, 'closeBatch'])->name('batches.close');
    Route::post('/bulk-print', [App\Http\Controllers\Admin\CourtMailingController::class, 'bulkPrint'])->name('bulk-print');
    Route::get('/reports/dashboard', [App\Http\Controllers\Admin\CourtMailingController::class, 'reports'])->name('reports');
});

// Nevada State Integration Routes
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin/nevada')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\NevadaController::class, 'dashboard'])->name('nevada.dashboard');

    // Courses
    Route::get('/courses', [App\Http\Controllers\Admin\NevadaController::class, 'courses'])->name('nevada.courses');
    Route::post('/courses', [App\Http\Controllers\Admin\NevadaController::class, 'storeCourse'])->name('nevada.courses.store');
    Route::put('/courses/{id}', [App\Http\Controllers\Admin\NevadaController::class, 'updateCourse'])->name('nevada.courses.update');

    // Students
    Route::get('/students', [App\Http\Controllers\Admin\NevadaController::class, 'students'])->name('nevada.students');
    Route::get('/students/{enrollment}', [App\Http\Controllers\Admin\NevadaController::class, 'studentDetail'])->name('nevada.students.detail');
    Route::get('/students/{enrollment}/activity-log', [App\Http\Controllers\Admin\NevadaController::class, 'activityLog'])->name('nevada.students.activity');

    // Certificates
    Route::get('/certificates', [App\Http\Controllers\Admin\NevadaController::class, 'certificates'])->name('nevada.certificates');
    Route::post('/certificates/{id}/submit', [App\Http\Controllers\Admin\NevadaController::class, 'submitCertificate'])->name('nevada.certificates.submit');

    // Compliance Logs (legacy: customer_search_log_nevada.jsp)
    Route::get('/compliance-logs', [App\Http\Controllers\Admin\NevadaController::class, 'complianceLogs'])->name('nevada.compliance.logs');
    Route::get('/compliance-logs/export', [App\Http\Controllers\Admin\NevadaController::class, 'exportLogs'])->name('nevada.compliance.export');

    // Submissions
    Route::get('/submissions', [App\Http\Controllers\Admin\NevadaController::class, 'submissions'])->name('nevada.submissions');
    Route::get('/submissions/{id}', [App\Http\Controllers\Admin\NevadaController::class, 'submissionDetail'])->name('nevada.submissions.detail');
    Route::post('/submissions/{id}/retry', [App\Http\Controllers\Admin\NevadaController::class, 'retrySubmission'])->name('nevada.submissions.retry');

    // Reports
    Route::get('/reports', [App\Http\Controllers\Admin\NevadaController::class, 'reports'])->name('nevada.reports');
    Route::get('/reports/compliance', [App\Http\Controllers\Admin\NevadaController::class, 'complianceReport'])->name('nevada.reports.compliance');
});

// Payment Gateway Management Routes - Admin
Route::middleware(['auth', 'role:super-admin'])->prefix('admin/payment-gateways')->name('admin.payment-gateways.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'index'])->name('index');
    Route::get('/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'show'])->name('show');
    Route::get('/{gateway}/edit', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'edit'])->name('edit');
    Route::put('/{gateway}', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'update'])->name('update');
    Route::put('/{gateway}/settings', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'updateSettings'])->name('update-settings');
    Route::post('/{gateway}/test', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'testConnection'])->name('test');
    Route::post('/{gateway}/activate', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'activate'])->name('activate');
    Route::post('/{gateway}/deactivate', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'deactivate'])->name('deactivate');
    Route::post('/{gateway}/toggle-mode', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'toggleMode'])->name('toggle-mode');
    Route::get('/{gateway}/logs', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'logs'])->name('logs');
    Route::post('/reorder', [App\Http\Controllers\Admin\PaymentGatewayController::class, 'reorder'])->name('reorder');
});

// Merchant Account Management Routes - Admin
Route::middleware(['auth', 'role:super-admin'])->prefix('admin/merchants')->name('admin.merchants.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\MerchantController::class, 'index'])->name('index');
    Route::get('/{account}', [App\Http\Controllers\Admin\MerchantController::class, 'show'])->name('show');
    Route::get('/{account}/transactions', [App\Http\Controllers\Admin\MerchantController::class, 'transactions'])->name('transactions');
    Route::get('/{account}/payouts', [App\Http\Controllers\Admin\MerchantController::class, 'payouts'])->name('payouts');
    Route::get('/{account}/reconciliation', [App\Http\Controllers\Admin\MerchantController::class, 'reconciliationIndex'])->name('reconciliation');
    Route::post('/{account}/reconciliation', [App\Http\Controllers\Admin\MerchantController::class, 'createReconciliation'])->name('reconciliation.create');
    Route::get('/reconciliation/{reconciliation}', [App\Http\Controllers\Admin\MerchantController::class, 'showReconciliation'])->name('reconciliation.show');
    Route::post('/reconciliation/{reconciliation}/resolve', [App\Http\Controllers\Admin\MerchantController::class, 'resolveReconciliation'])->name('reconciliation.resolve');
    Route::post('/{account}/sync', [App\Http\Controllers\Admin\MerchantController::class, 'syncWithGateway'])->name('sync');
    Route::get('/reports/summary', [App\Http\Controllers\Admin\MerchantController::class, 'reportsSummary'])->name('reports.summary');
});

// Court Code Management Routes
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->group(function () {
    // Court Code CRUD
    Route::get('/court-codes', [App\Http\Controllers\Admin\CourtCodeController::class, 'index'])->name('admin.court-codes.index');
    Route::get('/court-codes/create', [App\Http\Controllers\Admin\CourtCodeController::class, 'create'])->name('admin.court-codes.create');
    Route::post('/court-codes', [App\Http\Controllers\Admin\CourtCodeController::class, 'store'])->name('admin.court-codes.store');
    Route::get('/court-codes/{code}', [App\Http\Controllers\Admin\CourtCodeController::class, 'show'])->name('admin.court-codes.show');
    Route::get('/court-codes/{code}/edit', [App\Http\Controllers\Admin\CourtCodeController::class, 'edit'])->name('admin.court-codes.edit');
    Route::put('/court-codes/{code}', [App\Http\Controllers\Admin\CourtCodeController::class, 'update'])->name('admin.court-codes.update');
    Route::delete('/court-codes/{code}', [App\Http\Controllers\Admin\CourtCodeController::class, 'destroy'])->name('admin.court-codes.destroy');

    // Court Code Status
    Route::post('/court-codes/{code}/deactivate', [App\Http\Controllers\Admin\CourtCodeController::class, 'deactivate'])->name('admin.court-codes.deactivate');
    Route::post('/court-codes/{code}/reactivate', [App\Http\Controllers\Admin\CourtCodeController::class, 'reactivate'])->name('admin.court-codes.reactivate');

    // Court Code Mappings
    Route::get('/court-codes/{code}/mappings', [App\Http\Controllers\Admin\CourtCodeController::class, 'mappings'])->name('admin.court-codes.mappings');
    Route::post('/court-codes/{code}/mappings', [App\Http\Controllers\Admin\CourtCodeController::class, 'addMapping'])->name('admin.court-codes.mappings.store');
    Route::delete('/court-codes/mappings/{mapping}', [App\Http\Controllers\Admin\CourtCodeController::class, 'removeMapping'])->name('admin.court-codes.mappings.destroy');
    Route::post('/court-codes/mappings/{mapping}/verify', [App\Http\Controllers\Admin\CourtCodeController::class, 'verifyMapping'])->name('admin.court-codes.mappings.verify');

    // Court Code History
    Route::get('/court-codes/{code}/history', [App\Http\Controllers\Admin\CourtCodeController::class, 'history'])->name('admin.court-codes.history');

    // Court Code Search & Lookup
    Route::get('/court-codes/search', [App\Http\Controllers\Admin\CourtCodeController::class, 'search'])->name('admin.court-codes.search');
    Route::get('/court-codes/lookup/{codeValue}', [App\Http\Controllers\Admin\CourtCodeController::class, 'lookup'])->name('admin.court-codes.lookup');
    Route::post('/court-codes/translate', [App\Http\Controllers\Admin\CourtCodeController::class, 'translateCode'])->name('admin.court-codes.translate');

    // Court Code Import/Export
    Route::get('/court-codes/import', [App\Http\Controllers\Admin\CourtCodeController::class, 'importForm'])->name('admin.court-codes.import');
    Route::post('/court-codes/import', [App\Http\Controllers\Admin\CourtCodeController::class, 'import'])->name('admin.court-codes.import.process');
    Route::get('/court-codes/export', [App\Http\Controllers\Admin\CourtCodeController::class, 'export'])->name('admin.court-codes.export');

    // Court Code Reports
    Route::get('/court-codes/reports/expiring', [App\Http\Controllers\Admin\CourtCodeController::class, 'expiringCodes'])->name('admin.court-codes.reports.expiring');
    Route::get('/court-codes/reports/unmapped', [App\Http\Controllers\Admin\CourtCodeController::class, 'unmappedCodes'])->name('admin.court-codes.reports.unmapped');
    Route::get('/court-codes/reports/statistics', [App\Http\Controllers\Admin\CourtCodeController::class, 'statistics'])->name('admin.court-codes.reports.statistics');

    // Court Code by Court
    Route::get('/court-codes/court/{court}', [App\Http\Controllers\Admin\CourtCodeController::class, 'byCourtIndex'])->name('admin.court-codes.court.index');
    Route::post('/court-codes/court/{court}/codes', [App\Http\Controllers\Admin\CourtCodeController::class, 'addCodeToCourt'])->name('admin.court-codes.court.store');
});

// Court Code API Routes
Route::prefix('api/court-codes')->middleware('auth')->group(function () {
    Route::get('/search', [App\Http\Controllers\CourtCodeApiController::class, 'search']);
    Route::get('/lookup/{code}', [App\Http\Controllers\CourtCodeApiController::class, 'lookup']);
    Route::post('/validate', [App\Http\Controllers\CourtCodeApiController::class, 'validate']);
    Route::get('/for-court/{court}', [App\Http\Controllers\CourtCodeApiController::class, 'forCourt']);
    Route::post('/translate', [App\Http\Controllers\CourtCodeApiController::class, 'translate']);
});
