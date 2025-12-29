<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create your Account - Step 4</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
            color: #212529;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin: 0; }
        .header p { color: #6c757d; margin: 10px 0 0 0; }
        .registration-form { background: white; padding: 40px; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .instruction { color: #6c757d; font-size: 16px; margin-bottom: 30px; text-align: center; }
        .info-section { margin-bottom: 30px; }
        .section-title { 
            color: #0d6efd; 
            font-weight: bold; 
            font-size: 18px; 
            text-align: center; 
            margin-bottom: 20px; 
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .info-row { display: flex; margin-bottom: 8px; padding: 5px 0; }
        .info-label { color: #6c757d; width: 250px; font-size: 14px; }
        .info-value { color: #212529; font-weight: 500; }
        .terms-section { 
            margin: 30px 0; 
            text-align: center; 
            padding: 30px; 
            background: #f8f9fa; 
            border-radius: 0.375rem; 
            border: 1px solid #dee2e6;
        }
        .terms-text { color: #6c757d; margin-bottom: 20px; line-height: 1.5; }
        .name-input-row { margin: 20px 0; }
        .agreement-name-input { 
            width: 300px; 
            padding: 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            font-size: 16px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .agreement-name-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .checkbox-row { margin: 20px 0; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .checkbox-label { color: #212529; margin: 0; }
        .error-message { color: #dc3545; font-size: 14px; margin-top: 5px; display: none; }
        .form-group.error .agreement-name-input { border-color: #dc3545; }
        .button-row { display: flex; justify-content: center; gap: 20px; margin-top: 30px; }
        .btn { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            text-decoration: none; 
            display: inline-block;
            transition: background-color 0.15s ease-in-out;
            min-width: 120px;
            text-align: center;
        }
        .btn-continue { background: #516425; color: white; }
        .btn-continue:hover { background: #3d4b1c; }
        .btn-edit { background: #fd7e14; color: white; }
        .btn-edit:hover { background: #e8650e; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; }
        .footer a { color: #0d6efd; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Registration Form Review</h1>
            <p>Step 4 of 4 - Review and confirm your information</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 4) }}">
            @csrf
            
            @if(session('error'))
                <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #842029; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Error:</strong> {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div style="background: #d1e7dd; border: 1px solid #badbcc; color: #0f5132; padding: 15px; border-radius: 0.375rem; margin-bottom: 20px;">
                    <strong>Success:</strong> {{ session('success') }}
                </div>
            @endif
            
            <div class="registration-form">
                <div class="instruction">
                    Take your time and make sure it is accurate!
                </div>
                
                <!-- Your Information Section -->
                <div class="info-section">
                    <div class="section-title">Your Information</div>
                    
                    <div class="info-row">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">{{ session('registration_step_1.email', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">First Name:</div>
                        <div class="info-value">{{ session('registration_step_1.first_name', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Last Name:</div>
                        <div class="info-value">{{ session('registration_step_1.last_name', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Mailing Address:</div>
                        <div class="info-value">{{ session('registration_step_2.mailing_address', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">City:</div>
                        <div class="info-value">{{ session('registration_step_2.city', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">State:</div>
                        <div class="info-value">{{ session('registration_step_2.state', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Zip:</div>
                        <div class="info-value">{{ session('registration_step_2.zip', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Phone:</div>
                        <div class="info-value">{{ session('registration_step_2.phone_1', '') }}-{{ session('registration_step_2.phone_2', '') }}-{{ session('registration_step_2.phone_3', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Gender:</div>
                        <div class="info-value">{{ ucfirst(session('registration_step_2.gender', '')) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Birthday:</div>
                        <div class="info-value">{{ session('registration_step_2.birth_month', '') }}/{{ session('registration_step_2.birth_day', '') }}/{{ session('registration_step_2.birth_year', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Driver License</div>
                        <div class="info-value">{{ session('registration_step_2.driver_license', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">State Issue</div>
                        <div class="info-value">{{ session('registration_step_2.license_state', '') }}</div>
                    </div>
                </div>
                
                <!-- Court Information Section -->
                <div class="info-section">
                    <div class="section-title">Court Information</div>
                    
                    <div class="info-row">
                        <div class="info-label">Court Selected:</div>
                        <div class="info-value">{{ session('registration_step_2.court_selected', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Citation Number:</div>
                        <div class="info-value">{{ session('registration_step_2.citation_number', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Traffic school due date:</div>
                        <div class="info-value">{{ session('registration_step_2.due_month', '') }}/{{ session('registration_step_2.due_day', '') }}/{{ session('registration_step_2.due_year', '') }}</div>
                    </div>
                </div>
                
                <!-- Personal Information Section -->
                <div class="info-section">
                    <div class="section-title">Security Questions</div>
                    
                    <div class="info-row">
                        <div class="info-label">License expiration year:</div>
                        <div class="info-value">{{ session('registration_step_3.q1', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Weight on license:</div>
                        <div class="info-value">{{ session('registration_step_3.q2', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Number of cars owned:</div>
                        <div class="info-value">{{ session('registration_step_3.q3', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Last four digits of license:</div>
                        <div class="info-value">{{ session('registration_step_3.q4', '') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Current age:</div>
                        <div class="info-value">{{ session('registration_step_3.q5', '') }}</div>
                    </div>
                </div>
                
                <!-- Terms and Conditions Section -->
                <div class="terms-section">
                    <div class="terms-text">
                        Enter your name below and check the box to agree to the terms above. If you are under 18, a parent or guardian must enter their name. Please provide a valid citation number (or case number if no citation is available). Incorrect information requiring certificate resubmission will result in a $3.00 fee. By proceeding, you agree to these terms.
                    </div>
                    
                    <div class="name-input-row">
                        <div class="form-group">
                            <input type="text" name="agreement_name" class="agreement-name-input" placeholder="Type your full name here" pattern="[a-zA-Z\s\-']+" title="Only letters, spaces, hyphens, and apostrophes allowed" required>
                            <div class="error-message">Only letters, spaces, hyphens, and apostrophes allowed</div>
                        </div>
                    </div>
                    
                    <div class="checkbox-row">
                        <input type="checkbox" id="terms_agreement" name="terms_agreement" required>
                        <label for="terms_agreement" class="checkbox-label">I agree to the terms and conditions above.</label>
                    </div>
                </div>
                
                <div class="button-row">
                    <button type="submit" class="btn btn-continue">I Accept</button>
                    <a href="{{ route('register.step', 1) }}" class="btn btn-edit">Edit</a>
                </div>
            </div>
        </form>
        
        <div class="footer">
            Have an account? <a href="/login">Sign In</a>
        </div>
    </div>
    
    <script src="/js/csrf-handler.js"></script>
    <script>
        // Real-time validation for agreement name field
        document.querySelector('input[name="agreement_name"]').addEventListener('input', function(e) {
            const value = e.target.value;
            const regex = /^[a-zA-Z\s\-']*$/;
            const parent = e.target.closest('.form-group');
            const errorMsg = parent.querySelector('.error-message');
            
            if (!regex.test(value)) {
                parent.classList.add('error');
                errorMsg.style.display = 'block';
                e.target.value = value.replace(/[^a-zA-Z\s\-']/g, '');
            } else {
                parent.classList.remove('error');
                errorMsg.style.display = 'none';
            }
        });
    </script>
</body>
</html>
