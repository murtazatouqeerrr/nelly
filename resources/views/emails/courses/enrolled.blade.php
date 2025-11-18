<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2563eb; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .course-box { background: white; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #2563eb; }
        .btn { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Enrollment Confirmed!</h1>
        </div>
        
        <div class="content">
            <p>Hi {{ $user->first_name }},</p>
            
            <p>Congratulations! You have successfully enrolled in:</p>
            
            <div class="course-box">
                <h2 style="margin: 0 0 10px 0; color: #2563eb;">{{ $course->title }}</h2>
                <p style="margin: 5px 0;"><strong>Start Date:</strong> {{ $enrollment->enrolled_at->format('F d, Y') }}</p>
                <p style="margin: 5px 0;"><strong>Duration:</strong> {{ $course->total_duration ?? 'Self-paced' }} minutes</p>
                @if($enrollment->citation_number)
                <p style="margin: 5px 0;"><strong>Citation #:</strong> {{ $enrollment->citation_number }}</p>
                @endif
            </div>
            
            <p>You can now access your course materials and begin learning at your own pace.</p>
            
            <center>
                <a href="{{ url('/my-enrollments') }}" class="btn">Access Your Course</a>
            </center>
            
            <p style="margin-top: 30px;">Need help? Contact our support team at <a href="mailto:support@example.com">support@example.com</a></p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} E-Learning Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
