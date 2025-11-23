@extends('emails.layout')

@section('content')
<div class="header">
    <h1>✓ Payment Receipt</h1>
    <p>Your Transaction is Complete</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you for your payment! Your transaction has been successfully processed.</p>
    
    <div class="details">
        <h3>Receipt Details</h3>
        <p><strong>Receipt Number:</strong> {{ $receipt->receipt_number ?? 'N/A' }}</p>
        <p><strong>Transaction Date:</strong> {{ $receipt->created_at->format('M d, Y H:i A') }}</p>
        <p><strong>Amount Paid:</strong> <span class="accent-gold">${{ number_format($receipt->amount, 2) }}</span></p>
        <p><strong>Status:</strong> <span class="accent-green">Completed</span></p>
    </div>
    
    <h3>Payment Information</h3>
    <div style="background: white; padding: 15px; border-left: 4px solid #6B8E23; margin: 15px 0; border-radius: 4px;">
        <p><strong>Payment Method:</strong> {{ $receipt->payment_method ?? 'Credit Card' }}</p>
        <p><strong>Transaction ID:</strong> {{ $receipt->transaction_id ?? 'N/A' }}</p>
        <p><strong>Reference:</strong> {{ $receipt->reference ?? 'N/A' }}</p>
    </div>
    
    <div style="text-align: center;">
        <a href="{{ url('/payments') }}" class="button">View Payment History</a>
        <a href="{{ url('/receipts/' . ($receipt->id ?? '') . '/download') }}" class="button button-secondary">Download Receipt</a>
    </div>
    
    <div class="alert alert-success">
        <strong>🎓 Course Access Active:</strong> Your course enrollment is now active and you have immediate access to all materials.
    </div>
    
    <h3>Next Steps</h3>
    <ul style="margin-left: 20px; margin-top: 10px;">
        <li>Log in to your account</li>
        <li>Go to "My Enrollments"</li>
        <li>Start learning immediately</li>
        <li>Track your progress</li>
    </ul>
    
    <div class="details">
        <h3>Billing Address</h3>
        <p>{{ $user->first_name }} {{ $user->last_name }}<br>
        {{ $user->email }}</p>
    </div>
    
    <p style="margin-top: 30px;">If you have any questions about your payment or need assistance, please contact our support team.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Team</strong></p>
</div>
@endsection
