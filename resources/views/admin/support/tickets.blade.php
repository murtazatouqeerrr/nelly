@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2>Support Tickets</h2>
    <div id="support-tickets"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/api/support/tickets', {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('support-tickets');
        if (data.data && data.data.length > 0) {
            let html = '<table class="table"><thead><tr><th>ID</th><th>Subject</th><th>User</th><th>Status</th><th>Priority</th><th>Date</th></tr></thead><tbody>';
            data.data.forEach(ticket => {
                html += `<tr>
                    <td>${ticket.id}</td>
                    <td>${ticket.subject}</td>
                    <td>${ticket.user ? ticket.user.name : 'N/A'}</td>
                    <td><span class="badge bg-${ticket.status === 'open' ? 'warning' : 'success'}">${ticket.status}</span></td>
                    <td>${ticket.priority}</td>
                    <td>${new Date(ticket.created_at).toLocaleDateString()}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>No tickets found.</p>';
        }
    })
    .catch(error => {
        document.getElementById('support-tickets').innerHTML = '<p class="text-danger">Error loading tickets</p>';
    });
});
</script>
@endsection
