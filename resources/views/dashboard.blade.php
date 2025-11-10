<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Traffic School Dashboard</title>
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
        <div class="row">
            <div class="col-md-12">
                <h1>
                    <i class="fas fa-tachometer-alt"></i> 
                    Welcome, {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                </h1>
                <p class="text-muted">Role: {{ auth()->user()->role->name }}</p>
            </div>
        </div>
        
        @if(auth()->user()->role->slug === 'student')
        <!-- Student Dashboard -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Browse Courses</h5>
                        <p class="card-text">Find and enroll in traffic school courses for your state</p>
                        <a href="/courses" class="btn btn-primary">
                            <i class="fas fa-search"></i> View Courses
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-graduate fa-3x text-success mb-3"></i>
                        <h5 class="card-title">My Enrollments</h5>
                        <p class="card-text">Continue your enrolled courses and track progress</p>
                        <a href="/my-enrollments" class="btn btn-success">
                            <i class="fas fa-play"></i> My Courses
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Profile</h5>
                        <p class="card-text">Manage your account settings and information</p>
                        <a href="/profile" class="btn btn-info">
                            <i class="fas fa-cog"></i> View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Admin Dashboard -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Manage Courses</h5>
                        <p class="card-text">Create and edit courses, chapters, and content</p>
                        <a href="/create-course" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Manage Courses
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Enrollments</h5>
                        <p class="card-text">Monitor student enrollments and progress</p>
                        <a href="/admin/enrollments" class="btn btn-success">
                            <i class="fas fa-chart-line"></i> View Enrollments
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-cog fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">User Management</h5>
                        <p class="card-text">Manage users, roles, and permissions</p>
                        <a href="/admin/users" class="btn btn-warning">
                            <i class="fas fa-users-cog"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text">View system statistics and reports</p>
                        <a href="/admin/reports" class="btn btn-info">
                            <i class="fas fa-chart-pie"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h3><i class="fas fa-chart-line"></i> Quick Statistics</h3>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 id="total-courses">-</h4>
                                <p>Total Courses</p>
                            </div>
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 id="total-enrollments">-</h4>
                                <p>Total Enrollments</p>
                            </div>
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 id="total-users">-</h4>
                                <p>Total Users</p>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 id="completion-rate">-</h4>
                                <p>Completion Rate</p>
                            </div>
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <script>
        // Load dashboard statistics for admin users
        @if(auth()->user()->role->slug === 'super-admin' || auth()->user()->role->slug === 'admin')
        async function loadStats() {
            try {
                // Load courses count
                const coursesResponse = await fetch('/web/courses', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    credentials: 'same-origin'
                });
                if (coursesResponse.ok) {
                    const courses = await coursesResponse.json();
                    document.getElementById('total-courses').textContent = courses.length;
                }
                
                // Load enrollments count
                const enrollmentsResponse = await fetch('/web/enrollments', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    credentials: 'same-origin'
                });
                if (enrollmentsResponse.ok) {
                    const enrollments = await enrollmentsResponse.json();
                    document.getElementById('total-enrollments').textContent = enrollments.length;
                    
                    // Calculate completion rate
                    const completed = enrollments.filter(e => e.status === 'completed').length;
                    const rate = enrollments.length > 0 ? Math.round((completed / enrollments.length) * 100) : 0;
                    document.getElementById('completion-rate').textContent = rate + '%';
                }
                
                // Load users count
                const usersResponse = await fetch('/web/users', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    credentials: 'same-origin'
                });
                if (usersResponse.ok) {
                    const usersData = await usersResponse.json();
                    // Handle paginated response
                    const userCount = usersData.total || usersData.length || 0;
                    document.getElementById('total-users').textContent = userCount;
                }
                
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        loadStats();
        @endif
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <x-footer />
</body>
</html>
