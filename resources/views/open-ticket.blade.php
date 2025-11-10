@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2><i class="fas fa-ticket-alt me-2"></i>Open a Support Ticket</h2>
    
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form id="ticketForm">
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="category" required>
                                <option value="">Select Category</option>
                                <option value="technical">Technical Issue</option>
                                <option value="billing">Billing Question</option>
                                <option value="course">Course Content</option>
                                <option value="certificate">Certificate Issue</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>My Tickets</h5>
                </div>
                <div class="card-body" id="myTickets">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('ticketForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        subject: document.getElementById('subject').value,
        category: document.getElementById('category').value,
        priority: document.getElementById('priority').value,
        description: document.getElementById('description').value
    };
    
    try {
        const response = await fetch('/api/support/tickets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Ticket submitted successfully!');
            document.getElementById('ticketForm').reset();
            loadMyTickets();
        } else {
            console.error('Server response:', result);
            alert('Error: ' + (result.message || result.error || 'Failed to submit ticket'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit ticket');
    }
});

async function loadMyTickets() {
    try {
        const response = await fetch('/api/support/tickets', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        const container = document.getElementById('myTickets');
        
        // Handle paginated response
        const tickets = result.data || result;
        
        if (!Array.isArray(tickets) || tickets.length === 0) {
            container.innerHTML = '<p class="text-muted">No tickets yet</p>';
            return;
        }
        
        container.innerHTML = tickets.map(ticket => `
            <div class="mb-2 p-2 border-bottom">
                <strong>${ticket.subject}</strong>
                <br>
                <small class="text-muted">${ticket.status}</small>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading tickets:', error);
        document.getElementById('myTickets').innerHTML = '<p class="text-danger">Error loading tickets</p>';
    }
}

loadMyTickets();
</script>
@endsection
