<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Enrollments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2 class="mb-4">Student Enrollments</h2>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Enrolled Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="enrollments-list">
                            <tr>
                                <td colspan="7" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadEnrollments() {
            try {
                const response = await fetch('/api/enrollments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                const enrollments = await response.json();
                const tbody = document.getElementById('enrollments-list');
                
                if (!Array.isArray(enrollments) || enrollments.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center">No enrollments found</td></tr>';
                    return;
                }
                
                tbody.innerHTML = enrollments.map(enrollment => `
                    <tr>
                        <td>${enrollment.id}</td>
                        <td>${enrollment.user?.first_name || ''} ${enrollment.user?.last_name || ''}</td>
                        <td>${enrollment.course?.title || 'N/A'}</td>
                        <td><span class="badge bg-${enrollment.status === 'completed' ? 'success' : 'warning'}">${enrollment.status || 'pending'}</span></td>
                        <td><span class="badge bg-${enrollment.payment_status === 'paid' ? 'success' : 'danger'}">${enrollment.payment_status || 'unpaid'}</span></td>
                        <td>${enrollment.created_at ? new Date(enrollment.created_at).toLocaleDateString() : 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewEnrollment(${enrollment.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } catch (error) {
                console.error('Error loading enrollments:', error);
                document.getElementById('enrollments-list').innerHTML = 
                    '<tr><td colspan="7" class="text-center text-danger">Error loading enrollments</td></tr>';
            }
        }

        function viewEnrollment(id) {
            window.location.href = `/admin/enrollments/${id}`;
        }

        loadEnrollments();
    </script>
</body>
</html>
