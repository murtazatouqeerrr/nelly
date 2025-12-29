<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Course Management - {{ time() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/ym4g7vu3k0o8r84l3yr3n07oc2j5ebrdx8tol0iblu5lt1ck/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        html {
            scroll-behavior: smooth;
        }
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .form-select, .form-control, .form-check-input {
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
        .state-divider {
            background-color: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border) !important;
        }
        .state-divider .badge {
            background-color: var(--accent) !important;
            color: white !important;
            border: 1px solid var(--border);
        }
        .course-item {
            background-color: var(--bg-secondary);
            border-color: var(--border) !important;
            color: var(--text-primary);
        }
        .course-item:hover {
            background-color: var(--hover);
        }
        .course-item .text-muted {
            color: var(--text-secondary) !important;
        }
        .btn-group .btn {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .btn-group .btn:hover {
            background-color: var(--hover);
            border-color: var(--accent);
        }
        .btn-outline-secondary {
            color: var(--text-primary);
            border-color: var(--border);
        }
        .btn-outline-secondary:hover {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        .btn-secondary {
            background-color: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        
        /* Auto-scroll highlight animation */
        @keyframes highlightForm {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
            50% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0.2); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
        }
        
        .form-highlight {
            animation: highlightForm 1.5s ease-out;
        }
        
        /* Theme-responsive button colors */
        .btn-outline-info {
            color: var(--accent) !important;
            border-color: var(--accent) !important;
            background-color: transparent !important;
        }
        
        .btn-outline-info:hover {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: white !important;
        }
        
        .btn-outline-primary {
            color: var(--accent) !important;
            border-color: var(--accent) !important;
            background-color: transparent !important;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--accent) !important;
            border-color: var(--accent) !important;
            color: white !important;
        }
        
        .btn-outline-success {
            color: #516425 !important;
            border-color: #516425 !important;
            background-color: transparent !important;
        }
        
        .btn-outline-success:hover {
            background-color: #516425 !important;
            border-color: #516425 !important;
            color: white !important;
        }
        
        .btn-outline-danger {
            color: #dc3545 !important;
            border-color: #dc3545 !important;
            background-color: transparent !important;
        }
        
        .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
        }
        
        /* Ensure buttons work well in all themes */
        .btn-outline-secondary {
            color: var(--text-primary) !important;
            border-color: var(--border) !important;
            background-color: transparent !important;
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--hover) !important;
            border-color: var(--accent) !important;
            color: var(--text-primary) !important;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container-fluid mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">Course Management</h2>
                        <p class="text-muted mb-0">Create and manage your online courses</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="toggleGroupingCreate" onclick="toggleStateGroupingCreate()" class="btn btn-outline-secondary" title="Group by State">
                            <i class="fas fa-layer-group me-1"></i> Group by State
                        </button>
                        <button onclick="showCreateForm()" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> New Course
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fallback content -->
        <div id="fallback-content">
            <div class="row g-4">
                <div class="col-lg-4 col-md-5">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-book me-2"></i>Existing Courses
                            </h5>
                        </div>
                        <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                            <div id="courses-list" class="p-3">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0 text-muted">Loading courses...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8 col-md-7">
                    <!-- Welcome Message -->
                    <div class="card" id="welcome-card">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                                <h4>Course Management System</h4>
                                <p class="text-muted">Select a course from the left to edit, or create a new course to get started.</p>
                            </div>
                            <button onclick="showCreateForm()" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Create Your First Course
                            </button>
                        </div>
                    </div>

                    <!-- Course Form -->
                    <div class="card" id="course-form-card" style="display: none;">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" id="form-title">
                                    <i class="fas fa-edit me-2"></i>Create New Course
                                </h5>
                                <button type="button" onclick="hideForm()" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="course-form">
                                <input type="hidden" id="course-id" name="course_id">
                                
                                <!-- Basic Information Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-info-circle me-1"></i>Basic Information
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-semibold">Course Title *</label>
                                            <input type="text" class="form-control form-control-lg" id="title" name="title" required 
                                                   placeholder="Enter a descriptive course title">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Description *</label>
                                            <textarea class="form-control" id="description" name="description" rows="4" required
                                                      placeholder="Provide a detailed description of the course content and objectives"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Course Settings Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-cog me-1"></i>Course Settings
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="state_code" class="form-label fw-semibold">State *</label>
                                            <select class="form-select" id="state_code" name="state_code" required>
                                                <option value="">Select State</option>
                                                <option value="FL">Florida</option>
                                                <option value="CA">California</option>
                                                <option value="TX">Texas</option>
                                                <option value="MO">Missouri</option>
                                                <option value="DE">Delaware</option>
                                                <option value="NY">New York</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="min_pass_score" class="form-label fw-semibold">Minimum Pass Score (%) *</label>
                                            <input type="number" class="form-control" id="min_pass_score" name="min_pass_score" 
                                                   min="0" max="100" value="80" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="total_duration" class="form-label fw-semibold">Duration (minutes) *</label>
                                            <input type="number" class="form-control" id="total_duration" name="total_duration" required
                                                   placeholder="Total course duration">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price" class="form-label fw-semibold">Price ($) *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" required
                                                       placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Additional Settings Section -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-certificate me-1"></i>Additional Settings
                                        </h6>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="certificate_template" class="form-label fw-semibold">Certificate Template</label>
                                            <input type="text" class="form-control" id="certificate_template" name="certificate_template"
                                                   placeholder="Optional: Specify certificate template name">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                                            <label class="form-check-label fw-semibold" for="is_active">
                                                Active Course
                                                <small class="text-muted d-block">Students can enroll in active courses</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Form Actions -->
                                <div class="d-flex gap-2 pt-3 border-top">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-1"></i>Save Course
                                    </button>
                                    <button type="button" onclick="hideForm()" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-3" id="chapters-section" style="display: none;">
                        <div class="card-header d-flex justify-content-between">
                            <h5>Course Chapters</h5>
                            <button onclick="showChapterForm()" class="btn btn-sm btn-primary">Add Chapter</button>
                        </div>
                        <div class="card-body" id="chapters-list">
                            <p>No chapters added yet.</p>
                        </div>
                    </div>
                    
                    <div class="card mt-3" id="chapter-form-card" style="display: none;">
                        <div class="card-header">
                            <h5 id="chapter-form-title">Add Chapter</h5>
                        </div>
                        <div class="card-body">
                            <form id="chapter-form">
                                <input type="hidden" id="chapter-course-id" name="course_id">
                                <input type="hidden" id="chapter-id" name="chapter_id">
                                
                                <div class="mb-3">
                                    <label for="chapter-title" class="form-label">Chapter Title</label>
                                    <input type="text" class="form-control" id="chapter-title" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chapter-content" class="form-label">Content</label>
                                    <textarea class="form-control" id="chapter-content" name="content"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="chapter-duration" class="form-label">Duration (minutes)</label>
                                            <input type="number" class="form-control" id="chapter-duration" name="duration" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="video-url" class="form-label">Video URL</label>
                                            <input type="text" class="form-control" id="video-url" name="video_url" placeholder="Optional: External video URL or leave empty for uploaded videos">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="chapter-media" class="form-label">Upload Media</label>
                                    <div id="current-media" style="display: none;" class="mb-2">
                                        <small class="text-muted">Current media:</small>
                                        <div id="current-media-content"></div>
                                    </div>
                                    <input type="file" class="form-control" id="chapter-media" name="media[]" multiple accept="video/*,image/*,.pdf,.doc,.docx">
                                    <small class="text-muted">Supported: Videos, Images, PDF, Word documents. You can select multiple files.</small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Add Chapter</button>
                                    <button type="button" onclick="hideChapterForm()" class="btn btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Copy Course Modal -->
    <div class="modal fade" id="copyCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Copy Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="copyCourseForm">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Copying course: <strong id="sourceCourseTitle"></strong>
                        </div>
                        
                        <h6>New Course Details</h6>
                        <div class="mb-3">
                            <label class="form-label">New Course Title</label>
                            <input id="copyCourseTitle" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="copyCourseDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">State</label>
                                <select id="copyCourseState" class="form-select" required>
                                    <option value="">Select State</option>
                                    <option value="FL">Florida</option>
                                    <option value="CA">California</option>
                                    <option value="TX">Texas</option>
                                    <option value="MO">Missouri</option>
                                    <option value="NY">New York</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Min Pass Score (%)</label>
                                <input id="copyMinPassScore" type="number" class="form-control" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Duration (minutes)</label>
                                <input id="copyTotalDuration" type="number" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price ($)</label>
                                <input id="copyCoursePrice" type="number" step="0.01" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Certificate Template</label>
                            <input id="copyCertificateTemplate" type="text" class="form-control">
                        </div>
                        
                        <hr>
                        <h6>Copy Options</h6>
                        <div class="form-check mb-2">
                            <input id="copyBasicInfo" type="checkbox" class="form-check-input" checked disabled>
                            <label class="form-check-label" for="copyBasicInfo">
                                Basic Course Information (always copied)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input id="copyChapters" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyChapters">
                                Copy Chapters and Content
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input id="copyQuestions" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyQuestions">
                                Copy Chapter Questions
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input id="copyFinalExam" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyFinalExam">
                                Copy Final Exam Questions
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input id="copyActive" type="checkbox" class="form-check-input" checked>
                            <label class="form-check-label" for="copyActive">
                                Set new course as Active
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="executeCopy()">
                        <i class="fas fa-copy"></i> Copy Course
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Progress Modal -->
    <div class="modal fade" id="copyProgressModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Copying Course</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p id="copyProgressText">Preparing to copy course...</p>
                    <div class="progress">
                        <div id="copyProgressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentCourseId = null;
        let courses = [];
        
        // Helper function for smooth scrolling with highlight effect
        function scrollToElement(elementId, delay = 100) {
            setTimeout(() => {
                const element = document.getElementById(elementId);
                if (element) {
                    element.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Add highlight animation
                    element.classList.add('form-highlight');
                    setTimeout(() => {
                        element.classList.remove('form-highlight');
                    }, 1500);
                }
            }, delay);
        }
        
        async function loadCourses() {
            try {
                const response = await fetch('/web/courses', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    courses = await response.json();
                    displayCourses();
                }
            } catch (error) {
                console.error('Error loading courses:', error);
            }
        }
        
        function displayCourses() {
            const container = document.getElementById('courses-list');
            
            if (courses.length === 0) {
                container.innerHTML = '<div class="text-center py-4"><p class="text-muted mb-0">No courses found.</p></div>';
                return;
            }
            
            container.innerHTML = courses.map((course, index) => 
                `<div class="course-item mb-3 p-3 border rounded">
                    <div class="mb-3">
                        <div class="fw-semibold text-truncate" title="${course.title}">${course.title}</div>
                        <small class="text-muted">${course.state_code} • $${course.price} • ${course.table || 'courses'}</small>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <button onclick="copyCourse(${index})" class="btn btn-outline-info btn-sm w-100" title="Copy Course">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="editCourse(${index})" class="btn btn-outline-primary btn-sm w-100">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="manageCourse(${index})" class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-cog me-1"></i>Manage
                            </button>
                        </div>
                        <div class="col-6">
                            <button onclick="deleteCourse(${index})" class="btn btn-outline-danger btn-sm w-100" title="Delete Course">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>`
            ).join('');
        }
        
        // Add state-grouped display function for create-course page
        function displayCoursesGroupedByStateCreate() {
            const container = document.getElementById('courses-list');
            
            if (courses.length === 0) {
                container.innerHTML = '<p>No courses found.</p>';
                return;
            }
            
            // Group courses by state
            const coursesByState = {};
            courses.forEach((course, index) => {
                let state = course.state_code || 'Unknown';
                
                // Normalize state codes to handle variations
                switch(state.toUpperCase()) {
                    case 'MO':
                    case 'MISSOURI':
                        state = 'MO';
                        break;
                    case 'FL':
                    case 'FLORIDA':
                        state = 'FL';
                        break;
                    case 'CA':
                    case 'CALIFORNIA':
                        state = 'CA';
                        break;
                    case 'TX':
                    case 'TEXAS':
                        state = 'TX';
                        break;
                    case 'DE':
                    case 'DELAWARE':
                        state = 'DE';
                        break;
                    case 'NY':
                    case 'NEW YORK':
                        state = 'NY';
                        break;
                    default:
                        state = state.toUpperCase();
                }
                
                if (!coursesByState[state]) {
                    coursesByState[state] = [];
                }
                coursesByState[state].push({...course, originalIndex: index});
            });
            
            // Sort states alphabetically
            const sortedStates = Object.keys(coursesByState).sort();
            
            let html = '';
            sortedStates.forEach(state => {
                // Add state divider
                html += `
                    <div class="state-divider mb-3 p-2 rounded">
                        <h6 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            ${state === 'FL' ? 'Florida' : 
                              state === 'CA' ? 'California' : 
                              state === 'TX' ? 'Texas' : 
                              state === 'MO' ? 'Missouri' : 
                              state === 'DE' ? 'Delaware' : 
                              state === 'NY' ? 'New York' : state} Courses
                            <span class="badge ms-2">${coursesByState[state].length}</span>
                        </h6>
                    </div>
                `;
                
                // Add courses for this state
                coursesByState[state].forEach(course => {
                    html += `
                        <div class="course-item mb-3 p-3 border rounded" style="margin-left: 0.75rem; margin-right: 0.25rem;">
                            <div class="mb-3">
                                <div>
                                    <strong>${course.title}</strong>
                                    <br>
                                    <small class="text-muted">${course.state_code} • $${course.price} • ${course.table || 'courses'}</small>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button onclick="copyCourse(${course.originalIndex})" class="btn btn-outline-info btn-sm w-100" title="Copy Course">
                                            <i class="fas fa-copy me-1"></i>Copy
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button onclick="editCourse(${course.originalIndex})" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button onclick="manageCourse(${course.originalIndex})" class="btn btn-outline-success btn-sm w-100">
                                            <i class="fas fa-cog me-1"></i>Manage
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button onclick="deleteCourse(${course.originalIndex})" class="btn btn-outline-danger btn-sm w-100" title="Delete Course">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            });
            
            container.innerHTML = html;
        }
        
        function showCreateForm() {
            document.getElementById('form-title').textContent = 'Create New Course';
            document.getElementById('course-form').reset();
            document.getElementById('course-id').value = '';
            document.getElementById('course-form-card').style.display = 'block';
            document.getElementById('chapters-section').style.display = 'none';
            
            // Auto-scroll to the form with smooth animation and highlight
            scrollToElement('course-form-card');
        }
        
        function editCourse(courseIndex) {
            const course = courses[courseIndex];
            if (!course) return;
            
            console.log('Editing course:', course); // Debug
            
            document.getElementById('form-title').textContent = 'Edit Course';
            document.getElementById('course-id').value = course.id;
            document.getElementById('title').value = course.title;
            document.getElementById('description').value = course.description;
            document.getElementById('state_code').value = course.state_code;
            document.getElementById('min_pass_score').value = course.min_pass_score || course.passing_score;
            document.getElementById('total_duration').value = course.total_duration || course.duration;
            document.getElementById('price').value = course.price;
            document.getElementById('certificate_template').value = course.certificate_template || '';
            document.getElementById('is_active').checked = course.is_active;
            
            document.getElementById('course-form-card').style.display = 'block';
            
            // Auto-scroll to the form with smooth animation and highlight
            scrollToElement('course-form-card');
        }
        
        async function deleteCourse(courseIndex) {
            const course = courses[courseIndex];
            if (!course) return;
            
            if (!confirm('Are you sure you want to delete "' + course.title + '"? This action cannot be undone.')) {
                return;
            }
            
            try {
                let url;
                if (course.table === 'florida_courses') {
                    url = '/web/courses/' + course.real_id;
                } else {
                    url = '/web/courses/' + course.id;
                }
                
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    alert('Course deleted successfully!');
                    loadCourses();
                } else {
                    alert('Error deleting course');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error deleting course: ' + error.message);
            }
        }
        
        function manageCourse(courseIndex) {
            const course = courses[courseIndex];
            if (!course) return;
            
            console.log('Managing course:', course); // Debug
            
            currentCourseId = course.id;
            
            // Use the correct course data
            document.getElementById('form-title').textContent = 'Edit Course';
            document.getElementById('course-id').value = course.id;
            document.getElementById('title').value = course.title;
            document.getElementById('description').value = course.description;
            document.getElementById('state_code').value = course.state_code;
            document.getElementById('min_pass_score').value = course.min_pass_score || course.passing_score;
            document.getElementById('total_duration').value = course.total_duration || course.duration;
            document.getElementById('price').value = course.price;
            document.getElementById('certificate_template').value = course.certificate_template || '';
            document.getElementById('is_active').checked = course.is_active;
            
            document.getElementById('course-form-card').style.display = 'block';
            document.getElementById('chapters-section').style.display = 'block';
            loadChapters(course.id, course);
            
            // Auto-scroll to the form with smooth animation and highlight
            scrollToElement('course-form-card');
        }
        
        async function loadChapters(courseId, course) {
            try {
                if (!course) {
                    course = courses.find(c => c.id === courseId);
                }
                if (!course) return;
                
                // Use appropriate route based on course table
                let url;
                if (course.table === 'florida_courses') {
                    url = '/api/florida-courses/' + course.real_id + '/chapters';
                } else {
                    url = '/web/courses/' + courseId + '/chapters';
                }
                
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const chapters = await response.json();
                    displayChapters(chapters);
                }
            } catch (error) {
                console.error('Error loading chapters:', error);
            }
        }
        
        function displayChapters(chapters) {
            const container = document.getElementById('chapters-list');
            
            if (chapters.length === 0) {
                container.innerHTML = '<p>No chapters added yet.</p>';
                return;
            }
            
            container.innerHTML = chapters.map((chapter, index) => 
                '<div class="chapter-item mb-2 p-2 border rounded">' +
                    '<div class="d-flex justify-content-between">' +
                        '<div>' +
                            '<strong>' + (index + 1) + '. ' + chapter.title + '</strong>' +
                            '<br>' +
                            '<small class="text-muted">' + chapter.duration + ' minutes</small>' +
                            (chapter.video_url ? '<br><small class="text-info">📹 Has video</small>' : '') +
                        '</div>' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button onclick="editChapter(\'' + chapter.id + '\')" class="btn btn-outline-primary">Edit</button>' +
                            '<button onclick="deleteChapter(\'' + chapter.id + '\')" class="btn btn-outline-danger">Delete</button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            ).join('');
        }
        
        function showChapterForm() {
            document.getElementById('chapter-form-title').textContent = 'Add Chapter';
            document.getElementById('chapter-course-id').value = currentCourseId;
            document.getElementById('chapter-id').value = '';
            document.getElementById('chapter-form').reset();
            tinymce.get('chapter-content').setContent('');
            document.getElementById('current-media').style.display = 'none';
            document.getElementById('chapter-form-card').style.display = 'block';
            
            // Auto-scroll to the chapter form with smooth animation and highlight
            scrollToElement('chapter-form-card');
        }
        
        function editChapter(chapterId) {
            console.log('Edit chapter clicked:', chapterId); // Debug
            
            // Find the current course to determine the correct URL
            const course = courses.find(c => c.id === currentCourseId);
            if (!course) {
                console.error('Course not found:', currentCourseId);
                return;
            }
            
            console.log('Current course:', course); // Debug
            
            // Use appropriate route based on course table
            let url;
            if (course.table === 'florida_courses') {
                url = '/api/florida-courses/' + course.real_id + '/chapters';
            } else {
                url = '/web/courses/' + currentCourseId + '/chapters';
            }
            
            console.log('Fetching chapters from:', url); // Debug
            
            // Find chapter data from loaded chapters
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Chapters response:', response.status); // Debug
                return response.json();
            })
            .then(chapters => {
                console.log('Loaded chapters:', chapters); // Debug
                const chapter = chapters.find(c => c.id == chapterId); // Use == for loose comparison
                console.log('Found chapter:', chapter); // Debug
                
                if (chapter) {
                    document.getElementById('chapter-form-title').textContent = 'Edit Chapter';
                    document.getElementById('chapter-course-id').value = currentCourseId;
                    document.getElementById('chapter-id').value = chapter.id;
                    document.getElementById('chapter-title').value = chapter.title;
                    tinymce.get('chapter-content').setContent(chapter.content);
                    document.getElementById('chapter-duration').value = chapter.duration;
                    document.getElementById('video-url').value = chapter.video_url || '';
                    
                    // Show current media
                    const currentMediaDiv = document.getElementById('current-media');
                    const currentMediaContent = document.getElementById('current-media-content');
                    
                    // Extract media from content or video_url
                    let mediaHtml = '';
                    
                    if (chapter.video_url && chapter.video_url.includes('/storage/')) {
                        const fileName = chapter.video_url.split('/').pop();
                        mediaHtml += '<div class="mb-1"><span class="badge bg-info">Video:</span> <a href="' + chapter.video_url + '" target="_blank">' + fileName + '</a></div>';
                    }
                    
                    // Check for images and documents in content
                    const imgMatches = chapter.content.match(/<img[^>]+src="([^"]+)"/g);
                    if (imgMatches) {
                        imgMatches.forEach(match => {
                            const src = match.match(/src="([^"]+)"/)[1];
                            if (src.includes('/storage/')) {
                                const fileName = src.split('/').pop();
                                mediaHtml += '<div class="mb-1"><span class="badge bg-success">Image:</span> <a href="' + src + '" target="_blank">' + fileName + '</a></div>';
                            }
                        });
                    }
                    
                    const linkMatches = chapter.content.match(/<a[^>]+href="([^"]+)"[^>]*>Download ([^<]+)<\/a>/g);
                    if (linkMatches) {
                        linkMatches.forEach(match => {
                            const href = match.match(/href="([^"]+)"/)[1];
                            const fileName = match.match(/Download ([^<]+)/)[1];
                            if (href.includes('/storage/')) {
                                mediaHtml += '<div class="mb-1"><span class="badge bg-warning">Document:</span> <a href="' + href + '" target="_blank">' + fileName + '</a></div>';
                            }
                        });
                    }
                    
                    if (mediaHtml) {
                        currentMediaContent.innerHTML = mediaHtml;
                        currentMediaDiv.style.display = 'block';
                    } else {
                        currentMediaDiv.style.display = 'none';
                    }
                    
                    document.getElementById('chapter-form-card').style.display = 'block';
                    
                    // Auto-scroll to the chapter form with smooth animation and highlight
                    scrollToElement('chapter-form-card');
                }
            });
        }
        
        function hideForm() {
            document.getElementById('course-form-card').style.display = 'none';
            document.getElementById('chapters-section').style.display = 'none';
        }
        
        function hideChapterForm() {
            document.getElementById('chapter-form-card').style.display = 'none';
            document.getElementById('chapter-form').reset();
        }
        
        // Form submissions
        document.getElementById('course-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.is_active = document.getElementById('is_active').checked;
            
            console.log('Form data:', data); // Debug
            
            const courseId = document.getElementById('course-id').value;
            
            // Find the course to determine which table to update
            let course = null;
            if (courseId) {
                course = courses.find(c => c.id == courseId);
                console.log('Found course:', course); // Debug
            }
            
            let url, method;
            if (courseId && course) {
                // Updating existing course
                if (course.table === 'florida_courses') {
                    url = '/web/courses/' + course.real_id;
                } else {
                    url = '/web/courses/' + courseId;
                }
                method = 'PUT';
            } else {
                // Creating new course - default to florida_courses for now
                url = '/web/courses';
                method = 'POST';
            }
            
            console.log('Request:', { url, method, data }); // Debug
            
            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                console.log('Response:', result); // Debug
                
                if (response.ok) {
                    alert(courseId ? 'Course updated successfully!' : 'Course created successfully!');
                    loadCourses();
                    hideForm();
                } else {
                    alert('Error: ' + (result.message || 'Failed to save course'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving course: ' + error.message);
            }
        });
        
        document.getElementById('chapter-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Save TinyMCE content to textarea
            tinymce.triggerSave();
            
            // Validate content is not empty
            const content = tinymce.get('chapter-content').getContent();
            if (!content || content.trim() === '') {
                alert('Please enter chapter content');
                return;
            }
            
            console.log('📝 Chapter form submitted');
            const formData = new FormData(e.target);
            const chapterId = document.getElementById('chapter-id').value;
            
            // Log form data for debugging
            console.log('📋 Form data contents:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(key + ': File - ' + value.name + ' (' + value.size + ' bytes, ' + value.type + ')');
                } else {
                    console.log(key + ': ' + value);
                }
            }
            
            if (chapterId) {
                // For updates, add _method field for Laravel method spoofing
                formData.append('_method', 'PUT');
                console.log('🔄 Update mode - added _method: PUT');
            }
            
            // Determine the correct URL based on course table and operation
            let url;
            const course = courses.find(c => c.id === currentCourseId);
            
            if (chapterId) {
                // Updating existing chapter - use generic chapter route
                url = '/web/chapters/' + chapterId;
            } else {
                // Creating new chapter - use course-specific route
                if (course && course.table === 'florida_courses') {
                    url = '/api/florida-courses/' + course.real_id + '/chapters';
                } else {
                    url = '/web/courses/' + currentCourseId + '/chapters';
                }
            }
            
            console.log('📡 Submitting to: ' + url);
            
            try {
                const response = await fetch(url, {
                    method: 'POST', // Always use POST, Laravel will handle method spoofing
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: formData
                });
                
                console.log('📊 Response status:', response.status);
                
                if (response.ok) {
                    const result = await response.json();
                    console.log('✅ Chapter saved successfully:', result);
                    alert(chapterId ? 'Chapter updated successfully!' : 'Chapter added successfully!');
                    loadChapters(currentCourseId);
                    hideChapterForm();
                } else {
                    const errorText = await response.text();
                    console.error('❌ Response not OK:', response.status, errorText);
                    alert('Failed to save chapter: ' + response.status);
                }
            } catch (error) {
                console.error('❌ Error saving chapter:', error);
                alert('Failed to save chapter: ' + error.message);
            }
        });
        
        async function deleteChapter(chapterId) {
            if (!confirm('Are you sure you want to delete this chapter?')) return;
            
            // Handle special case for final-exam chapter
            if (chapterId === 'final-exam') {
                alert('Cannot delete the Final Exam chapter.');
                return;
            }
            
            try {
                const response = await fetch('/web/chapters/' + chapterId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    alert('Chapter deleted successfully!');
                    loadChapters(currentCourseId);
                } else {
                    alert('Failed to delete chapter');
                }
            } catch (error) {
                console.error('Error deleting chapter:', error);
                alert('Failed to delete chapter');
            }
        }
        
        // Initialize TinyMCE
        tinymce.init({
            selector: '#chapter-content',
            height: 400,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount', 'paste'
            ],
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | image media table link',
            content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px }',
            paste_data_images: true,
            paste_as_text: false,
            paste_word_valid_elements: "b,strong,i,em,h1,h2,h3,h4,h5,h6,p,ol,ul,li,a[href],span,table,tr,td,th,img[src|alt|width|height]",
            paste_retain_style_properties: "color font-size font-weight text-align",
            paste_webkit_styles: "color font-size font-weight text-align",
            paste_merge_formats: true,
            smart_paste: true,
            paste_postprocess: function(plugin, args) {
                // Convert file:// images to placeholder instead of removing them
                var images = args.node.querySelectorAll('img[src^="file://"]');
                images.forEach(function(img) {
                    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIFBsYWNlaG9sZGVyPC90ZXh0Pjwvc3ZnPg==';
                    img.alt = 'Image from Word document (please re-upload)';
                    img.style.border = '2px dashed #ccc';
                });
            },
            images_upload_handler: function (blobInfo, success, failure) {
                console.log('Image upload handler called', blobInfo);
                
                // Check if blobInfo and blob exist
                if (!blobInfo || !blobInfo.blob || typeof blobInfo.blob !== 'function') {
                    console.error('Invalid blobInfo or blob function');
                    failure('Invalid image data');
                    return;
                }
                
                try {
                    // Get the blob
                    const blob = blobInfo.blob();
                    
                    if (!blob) {
                        console.error('No blob returned');
                        failure('No image data');
                        return;
                    }
                    
                    // Convert to base64
                    const reader = new FileReader();
                    reader.onload = function() {
                        console.log('Image converted to base64 successfully');
                        success(reader.result);
                    };
                    reader.onerror = function(error) {
                        console.error('FileReader error:', error);
                        // Provide fallback placeholder image
                        success('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIFBsYWNlaG9sZGVyPC90ZXh0Pjwvc3ZnPg==');
                    };
                    reader.readAsDataURL(blob);
                } catch (error) {
                    console.error('Error in image upload handler:', error);
                    failure('Error processing image: ' + error.message);
                }
            },
            automatic_uploads: true,
            images_reuse_filename: true,
            images_upload_url: false, // Disable server upload to prevent upload dialogs
            file_picker_types: 'image', // Only allow image picker, not file upload dialogs
            file_picker_callback: function (callback, value, meta) {
                // Create file input for image selection
                if (meta.filetype === 'image') {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    
                    input.onchange = function () {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function () {
                                // Call the callback with the base64 data URL
                                callback(reader.result, {
                                    alt: file.name,
                                    title: file.name
                                });
                            };
                            reader.onerror = function () {
                                console.error('Error reading file');
                                // Provide fallback
                                callback('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkVycm9yIExvYWRpbmcgSW1hZ2U8L3RleHQ+PC9zdmc+', {
                                    alt: 'Error loading image'
                                });
                            };
                            reader.readAsDataURL(file);
                        }
                    };
                    
                    input.click();
                }
            },
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
                
                // Handle paste events silently without showing dialogs
                editor.on('paste', function(e) {
                    console.log('Paste event detected - processing images silently');
                });
                
                // Add error handling for image operations
                editor.on('ImageUploadError', function(e) {
                    console.error('Image upload error:', e);
                });
                
                // Prevent image upload dialogs from appearing
                editor.on('BeforeOpenDialog', function(e) {
                    // Allow the dialog to open but suppress upload-related dialogs
                    if (e.dialog && e.dialog.type === 'upload') {
                        e.preventDefault();
                        return false;
                    }
                });
                
                // Handle initialization errors
                editor.on('init', function() {
                    console.log('TinyMCE initialized successfully');
                });
                
                editor.on('LoadContent', function() {
                    console.log('Content loaded in TinyMCE');
                });
            }
        });
        
        // Copy course functionality
        let copyingCourseIndex = null;
        
        function copyCourse(courseIndex) {
            copyingCourseIndex = courseIndex;
            const course = courses[courseIndex];
            if (course) {
                // Fill the form with source course data
                document.getElementById('sourceCourseTitle').textContent = course.title;
                document.getElementById('copyCourseTitle').value = course.title + ' (Copy)';
                document.getElementById('copyCourseDescription').value = course.description || '';
                document.getElementById('copyCourseState').value = course.state_code;
                document.getElementById('copyMinPassScore').value = course.min_pass_score || course.passing_score || 80;
                document.getElementById('copyTotalDuration').value = course.total_duration || course.duration;
                document.getElementById('copyCoursePrice').value = course.price;
                document.getElementById('copyCertificateTemplate').value = course.certificate_template || '';
                
                new bootstrap.Modal(document.getElementById('copyCourseModal')).show();
            }
        }
        
        async function executeCopy() {
            const course = courses[copyingCourseIndex];
            const copyData = {
                source_course_id: course.id,
                source_table: course.table || 'courses',
                title: document.getElementById('copyCourseTitle').value,
                description: document.getElementById('copyCourseDescription').value,
                state_code: document.getElementById('copyCourseState').value,
                min_pass_score: document.getElementById('copyMinPassScore').value,
                total_duration: document.getElementById('copyTotalDuration').value,
                price: document.getElementById('copyCoursePrice').value,
                certificate_template: document.getElementById('copyCertificateTemplate').value,
                is_active: document.getElementById('copyActive').checked,
                copy_options: {
                    chapters: document.getElementById('copyChapters').checked,
                    questions: document.getElementById('copyQuestions').checked,
                    final_exam: document.getElementById('copyFinalExam').checked
                }
            };
            
            // Hide copy modal and show progress modal
            bootstrap.Modal.getInstance(document.getElementById('copyCourseModal')).hide();
            const progressModal = new bootstrap.Modal(document.getElementById('copyProgressModal'));
            progressModal.show();
            
            try {
                updateCopyProgress(20, 'Creating new course...');
                
                const response = await fetch('/api/courses/copy', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(copyData)
                });
                
                if (response.ok) {
                    updateCopyProgress(100, 'Course copied successfully!');
                    setTimeout(() => {
                        progressModal.hide();
                        loadCourses();
                        alert('Course copied successfully!');
                    }, 1500);
                } else {
                    const error = await response.json();
                    throw new Error(error.message || 'Failed to copy course');
                }
            } catch (error) {
                console.error('Copy error:', error);
                progressModal.hide();
                alert('Error copying course: ' + error.message);
            }
        }
        
        function updateCopyProgress(percent, text) {
            document.getElementById('copyProgressBar').style.width = percent + '%';
            document.getElementById('copyProgressText').textContent = text;
        }

        
        // State grouping toggle functionality for create-course page
        let isGroupedByStateCreate = false;
        
        function toggleStateGroupingCreate() {
            isGroupedByStateCreate = !isGroupedByStateCreate;
            const toggleBtn = document.getElementById('toggleGroupingCreate');
            
            if (isGroupedByStateCreate) {
                displayCoursesGroupedByStateCreate();
                toggleBtn.innerHTML = '<i class="fas fa-list"></i> Show All';
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-secondary');
                toggleBtn.title = 'Show All';
            } else {
                displayCourses();
                toggleBtn.innerHTML = '<i class="fas fa-layer-group"></i> Group by State';
                toggleBtn.classList.remove('btn-secondary');
                toggleBtn.classList.add('btn-outline-secondary');
                toggleBtn.title = 'Group by State';
            }
        }

        // Load courses immediately
        loadCourses();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    
    <x-footer />
</body>
</html>
