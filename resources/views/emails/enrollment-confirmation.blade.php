<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .details { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #0d6efd; }
        .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to DummiesTrafficSchool.com!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $user->first_name }},</p>
            
            <p>Thank you for enrolling in our course! We're excited to have you as a student.</p>
            
            <div class="details">
                <h3>Enrollment Details</h3>
                <p><strong>Course:</strong> {{ $course->title }}</p>
                <p><strong>Enrollment Date:</strong> {{ now()->format('M d, Y') }}</p>
                <p><strong>Amount Paid:</strong> ${{ number_format($enrollment->amount_paid, 2) }}</p>
            </div>
            
            <p>You can now access the course and start learning. Click the button below to begin:</p>
            
            <a href="{{ url('/my-enrollments') }}" class="button">Start Learning</a>
            
            <p style="margin-top: 30px;">If you have any questions, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            <strong>DummiesTrafficSchool.com Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} DummiesTrafficSchool.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
