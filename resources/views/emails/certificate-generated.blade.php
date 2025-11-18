<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .congratulations { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #28a745; text-align: center; }
        .congratulations h2 { color: #28a745; margin: 0; }
        .details { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #0d6efd; }
        .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Congratulations!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $user->first_name }},</p>
            
            <div class="congratulations">
                <h2>You Have Successfully Completed Your Course!</h2>
                <p style="font-size: 18px; margin: 10px 0;">Your certificate is ready for download</p>
            </div>
            
            <div class="details">
                <h3>Certificate Details</h3>
                <p><strong>Course:</strong> {{ $course->title }}</p>
                <p><strong>Completion Date:</strong> {{ now()->format('M d, Y') }}</p>
                <p><strong>Certificate Number:</strong> {{ $certificateNumber }}</p>
            </div>
            
            <p>Your certificate has been attached to this email. You can also download it from your account dashboard.</p>
            
            <a href="{{ url('/my-certificates') }}" class="button">View My Certificates</a>
            
            <p style="margin-top: 30px;">Thank you for completing your course with DummiesTrafficSchool.com. We hope you found the course valuable!</p>
            
            <p>Best regards,<br>
            <strong>DummiesTrafficSchool.com Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} DummiesTrafficSchool.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
