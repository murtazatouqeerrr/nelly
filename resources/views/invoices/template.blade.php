<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info, .customer-info {
            width: 48%;
        }
        .invoice-info h3, .customer-info h3 {
            margin-top: 0;
            color: #007bff;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .invoice-table th, .invoice-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .invoice-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Traffic School Pro</div>
        <div>Professional Driver Education Services</div>
    </div>

    <div class="invoice-details">
        <div class="invoice-info">
            <h3>Invoice Details</h3>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
        </div>
        
        <div class="customer-info">
            <h3>Bill To</h3>
            <p><strong>{{ $invoice->payment->user->first_name }} {{ $invoice->payment->user->last_name }}</strong></p>
            <p>{{ $invoice->payment->user->email }}</p>
            @if($invoice->payment->user->phone)
                <p>{{ $invoice->payment->user->phone }}</p>
            @endif
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Course</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Course Enrollment Fee</td>
                <td>{{ $invoice->payment->enrollment->course->title ?? 'N/A' }}</td>
                <td>${{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Subtotal: ${{ number_format($invoice->total_amount, 2) }}</strong></p>
        <p><strong>Tax: $0.00</strong></p>
        <p class="total-amount">Total: ${{ number_format($invoice->total_amount, 2) }}</p>
    </div>

    <div class="footer">
        <p>Thank you for choosing Traffic School Pro!</p>
        <p>For questions about this invoice, please contact us at support@trafficschoolpro.com</p>
    </div>
</body>
</html>
