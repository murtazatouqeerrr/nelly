<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #3490dc;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f8f9fa;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #3490dc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Congratulations!</h1>
        </div>
        
        <div class="content">
            <p>Dear {{ $user->first_name }} {{ $user->last_name }},</p>
            
            <p>Congratulations on successfully completing the Florida 4-Hour Basic Driver Improvement (BDI) Course!</p>
            
            <p><strong>Certificate Details:</strong></p>
            <ul>
                <li>Certificate Number: <strong>{{ $certificate->certificate_number }}</strong></li>
                <li>Completion Date: <strong>{{ $certificate->completion_date->format('m/d/Y') }}</strong></li>
                <li>Final Exam Score: <strong>{{ $certificate->exam_score }}%</strong></li>
            </ul>
            
            <p>Your official certificate is attached to this email as a PDF file. Please save it for your records and submit it to the appropriate authority as required.</p>
            
            <p><strong>Important Notes:</strong></p>
            <ul>
                <li>Only original certificates are acceptable. Photocopies are not acceptable.</li>
                <li>Your completion has been submitted to the Florida DMV.</li>
                <li>Keep this certificate in a safe place for your records.</li>
            </ul>
            
            <p>If you have any questions or need assistance, please contact our support team:</p>
            <ul>
                <li>Email: Support@DummiesTrafficSchool.com</li>
                <li>Phone: (877) 382-3700</li>
                <li>Hours: Monday-Friday 8am-4pm PST</li>
            </ul>
            
            <p>Thank you for choosing DummiesTrafficSchool.com!</p>
            
            <p>Best regards,<br>
            <strong>DummiesTrafficSchool.com Team</strong></p>
        </div>
        
        <div class="footer">
            <p>DummiesTrafficSchool.com<br>
            524 N. Mountain View Ave. #2<br>
            San Bernardino, CA 92401</p>
            
            <p>This is an automated email. Please do not reply directly to this message.</p>
        </div>
    </div>
</body>
</html>
