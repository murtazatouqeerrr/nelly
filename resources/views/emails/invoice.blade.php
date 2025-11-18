<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .invoice-details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice {{ $invoice->invoice_number }}</h1>
        <p>DummiesTrafficSchool.com</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $invoice->payment->user->first_name }} {{ $invoice->payment->user->last_name }},</p>
        
        <p>Thank you for your enrollment with DummiesTrafficSchool.com. Please find your invoice attached to this email.</p>
        
        <div class="invoice-details">
            <h3>Invoice Summary</h3>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
            <p><strong>Course:</strong> {{ $invoice->payment->enrollment->course->title ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> ${{ number_format($invoice->total_amount, 2) }}</p>
        </div>
        
        <p>If you have any questions about this invoice, please don't hesitate to contact our support team.</p>
        
        <p>Thank you for choosing DummiesTrafficSchool.com!</p>
        
        <p>Best regards,<br>
        The DummiesTrafficSchool.com Team</p>
    </div>
    
    <div class="footer">
        <p>DummiesTrafficSchool.com | Professional Driver Education Services</p>
        <p>Email: support@dummiestrafficschool.com | Phone: (555) 123-4567</p>
    </div>
</body>
</html>
