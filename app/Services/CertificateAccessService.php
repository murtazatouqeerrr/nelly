<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserCourseEnrollment;

class CertificateAccessService
{
    public function handleCertificateDownload(User $user)
    {
        // Get all enrollments for this user (excluding revoked ones)
        $enrollments = UserCourseEnrollment::where('user_id', $user->id)
            ->where('access_revoked', false)
            ->get();
        
        // Revoke access to all courses immediately
        foreach ($enrollments as $enrollment) {
            $enrollment->update([
                'access_revoked' => true,
                'access_revoked_at' => now()
            ]);
        }

        // Lock the user account
        if ($enrollments->count() > 0) {
            $user->update([
                'account_locked' => true,
                'lock_reason' => 'All course certificates downloaded',
                'locked_at' => now()
            ]);

            return [
                'status' => 'account_locked',
                'message' => 'Your account has been locked after downloading all certificates. Please contact support to regain access.'
            ];
        }

        return ['status' => 'success'];
    }
}
