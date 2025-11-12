<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create your Account - Step 3</title>
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
        .info-text { color: #6c757d; margin-bottom: 20px; line-height: 1.6; }
        .warning-text { 
            color: #dc3545; 
            font-weight: bold; 
            margin: 20px 0; 
            padding: 15px; 
            background: #f8d7da; 
            border: 1px solid #f5c6cb; 
            border-radius: 0.375rem; 
        }
        .question-row { display: flex; margin-bottom: 15px; align-items: center; }
        .question-number { color: #0d6efd; font-weight: bold; width: 30px; }
        .question-text { color: #212529; flex: 1; margin-right: 20px; }
        .answer-input { 
            width: 200px; 
            padding: 8px 12px; 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .answer-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .button-row { display: flex; justify-content: space-between; margin-top: 30px; gap: 20px; }
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
            flex: 1;
            text-align: center;
        }
        .btn-continue { background: #0d6efd; color: white; }
        .btn-continue:hover { background: #0b5ed7; }
        .btn-back { background: #6c757d; color: white; }
        .btn-back:hover { background: #5c636a; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verification of Student Identity</h1>
            <p>Step 3 of 4 - Security questions for identity verification</p>
        </div>
        
        <form method="POST" action="{{ route('register.process', 3) }}">
            @csrf
            <div class="registration-form">
                <div class="info-text">
                    We are required by the DMV/Courts to include various identification checkpoints throughout the course. These checkpoints enable us to verify the identity of the test taker. The individual who takes and completes the course must be the same person who begins the course. The information below will be used in each checkpoint. Please remember your answers to the questions below, they will be the same questions on the verification checkpoints. If you answer any question wrong, your account will be locked and you'll have to contact us for ID verification and account unlock. When registering to take this course, you are saying to the court that you are the ticket holder and have elected to traffic school.
                </div>
                
                <div class="info-text">
                    The information we are asking you below is only to verify that the person that started the course is the person that will be finishing the course. This information will not be shared with the courts or any third party.
                </div>
                
                <div class="warning-text">
                    YOU MUST ENTER THESE ANSWERS ON THE COURSE EXACTLY AS THEY APPEAR BELOW. IF ANSWER INCORRECTLY YOU WILL BE LOCKED OUT OF YOUR ACCOUNT AND BE REQUIRED TO CONTACT CUSTOMER SUPPORT. PLEASE WRITE THEM DOWN.
                </div>
                
                <div class="question-row">
                    <div class="question-number">1.</div>
                    <div class="question-text">When does your driver's license expire? ONLY THE YEAR ( exe. 2018 )</div>
                    <input type="text" name="q1" class="answer-input" value="{{ old('q1', session('registration_step_3.q1')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">2.</div>
                    <div class="question-text">What is the weight listed on your driver's license? (Only in numbers exe. 162)</div>
                    <input type="text" name="q2" class="answer-input" value="{{ old('q2', session('registration_step_3.q2')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">3.</div>
                    <div class="question-text">How many cars do you own? (Only in Numbers exe. 1)</div>
                    <input type="text" name="q3" class="answer-input" value="{{ old('q3', session('registration_step_3.q3')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">4.</div>
                    <div class="question-text">What are the last four digits of your Drivers License Number? (6374)</div>
                    <input type="text" name="q4" class="answer-input" value="{{ old('q4', session('registration_step_3.q4')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">5.</div>
                    <div class="question-text">What is your age? (Only in numbers exe. 31 )</div>
                    <input type="text" name="q5" class="answer-input" value="{{ old('q5', session('registration_step_3.q5')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">6.</div>
                    <div class="question-text">How old were you when you got your Drivers License? (Only in numbers exe. 16 )</div>
                    <input type="text" name="q6" class="answer-input" value="{{ old('q6', session('registration_step_3.q6')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">7.</div>
                    <div class="question-text">What zip code do you live in? ( exe. 90210 )</div>
                    <input type="text" name="q7" class="answer-input" value="{{ old('q7', session('registration_step_3.q7')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">8.</div>
                    <div class="question-text">In what year were you born? (exe. 1980 )</div>
                    <input type="text" name="q8" class="answer-input" value="{{ old('q8', session('registration_step_3.q8')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">9.</div>
                    <div class="question-text">What color is your hair?</div>
                    <input type="text" name="q9" class="answer-input" value="{{ old('q9', session('registration_step_3.q9')) }}" required>
                </div>
                
                <div class="question-row">
                    <div class="question-number">10.</div>
                    <div class="question-text">What city do you live in?</div>
                    <input type="text" name="q10" class="answer-input" value="{{ old('q10', session('registration_step_3.q10')) }}" required>
                </div>
                
                <div class="button-row">
                    <a href="{{ route('register.step', 2) }}" class="btn btn-back">Back</a>
                    <button type="submit" class="btn btn-continue">Continue</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
