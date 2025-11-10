<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Enrollments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div id="app">
            <my-enrollments></my-enrollments>
        </div>
        
        <!-- Fallback content -->
        <div id="fallback-content">
            <h2>My Course Enrollments</h2>
            <div id="enrollments-container">
                <p>Loading enrollments...</p>
            </div>
        </div>
    </div>
    
    <script>
        async function loadEnrollments() {
            try {
                const response = await fetch('/web/my-enrollments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error('Failed to load enrollments');
                }
                
                const enrollments = await response.json();
                console.log('Enrollments data:', enrollments);
                const container = document.getElementById('enrollments-container');
                
                if (enrollments.length === 0) {
                    container.innerHTML = '<div class="text-center py-4"><p>No enrollments found. <a href="/courses">Browse courses</a> to get started.</p></div>';
                    return;
                }
                
                container.innerHTML = '<div class="row">' + enrollments.map(enrollment => {
                    // Handle both old and new API response formats
                    const course = enrollment.course || {};
                    const courseTitle = course.title || `4-Hour Florida ${course.course_type || 'BDI'} Course`;
                    const courseDesc = course.description || 'No description available';
                    const status = enrollment.status || 'active';
                    const paymentStatus = enrollment.payment_status || 'pending';
                    const progress = enrollment.progress_percentage || 0;
                    
                    return `
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${courseTitle}</h5>
                                <p class="card-text">${courseDesc}</p>
                                <div class="progress mb-2">
                                    <div class="progress-bar" style="width: ${progress}%">
                                        ${progress}%
                                    </div>
                                </div>
                                <p><strong>Status:</strong> <span class="badge bg-primary">${status}</span></p>
                                <p><strong>Payment:</strong> <span class="badge bg-${paymentStatus === 'paid' ? 'success' : 'warning'}">${paymentStatus}</span></p>
                                <a href="/course-player?enrollmentId=${enrollment.id}" class="btn btn-primary">Continue Course</a>
                            </div>
                        </div>
                    </div>
                `;
                }).join('') + '</div>';
            } catch (error) {
                console.error('Error loading enrollments:', error);
                document.getElementById('enrollments-container').innerHTML = '<p class="text-danger">Error loading enrollments.</p>';
            }
        }
        
        // Show fallback and load enrollments if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app my-enrollments');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadEnrollments();
            }
        }, 1000);
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
