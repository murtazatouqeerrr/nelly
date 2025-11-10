<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Available Courses</title>
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
            <course-list></course-list>
        </div>
        
        <!-- Fallback content -->
        <div id="fallback-content">
            <h2>Available Courses</h2>
            <div class="row">
                <div class="col-md-12">
                    <p>Loading courses...</p>
                    <div id="courses-container"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Fallback course loading
        async function loadCourses() {
            try {
                const response = await fetch('/web/courses', {
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
                    throw new Error('Failed to load courses');
                }
                
                const courses = await response.json();
                const container = document.getElementById('courses-container');
                
                if (courses.length === 0) {
                    container.innerHTML = '<p>No courses available.</p>';
                    return;
                }
                
                container.innerHTML = courses.map(course => `
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${course.title}</h5>
                                <p class="card-text">${course.description}</p>
                                <p><strong>State:</strong> ${course.state_code}</p>
                                <p><strong>Duration:</strong> ${course.total_duration} minutes</p>
                                <p><strong>Price:</strong> $${course.price}</p>
                                <button onclick="enrollCourse('${course.id}')" class="btn btn-primary">Enroll</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading courses:', error);
                document.getElementById('courses-container').innerHTML = '<p>Error loading courses.</p>';
            }
        }
        
        async function enrollCourse(courseId) {
            try {
                const response = await fetch('/web/enrollments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ course_id: courseId })
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    const data = await response.json();
                    throw new Error(data.error || 'Failed to enroll');
                }
                
                alert('Enrolled successfully!');
            } catch (error) {
                console.error('Error enrolling:', error);
                alert(error.message || 'Failed to enroll in course');
            }
        }
        
        // Show fallback and load courses if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app course-list');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadCourses();
            }
        }, 1000);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
