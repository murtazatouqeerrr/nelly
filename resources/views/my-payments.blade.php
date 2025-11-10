<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2>My Payment History</h2>
        
        <div class="card mt-4">
            <div class="card-body">
                <div id="payments-table">
                    <p>Loading payments...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function loadPayments() {
            try {
                const response = await fetch('/web/my-payments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const payments = await response.json();
                    displayPayments(payments);
                } else {
                    document.getElementById('payments-table').innerHTML = '<p class="text-danger">Error loading payments.</p>';
                }
            } catch (error) {
                console.error('Error loading payments:', error);
                document.getElementById('payments-table').innerHTML = '<p class="text-danger">Error loading payments.</p>';
            }
        }
        
        function displayPayments(payments) {
            const container = document.getElementById('payments-table');
            
            if (payments.length === 0) {
                container.innerHTML = '<p>No payments found.</p>';
                return;
            }
            
            container.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Course</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${payments.map(payment => `
                                <tr>
                                    <td>${new Date(payment.created_at).toLocaleDateString()}</td>
                                    <td>${payment.enrollment?.course?.title || 'N/A'}</td>
                                    <td>$${payment.amount}</td>
                                    <td><span class="badge bg-${payment.status === 'completed' ? 'success' : 'warning'}">${payment.status}</span></td>
                                    <td>${payment.payment_method || 'N/A'}</td>
                                    <td>
                                        ${payment.invoice ? `<a href="/invoices/${payment.invoice.id}/download" class="btn btn-sm btn-outline-primary">Download</a>` : 'N/A'}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        loadPayments();
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
