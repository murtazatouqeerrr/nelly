<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create your Account - Step 1</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa; 
            color: #212529;
        }
        .container { max-width: 600px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin: 0; }
        .header p { color: #6c757d; margin: 10px 0 0 0; }
        .registration-form { background: white; padding: 40px; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075); }
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; }
        .form-group label { 
            display: block; 
            color: #212529; 
            font-weight: bold; 
            margin-bottom: 8px; 
            font-size: 14px;
        }
        .form-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            font-size: 16px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-group input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .note-section { 
            background: #fff3cd; 
            border: 1px solid #ffeaa7; 
            padding: 20px; 
            margin-top: 30px; 
            border-radius: 0.375rem; 
        }
        .note-section strong { color: #856404; }
        .note-text { color: #856404; margin-top: 10px; }
        .button-row { display: flex; justify-content: space-between; margin-top: 30px; }
        .btn { 
            padding: 12px 30px; 
            border: none; 
            border-radius: 0.375rem; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: bold;
            transition: background-color 0.15s ease-in-out;
        }
        .btn-next { 
            background: #0d6efd; 
            color: white; 
            width: 100%;
        }
        .btn-next:hover { background: #0b5ed7; }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5c636a; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; }
        .footer a { color: #0d6efd; text-decoration: none; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create your Account</h1>
            <p>Get started with your free account</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 1) }}">
            @csrf
            <div class="registration-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', session('registration_step_1.first_name')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', session('registration_step_1.last_name')) }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email/Login ID</label>
                        <input type="email" id="email" name="email" value="{{ old('email', session('registration_step_1.email')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="email_confirmation">Re Enter Email</label>
                        <input type="email" id="email_confirmation" name="email_confirmation" value="{{ old('email_confirmation', session('registration_step_1.email_confirmation')) }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Retype Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                
                <div class="button-row">
                    <button type="submit" class="btn btn-next">Next</button>
                </div>
            </div>
        </form>
        
        <div class="note-section">
            <strong>Note:</strong>
            <div class="note-text">
                The student is responsible to ensure completion of the course is accepted by the entity for which you are taking the course i.e. Courthouse, DMV, Insurance Company, etc.
            </div>
        </div>
        
        <div class="footer">
            Have an account? <a href="/login">Sign In</a>
        </div>
    </div>
</body>
</html>
