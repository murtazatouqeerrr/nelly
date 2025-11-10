<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Florida Course Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .container-fluid {
            padding: 20px;
        }
        h2 {
            color: var(--text-primary);
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .table {
            color: var(--text-primary);
        }
        .table thead {
            background-color: var(--accent);
            color: white;
        }
        .table tbody tr {
            border-color: var(--border);
        }
        .table tbody tr:hover {
            background-color: var(--hover);
        }
        .form-select, .form-control {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .form-select:focus, .form-control:focus {
            background-color: var(--bg-secondary);
            border-color: var(--accent);
            color: var(--text-primary);
        }
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        .modal-content {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        .modal-header {
            border-bottom-color: var(--border);
        }
        .modal-footer {
            border-top-color: var(--border);
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Florida Course Management</h2>
            <button class="btn btn-primary" onclick="showCreateModal()">
                <i class="fas fa-plus"></i> Create Course
            </button>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <select id="courseTypeFilter" class="form-select">
                    <option value="">All Course Types</option>
                    <option value="BDI">BDI</option>
                    <option value="ADI">ADI</option>
                    <option value="TLSAE">TLSAE</option>
                </select>
            </div>
            <div class="col-md-4">
                <select id="deliveryTypeFilter" class="form-select">
                    <option value="">All Delivery Types</option>
                    <option value="internet">Internet</option>
                    <option value="in_person">In Person</option>
                    <option value="cd_rom">CD ROM</option>
                    <option value="video">Video</option>
                    <option value="dvd">DVD</option>
                </select>
            </div>
            <div class="col-md-4">
                <input id="searchInput" type="text" class="form-control" placeholder="Search courses...">
            </div>
        </div>

        <div id="courses-table" class="table-responsive">
            <p>Loading courses...</p>
        </div>
    </div>

    <!-- Create/Edit Course Modal -->
    <div class="modal fade" id="courseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="courseModalTitle">Create Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="courseForm">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Course Type</label>
                                <select id="courseType" class="form-select" required>
                                    <option value="BDI">BDI</option>
                                    <option value="ADI">ADI</option>
                                    <option value="TLSAE">TLSAE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery Type</label>
                                <select id="deliveryType" class="form-select" required>
                                    <option value="internet">Internet</option>
                                    <option value="in_person">In Person</option>
                                    <option value="cd_rom">CD ROM</option>
                                    <option value="video">Video</option>
                                    <option value="dvd">DVD</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input id="courseTitle" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="courseDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Duration (minutes)</label>
                                <input id="courseDuration" type="number" class="form-control" min="240" value="240" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Min Pass Score (%)</label>
                                <input id="coursePassScore" type="number" class="form-control" min="0" max="100" value="80" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Price ($)</label>
                                <input id="coursePrice" type="number" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">DICDS Course ID</label>
                            <input id="dicdsId" type="text" class="form-control" required>
                        </div>
                        <div class="form-check">
                            <input id="courseActive" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="courseActive">Active</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveCourse()">Save Course</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let courses = [];
        let editingCourseId = null;
        
        async function loadCourses() {
            try {
                const response = await fetch('/api/florida-courses', {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    courses = result.data || result;
                    displayCourses();
                } else {
                    document.getElementById('courses-table').innerHTML = '<p class="text-danger">Error loading courses.</p>';
                }
            } catch (error) {
                console.error('Error loading courses:', error);
                document.getElementById('courses-table').innerHTML = '<p class="text-danger">Error loading courses: ' + error.message + '</p>';
            }
        }
        
        function displayCourses() {
            const container = document.getElementById('courses-table');
            
            if (!courses || !Array.isArray(courses) || courses.length === 0) {
                container.innerHTML = '<p>No courses found.</p>';
                return;
            }
            
            container.innerHTML = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Delivery</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>DICDS ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${courses.map(course => `
                            <tr>
                                <td>${course.title}</td>
                                <td><span class="badge bg-info">${course.course_type}</span></td>
                                <td>${course.delivery_type}</td>
                                <td>${course.total_duration} min</td>
                                <td>$${course.price}</td>
                                <td>${course.dicds_course_id}</td>
                                <td>
                                    <span class="${course.is_active ? 'badge bg-success' : 'badge bg-danger'}">
                                        ${course.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editCourse(${course.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success me-1" onclick="manageChapters(${course.id})">
                                        <i class="fas fa-book"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(${course.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }
        
        function showCreateModal() {
            editingCourseId = null;
            document.getElementById('courseModalTitle').textContent = 'Create Course';
            clearForm();
            new bootstrap.Modal(document.getElementById('courseModal')).show();
        }
        
        function editCourse(id) {
            editingCourseId = id;
            const course = courses.find(c => c.id === id);
            if (course) {
                document.getElementById('courseModalTitle').textContent = 'Edit Course';
                fillForm(course);
                new bootstrap.Modal(document.getElementById('courseModal')).show();
            }
        }
        
        function fillForm(course) {
            document.getElementById('courseType').value = course.course_type;
            document.getElementById('deliveryType').value = course.delivery_type;
            document.getElementById('courseTitle').value = course.title;
            document.getElementById('courseDescription').value = course.description || '';
            document.getElementById('courseDuration').value = course.total_duration;
            document.getElementById('coursePassScore').value = course.min_pass_score || 80;
            document.getElementById('coursePrice').value = course.price;
            document.getElementById('dicdsId').value = course.dicds_course_id;
            document.getElementById('courseActive').checked = course.is_active;
        }
        
        function clearForm() {
            document.getElementById('courseType').value = 'BDI';
            document.getElementById('deliveryType').value = 'internet';
            document.getElementById('courseTitle').value = '';
            document.getElementById('courseDescription').value = '';
            document.getElementById('courseDuration').value = 240;
            document.getElementById('coursePassScore').value = 80;
            document.getElementById('coursePrice').value = '';
            document.getElementById('dicdsId').value = '';
            document.getElementById('courseActive').checked = true;
        }
        
        async function saveCourse() {
            const formData = {
                course_type: document.getElementById('courseType').value,
                delivery_type: document.getElementById('deliveryType').value,
                title: document.getElementById('courseTitle').value,
                description: document.getElementById('courseDescription').value,
                total_duration: document.getElementById('courseDuration').value,
                min_pass_score: document.getElementById('coursePassScore').value,
                price: document.getElementById('coursePrice').value,
                dicds_course_id: document.getElementById('dicdsId').value,
                is_active: document.getElementById('courseActive').checked
            };
            
            try {
                const url = editingCourseId ? `/api/florida-courses/${editingCourseId}` : '/api/florida-courses';
                const method = editingCourseId ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(formData)
                });
                
                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('courseModal')).hide();
                    loadCourses();
                    alert(editingCourseId ? 'Course updated successfully!' : 'Course created successfully!');
                } else {
                    alert('Error saving course');
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }
        
        function manageChapters(id) {
            window.location.href = `/admin/florida-courses/${id}/chapters`;
        }
        
        async function deleteCourse(id) {
            if (confirm('Are you sure you want to delete this course?')) {
                try {
                    const response = await fetch(`/api/florida-courses/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (response.ok) {
                        loadCourses();
                        alert('Course deleted successfully!');
                    } else {
                        alert('Error deleting course');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            loadCourses();
        });
    </script>
</body>
</html>
