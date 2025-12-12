<?php

use App\Http\Controllers\DicdsOrderAmendmentController;
use App\Http\Controllers\DicdsReceiptController;
use App\Http\Controllers\FloridaApprovalController;
use App\Http\Controllers\FloridaAuditController;
use App\Http\Controllers\FloridaComplianceController;
use App\Http\Controllers\FloridaDataExportController;
use App\Http\Controllers\FloridaSecurityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // Order Amendment Routes
    Route::put('/dicds-orders/{id}/amend', [DicdsOrderAmendmentController::class, 'amend']);
    Route::get('/dicds-orders/{id}/amendment-history', [DicdsOrderAmendmentController::class, 'history']);

    // Receipt Routes
    Route::post('/dicds-orders/{id}/generate-receipt', [DicdsReceiptController::class, 'generate']);
    Route::get('/dicds-orders/{id}/receipt', [DicdsReceiptController::class, 'show']);
    Route::post('/dicds-orders/{id}/mark-printed', [DicdsReceiptController::class, 'markPrinted']);

    // Florida Approval Routes
    Route::put('/dicds-orders/{id}/update-approval', [FloridaApprovalController::class, 'updateApproval']);
    Route::get('/dicds-orders/pending-approval', [FloridaApprovalController::class, 'pendingApproval']);

    // Florida Security Routes
    Route::get('/florida-security/logs', [FloridaSecurityLogController::class, 'index']);
    Route::post('/florida-security/force-logout', [FloridaSecurityLogController::class, 'forceLogout']);
    Route::get('/florida-security/my-sessions', [FloridaSecurityLogController::class, 'mySessions']);
    Route::post('/florida-security/revoke-session/{id}', [FloridaSecurityLogController::class, 'revokeSession']);

    // Florida Audit Routes
    Route::get('/florida-audit/trails', [FloridaAuditController::class, 'trails']);
    Route::post('/florida-audit/generate-report', [FloridaAuditController::class, 'generateReport']);
    Route::get('/florida-audit/compliance-status', [FloridaAuditController::class, 'complianceStatus']);

    // Florida Compliance Routes
    Route::get('/florida-compliance/checks', [FloridaComplianceController::class, 'index']);
    Route::post('/florida-compliance/checks/{checkType}/run', [FloridaComplianceController::class, 'runCheck']);
    Route::get('/florida-compliance/upcoming-due', [FloridaComplianceController::class, 'upcomingDue']);

    // Florida Data Export Routes
    Route::post('/florida-data-exports/request', [FloridaDataExportController::class, 'request']);
    Route::get('/florida-data-exports/status/{id}', [FloridaDataExportController::class, 'status']);
    Route::get('/florida-data-exports/download/{id}', [FloridaDataExportController::class, 'download']);

    // California CTSI Callback Route (Public - receives XML from CTSI)
    Route::post('/ctsi/result', [App\Http\Controllers\Api\CtsiCallbackController::class, 'handleCallback'])->name('api.ctsi.result');

    // Florida Accessibility Routes
    Route::get('/florida-accessibility/preferences', [App\Http\Controllers\FloridaAccessibilityController::class, 'getPreferences']);
    Route::put('/florida-accessibility/preferences', [App\Http\Controllers\FloridaAccessibilityController::class, 'updatePreferences']);
    Route::post('/florida-accessibility/reset-preferences', [App\Http\Controllers\FloridaAccessibilityController::class, 'resetPreferences']);

    // Florida Mobile Optimization Routes
    Route::get('/florida-device/info', [App\Http\Controllers\FloridaMobileOptimizationController::class, 'getDeviceInfo']);
    Route::get('/florida-mobile/course/{courseId}', [App\Http\Controllers\FloridaMobileOptimizationController::class, 'getMobileCourse']);
    Route::post('/florida-mobile/track-activity', [App\Http\Controllers\FloridaMobileOptimizationController::class, 'trackActivity']);

    // Florida Analytics Routes
    Route::get('/florida-analytics/mobile-performance', [App\Http\Controllers\FloridaAnalyticsController::class, 'mobilePerformance']);

    // DICDS System Routes
    Route::get('/dicds/welcome', [App\Http\Controllers\DicdsWelcomeController::class, 'welcome']);
    Route::post('/dicds/welcome/continue', [App\Http\Controllers\DicdsWelcomeController::class, 'continue']);

    Route::get('/dicds/main-menu', [App\Http\Controllers\DicdsNavigationController::class, 'mainMenu']);
    Route::post('/dicds/navigation/{action}', [App\Http\Controllers\DicdsNavigationController::class, 'navigate']);

    Route::get('/dicds/user-management/users', [App\Http\Controllers\DicdsUserManagementController::class, 'getUsers']);
    Route::put('/dicds/user-management/users/{id}/status', [App\Http\Controllers\DicdsUserManagementController::class, 'updateStatus']);
    Route::post('/dicds/user-management/users/{id}/reset-password', [App\Http\Controllers\DicdsUserManagementController::class, 'resetPassword']);
    Route::put('/dicds/user-management/users/{id}/access-role', [App\Http\Controllers\DicdsUserManagementController::class, 'updateAccessRole']);

    Route::get('/dicds/access-requests', [App\Http\Controllers\DicdsAccessController::class, 'index']);
    Route::post('/dicds/access-requests', [App\Http\Controllers\DicdsAccessController::class, 'store']);
    Route::put('/dicds/access-requests/{id}/approve', [App\Http\Controllers\DicdsAccessController::class, 'approve']);

    Route::post('/dicds/help/tickets', [App\Http\Controllers\DicdsHelpController::class, 'submitTicket']);
    Route::get('/dicds/help/tickets', [App\Http\Controllers\DicdsHelpController::class, 'getTickets']);
    Route::put('/dicds/help/tickets/{id}/respond', [App\Http\Controllers\DicdsHelpController::class, 'respond']);
});

// Florida PWA Routes (public)
Route::get('/florida-pwa/manifest', [App\Http\Controllers\FloridaPWAController::class, 'manifest']);
Route::get('/florida-pwa/service-worker', [App\Http\Controllers\FloridaPWAController::class, 'serviceWorker']);

// Florida Analytics (public for testing)
Route::get('/florida-analytics/mobile-performance', function () {
    return response()->json([
        'analytics' => [],
        'device_sessions' => [],
        'total_mobile_users' => 0,
    ]);
});

// DICDS Routes (public for testing)
Route::get('/dicds/user-management/users', [App\Http\Controllers\DicdsUserManagementController::class, 'getUsers']);
Route::put('/dicds/user-management/users/{id}/status', [App\Http\Controllers\DicdsUserManagementController::class, 'updateStatus']);
Route::post('/dicds/user-management/users/{id}/reset-password', [App\Http\Controllers\DicdsUserManagementController::class, 'resetPassword']);

// Florida DICDS Integration
Route::post('/enrollments/{enrollmentId}/submit-to-dicds', [App\Http\Controllers\DicdsIntegrationController::class, 'submitToDicds']);
Route::get('/enrollments/{enrollmentId}/submission-status', [App\Http\Controllers\DicdsIntegrationController::class, 'getSubmissionStatus']);
Route::post('/dicds/test-connection', [App\Http\Controllers\DicdsIntegrationController::class, 'testConnection']);

// Certificate Generation & Verification
Route::get('/certificates/{id}/download', [App\Http\Controllers\CertificateController::class, 'download']);
Route::middleware('web')->post('/certificates/{id}/download', function ($id, Request $request) {
    $certificate = \App\Models\FloridaCertificate::findOrFail($id);

    // Update certificate with additional info
    $certificate->update([
        'driver_license_number' => $request->driver_license_number,
        'citation_number' => $request->citation_number,
        'citation_county' => $request->citation_county,
        'traffic_school_due_date' => $request->traffic_school_due_date,
        'student_address' => $request->student_address,
        'student_date_of_birth' => $request->student_date_of_birth,
        'court_name' => $request->court_name,
    ]);

    $html = view('certificates.florida-certificate', compact('certificate'))->render();

    return response($html)
        ->header('Content-Type', 'text/html')
        ->header('Content-Disposition', 'attachment; filename="certificate-'.$certificate->dicds_certificate_number.'.html"');
});
Route::get('/certificates/verify/{hash}', [App\Http\Controllers\CertificateVerificationController::class, 'verify']);

// My Certificates
// Push Notifications
Route::middleware('web')->post('/push-notification', function (Request $request) {
    $email = $request->email;
    $type = $request->type;
    $title = $request->title;
    $message = $request->message;

    \Log::info('Sending notification to: '.$email, [
        'type' => $type,
        'title' => $title,
        'message' => $message,
    ]);

    // Store notification in database for the specific user
    $notification = \App\Models\PushNotification::create([
        'user_email' => $email,
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'is_read' => false,
    ]);

    \Log::info('Notification stored in database with ID: '.$notification->id);

    return response()->json(['success' => true, 'debug' => 'Notification stored for '.$email]);
});

Route::middleware('web')->get('/check-notifications', function () {
    if (! auth()->check()) {
        return response()->json(['debug' => 'Not authenticated']);
    }

    $user = auth()->user();
    $userEmail = $user->email;

    // Get unread notifications for this user
    $notification = \App\Models\PushNotification::where('user_email', $userEmail)
        ->where('is_read', false)
        ->orderBy('created_at', 'desc')
        ->first();

    if ($notification) {
        \Log::info('Found notification for user: '.$userEmail, $notification->toArray());

        // Mark as read
        $notification->update(['is_read' => true]);

        return response()->json([
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
        ]);
    }

    return response()->json(['debug' => 'No notification found for '.$userEmail]);
});

// Florida Security Dashboard
Route::middleware('web')->get('/florida-security-data', function () {
    try {
        // Get security stats from actual data
        $events24h = \App\Models\SecurityLog::where('created_at', '>=', now()->subDay())->count();
        $failedLogins = \App\Models\SecurityLog::where('event_type', 'failed_login')
            ->where('created_at', '>=', now()->subDay())->count();
        $highRisk = \App\Models\SecurityLog::where('risk_level', 'high')
            ->where('created_at', '>=', now()->subDay())->count();

        // Get recent security events
        $recentEvents = \App\Models\SecurityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'event' => $log->event_type,
                    'user' => $log->user ? $log->user->email : $log->ip_address,
                    'risk_level' => $log->risk_level,
                    'time' => $log->created_at->format('M j, Y g:i A'),
                    'details' => $log->details,
                ];
            });

        return response()->json([
            'stats' => [
                'events24h' => $events24h,
                'failedLogins' => $failedLogins,
                'highRisk' => $highRisk,
            ],
            'recentEvents' => $recentEvents,
        ]);

    } catch (\Exception $e) {
        // Fallback to sample data if SecurityLog model doesn't exist
        return response()->json([
            'stats' => [
                'events24h' => rand(20, 50),
                'failedLogins' => rand(0, 10),
                'highRisk' => rand(0, 5),
            ],
            'recentEvents' => [
                [
                    'event' => 'Login Success',
                    'user' => 'admin@example.com',
                    'risk_level' => 'low',
                    'time' => now()->format('M j, Y g:i A'),
                    'details' => 'Successful login from trusted IP',
                ],
                [
                    'event' => 'Failed Login',
                    'user' => 'unknown@example.com',
                    'risk_level' => 'medium',
                    'time' => now()->subMinutes(15)->format('M j, Y g:i A'),
                    'details' => 'Multiple failed login attempts',
                ],
                [
                    'event' => 'Password Reset',
                    'user' => 'user@example.com',
                    'risk_level' => 'low',
                    'time' => now()->subHour()->format('M j, Y g:i A'),
                    'details' => 'Password reset requested',
                ],
            ],
        ]);
    }
});

Route::middleware('web')->get('/my-certificates', function () {
    try {
        if (! auth()->check()) {
            return response()->json(['debug' => 'Not authenticated']);
        }

        $userId = auth()->id();

        // Direct query using DB to avoid relationship issues
        $certificates = DB::table('florida_certificates as fc')
            ->join('user_course_enrollments as uce', 'fc.enrollment_id', '=', 'uce.id')
            ->where('uce.user_id', $userId)
            ->select('fc.*')
            ->get();

        return response()->json($certificates);
    } catch (\Exception $e) {
        \Log::error('My Certificates Error: '.$e->getMessage());

        return response()->json(['error' => $e->getMessage()]);
    }
});

// Enrollments API
Route::get('/enrollments', function () {
    try {
        $enrollments = \App\Models\UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($enrollments);
    } catch (\Exception $e) {
        \Log::error('Enrollments API error: '.$e->getMessage());

        return response()->json([]);
    }
});

// Get questions for a chapter
Route::get('/chapters/{chapterId}/questions', function ($chapterId) {
    \Log::info("API: Fetching questions for chapter {$chapterId}");

    try {
        $questions = \App\Models\Question::where('chapter_id', $chapterId)
            ->orderBy('order_index')
            ->get();

        \Log::info("API: Found {$questions->count()} questions for chapter {$chapterId}");

        $processedQuestions = $questions->map(function ($question) {
            $data = $question->toArray();

            \Log::info("API: Processing question {$question->id}, options raw: ".json_encode($data['options'] ?? null));

            // Handle options field safely
            if (isset($data['options'])) {
                if (is_string($data['options'])) {
                    $cleaned = trim($data['options']);
                    \Log::info("API: Cleaned options string: '{$cleaned}'");

                    if (empty($cleaned) || $cleaned === 'null') {
                        $data['options'] = [];
                    } else {
                        $decoded = json_decode($cleaned, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            \Log::error('API: JSON decode error: '.json_last_error_msg()." for string: '{$cleaned}'");
                            $data['options'] = [];
                        } else {
                            $data['options'] = $decoded !== null ? $decoded : [];
                        }
                    }
                } elseif (! is_array($data['options'])) {
                    $data['options'] = [];
                }
            } else {
                $data['options'] = [];
            }

            return $data;
        });

        \Log::info('API: Response created successfully');

        return response()->json($processedQuestions);
    } catch (\Exception $e) {
        \Log::error('API: Chapter questions error: '.$e->getMessage());

        return response()->json([]);
    }
});

// Export sample DOCX
Route::get('/chapters/{chapterId}/questions/export-sample', function ($chapterId) {
    try {
        $phpWord = new \PhpOffice\PhpWord\PhpWord;
        $section = $phpWord->addSection();

        $section->addTitle('Question Import Template', 1);
        $section->addText('Instructions: Fill in the table below with your questions. Each row represents one question.');
        $section->addTextBreak();

        $table = $section->addTable();
        $table->addRow();
        $table->addCell(1500)->addText('Question Text');
        $table->addCell(1500)->addText('Type');
        $table->addCell(2000)->addText('Options');
        $table->addCell(1500)->addText('Correct Answer');
        $table->addCell(1500)->addText('Explanation');
        $table->addCell(1000)->addText('Points');
        $table->addCell(1000)->addText('Order');

        // Add sample row
        $table->addRow();
        $table->addCell(1500)->addText('What is 2+2?');
        $table->addCell(1500)->addText('multiple_choice');
        $table->addCell(2000)->addText('3|4|5|6');
        $table->addCell(1500)->addText('4');
        $table->addCell(1500)->addText('Basic arithmetic');
        $table->addCell(1000)->addText('1');
        $table->addCell(1000)->addText('1');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $filename = 'questions_sample_chapter_'.$chapterId.'.docx';
        $path = storage_path('app/temp/'.$filename);

        if (! is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $objWriter->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        \Log::error('Export error: '.$e->getMessage());

        return response()->json(['error' => 'Export failed'], 500);
    }
});

// Import DOCX
Route::post('/chapters/{chapterId}/questions/import', function ($chapterId) {
    try {
        \Log::info('=== IMPORT START ===');
        \Log::info("Chapter ID: {$chapterId}");

        if (! request()->hasFile('file')) {
            \Log::error('No file uploaded');

            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = request()->file('file');
        \Log::info('File received: '.$file->getClientOriginalName());

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($file->path());
        \Log::info('DOCX loaded successfully');

        $count = 0;
        $sectionCount = 0;
        $tableCount = 0;
        $rowCount = 0;

        foreach ($phpWord->getSections() as $section) {
            $sectionCount++;
            \Log::info("Processing section {$sectionCount}");

            foreach ($section->getElements() as $element) {
                \Log::info('Element type: '.get_class($element));

                if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                    $tableCount++;
                    \Log::info("Found table {$tableCount}");

                    $rows = $element->getRows();
                    \Log::info('Total rows in table: '.count($rows));

                    foreach ($rows as $index => $row) {
                        $rowCount++;
                        \Log::info("Processing row {$index}");

                        if ($index === 0) {
                            \Log::info('Skipping header row');

                            continue;
                        }

                        $cells = $row->getCells();
                        \Log::info("Row {$index} has ".count($cells).' cells');

                        if (count($cells) < 7) {
                            \Log::warning("Row {$index} has less than 7 cells, skipping");

                            continue;
                        }

                        $questionText = '';
                        $type = '';
                        $optionsStr = '';
                        $correctAnswer = '';
                        $explanation = '';
                        $points = 1;
                        $order = 1;

                        // Extract from each cell
                        for ($i = 0; $i < 7; $i++) {
                            $cellText = '';
                            foreach ($cells[$i]->getElements() as $elem) {
                                if ($elem instanceof \PhpOffice\PhpWord\Element\Paragraph) {
                                    foreach ($elem->getElements() as $paraElem) {
                                        if ($paraElem instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                            foreach ($paraElem->getElements() as $textElem) {
                                                if ($textElem instanceof \PhpOffice\PhpWord\Element\Text) {
                                                    $cellText .= $textElem->getText();
                                                }
                                            }
                                        } elseif ($paraElem instanceof \PhpOffice\PhpWord\Element\Text) {
                                            $cellText .= $paraElem->getText();
                                        }
                                    }
                                } elseif ($elem instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                    foreach ($elem->getElements() as $textElem) {
                                        if ($textElem instanceof \PhpOffice\PhpWord\Element\Text) {
                                            $cellText .= $textElem->getText();
                                        }
                                    }
                                } elseif ($elem instanceof \PhpOffice\PhpWord\Element\Text) {
                                    $cellText .= $elem->getText();
                                }
                            }
                            \Log::info("Cell {$i}: '".$cellText."'");

                            if ($i === 0) {
                                $questionText = $cellText;
                            } elseif ($i === 1) {
                                $type = $cellText;
                            } elseif ($i === 2) {
                                $optionsStr = $cellText;
                            } elseif ($i === 3) {
                                $correctAnswer = $cellText;
                            } elseif ($i === 4) {
                                $explanation = $cellText;
                            } elseif ($i === 5) {
                                $points = (int) $cellText ?: 1;
                            } elseif ($i === 6) {
                                $order = (int) $cellText ?: 1;
                            }
                        }

                        $questionText = trim($questionText);
                        \Log::info("Trimmed question text: '{$questionText}'");

                        if (empty($questionText)) {
                            \Log::warning('Question text is empty, skipping row');

                            continue;
                        }

                        $options = array_map('trim', array_filter(explode('|', $optionsStr)));
                        \Log::info('Options: '.json_encode($options));

                        // Get course_id from chapter
                        $chapter = \App\Models\Chapter::find($chapterId);
                        $courseId = $chapter ? $chapter->course_id : null;

                        if (! $courseId) {
                            \Log::error("Could not find course_id for chapter {$chapterId}");

                            continue;
                        }

                        $question = \App\Models\Question::create([
                            'chapter_id' => $chapterId,
                            'course_id' => $courseId,
                            'question_text' => $questionText,
                            'question_type' => trim($type) ?: 'multiple_choice',
                            'options' => json_encode($options),
                            'correct_answer' => trim($correctAnswer),
                            'explanation' => trim($explanation),
                            'points' => $points,
                            'order_index' => $order,
                        ]);

                        \Log::info('Question created with ID: '.$question->id);
                        $count++;
                    }
                }
            }
        }

        \Log::info('=== IMPORT END ===');
        \Log::info("Total questions imported: {$count}");

        return response()->json(['count' => $count, 'message' => 'Import successful', 'debug' => [
            'sections' => $sectionCount,
            'tables' => $tableCount,
            'rows' => $rowCount,
        ]]);
    } catch (\Exception $e) {
        \Log::error('Import error: '.$e->getMessage());
        \Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json(['message' => 'Import failed: '.$e->getMessage(), 'trace' => $e->getTraceAsString()], 500);
    }
});

Route::get('/enrollments/{enrollmentId}/progress', function ($enrollmentId) {
    try {
        \Log::info('=== COURSE PLAYER DEBUG START ===');
        \Log::info("Enrollment ID: {$enrollmentId}");

        $enrollment = \App\Models\UserCourseEnrollment::with('course')->findOrFail($enrollmentId);
        \Log::info("Enrollment found - User ID: {$enrollment->user_id}, Course ID: {$enrollment->course_id}");

        if (! $enrollment->course) {
            \Log::error("❌ Course relationship returned NULL for course_id: {$enrollment->course_id}");

            // Check if course exists in florida_courses table
            $courseExists = DB::table('florida_courses')->where('id', $enrollment->course_id)->exists();
            \Log::info('Course exists in florida_courses table: '.($courseExists ? 'YES' : 'NO'));

            return response()->json(['error' => 'Course not found', 'chapters' => [], 'progress' => [], 'enrollment' => $enrollment]);
        }

        \Log::info("✓ Course loaded: {$enrollment->course->title}");

        // Check total chapters in database
        $totalChapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)->count();
        \Log::info("Total chapters in DB for course_id {$enrollment->course_id}: {$totalChapters}");

        $chapters = \App\Models\Chapter::where('course_id', $enrollment->course_id)
            ->where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->get();

        \Log::info("✓ Active chapters found: {$chapters->count()}");

        if ($chapters->isEmpty()) {
            \Log::error('❌ NO CHAPTERS FOUND - Checking database...');
            $allChapters = DB::table('chapters')->where('course_id', $enrollment->course_id)->get();
            \Log::info('Raw DB query result: '.json_encode($allChapters));
        } else {
            \Log::info('Chapter IDs: '.$chapters->pluck('id')->implode(', '));
        }

        $progress = \App\Models\ChapterProgress::where('enrollment_id', $enrollmentId)->get();
        \Log::info("Progress records: {$progress->count()}");
        
        // Add is_completed flag to each chapter
        $chaptersWithProgress = $chapters->map(function ($chapter) use ($progress) {
            $chapterProgress = $progress->firstWhere('chapter_id', $chapter->id);
            $chapter->is_completed = $chapterProgress && $chapterProgress->is_completed ? true : false;
            $chapter->progress_percentage = $chapterProgress ? $chapterProgress->progress_percentage : 0;
            return $chapter;
        });
        
        \Log::info('=== COURSE PLAYER DEBUG END ===');

        return response()->json([
            'enrollment' => $enrollment,
            'chapters' => $chaptersWithProgress,
            'progress' => $progress,
            'course' => $enrollment->course,
        ]);
    } catch (\Exception $e) {
        \Log::error('❌ Enrollment progress error: '.$e->getMessage());
        \Log::error('Stack trace: '.$e->getTraceAsString());

        return response()->json(['error' => $e->getMessage(), 'chapters' => [], 'progress' => [], 'enrollment' => null], 500);
    }
});

Route::post('/timer/start', function (\Illuminate\Http\Request $request) {
    try {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:user_course_enrollments,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        return response()->json(['success' => true, 'started_at' => now()]);
    } catch (\Exception $e) {
        \Log::error('Timer start error: '.$e->getMessage());

        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Get reviews for a specific course
Route::get('/courses/{courseId}/reviews', function ($courseId) {
    try {
        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.course_name', 'LIKE', '%' . $courseId . '%')
            ->orWhereExists(function ($query) use ($courseId) {
                $query->select(DB::raw(1))
                    ->from('user_course_enrollments')
                    ->whereColumn('user_course_enrollments.id', 'reviews.enrollment_id')
                    ->where('user_course_enrollments.course_id', $courseId);
            })
            ->select(
                'reviews.id',
                'reviews.rating',
                'reviews.feedback',
                'reviews.course_name',
                'reviews.created_at',
                'users.first_name as user_name'
            )
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        $averageRating = $reviews->avg('rating');
        $totalReviews = $reviews->count();

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'rating_breakdown' => [
                '5_star' => $reviews->where('rating', 5)->count(),
                '4_star' => $reviews->where('rating', 4)->count(),
                '3_star' => $reviews->where('rating', 3)->count(),
                '2_star' => $reviews->where('rating', 2)->count(),
                '1_star' => $reviews->where('rating', 1)->count(),
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Course reviews API error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load reviews'], 500);
    }
});

// Get all reviews (with pagination)
Route::get('/reviews', function () {
    try {
        $page = request('page', 1);
        $limit = request('limit', 20);
        $offset = ($page - 1) * $limit;

        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->select('reviews.id',
                'reviews.rating',
                'reviews.feedback',
                'reviews.course_name',
                'reviews.created_at',
                'users.first_name as user_name')
            ->orderBy('reviews.created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $total = DB::table('reviews')->count();

        return response()->json([
            'reviews' => $reviews,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total,
        ]);
    } catch (\Exception $e) {
        \Log::error('Reviews API error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load reviews'], 500);
    }
});
// Update the public courses endpoint to include review stats
Route::get('/public/courses', function () {
    try {
        $floridaCourses = DB::table('florida_courses')
            ->where('is_active', true)
            ->select('id', 'title', 'description', 'state_code', 'duration', 'price','course_details')
            ->get();

        $regularCourses = DB::table('courses')
            ->where('is_active', true)
            ->select('id', 'title', 'description', 'state', 'duration', 'price','course_details')
            ->get();

        $allCourses = collect();

        foreach ($floridaCourses as $course) {
            // Get review stats for this course
            $reviews = DB::table('reviews')
                ->join('user_course_enrollments', 'reviews.enrollment_id', '=', 'user_course_enrollments.id')
                ->where('user_course_enrollments.course_id', $course->id)
                ->select('reviews.rating')
                ->get();

            $allCourses->push([
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'course_details' => $course->course_details ?? '',
                'state_code' => $course->state_code ?? 'FL',
                'price' => (float) $course->price,
                'duration' => (int) $course->duration,
                'average_rating' => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null,
                'total_reviews' => $reviews->count(),
            ]);
        }

        foreach ($regularCourses as $course) {
            // Get review stats for this course
            $reviews = DB::table('reviews')
                ->join('user_course_enrollments', 'reviews.enrollment_id', '=', 'user_course_enrollments.id')
                ->where('user_course_enrollments.course_id', $course->id)
                ->select('reviews.rating')
                ->get();

            $allCourses->push([
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description ?? '',
                'state_code' => $course->state ?? 'FL',
                'price' => (float) $course->price,
                'duration' => (int) $course->duration,
                'average_rating' => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : null,
                'total_reviews' => $reviews->count(),
            ]);
        }

        return response()->json($allCourses);
    } catch (\Exception $e) {
        \Log::error('Public courses API error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to load courses'], 500);
    }
});

// Court API Routes
Route::get('/courts/states', function () {
    return response()->json(
        \App\Models\Court::distinct()->pluck('state')->sort()->values()
    );
});

Route::get('/courts/by-state/{state}', function ($state) {
    return response()->json(
        \App\Models\Court::where('state', $state)
            ->distinct()
            ->pluck('county')
            ->sort()
            ->values()
    );
});

Route::get('/courts/by-county/{state}/{county}', function ($state, $county) {
    $page = request('page', 1);
    $limit = 100;
    $offset = ($page - 1) * $limit;

    $courts = \App\Models\Court::where('state', $state)
        ->where('county', $county)
        ->orderBy('court')
        ->offset($offset)
        ->limit($limit)
        ->pluck('court');

    $total = \App\Models\Court::where('state', $state)
        ->where('county', $county)
        ->count();

    return response()->json([
        'courts' => $courts,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'has_more' => ($offset + $limit) < $total,
    ]);
});

// Include new modules API routes
require __DIR__.'/new-modules-api.php';


// State Integration Callbacks (no auth - external systems)
Route::post('/ctsi/result', [App\Http\Controllers\Api\CtsiCallbackController::class, 'handle']);
Route::post('/ntsa/result', [App\Http\Controllers\Api\NtsaCallbackController::class, 'handle']);
Route::post('/ccs/result', [App\Http\Controllers\Api\CcsCallbackController::class, 'handle']);
