<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .achievement-box { background: white; padding: 25px; border-radius: 6px; margin: 20px 0; text-align: center; border: 2px solid #667eea; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #667eea; }
        .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="font-size: 36px; margin: 0;">🎓 Congratulations!</h1>
            <p style="font-size: 18px; margin: 10px 0 0 0;">You've Completed the Course</p>
        </div>
        
        <div class="content">
            <p>Hi {{ $user->first_name }},</p>
            
            <p>Amazing work! You have successfully completed:</p>
            
            <div class="achievement-box">
                <h2 style="margin: 0 0 15px 0; color: #667eea;">{{ $course->title }}</h2>
                <p style="font-size: 18px; color: #6b7280;">100% Complete ✓</p>
            </div>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-value">{{ $enrollment->progress->count() }}</div>
                    <div>Chapters</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $course->total_duration ?? 'N/A' }}</div>
                    <div>Minutes</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $enrollment->enrolled_at->diffInDays(now()) }}</div>
                    <div>Days</div>
                </div>
            </div>
            
            <p style="text-align: center; margin: 30px 0;">Your certificate is being generated and will be available shortly.</p>
            
            <center>
                <a href="{{ url('/certificates') }}" class="btn">View Certificate</a>
                <a href="{{ url('/courses') }}" class="btn" style="background: #10b981;">Browse More Courses</a>
            </center>
            
            <p style="margin-top: 30px; padding: 20px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                <strong>What's Next?</strong><br>
                Continue your learning journey by exploring our other courses!
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} E-Learning Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
