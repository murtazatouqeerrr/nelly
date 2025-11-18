@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Support Tickets</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ticket-recipients.index') }}" class="btn btn-secondary">
                <i class="fas fa-envelope"></i> Manage Recipients
            </a>
        </div>
    </div>
    <div id="support-tickets"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/support/tickets', {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('support-tickets');
        let tickets = data.data || data;
        
        if (Array.isArray(tickets) && tickets.length > 0) {
            let html = '<table class="table"><thead><tr><th>ID</th><th>Subject</th><th>Email</th><th>Status</th><th>Priority</th><th>Date</th></tr></thead><tbody>';
            tickets.forEach(ticket => {
                html += `<tr>
                    <td>#${ticket.id}</td>
                    <td>${ticket.subject}</td>
                    <td>${ticket.email || 'N/A'}</td>
                    <td><span class="badge bg-${ticket.status === 'open' ? 'warning' : 'success'}">${ticket.status}</span></td>
                    <td><span class="badge bg-info">${ticket.priority}</span></td>
                    <td>${new Date(ticket.created_at).toLocaleDateString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted">No tickets found.</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('support-tickets').innerHTML = '<p class="text-danger">Error loading tickets: ' + error.message + '</p>';
    });
});
</script>
@endsection
