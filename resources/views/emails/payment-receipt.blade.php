<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .highlight { color: #667eea; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Payment Receipt</h1>
            <p>Your Transaction is Complete</p>
        </div>

        <div class="content">
            <p>Hi <span class="highlight">{{ $payment->billing_name }}</span>,</p>
            
            <p>Thank you for your payment! Your transaction has been successfully processed.</p>
            
            <div class="details">
                <h3>Receipt Details</h3>
                <table>
                    <tr>
                        <th>Receipt Number:</th>
                        <td>#{{ $payment->id }}</td>
                    </tr>
                    <tr>
                        <th>Transaction Date:</th>
                        <td>{{ $payment->created_at->format('M d, Y H:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Amount Paid:</th>
                        <td><strong style="color: #667eea; font-size: 1.2em;">${{ number_format($payment->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                    </tr>
                    <tr>
                        <th>Transaction ID:</th>
                        <td>{{ $payment->gateway_payment_id }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td><span style="color: #516425; font-weight: bold;">✓ Completed</span></td>
                    </tr>
                </table>
            </div>
            
            @if(isset($course))
            <div class="details">
                <h3>Course Information</h3>
                <p><strong>Course:</strong> {{ $course->title }}</p>
                <p><strong>Duration:</strong> {{ $course->duration ?? 'N/A' }} hours</p>
            </div>
            @endif
            
            <div style="background: #f4f6f0; border: 1px solid #516425; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <strong>🎓 Course Access Active:</strong> Your course enrollment is now active and you have immediate access to all materials.
            </div>
            
            <h3>Next Steps</h3>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Log in to your account</li>
                <li>Go to "My Enrollments"</li>
                <li>Start learning immediately</li>
                <li>Track your progress</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ url('/my-enrollments') }}" class="button">View My Courses</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 0.9em; color: #666;">If you have any questions about your payment or need assistance, please contact our support team.</p>
            
            <p>Best regards,<br>
            <strong>Traffic School Team</strong></p>
        </div>
    </div>
</body>
</html>
