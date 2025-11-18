<?php

namespace App\Providers;

use App\Events\UserEnrolled;
use App\Events\PaymentApproved;
use App\Events\CourseCompleted;
use App\Events\CertificateGenerated;
use App\Listeners\SendEnrollmentConfirmation;
use App\Listeners\SendPaymentApprovedEmail;
use App\Listeners\SendCourseCompletedEmail;
use App\Listeners\SendCertificateEmail;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserEnrolled::class => [
            SendEnrollmentConfirmation::class,
        ],
        PaymentApproved::class => [
            SendPaymentApprovedEmail::class,
        ],
        CourseCompleted::class => [
            SendCourseCompletedEmail::class,
        ],
        CertificateGenerated::class => [
            SendCertificateEmail::class,
        ],
    ];

    public function boot()
    {
        //
    }
}
