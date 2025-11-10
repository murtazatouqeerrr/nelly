<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt #{{ $payment->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px; }
        .company-name { font-size: 24px; font-weight: bold; color: #007bff; }
        .receipt-details { margin-bottom: 30px; }
        .receipt-table { width: 100%; border-collapse: collapse; }
        .receipt-table th, .receipt-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .receipt-table th { background-color: #f8f9fa; }
        .total { font-size: 18px; font-weight: bold; color: #007bff; text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Traffic School Pro</div>
        <div>Payment Receipt</div>
    </div>

    <div class="receipt-details">
        <p><strong>Receipt #:</strong> {{ $payment->id }}</p>
        <p><strong>Date:</strong> {{ $payment->created_at->format('M d, Y') }}</p>
        <p><strong>Customer:</strong> {{ $payment->user->first_name }} {{ $payment->user->last_name }}</p>
        <p><strong>Email:</strong> {{ $payment->user->email }}</p>
    </div>

    <table class="receipt-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Course</th>
                <th>Payment Method</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Course Enrollment Payment</td>
                <td>{{ $payment->enrollment->course->title ?? 'N/A' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td>${{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        Total Paid: ${{ number_format($payment->amount, 2) }}
    </div>
</body>
</html>
