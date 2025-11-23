@extends('emails.layout')

@section('content')
<div class="header">
    <h1>📋 Invoice</h1>
    <p>Your Payment Receipt</p>
</div>

<div class="content">
    <p>Hi <span class="highlight">{{ $user->first_name }}</span>,</p>
    
    <p>Thank you for your payment. Please find your invoice details below.</p>
    
    <div class="details">
        <h3>Invoice Details</h3>
        <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number ?? 'N/A' }}</p>
        <p><strong>Invoice Date:</strong> {{ $invoice->created_at->format('M d, Y') }}</p>
        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') ?? 'Paid' }}</p>
        <p><strong>Status:</strong> <span class="accent-green">Paid</span></p>
    </div>
    
    <h3>Billing Information</h3>
    <div style="background: white; padding: 15px; border-left: 4px solid #6B8E23; margin: 15px 0; border-radius: 4px;">
        <p><strong>Bill To:</strong><br>
        {{ $user->first_name }} {{ $user->last_name }}<br>
        {{ $user->email }}</p>
    </div>
    
    <h3>Invoice Items</h3>
    <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
        <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
            <th style="padding: 10px; text-align: left; color: #556B2F;">Description</th>
            <th style="padding: 10px; text-align: right; color: #556B2F;">Amount</th>
        </tr>
        @if(isset($invoice->items))
            @foreach($invoice->items as $item)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px;">{{ $item->description ?? 'Course Enrollment' }}</td>
                <td style="padding: 10px; text-align: right;">${{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        @else
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 10px;">Course Enrollment</td>
            <td style="padding: 10px; text-align: right;">${{ number_format($invoice->amount ?? 0, 2) }}</td>
        </tr>
        @endif
        <tr style="background: #f5f5f5; border-top: 2px solid #ddd;">
            <td style="padding: 10px; font-weight: 600; color: #556B2F;">Total</td>
            <td style="padding: 10px; text-align: right; font-weight: 600; color: #DAA520;">${{ number_format($invoice->total ?? $invoice->amount ?? 0, 2) }}</td>
        </tr>
    </table>
    
    <div style="text-align: center;">
        <a href="{{ url('/invoices/' . ($invoice->id ?? '')) }}" class="button">View Full Invoice</a>
        <a href="{{ url('/invoices/' . ($invoice->id ?? '') . '/download') }}" class="button button-secondary">Download PDF</a>
    </div>
    
    <div class="alert alert-success">
        <strong>✓ Payment Confirmed:</strong> Your payment has been successfully processed. Your course access is now active.
    </div>
    
    <h3>Payment Method</h3>
    <p><strong>Payment Method:</strong> {{ $invoice->payment_method ?? 'Credit Card' }}</p>
    <p><strong>Transaction ID:</strong> {{ $invoice->transaction_id ?? 'N/A' }}</p>
    
    <p style="margin-top: 30px;">If you have any questions about this invoice or need a receipt, please don't hesitate to contact us.</p>
    
    <p>Best regards,<br>
    <strong>{{ config('app.name', 'E-Learning Platform') }} Billing Team</strong></p>
</div>
@endsection
