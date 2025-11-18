<?php

namespace App\Listeners;

use App\Events\CertificateGenerated;
use App\Notifications\CertificateGeneratedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCertificateEmail implements ShouldQueue
{
    public function handle(CertificateGenerated $event)
    {
        $event->certificate->user->notify(new CertificateGeneratedNotification($event->certificate));
    }
}
