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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <!-- Fallback content -->
        <div id="fallback-content">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h5>Existing Courses</h5>
                            <button onclick="showCreateForm()" class="btn btn-sm btn-primary">New Course</button>
                        </div>
                        <div class="card-body" id="courses-list">
                            <p>Loading courses...</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card" id="course-form-card" style="display: none;">
                        <div class="card-header">
                            <h5 id="form-title">Create New Course</h5>
                        </div>
                        <div class="card-body">
                            <form id="course-form">
                                <input type="hidden" id="course-id" name="course_id">
                                
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="state_code" class="form-label">State</label>
                                            <select class="form-select" id="state_code" name="state_code" required>
                                                <option value="">Select State</option>
                                                <option value="FL">Florida</option>
                                                <option value="CA">California</option>
                                                <option value="TX">Texas</option>
                                                <option value="NY">New York</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="min_pass_score" class="form-label">Min Pass Score (%)</label>
                                            <input type="number" class="form-control" id="min_pass_score" name="min_pass_score" min="0" max="100" value="80" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="total_duration" class="form-label">Duration (minutes)</label>
                                            <input type="number" class="form-control" id="total_duration" name="total_duration" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price ($)</label>
                                            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="certificate_template" class="form-label">Certificate Template</label>
                                    <input type="text" class="form-control" id="certificate_template" name="certificate_template">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Save Course</button>
                                    <button type="button" onclick="hideForm()" class="btn btn-secondary">Cancel</button>
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
                                    <textarea class="form-control" id="chapter-content" name="content" rows="5" required></textarea>
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
    
    <script>
        let currentCourseId = null;
        let courses = [];
        
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
                container.innerHTML = '<p>No courses found.</p>';
                return;
            }
            
            container.innerHTML = courses.map(course => 
                '<div class="course-item mb-2 p-2 border rounded">' +
                    '<div class="d-flex justify-content-between align-items-start">' +
                        '<div>' +
                            '<strong>' + course.title + '</strong>' +
                            '<br>' +
                            '<small class="text-muted">' + course.state_code + ' • $' + course.price + '</small>' +
                        '</div>' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button onclick="editCourse(' + course.id + ')" class="btn btn-outline-primary">Edit</button>' +
                            '<button onclick="manageCourse(' + course.id + ')" class="btn btn-outline-success">Manage</button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            ).join('');
        }
        
        function showCreateForm() {
            document.getElementById('form-title').textContent = 'Create New Course';
            document.getElementById('course-form').reset();
            document.getElementById('course-id').value = '';
            document.getElementById('course-form-card').style.display = 'block';
            document.getElementById('chapters-section').style.display = 'none';
        }
        
        function editCourse(courseId) {
            const course = courses.find(c => c.id === courseId);
            if (!course) return;
            
            document.getElementById('form-title').textContent = 'Edit Course';
            document.getElementById('course-id').value = course.id;
            document.getElementById('title').value = course.title;
            document.getElementById('description').value = course.description;
            document.getElementById('state_code').value = course.state_code;
            document.getElementById('min_pass_score').value = course.min_pass_score;
            document.getElementById('total_duration').value = course.total_duration;
            document.getElementById('price').value = course.price;
            document.getElementById('certificate_template').value = course.certificate_template || '';
            document.getElementById('is_active').checked = course.is_active;
            
            document.getElementById('course-form-card').style.display = 'block';
        }
        
        function manageCourse(courseId) {
            currentCourseId = courseId;
            editCourse(courseId);
            document.getElementById('chapters-section').style.display = 'block';
            loadChapters(courseId);
        }
        
        async function loadChapters(courseId) {
            try {
                const response = await fetch('/web/courses/' + courseId + '/chapters', {
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
                            '<button onclick="editChapter(' + chapter.id + ')" class="btn btn-outline-primary">Edit</button>' +
                            '<button onclick="deleteChapter(' + chapter.id + ')" class="btn btn-outline-danger">Delete</button>' +
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
            document.getElementById('current-media').style.display = 'none';
            document.getElementById('chapter-form-card').style.display = 'block';
        }
        
        function editChapter(chapterId) {
            // Find chapter data from loaded chapters
            fetch('/web/courses/' + currentCourseId + '/chapters', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(chapters => {
                const chapter = chapters.find(c => c.id === chapterId);
                if (chapter) {
                    document.getElementById('chapter-form-title').textContent = 'Edit Chapter';
                    document.getElementById('chapter-course-id').value = currentCourseId;
                    document.getElementById('chapter-id').value = chapter.id;
                    document.getElementById('chapter-title').value = chapter.title;
                    document.getElementById('chapter-content').value = chapter.content;
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
            
            const courseId = document.getElementById('course-id').value;
            const url = courseId ? '/web/courses/' + courseId : '/web/courses';
            const method = courseId ? 'PUT' : 'POST';
            
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
                
                if (response.ok) {
                    alert(courseId ? 'Course updated successfully!' : 'Course created successfully!');
                    loadCourses();
                    hideForm();
                }
            } catch (error) {
                console.error('Error saving course:', error);
                alert('Failed to save course');
            }
        });
        
        document.getElementById('chapter-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
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
            
            const url = chapterId ? '/web/chapters/' + chapterId : '/web/courses/' + currentCourseId + '/chapters';
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
                }
            } catch (error) {
                console.error('Error deleting chapter:', error);
                alert('Failed to delete chapter');
            }
        }
        
        // Load courses immediately
        loadCourses();
    </script>
    
    <x-footer />
</body>
</html>
