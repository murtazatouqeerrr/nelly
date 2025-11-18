<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
        .ticket-details { background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #0d6efd; }
        .detail-row { margin: 10px 0; }
        .label { font-weight: bold; color: #0d6efd; }
        .button { display: inline-block; background: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Support Ticket</h1>
        </div>
        <div class="content">
            <p>A new support ticket has been created. Here are the details:</p>
            
            <div class="ticket-details">
                <div class="detail-row">
                    <span class="label">Ticket ID:</span> #{{ $ticket->id }}
                </div>
                <div class="detail-row">
                    <span class="label">Subject:</span> {{ $ticket->subject }}
                </div>
                <div class="detail-row">
                    <span class="label">Priority:</span> <strong>{{ ucfirst($ticket->priority) }}</strong>
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span> {{ ucfirst($ticket->status) }}
                </div>
                <div class="detail-row">
                    <span class="label">From:</span> {{ $ticket->user->first_name ?? 'N/A' }} ({{ $ticket->email }})
                </div>
                <div class="detail-row">
                    <span class="label">Created:</span> {{ $ticket->created_at->format('M d, Y H:i A') }}
                </div>
                <div class="detail-row" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                    <span class="label">Description:</span><br>
                    <p>{{ $ticket->description }}</p>
                </div>
            </div>
            
            <a href="{{ url('/admin/support/tickets') }}" class="button">View Ticket</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Support System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
