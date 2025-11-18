<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 40px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .certificate-box { background: white; padding: 30px; border-radius: 6px; margin: 20px 0; text-align: center; border: 3px solid #f5576c; }
        .btn { display: inline-block; padding: 12px 30px; background: #f5576c; color: white; text-decoration: none; border-radius: 6px; margin: 10px 5px; }
        .verification { background: #fef3c7; padding: 15px; border-radius: 6px; margin: 20px 0; font-size: 14px; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="font-size: 36px; margin: 0;">📜 Your Certificate is Ready!</h1>
        </div>
        
        <div class="content">
            <p>Hi {{ $user->first_name }},</p>
            
            <p>Congratulations! Your certificate of completion has been generated.</p>
            
            <div class="certificate-box">
                <h2 style="margin: 0 0 10px 0; color: #f5576c;">Certificate of Completion</h2>
                <h3 style="margin: 10px 0; color: #333;">{{ $certificate->course_name ?? $certificate->course_title }}</h3>
                
                <p style="margin: 20px 0;">
                    <strong>Issued To:</strong> {{ $certificate->student_name }}<br>
                    <strong>Completion Date:</strong> {{ $certificate->completion_date->format('F d, Y') }}<br>
                    <strong>Certificate ID:</strong> {{ $certificate->certificate_number ?? $certificate->dicds_certificate_number }}
                </p>
                
                <p style="font-size: 48px; margin: 20px 0;">🏆</p>
            </div>
            
            <center>
                <a href="{{ url('/certificates/' . $certificate->id . '/download') }}" class="btn">Download Certificate PDF</a>
                <a href="{{ url('/certificates/' . $certificate->id . '/verify') }}" class="btn" style="background: #2563eb;">Verify Certificate</a>
            </center>
            
            <div class="verification">
                <strong>📋 Certificate Verification</strong><br>
                Anyone can verify this certificate using the verification URL:<br>
                <a href="{{ url('/verify-certificate/' . ($certificate->certificate_number ?? $certificate->dicds_certificate_number)) }}">
                    {{ url('/verify-certificate/' . ($certificate->certificate_number ?? $certificate->dicds_certificate_number)) }}
                </a>
            </div>
            
            <p style="margin-top: 30px;">
                Your certificate PDF is attached to this email. You can also download it anytime from your dashboard.
            </p>
            
            <p style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px;">
                Share your achievement on social media! 🎉
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} E-Learning Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
