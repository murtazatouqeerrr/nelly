<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #059669; color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .invoice-box { background: white; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .invoice-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
        .total { font-size: 20px; font-weight: bold; color: #059669; }
        .btn { display: inline-block; padding: 12px 30px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Payment Approved</h1>
        </div>
        
        <div class="content">
            <p>Hi {{ $user->first_name }},</p>
            
            <p>Your payment has been successfully processed!</p>
            
            <div class="invoice-box">
                <h3 style="margin-top: 0;">Order Summary</h3>
                
                <div class="invoice-row">
                    <span>Order ID:</span>
                    <strong>#{{ $payment->id }}</strong>
                </div>
                
                <div class="invoice-row">
                    <span>Payment Method:</span>
                    <strong>{{ ucfirst($payment->gateway) }}</strong>
                </div>
                
                <div class="invoice-row">
                    <span>Transaction ID:</span>
                    <strong>{{ $payment->gateway_payment_id }}</strong>
                </div>
                
                <div class="invoice-row">
                    <span>Date:</span>
                    <strong>{{ $payment->created_at->format('F d, Y h:i A') }}</strong>
                </div>
                
                <div class="invoice-row" style="border: none; margin-top: 10px;">
                    <span class="total">Total Paid:</span>
                    <span class="total">${{ number_format($payment->amount, 2) }}</span>
                </div>
            </div>
            
            <p>You now have full access to your enrolled course.</p>
            
            <center>
                <a href="{{ url('/my-enrollments') }}" class="btn">Access Your Course</a>
            </center>
            
            <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
                A receipt has been sent to {{ $payment->billing_email }}
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} E-Learning Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
