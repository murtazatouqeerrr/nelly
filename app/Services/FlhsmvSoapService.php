<?php

namespace App\Services;

use App\Models\FlhsmvSubmission;
use App\Models\FlhsmvSubmissionError;
use SoapClient;
use Exception;

class FlhsmvSoapService
{
    private $client;
    private $username;
    private $password;
    private $wsdlUrl;

    public function __construct()
    {
        $this->username = config('flhsmv.username');
        $this->password = config('flhsmv.password');
        $this->wsdlUrl = config('flhsmv.wsdl_url');
    }

    public function submitCompletion($certificateId)
    {
        $certificate = \App\Models\FloridaCertificate::with('user')->findOrFail($certificateId);
        
        $submissionData = $this->prepareSubmissionData($certificate);
        
        $submission = FlhsmvSubmission::create([
            'user_id' => $certificate->user_id,
            'florida_certificate_id' => $certificateId,
            'submission_data' => $submissionData,
            'status' => 'pending',
            'retry_count' => 0
        ]);

        try {
            $response = $this->sendSoapRequest($submissionData);
            
            if ($this->isSuccessResponse($response)) {
                $submission->update([
                    'status' => 'completed',
                    'response_data' => $response,
                    'completed_at' => now(),
                    'submitted_at' => now()
                ]);
                
                $certificate->update([
                    'dmv_certificate_number' => $response['certificate_number'] ?? null,
                    'dmv_submission_status' => 'approved'
                ]);
                
                return ['success' => true, 'submission' => $submission];
            } else {
                return $this->handleError($submission, $response);
            }
        } catch (Exception $e) {
            return $this->handleException($submission, $e);
        }
    }

    private function prepareSubmissionData($certificate)
    {
        $user = $certificate->user;
        
        return [
            'mvUserid' => $this->username,
            'mvPassword' => $this->password,
            'mvSchoolid' => $certificate->school_id ?? config('flhsmv.default_school_id'),
            'mvClassDate' => $certificate->completion_date->format('mdY'),
            'mvStartTime' => $certificate->class_start_time ?? '0001',
            'mvSchoolIns' => $certificate->instructor_id ?? config('flhsmv.default_instructor_id'),
            'mvSchoolCourse' => $certificate->course_id,
            'mvFirstName' => $user->first_name,
            'mvMiddleName' => $user->middle_name ?? '',
            'mvLastName' => $user->last_name,
            'mvSuffix' => $user->suffix ?? '',
            'mvDob' => $user->date_of_birth->format('mdY'),
            'mvSex' => $user->gender,
            'mvSocialSN' => $user->ssn_last_four ?? '',
            'mvCitationDate' => $certificate->citation_date ? $certificate->citation_date->format('mdY') : '',
            'mvCitationCounty' => $certificate->citation_county ?? '',
            'mvCitationNumber' => $certificate->citation_number ?? '',
            'mvReasonAttending' => $certificate->reason_attending,
            'mvDriversLicense' => $user->drivers_license_number,
            'mvdlStateOfRecordCode' => $user->dl_state ?? 'FL',
            'mvAlienNumber' => $user->alien_number ?? '',
            'mvNonAlien' => $user->non_alien_number ?? '',
            'mvStreet' => $user->address ?? '',
            'mvApartment' => $user->apartment ?? '',
            'mvCity' => $user->city ?? '',
            'mvState' => $user->state ?? '',
            'mvZipCode' => $user->zip_code ?? '',
            'mvZipPlus' => $user->zip_plus ?? '',
            'mvPhone' => $user->phone ?? '',
            'mvEmail' => $user->email
        ];
    }

    private function sendSoapRequest($data)
    {
        if (!$this->wsdlUrl) {
            throw new Exception('FLHSMV WSDL URL not configured');
        }

        $this->client = new SoapClient($this->wsdlUrl, [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE
        ]);

        return $this->client->submitStudentCompletion($data);
    }

    private function isSuccessResponse($response)
    {
        return isset($response['status']) && $response['status'] === 'success';
    }

    private function handleError($submission, $response)
    {
        $errorCode = $response['error_code'] ?? 'UNKNOWN';
        $errorMessage = $response['error_message'] ?? 'Unknown error';

        FlhsmvSubmissionError::create([
            'flhsmv_submission_id' => $submission->id,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'error_details' => $response,
            'occurred_at' => now()
        ]);

        $submission->update([
            'status' => 'failed',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'response_data' => $response
        ]);

        return ['success' => false, 'error' => $errorMessage, 'code' => $errorCode];
    }

    private function handleException($submission, $e)
    {
        FlhsmvSubmissionError::create([
            'flhsmv_submission_id' => $submission->id,
            'error_code' => 'EXCEPTION',
            'error_message' => $e->getMessage(),
            'error_details' => ['trace' => $e->getTraceAsString()],
            'occurred_at' => now()
        ]);

        $submission->update([
            'status' => 'failed',
            'error_code' => 'EXCEPTION',
            'error_message' => $e->getMessage()
        ]);

        return ['success' => false, 'error' => $e->getMessage()];
    }
}
