<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .certificate-details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .verification-link { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Certificate of Completion</h1>
        <p>Traffic School Pro</p>
    </div>
    
    <div class="content">
        <p>Congratulations {{ $certificate->student_name }}!</p>
        
        <p>You have successfully completed your traffic school course. Your certificate is attached to this email.</p>
        
        <div class="certificate-details">
            <h3>Certificate Details</h3>
            <p><strong>Certificate Number:</strong> {{ $certificate->certificate_number }}</p>
            <p><strong>Student Name:</strong> {{ $certificate->student_name }}</p>
            <p><strong>Course:</strong> {{ $certificate->course_name }}</p>
            <p><strong>State:</strong> {{ $certificate->state_code }}</p>
            <p><strong>Completion Date:</strong> {{ \Carbon\Carbon::parse($certificate->completion_date)->format('M d, Y') }}</p>
            <p><strong>Status:</strong> {{ ucfirst($certificate->status) }}</p>
        </div>
        
        <p>You can verify this certificate online using the link below:</p>
        <a href="{{ url('/certificates/' . $certificate->verification_hash . '/verify') }}" class="verification-link">
            Verify Certificate Online
        </a>
        
        <p>Please keep this certificate for your records. If you need additional copies, you can download them from your student dashboard.</p>
        
        <p>Congratulations again on completing your course!</p>
        
        <p>Best regards,<br>The Traffic School Pro Team</p>
    </div>
</body>
</html>
