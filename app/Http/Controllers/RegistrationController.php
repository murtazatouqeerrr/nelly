<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class RegistrationController extends Controller
{
    public function showStep($step = 1)
    {
        $step = (int) $step;
        if ($step < 1 || $step > 4) {
            $step = 1;
        }
        
        return view('registration.step' . $step, compact('step'));
    }
    
    public function processStep(Request $request, $step)
    {
        $step = (int) $step;
        
        // Store step data in session
        $sessionKey = 'registration_step_' . $step;
        session([$sessionKey => $request->all()]);
        
        // Move to next step or complete registration
        if ($step < 4) {
            return redirect()->route('register.step', $step + 1);
        }
        
        // Complete registration (step 4)
        return $this->completeRegistration();
    }
    
    private function completeRegistration()
    {
        // Get all step data from session
        $step1 = session('registration_step_1', []);
        $step2 = session('registration_step_2', []);
        $step3 = session('registration_step_3', []);
        $step4 = session('registration_step_4', []);
        
        // Create user with all registration data
        $user = User::create([
            // Step 1 - Basic Info
            'first_name' => $step1['first_name'],
            'last_name' => $step1['last_name'],
            'email' => $step1['email'],
            'password' => bcrypt($step1['password']),
            'role_id' => 4, // Student role
            'status' => 'active',
            
            // Step 2 - Personal Info (existing fields)
            'mailing_address' => $step2['mailing_address'] ?? null,
            'city' => $step2['city'] ?? null,
            'state' => $step2['state'] ?? null,
            'zip' => $step2['zip'] ?? null,
            'phone_1' => $step2['phone_1'] ?? null,
            'phone_2' => $step2['phone_2'] ?? null,
            'phone_3' => $step2['phone_3'] ?? null,
            'gender' => $step2['gender'] ?? null,
            'birth_month' => $step2['birth_month'] ?? null,
            'birth_day' => $step2['birth_day'] ?? null,
            'birth_year' => $step2['birth_year'] ?? null,
            'driver_license' => $step2['driver_license'] ?? null,
            
            // Step 2 - New fields
            'license_state' => $step2['license_state'] ?? null,
            'license_class' => $step2['license_class'] ?? null,
            'court_selected' => $step2['court_selected'] ?? null,
            'citation_number' => $step2['citation_number'] ?? null,
            'due_month' => $step2['due_month'] ?? null,
            'due_day' => $step2['due_day'] ?? null,
            'due_year' => $step2['due_year'] ?? null,
            
            // Step 3 - Security Questions
            'security_q1' => $step3['q1'] ?? null,
            'security_q2' => $step3['q2'] ?? null,
            'security_q3' => $step3['q3'] ?? null,
            'security_q4' => $step3['q4'] ?? null,
            'security_q5' => $step3['q5'] ?? null,
            'security_q6' => $step3['q6'] ?? null,
            'security_q7' => $step3['q7'] ?? null,
            'security_q8' => $step3['q8'] ?? null,
            'security_q9' => $step3['q9'] ?? null,
            'security_q10' => $step3['q10'] ?? null,
            
            // Step 4 - Agreement
            'agreement_name' => $step4['agreement_name'] ?? null,
            'terms_agreement' => isset($step4['terms_agreement']),
            'registration_completed_at' => now(),
        ]);
        
        // Clear session data
        session()->forget(['registration_step_1', 'registration_step_2', 'registration_step_3', 'registration_step_4']);
        
        return redirect()->route('login')->with('success', 'Registration completed successfully! Please login with your credentials.');
    }
}
