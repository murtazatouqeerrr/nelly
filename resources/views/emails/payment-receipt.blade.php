<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f8f9fa; padding: 20px; border-radius: 0 0 5px 5px; }
        .receipt-details { background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Receipt</h1>
        <p>Traffic School Pro</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $payment->user->first_name }} {{ $payment->user->last_name }},</p>
        
        <p>Thank you for your payment. Your payment receipt is attached to this email.</p>
        
        <div class="receipt-details">
            <h3>Payment Summary</h3>
            <p><strong>Receipt #:</strong> {{ $payment->id }}</p>
            <p><strong>Date:</strong> {{ $payment->created_at->format('M d, Y') }}</p>
            <p><strong>Course:</strong> {{ $payment->enrollment->course->title ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> ${{ number_format($payment->amount, 2) }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
        </div>
        
        <p>If you have any questions about this payment, please contact our support team.</p>
        
        <p>Best regards,<br>The Traffic School Pro Team</p>
    </div>
</body>
</html>
