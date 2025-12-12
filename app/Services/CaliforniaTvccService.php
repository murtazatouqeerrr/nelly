<?php

namespace App\Services;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;

class CaliforniaTvccService
{
    protected string $apiUrl;
    protected string $username;
    protected string $password;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('state-integrations.california.tvcc.url');
        $this->username = config('state-integrations.california.tvcc.user');
        $this->password = $this->getTvccPassword();
        $this->timeout = config('state-integrations.california.tvcc.timeout', 30);
    }

    /**
     * Send transmission to California TVCC (Traffic Violator Certificate Completion).
     */
    public function sendTransmission(StateTransmission $transmission): bool
    {
        try {
            $enrollment = $transmission->enrollment()->with(['user', 'course'])->first();

            if (!$enrollment) {
                $this->markAsError($transmission, 'ENROLLMENT_NOT_FOUND', 'Enrollment record not found');
                return false;
            }

            $validationErrors = $this->validateRequiredFields($enrollment);
            if (!empty($validationErrors)) {
                $this->markAsError($transmission, 'VALIDATION_ERROR', implode(', ', $validationErrors));
                return false;
            }

            $payload = $this->buildPayload($enrollment);
            $transmission->update(['payload_json' => $payload]);

            $response = $this->callTvccApi($payload);

            return $this->handleResponse($transmission, $response);

        } catch (Exception $e) {
            Log::error('TVCC transmission failed', [
                'transmission_id' => $transmission->id,
                'error' => $e->getMessage(),
            ]);

            $this->markAsError($transmission, 'EXCEPTION', $e->getMessage());
            return false;
        }
    }

    protected function validateRequiredFields(UserCourseEnrollment $enrollment): array
    {
        $errors = [];
        $user = $enrollment->user;

        if (empty($user->first_name) || empty($user->last_name)) {
            $errors[] = 'Student name is required';
        }

        if (empty($user->driver_license)) {
            $errors[] = 'California driver license is required';
        }

        if (empty($user->date_of_birth)) {
            $errors[] = 'Date of birth is required';
        }

        if (empty($enrollment->citation_number)) {
            $errors[] = 'Citation number is required';
        }

        if (empty($enrollment->completed_at)) {
            $errors[] = 'Course completion date is required';
        }

        if (empty($user->court_selected)) {
            $errors[] = 'Court selection is required';
        }

        return $errors;
    }

    protected function buildPayload(UserCourseEnrollment $enrollment): array
    {
        $user = $enrollment->user;

        return [
            'ccDate' => $enrollment->completed_at->format('Y-m-d'),
            'courtCd' => $this->getCourtCode($user->court_selected),
            'dateOfBirth' => $user->date_of_birth->format('Y-m-d'),
            'dlNbr' => $user->driver_license,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'modality' => config('state-integrations.california.tvcc.modality', '4T'),
            'refNbr' => $enrollment->citation_number,
            'userDto' => [
                'userId' => $this->username,
                'password' => $this->password,
            ],
        ];
    }

    protected function callTvccApi(array $payload): array
    {
        Log::info('Sending TVCC transmission', [
            'url' => $this->apiUrl,
            'payload' => array_merge($payload, ['userDto' => ['userId' => $this->username, 'password' => '[REDACTED]']]),
        ]);

        try {
            // Use SOAP client for TVCC API
            $soapClient = new SoapClient($this->apiUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => $this->timeout,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            $response = $soapClient->submitCertificateCompletion($payload);

            Log::info('TVCC API response received', [
                'response' => $response,
            ]);

            return [
                'success' => true,
                'response' => $response,
            ];

        } catch (SoapFault $e) {
            Log::error('TVCC SOAP fault', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ];

        } catch (Exception $e) {
            Log::error('TVCC API call failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => 'API_ERROR',
            ];
        }
    }

    protected function handleResponse(StateTransmission $transmission, array $response): bool
    {
        Log::info('TVCC API response', [
            'transmission_id' => $transmission->id,
            'response' => $response,
        ]);

        if ($response['success']) {
            $apiResponse = $response['response'];
            
            $transmission->update([
                'status' => 'success',
                'response_code' => $apiResponse->ccStatCd ?? 'SUCCESS',
                'response_message' => "TVCC submission successful. Sequence: " . ($apiResponse->ccSeqNbr ?? 'N/A'),
                'sent_at' => now(),
            ]);

            return true;
        } else {
            $this->markAsError(
                $transmission,
                $response['code'] ?? 'API_ERROR',
                $response['error'] ?? 'Unknown TVCC API error'
            );

            return false;
        }
    }

    protected function markAsError(StateTransmission $transmission, string $code, string $message): void
    {
        $transmission->update([
            'status' => 'error',
            'response_code' => $code,
            'response_message' => $message,
            'retry_count' => $transmission->retry_count + 1,
        ]);
    }

    protected function getTvccPassword(): string
    {
        // Get password from database table
        $passwordRecord = \DB::table('tvcc_passwords')->latest('updated_at')->first();
        
        if ($passwordRecord) {
            return $passwordRecord->password;
        }

        // Fallback to environment variable
        return config('state-integrations.california.tvcc.password', '');
    }

    protected function getCourtCode(string $courtName): string
    {
        // Map court names to TVCC court codes
        // This should be populated from your courts table with tvcc_court_code field
        $courtMappings = [
            'Los Angeles Superior Court' => 'LA001',
            'Orange County Superior Court' => 'OC001',
            'San Diego Superior Court' => 'SD001',
            // Add more mappings as needed
        ];

        return $courtMappings[$courtName] ?? 'UNK001';
    }

    /**
     * Test TVCC connection.
     */
    public function testConnection(): array
    {
        // First check if WSDL is accessible
        if (!$this->isWsdlAccessible()) {
            return [
                'success' => false,
                'error' => 'WSDL URL is not accessible: ' . $this->apiUrl,
                'suggestion' => 'Check network connectivity, firewall settings, or contact California DMV for correct endpoint',
            ];
        }

        try {
            $soapClient = new SoapClient($this->apiUrl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 10,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ]);

            // Get available methods
            $methods = $soapClient->__getFunctions();

            return [
                'success' => true,
                'message' => 'TVCC SOAP connection successful',
                'methods' => $methods,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'suggestion' => 'WSDL is accessible but SOAP client failed. Check credentials or SOAP configuration.',
            ];
        }
    }

    /**
     * Check if WSDL is accessible.
     */
    protected function isWsdlAccessible(): bool
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'method' => 'GET',
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $headers = @get_headers($this->apiUrl, 1, $context);
            return $headers && strpos($headers[0], '200') !== false;
        } catch (Exception $e) {
            return false;
        }
    }
}