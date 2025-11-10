<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Player</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .card {
            background-color: var(--bg-secondary);
            border-color: var(--border);
            color: var(--text-primary);
        }
        .card-header {
            background-color: var(--accent);
            color: white;
            border-bottom-color: var(--border);
        }
        .chapter-item {
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 5px;
            border: 1px solid var(--border);
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        .chapter-item:hover {
            background-color: var(--hover);
        }
        .chapter-item.active {
            background-color: var(--accent);
            color: white;
        }
        .chapter-item.completed {
            opacity: 0.7;
        }
        .chapter-item.completed::after {
            content: ' ✓';
            color: green;
            font-weight: bold;
        }
        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
        }
        .btn-primary:hover {
            background-color: var(--hover);
            border-color: var(--hover);
        }
        .progress {
            background-color: var(--bg-primary);
        }
        .progress-bar {
            background-color: var(--accent);
        }
        .form-check {
            padding-left: 1.5em;
            margin-bottom: 0.5rem;
        }
        .form-check-input {
            margin-top: 0.25em;
        }
        .form-check-label {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')
        <div class="container-fluid mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        
        <!-- Course Timer Display -->
        <div class="alert alert-info mb-3" id="timer-display" style="display: none;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-clock me-2"></i>
                    <strong>Chapter Timer:</strong> <span id="timer-text">00:00</span>
                    <span class="ms-3 text-muted">Required: <span id="required-time">0</span> minutes</span>
                </div>
                <div>
                    <span class="badge bg-warning" id="timer-status">In Progress</span>
                </div>
            </div>
            <div class="progress mt-2" style="height: 5px;">
                <div class="progress-bar" id="timer-progress" role="progressbar" style="width: 0%"></div>
            </div>
        </div>
        
        <div id="app">
            <course-player></course-player>
        </div>
        
        <!-- Fallback content -->
        <div id="fallback-content">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5>Course Chapters</h5>
                        </div>
                        <div class="card-body" id="chapters-list">
                            <p>Loading chapters...</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h5 id="chapter-title">Course Content</h5>
                        </div>
                        <div class="card-body" id="chapter-content">
                            <p>Select a chapter to begin learning.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const enrollmentId = urlParams.get('enrollmentId');
        
        let currentEnrollment = null;
        let chapters = [];
        
        async function loadCourseData() {
            if (!enrollmentId) {
                document.getElementById('chapters-list').innerHTML = '<p class="text-danger">No enrollment ID provided.</p>';
                return;
            }
            
            try {
                const response = await fetch(`/web/enrollments/${enrollmentId}`, {
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
                    throw new Error('Failed to load course data');
                }
                
                currentEnrollment = await response.json();
                
                // Load chapters
                const chaptersResponse = await fetch(`/web/courses/${currentEnrollment.course.id}/chapters?enrollmentId=${enrollmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (chaptersResponse.ok) {
                    chapters = await chaptersResponse.json();
                    displayChapters();
                }
                
            } catch (error) {
                console.error('Error loading course data:', error);
                document.getElementById('chapters-list').innerHTML = '<p class="text-danger">Error loading course data.</p>';
            }
        }
        
        function displayChapters() {
            const container = document.getElementById('chapters-list');
            
            if (chapters.length === 0) {
                container.innerHTML = '<p>No chapters available.</p>';
                return;
            }
            
            container.innerHTML = chapters.map((chapter, index) => {
                const isCompleted = chapter.is_completed || false;
                const completedClass = isCompleted ? 'completed' : '';
                return `
                    <div class="chapter-item ${completedClass}" onclick="selectChapter(${chapter.id})" data-chapter-id="${chapter.id}">
                        <strong>${index + 1}. ${chapter.title}</strong>
                        <br>
                        <small class="text-muted">${chapter.duration} minutes</small>
                    </div>
                `;
            }).join('');
        }
        
        function selectChapter(chapterId) {
            const chapter = chapters.find(c => c.id === chapterId);
            if (!chapter) return;
            
            // Start timer for this chapter
            startChapterTimer(chapterId);
            
            console.log('📖 Loading chapter:', chapter);
            console.log('📖 Video URL:', chapter.video_url);
            console.log('📖 Original content:', chapter.content);
            
            document.getElementById('chapter-title').textContent = chapter.title;
            
            // Process chapter content to ensure media displays properly
            let processedContent = chapter.content;
            
            console.log('📖 Content before processing:', processedContent);
            
            // Convert all storage URLs to files URLs - handle various formats
            processedContent = processedContent.replace(/src='\/storage\/course-media\//g, "src='/files/");
            processedContent = processedContent.replace(/href='\/storage\/course-media\//g, "href='/files/");
            processedContent = processedContent.replace(/src="\/storage\/course-media\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="\/storage\/course-media\//g, 'href="/files/');
            processedContent = processedContent.replace(/src="\/storage\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="\/storage\//g, 'href="/files/');
            
            // Also handle URLs without leading slash
            processedContent = processedContent.replace(/src='storage\/course-media\//g, "src='/files/");
            processedContent = processedContent.replace(/href='storage\/course-media\//g, "href='/files/");
            processedContent = processedContent.replace(/src="storage\/course-media\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="storage\/course-media\//g, 'href="/files/');
            processedContent = processedContent.replace(/src="storage\//g, 'src="/files/');
            processedContent = processedContent.replace(/href="storage\//g, 'href="/files/');
            
            console.log('📖 Content after processing:', processedContent);
            
            // Check if there's any media in the content
            const hasImages = processedContent.includes('<img');
            const hasVideos = processedContent.includes('<video');
            const hasAudio = processedContent.includes('<audio');
            const hasPDFs = processedContent.includes('.pdf');
            console.log('📖 Media found - Images:', hasImages, 'Videos:', hasVideos, 'Audio:', hasAudio, 'PDFs:', hasPDFs);
            
            // Extract PDF links and convert them to embedded viewers
            let pdfContent = '';
            const pdfRegex = /<a[^>]*href="([^"]*\.pdf)"[^>]*>([^<]*)<\/a>/gi;
            let match;
            while ((match = pdfRegex.exec(processedContent)) !== null) {
                const pdfUrl = match[1];
                const linkText = match[2];
                pdfContent += `
                    <div class="mb-3">
                        <h6><i class="fas fa-file-pdf text-danger"></i> ${linkText}</h6>
                        <div class="pdf-container" style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
                            <iframe src="${pdfUrl}" width="100%" height="600px" style="border: none;">
                                <p>Your browser does not support PDFs. <a href="${pdfUrl}" target="_blank">Download the PDF</a>.</p>
                            </iframe>
                        </div>
                        <div class="mt-2">
                            <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Open in New Tab
                            </a>
                            <a href="${pdfUrl}" download class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('chapter-content').innerHTML = `
                <div class="mb-3">
                    <div class="progress">
                        <div class="progress-bar" style="width: ${currentEnrollment.progress_percentage || 0}%">
                            ${currentEnrollment.progress_percentage || 0}%
                        </div>
                    </div>
                </div>
                ${chapter.video_url ? `
                    <div class="mb-3">
                        <video src="${chapter.video_url.replace(/\/storage\/course-media\//, '/files/').replace(/\/storage\//, '/files/')}" controls width="100%" style="max-height: 400px;" preload="metadata">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                ` : ''}
                ${pdfContent}
                <div class="chapter-text">${processedContent}</div>
                <div id="questions-section" class="mt-4"></div>
                <div class="mt-4">
                    <button onclick="completeChapter(${chapter.id})" class="btn btn-success">Complete Chapter</button>
                </div>
            `;
            
            // Load questions for this chapter
            loadChapterQuestions(chapter.id);
            
            // Highlight selected chapter
            document.querySelectorAll('.chapter-item').forEach(item => {
                item.classList.remove('active');
            });
            
            document.querySelector(`.chapter-item[data-chapter-id="${chapterId}"]`)?.classList.add('active');
            
            // Add error handling for media elements
            setTimeout(() => {
                const videos = document.querySelectorAll('#chapter-content video');
                videos.forEach(video => {
                    video.onerror = function() {
                        console.error('Video failed to load:', video.src);
                        video.outerHTML = `<div class="alert alert-warning">Video could not be loaded: ${video.src}</div>`;
                    };
                });
                
                const images = document.querySelectorAll('#chapter-content img');
                images.forEach(img => {
                    img.onerror = function() {
                        console.error('Image failed to load:', img.src);
                        img.outerHTML = `<div class="alert alert-warning">Image could not be loaded: ${img.src}</div>`;
                    };
                });
            }, 100);
        }
        
        async function completeChapter(chapterId) {
            try {
                const response = await fetch(`/web/enrollments/${enrollmentId}/complete-chapter/${chapterId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    alert('Chapter completed successfully!');
                    loadCourseData(); // Reload to update progress
                }
            } catch (error) {
                console.error('Error completing chapter:', error);
                alert('Failed to complete chapter');
            }
        }
        
        let currentQuestions = [];
        let questionAttempts = {};
        
        async function loadChapterQuestions(chapterId) {
            try {
                console.log('🔍 Loading questions for chapter:', chapterId);
                const response = await fetch(`/api/chapters/${chapterId}/questions`);
                console.log('🔍 Questions response status:', response.status);
                
                currentQuestions = await response.json();
                console.log('🔍 Questions loaded:', currentQuestions.length, currentQuestions);
                
                if (currentQuestions.length > 0) {
                    currentQuestions.forEach(q => questionAttempts[q.id] = 0);
                    displayQuestions();
                } else {
                    console.log('⚠️ No questions found for this chapter');
                    document.getElementById('questions-section').innerHTML = '<p class="text-muted">No quiz available for this chapter.</p>';
                }
            } catch (error) {
                console.error('❌ Error loading questions:', error);
                document.getElementById('questions-section').innerHTML = '<p class="text-danger">Error loading quiz.</p>';
            }
        }
        
        function displayQuestions() {
            const container = document.getElementById('questions-section');
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Chapter Quiz</h5>
                    </div>
                    <div class="card-body">
                        ${currentQuestions.map((q, index) => {
                            // Handle options - it might be already an array or a JSON string
                            const options = Array.isArray(q.options) ? q.options : JSON.parse(q.options);
                            return `
                                <div class="mb-4">
                                    <h6>${index + 1}. ${q.question_text}</h6>
                                    ${options.map((opt, optIndex) => `
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_${q.id}" id="q${q.id}_opt${optIndex}" value="${opt}">
                                            <label class="form-check-label" for="q${q.id}_opt${optIndex}">
                                                ${opt}
                                            </label>
                                        </div>
                                    `).join('')}
                                    <button onclick="checkAnswer(${q.id})" class="btn btn-sm btn-primary mt-2" id="checkBtn_${q.id}">Check Answer</button>
                                    <button onclick="showAnswer(${q.id})" class="btn btn-sm btn-warning mt-2" id="showBtn_${q.id}" style="display:none;">Show Answer</button>
                                    <div id="result_${q.id}" class="mt-2"></div>
                                </div>
                            `;
                        }).join('')}
                        <button onclick="restartQuiz()" class="btn btn-secondary mt-3">Restart Quiz</button>
                    </div>
                </div>
            `;
        }
        
        function checkAnswer(questionId) {
            const question = currentQuestions.find(q => q.id === questionId);
            const selected = document.querySelector(`input[name="question_${questionId}"]:checked`);
            const resultDiv = document.getElementById(`result_${questionId}`);
            
            if (!selected) {
                resultDiv.innerHTML = '<span class="text-warning">Please select an answer</span>';
                return;
            }
            
            const userAnswer = selected.value;
            const isCorrect = userAnswer === question.correct_answer;
            
            questionAttempts[questionId]++;
            
            if (isCorrect) {
                resultDiv.innerHTML = `<span class="text-success">✓ Correct!</span>`;
                document.getElementById(`checkBtn_${questionId}`).style.display = 'none';
                document.querySelectorAll(`input[name="question_${questionId}"]`).forEach(input => {
                    input.disabled = true;
                });
            } else {
                resultDiv.innerHTML = `<span class="text-danger">✗ Incorrect. Try again!</span>`;
                
                if (questionAttempts[questionId] >= 2) {
                    document.getElementById(`showBtn_${questionId}`).style.display = 'inline-block';
                }
            }
        }
        
        function showAnswer(questionId) {
            const question = currentQuestions.find(q => q.id === questionId);
            const resultDiv = document.getElementById(`result_${questionId}`);
            
            resultDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>Correct Answer:</strong> ${question.correct_answer}<br>
                    ${question.explanation ? `<strong>Explanation:</strong> ${question.explanation}` : ''}
                </div>
            `;
            
            document.getElementById(`checkBtn_${questionId}`).style.display = 'none';
            document.getElementById(`showBtn_${questionId}`).style.display = 'none';
            document.querySelectorAll(`input[name="question_${questionId}"]`).forEach(input => {
                input.disabled = true;
            });
        }
        
        function restartQuiz() {
            currentQuestions.forEach(q => {
                questionAttempts[q.id] = 0;
                document.querySelectorAll(`input[name="question_${q.id}"]`).forEach(input => {
                    input.checked = false;
                    input.disabled = false;
                });
                document.getElementById(`result_${q.id}`).innerHTML = '';
                document.getElementById(`checkBtn_${q.id}`).style.display = 'inline-block';
                document.getElementById(`showBtn_${q.id}`).style.display = 'none';
            });
        }
        
        // Show fallback and load course data if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app course-player');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadCourseData();
            }
        }, 1000);
        
        // Timer functionality
        let timerInterval = null;
        let timerStartTime = 0;
        let timerElapsed = 0;
        let timerRequired = 0;
        let currentTimerSession = null;
        
        async function startChapterTimer(chapterId) {
            console.log('Starting timer for chapter:', chapterId);
            
            // Find the chapter to get its type
            const chapter = chapters.find(c => c.id === chapterId);
            const chapterType = chapter?.chapter_type || 'chapters';
            
            try {
                const response = await fetch('/api/timer/start', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ 
                        chapter_id: chapterId,
                        chapter_type: chapterType
                    })
                });
                
                const data = await response.json();
                console.log('Timer response:', data);
                
                if (data.success && data.timer_required !== false) {
                    currentTimerSession = data.session;
                    timerRequired = data.required_time || 0;
                    timerElapsed = data.session?.time_spent_seconds || 0;
                    
                    document.getElementById('timer-display').style.display = 'block';
                    document.getElementById('required-time').textContent = Math.floor(timerRequired / 60);
                    
                    startTimerDisplay();
                } else {
                    console.log('No timer required for this chapter');
                    document.getElementById('timer-display').style.display = 'none';
                }
            } catch (error) {
                console.error('Timer start error:', error);
            }
        }
        
        function startTimerDisplay() {
            if (timerInterval) clearInterval(timerInterval);
            
            timerStartTime = Date.now() - (timerElapsed * 1000);
            
            timerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - timerStartTime) / 1000);
                timerElapsed = elapsed;
                
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                document.getElementById('timer-text').textContent = 
                    `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                
                const progress = (elapsed / timerRequired) * 100;
                document.getElementById('timer-progress').style.width = Math.min(progress, 100) + '%';
                
                if (elapsed >= timerRequired) {
                    document.getElementById('timer-status').textContent = 'Completed';
                    document.getElementById('timer-status').className = 'badge bg-success';
                }
                
                // Update server every 30 seconds
                if (elapsed % 30 === 0 && currentTimerSession) {
                    updateTimerOnServer(elapsed);
                }
            }, 1000);
        }
        
        async function updateTimerOnServer(timeSpent) {
            if (!currentTimerSession) return;
            
            try {
                await fetch('/api/timer/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        session_id: currentTimerSession.id,
                        time_spent: timeSpent
                    })
                });
            } catch (error) {
                console.error('Timer update error:', error);
            }
        }
        
        function stopTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
                if (currentTimerSession) {
                    updateTimerOnServer(timerElapsed);
                }
            }
        }
        
        // Override loadChapter to start timer
        const originalLoadChapter = window.loadChapter;
        window.loadChapter = function(chapterId) {
            stopTimer();
            if (originalLoadChapter) {
                originalLoadChapter(chapterId);
            }
            startChapterTimer(chapterId);
        };
    </script>
    </div>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
