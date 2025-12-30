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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/strict-timer.js"></script>
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
            background-color: var(--success-bg) !important;
            border-left: 4px solid var(--success-dark) !important;
            color: white !important;
        }
        .chapter-item.locked {
            cursor: not-allowed;
            opacity: 0.5;
            background-color: var(--bg-secondary);
            color: var(--text-muted);
        }
        .chapter-item.locked:hover {
            background-color: var(--bg-secondary);
        }
        .chapter-item.completed::before {
            content: '✓ ';
            color: white;
            font-weight: bold;
            font-size: 1.2em;
            margin-right: 8px;
        }
        .chapter-item.completed .chapter-status {
            color: white;
            font-weight: 600;
            font-size: 0.85em;
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
        /* Form controls now handled by global themes.css */
        .chapter-text {
            line-height: 1.6;
            font-size: 16px;
        }
        .chapter-text h1, .chapter-text h2, .chapter-text h3, 
        .chapter-text h4, .chapter-text h5, .chapter-text h6 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-weight: 600;
        }
        .chapter-text h1 { font-size: 2em; }
        .chapter-text h2 { font-size: 1.75em; }
        .chapter-text h3 { font-size: 1.5em; }
        .chapter-text h4 { font-size: 1.25em; }
        .chapter-text p {
            margin-bottom: 1em;
        }
        .chapter-text ul, .chapter-text ol {
            margin-bottom: 1em;
            padding-left: 1.5em;
        }
        .chapter-text li {
            margin-bottom: 0.3em;
            margin-left: 0;
        }
        .chapter-text img {
            max-width: 100%;
            height: auto;
            margin: 1em 0;
            border-radius: 4px;
        }
        .chapter-text table {
            width: 100%;
            margin: 1em 0;
            border-collapse: collapse;
        }
        
        /* Pagination Styles */
        .content-pagination {
            margin: 20px 0;
            padding: 15px;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
        }
        
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .pagination-info {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .pagination-buttons {
            display: flex;
            gap: 10px;
        }
        
        .pagination-btn {
            padding: 8px 16px;
            border: 1px solid var(--border);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background-color: var(--hover);
            border-color: var(--accent);
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-progress {
            width: 100%;
            height: 6px;
            background-color: var(--bg-primary);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .pagination-progress-bar {
            height: 100%;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }
        
        .content-page {
            display: none;
            min-height: 400px;
        }
        
        .content-page.active {
            display: block;
        }
        
        .page-break-indicator {
            margin: 20px 0;
            text-align: center;
            color: var(--text-secondary);
            font-size: 12px;
            border-top: 1px dashed var(--border);
            padding-top: 10px;
        }
        
        .pagination-settings {
            display: none;
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .pagination-settings.show {
            display: block;
        }
        
        .settings-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .settings-row:last-child {
            margin-bottom: 0;
        }
        
        .settings-label {
            font-weight: 600;
            min-width: 120px;
        }
        
        .settings-control {
            flex: 1;
        }
        
        .range-input {
            width: 100%;
        }
        
        .range-value {
            font-weight: 600;
            color: var(--accent);
            min-width: 80px;
            text-align: right;
        }
        .chapter-text table th,
        .chapter-text table td {
            border: 1px solid var(--border);
            padding: 8px 12px;
        }
        .chapter-text table th {
            background-color: var(--bg-primary);
            font-weight: 600;
        }
        .chapter-text strong {
            font-weight: 600;
        }
        .chapter-text em {
            font-style: italic;
        }
        .chapter-text a {
            color: var(--accent);
            text-decoration: underline;
        }
        
        /* Ensure all course content text has proper contrast */
        #chapter-content, #chapter-content * {
            color: var(--text-secondary) !important;
        }
        
        #chapter-content h1, #chapter-content h2, #chapter-content h3,
        #chapter-content h4, #chapter-content h5, #chapter-content h6,
        #chapter-content strong, #chapter-content b {
            color: var(--text-primary) !important;
        }
        
        /* Remove any hardcoded gray highlights */
        #chapter-content *[style*="color: gray"],
        #chapter-content *[style*="color: grey"],
        #chapter-content *[style*="background"] {
            color: var(--text-secondary) !important;
            background: transparent !important;
        }
        
        /* Ensure good contrast for highlighted text */
        #chapter-content mark, #chapter-content .highlight {
            background: var(--warning-light) !important;
            color: var(--text-primary) !important;
            padding: 2px 4px;
            border-radius: 3px;
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 id="chapter-title" class="mb-0">Course Content</h5>
                            <button class="btn btn-sm btn-outline-light" onclick="togglePaginationSettings()" title="Pagination Settings">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
                        <div class="card-body" id="chapter-content">
                            <p>Select a chapter to begin learning.</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <button class="btn btn-secondary" id="prevBtn" onclick="previousChapter()">
                                <i class="fas fa-chevron-left me-2"></i>Previous
                            </button>
                            <button class="btn btn-primary" id="nextBtn" onclick="nextChapter()">
                                Next<i class="fas fa-chevron-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        // Get enrollment ID from URL path (/course-player/19) or query parameter (?enrollmentId=19)
        const pathParts = window.location.pathname.split('/');
        const enrollmentId = pathParts[2] || urlParams.get('enrollmentId');
        let courseStateCode = ''; // Will be set when enrollment is loaded
        
        let currentEnrollment = null;
        let chapters = [];
        let strictDurationEnabled = false;
        
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
                
                // Set course state code for Delaware quiz rotation
                courseStateCode = currentEnrollment.course?.state_code || '';
                
                // Check if course data is available
                if (!currentEnrollment.course || !currentEnrollment.course.id) {
                    throw new Error('Course data not found for this enrollment');
                }
                
                // Load strict duration setting
                strictDurationEnabled = currentEnrollment.course.strict_duration_enabled || false;
                window.strictDurationEnabled = strictDurationEnabled;
                console.log('Strict duration enabled:', strictDurationEnabled);
                
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
                    
                    // Initialize chapter completion count for security questions
                    chaptersCompletedCount = chapters.filter(chapter => chapter.is_completed).length;
                    console.log(`Initialized with ${chaptersCompletedCount} completed chapters`);
                    
                    displayChapters();
                    
                    // Auto-select the first available chapter
                    const firstAvailableChapter = chapters.find((chapter, index) => isChapterUnlocked(index));
                    if (firstAvailableChapter) {
                        selectChapter(firstAvailableChapter.id);
                    }
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
                const isLocked = !isChapterUnlocked(index);
                const isCompleted = chapter.is_completed || false;
                
                // Only show completed styling if NOT locked
                const completedClass = (isCompleted && !isLocked) ? 'completed' : '';
                const lockedClass = isLocked ? 'locked' : '';
                const completedBadge = (isCompleted && !isLocked) ? '<span class="chapter-status">Completed</span>' : '';
                const lockedBadge = isLocked ? '<span class="chapter-status text-muted">🔒 Locked</span>' : '';
                
                const clickHandler = isLocked ? 'showLockedChapterMessage()' : `selectChapter('${chapter.id}')`;
                
                return `
                    <div class="chapter-item ${completedClass} ${lockedClass}" onclick="${clickHandler}" data-chapter-id="${chapter.id}">
                        <strong>${index + 1}. ${chapter.title}</strong>
                        ${completedBadge}
                        ${lockedBadge}
                        <br>
                        <small class="text-muted">${chapter.duration} minutes</small>
                    </div>
                `;
            }).join('');
        }
        
        function isChapterUnlocked(chapterIndex) {
            // First chapter is always unlocked
            if (chapterIndex === 0) return true;
            
            // Second chapter is unlocked if first is completed OR if we're just starting
            if (chapterIndex === 1) {
                return chapters[0].is_completed || true; // Allow second chapter to be accessible
            }
            
            // Check if all previous chapters are completed
            for (let i = 0; i < chapterIndex; i++) {
                if (!chapters[i].is_completed) {
                    return false;
                }
            }
            return true;
        }
        
        function showLockedChapterMessage() {
            alert('⚠️ Please complete the previous chapters first before accessing this chapter.');
        }
        
        let currentChapterId = null;
        let chapterTimer = null;
        let timerStartTime = null;
        let timerElapsed = 0;
        let timerRequired = 0;
        let timerInterval = null;
        let timerRunning = false;
        let timeRemaining = 0;
        
        // Pagination variables
        let contentPages = [];
        let currentPage = 0;
        let wordsPerPage = 800; // Approximately 3-4 minutes of reading
        
        function selectChapter(chapterId) {
            console.log('🔍 selectChapter called with:', chapterId, 'Type:', typeof chapterId);
            
            // Convert to string if needed
            const chapterIdStr = String(chapterId);
            
            const chapter = chapters.find(c => String(c.id) === chapterIdStr);
            console.log('🔍 Found chapter:', chapter);
            
            if (!chapter) {
                console.error('Chapter not found:', chapterId);
                console.log('Available chapters:', chapters.map(c => ({ id: c.id, title: c.title })));
                return;
            }
            
            // Handle final exam differently
            if (chapterIdStr === 'final-exam') {
                loadFinalExam();
                return;
            }
            
            // Check if chapter is unlocked
            const chapterIndex = chapters.findIndex(c => String(c.id) === chapterIdStr);
            console.log('🔍 Chapter index:', chapterIndex, 'Is unlocked:', isChapterUnlocked(chapterIndex));
            
            if (!isChapterUnlocked(chapterIndex)) {
                console.warn(`Chapter ${chapterId} is locked. Chapter index: ${chapterIndex}`);
                console.warn('Previous chapters:', chapters.slice(0, chapterIndex).map(c => ({ id: c.id, completed: c.is_completed })));
                showLockedChapterMessage();
                return;
            }
            
            console.log(`✅ Loading chapter ${chapterId} (index: ${chapterIndex})`);
            currentChapterId = chapterId;
            
            // Show timer display if strict duration is enabled
            if (strictDurationEnabled) {
                console.log('⏱️ Strict duration enabled, showing timer');
                const timerDisplay = document.getElementById('timer-display');
                if (timerDisplay) {
                    timerDisplay.style.display = 'block';
                }
            }
            
            // Check for timer configuration
            checkChapterTimer(chapterId);
            
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
                        <div class="mt-2 d-flex justify-content-end gap-2">
                            <a href="${pdfUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt"></i> Open in New Tab
                            </a>
                            <a href="${pdfUrl}" download class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        </div>
                    </div>
                `;
            }
            
            document.getElementById('chapter-content').innerHTML = `
                <!-- Pagination Settings Panel -->
                <div id="pagination-settings" class="pagination-settings">
                    <h6><i class="fas fa-cog"></i> Reading Experience Settings</h6>
                    <div class="settings-row">
                        <div class="settings-label">Content per page:</div>
                        <div class="settings-control">
                            <input type="range" id="words-per-page-range" class="range-input" 
                                   min="400" max="1600" step="100" value="${wordsPerPage}">
                        </div>
                        <div id="words-per-page-value" class="range-value">${wordsPerPage} words (~${Math.ceil(wordsPerPage/200)} min)</div>
                    </div>
                    <div class="settings-row">
                        <div class="settings-label"></div>
                        <div class="settings-control">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Adjust how much content appears on each page. Lower values = more pages, easier reading.
                            </small>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="resetPaginationSettings(event)">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-keyboard"></i> Use Ctrl + ← → to navigate pages
                        </small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><strong>Course Progress:</strong> ${Math.min(currentEnrollment.progress_percentage || 0, 100).toFixed(0)}%</span>
                        ${currentEnrollment.quiz_average ? `<span><strong>Quiz Average:</strong> ${currentEnrollment.quiz_average}%</span>` : ''}
                    </div>
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
                ${renderPaginatedContent(processedContent)}
                <div id="quiz-section" class="mt-4" style="display: none;"></div>
                <div class="mt-4" id="action-button-container">
                    <!-- Button will be set dynamically based on quiz availability -->
                </div>
            `;
            
            // Initialize pagination settings after content is loaded
            setTimeout(() => {
                initializePaginationSettings();
                setupSettingsPanelEvents();
                // Initial action button setup (will be updated again after questions load)
                updateActionButtons();
            }, 100);
            
            // Load questions for this chapter (but don't display them yet)
            loadChapterQuestions(chapter.id);
            
            // Check if user has already taken this quiz
            checkExistingQuizResult(chapter.id);
            
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
                
                // Update navigation buttons
                updateNavigationButtons();
            }, 100);
        }

        function nextChapter() {
            // Go to next page in pagination, not next chapter
            console.log('Next button clicked. Current page:', currentPage, 'Total pages:', contentPages.length);
            nextPage();
        }

        function previousChapter() {
            // Go to previous page in pagination, not previous chapter
            console.log('Previous button clicked. Current page:', currentPage, 'Total pages:', contentPages.length);
            previousPage();
        }

        function updateNavigationButtons() {
            // Show/hide buttons based on pagination state
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            
            if (!prevBtn || !nextBtn) return;
            
            // Always show buttons if chapter is loaded
            if (currentChapterId) {
                prevBtn.disabled = currentPage === 0;
                nextBtn.disabled = currentPage === contentPages.length - 1;
                prevBtn.style.display = 'block';
                nextBtn.style.display = 'block';
            }
        }
        
        // Pagination Functions
        function splitContentIntoPages(content) {
            // Create a temporary div to parse HTML content
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = content;
            
            // Get all text content and count words
            const textContent = tempDiv.textContent || tempDiv.innerText || '';
            const wordCount = textContent.trim().split(/\s+/).length;
            
            // If content is short enough, return as single page
            if (wordCount <= wordsPerPage) {
                return [content];
            }
            
            // Split content into pages based on paragraphs and headings
            const elements = Array.from(tempDiv.children);
            const pages = [];
            let currentPageContent = '';
            let currentWordCount = 0;
            let lastWasHeading = false;
            
            for (let i = 0; i < elements.length; i++) {
                const element = elements[i];
                const elementText = element.textContent || element.innerText || '';
                const elementWordCount = elementText.trim().split(/\s+/).length;
                const isHeading = /^h[1-6]$/i.test(element.tagName);
                
                // If adding this element would exceed the page limit and we have content
                // BUT don't break if the last element was a heading (keep heading with content)
                if (currentWordCount + elementWordCount > wordsPerPage && currentPageContent && !lastWasHeading) {
                    pages.push(currentPageContent);
                    currentPageContent = element.outerHTML;
                    currentWordCount = elementWordCount;
                } else {
                    currentPageContent += element.outerHTML;
                    currentWordCount += elementWordCount;
                }
                
                lastWasHeading = isHeading;
            }
            
            // Add the last page if there's content
            if (currentPageContent) {
                pages.push(currentPageContent);
            }
            
            return pages.length > 0 ? pages : [content];
        }
        
        function renderPaginatedContent(content) {
            contentPages = splitContentIntoPages(content);
            currentPage = 0;
            
            if (contentPages.length <= 1) {
                // No pagination needed
                return `<div class="chapter-text">${content}</div>`;
            }
            
            // Create paginated content structure
            let paginatedHTML = `
                <div class="content-pagination">
                    <div class="pagination-controls">
                        <div class="pagination-info">
                            Page <span id="current-page-num">1</span> of <span id="total-pages">${contentPages.length}</span>
                            <span class="text-muted">• Estimated reading time: ${Math.ceil(contentPages.length * 3)} minutes</span>
                        </div>
                        <div class="pagination-buttons">
                            <button id="prev-page-btn" class="pagination-btn" onclick="previousPage()" disabled>
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                            <button id="next-page-btn" class="pagination-btn" onclick="nextPage()">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="pagination-progress">
                        <div id="pagination-progress-bar" class="pagination-progress-bar" style="width: ${(1/contentPages.length)*100}%"></div>
                    </div>
                </div>
                
                <div id="paginated-content">
                    ${contentPages.map((pageContent, index) => `
                        <div class="content-page ${index === 0 ? 'active' : ''}" data-page="${index}">
                            <div class="chapter-text">${pageContent}</div>
                            ${index < contentPages.length - 1 ? '<div class="page-break-indicator">— Page Break —</div>' : ''}
                        </div>
                    `).join('')}
                </div>
            `;
            
            return paginatedHTML;
        }
        
        function updatePaginationControls() {
            const currentPageNum = document.getElementById('current-page-num');
            const prevBtn = document.getElementById('prev-page-btn');
            const nextBtn = document.getElementById('next-page-btn');
            const progressBar = document.getElementById('pagination-progress-bar');
            
            if (currentPageNum) currentPageNum.textContent = currentPage + 1;
            if (prevBtn) prevBtn.disabled = currentPage === 0;
            if (nextBtn) nextBtn.disabled = currentPage === contentPages.length - 1;
            if (progressBar) {
                const progress = ((currentPage + 1) / contentPages.length) * 100;
                progressBar.style.width = `${progress}%`;
            }
            
            // Update action buttons based on pagination state
            updateActionButtons();
            
            // Update bottom navigation buttons
            updateNavigationButtons();
        }
        
        function showPage(pageIndex) {
            if (pageIndex < 0 || pageIndex >= contentPages.length) return;
            
            // Hide all pages
            document.querySelectorAll('.content-page').forEach(page => {
                page.classList.remove('active');
            });
            
            // Show current page
            const targetPage = document.querySelector(`[data-page="${pageIndex}"]`);
            if (targetPage) {
                targetPage.classList.add('active');
                targetPage.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            currentPage = pageIndex;
            updatePaginationControls();
        }
        
        // Update action buttons based on pagination and quiz state
        function updateActionButtons() {
            const actionContainer = document.getElementById('action-button-container');
            if (!actionContainer) return;
            
            const isOnLastPage = contentPages.length <= 1 || currentPage === contentPages.length - 1;
            const hasQuestions = currentQuestions && currentQuestions.length > 0;
            
            if (hasQuestions) {
                // Show quiz button regardless of page (quiz can be taken anytime)
                actionContainer.innerHTML = `
                    <button onclick="showQuiz()" class="btn btn-primary btn-lg">
                        <i class="fas fa-play"></i> Take Quiz
                    </button>
                `;
            } else if (isOnLastPage) {
                // Show complete button only on last page or single page
                const timerActive = window.strictTimer && window.strictTimer.isActive;
                const isDisabled = window.strictDurationEnabled && timerActive;
                const disabledAttr = isDisabled ? 'disabled' : '';
                const disabledClass = isDisabled ? 'opacity-50' : '';
                const timeRemaining = window.strictTimer ? Math.ceil(Math.max(0, window.strictTimer.requiredTime - window.strictTimer.elapsedTime)) : 0;
                const title = isDisabled ? 'Wait for timer to complete' : '';
                
                actionContainer.innerHTML = `
                    <button onclick="completeChapter()" class="btn btn-success btn-lg ${disabledClass}" ${disabledAttr} title="${title}">
                        <i class="fas fa-check-circle"></i> Mark Chapter as Complete
                        ${isDisabled ? '<br><small>Timer: ' + timeRemaining + 's remaining</small>' : ''}
                    </button>
                `;
            } else {
                // Show message to continue reading
                actionContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-book-open"></i> 
                        <strong>Continue Reading:</strong> Please read all content before completing this chapter.
                        <br><small class="text-muted">Use the "Next" button above to continue to page ${currentPage + 2} of ${contentPages.length}.</small>
                    </div>
                `;
            }
        }
        
        function nextPage() {
            if (currentPage < contentPages.length - 1) {
                showPage(currentPage + 1);
            }
        }
        
        function previousPage() {
            if (currentPage > 0) {
                showPage(currentPage - 1);
            }
        }
        
        // Keyboard navigation for pagination
        document.addEventListener('keydown', function(e) {
            if (contentPages.length > 1) {
                if (e.key === 'ArrowLeft' && e.ctrlKey) {
                    e.preventDefault();
                    previousPage();
                } else if (e.key === 'ArrowRight' && e.ctrlKey) {
                    e.preventDefault();
                    nextPage();
                }
            }
        });
        
        // Pagination Settings Functions
        function togglePaginationSettings() {
            console.log('🔧 Toggling pagination settings...');
            const settingsPanel = document.getElementById('pagination-settings');
            console.log('🔧 Settings panel found:', !!settingsPanel);
            
            if (settingsPanel) {
                const isCurrentlyVisible = settingsPanel.classList.contains('show');
                settingsPanel.classList.toggle('show');
                console.log('🔧 Settings panel toggled:', !isCurrentlyVisible ? 'shown' : 'hidden');
            } else {
                console.warn('🔧 Settings panel not found - may need to load a chapter first');
            }
        }
        
        function updateWordsPerPage(value) {
            wordsPerPage = parseInt(value);
            const valueDisplay = document.getElementById('words-per-page-value');
            if (valueDisplay) {
                valueDisplay.textContent = `${value} words (~${Math.ceil(value/200)} min)`;
            }
            
            // Save to localStorage
            localStorage.setItem('coursePlayerWordsPerPage', value);
            
            // Re-render current content if available
            if (currentChapterId) {
                const currentChapter = chapters.find(c => c.id === currentChapterId);
                if (currentChapter) {
                    // Re-load the chapter with new pagination settings
                    selectChapter(currentChapterId);
                }
            }
        }
        
        function resetPaginationSettings(event) {
            if (event) {
                event.stopPropagation();
            }
            
            wordsPerPage = 800;
            const rangeInput = document.getElementById('words-per-page-range');
            if (rangeInput) {
                rangeInput.value = 800;
            }
            updateWordsPerPage(800);
            localStorage.removeItem('coursePlayerWordsPerPage');
        }
        
        // Load saved settings on page load
        function loadPaginationSettings() {
            const savedWordsPerPage = localStorage.getItem('coursePlayerWordsPerPage');
            if (savedWordsPerPage) {
                wordsPerPage = parseInt(savedWordsPerPage);
            }
        }
        
        // Initialize pagination settings after content is loaded
        function initializePaginationSettings() {
            const rangeInput = document.getElementById('words-per-page-range');
            const valueDisplay = document.getElementById('words-per-page-value');
            
            if (rangeInput) {
                rangeInput.value = wordsPerPage;
                
                // Remove any existing event listeners to prevent duplicates
                rangeInput.removeEventListener('input', handleSliderChange);
                rangeInput.removeEventListener('change', handleSliderChange);
                
                // Add event listeners with proper event handling
                rangeInput.addEventListener('input', handleSliderChange);
                rangeInput.addEventListener('change', handleSliderChange);
                
                // Prevent the settings panel from closing when interacting with slider
                rangeInput.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                rangeInput.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                });
            }
            
            if (valueDisplay) {
                valueDisplay.textContent = `${wordsPerPage} words (~${Math.ceil(wordsPerPage/200)} min)`;
            }
        }
        
        // Handle slider changes
        function handleSliderChange(e) {
            e.stopPropagation();
            updateWordsPerPage(e.target.value);
        }
        
        // Setup event handlers for the settings panel
        function setupSettingsPanelEvents() {
            const settingsPanel = document.getElementById('pagination-settings');
            
            if (settingsPanel) {
                // Prevent settings panel from closing when clicking inside it
                settingsPanel.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                // Also prevent mousedown events from bubbling
                settingsPanel.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                });
            }
            
            // Add click handler to document to close settings when clicking outside
            document.addEventListener('click', function(e) {
                const settingsPanel = document.getElementById('pagination-settings');
                const settingsButton = e.target.closest('[onclick*="togglePaginationSettings"]');
                
                // If clicking outside settings panel and not on the settings button, close panel
                if (settingsPanel && settingsPanel.classList.contains('show') && !settingsButton) {
                    if (!settingsPanel.contains(e.target)) {
                        settingsPanel.classList.remove('show');
                    }
                }
            });
        }
        
        async function completeChapter(chapterId = null) {
            // Use current chapter if no chapterId provided
            const targetChapterId = chapterId || currentChapterId;
            
            if (!targetChapterId) {
                console.error('No chapter ID available');
                alert('Please select a chapter first');
                return Promise.reject('No chapter ID available');
            }

            // Check strict duration enforcement
            const timerActive = window.strictTimer && window.strictTimer.isActive;
            if (window.strictDurationEnabled && timerActive) {
                alert('You must complete the full chapter duration before marking as complete.');
                return Promise.reject('Strict duration not met');
            }
            
            try {
                console.log('Completing chapter:', targetChapterId);
                
                const response = await fetch(`/web/enrollments/${enrollmentId}/complete-chapter/${targetChapterId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        time_spent: 60
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    console.log('Chapter completed:', data);
                    
                    // Update the chapter in the local chapters array
                    const chapterIndex = chapters.findIndex(c => c.id === targetChapterId);
                    if (chapterIndex !== -1) {
                        chapters[chapterIndex].is_completed = true;
                    }
                    
                    // Update the progress percentage
                    if (currentEnrollment) {
                        currentEnrollment.progress_percentage = data.progress_percentage;
                    }
                    
                    // Refresh the chapters display to show green mark and unlock next chapter
                    displayChapters();
                    
                    // If course completed, redirect to certificate page after a moment
                    if (data.enrollment_completed) {
                        setTimeout(() => {
                            window.location.href = '/generate-certificates';
                        }, 2000);
                        return Promise.resolve(data);
                    }
                    
                    return Promise.resolve(data);
                } else {
                    console.error('Failed to complete chapter:', data);
                    const errorMsg = 'Failed to complete chapter: ' + (data.error || 'Unknown error');
                    alert(errorMsg);
                    return Promise.reject(errorMsg);
                }
            } catch (error) {
                console.error('Error completing chapter:', error);
                alert('Failed to complete chapter. Please try again.');
                return Promise.reject(error);
            }
        }
        
        let currentQuestions = [];
        let questionAttempts = {};
        
        async function loadChapterQuestions(chapterId) {
            try {
                console.log('🔍 Loading questions for chapter:', chapterId);
                
                // Build URL with quiz_set parameter for Delaware courses
                let url = `/api/chapters/${chapterId}/questions`;
                
                // Check if this is a Delaware course and get current quiz set
                if (courseStateCode === 'DE') {
                    const quizSet = await getCurrentQuizSet(chapterId);
                    url += `?quiz_set=${quizSet}`;
                    console.log('🔍 Delaware course - loading quiz set:', quizSet);
                }
                
                const response = await fetch(url);
                console.log('🔍 Questions response status:', response.status);
                
                currentQuestions = await response.json();
                console.log('🔍 Questions loaded:', currentQuestions.length, currentQuestions);
                
                if (currentQuestions.length > 0) {
                    currentQuestions.forEach(q => questionAttempts[q.id] = 0);
                    console.log('✅ Quiz questions ready for chapter');
                } else {
                    console.log('⚠️ No questions found for this chapter');
                }
                
                // Update action buttons based on current state
                updateActionButtons();
            } catch (error) {
                console.error('❌ Error loading questions:', error);
                // Update action buttons (will show appropriate message based on pagination)
                updateActionButtons();
            }
        }
        
        // Get current quiz set for Delaware courses
        async function getCurrentQuizSet(chapterId) {
            try {
                const response = await fetch(`/api/chapters/${chapterId}/quiz-progress?enrollment_id=${enrollmentId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    return data.current_quiz_set || 1;
                }
            } catch (error) {
                console.error('Error getting quiz set:', error);
            }
            
            return 1; // Default to quiz set 1
        }
        
        function showQuiz() {
            if (currentQuestions.length === 0) {
                alert('No quiz available for this chapter.');
                return;
            }
            
            // Hide content and show quiz
            document.querySelector('.chapter-text').style.display = 'none';
            document.querySelector('#action-button-container').style.display = 'none';
            document.querySelector('#quiz-section').style.display = 'block';
            
            displayQuestions();
        }
        
        async function completeChapter() {
            if (!currentChapterId) {
                alert('No chapter selected');
                return;
            }
            
            // Check strict duration enforcement
            const timerActive = window.strictTimer && window.strictTimer.isActive;
            if (window.strictDurationEnabled && timerActive) {
                alert('You must complete the full chapter duration before marking as complete.');
                return;
            }
            
            console.log('Completing chapter:', currentChapterId);
            
            try {
                const response = await fetch(`/web/enrollments/${enrollmentId}/complete-chapter/${currentChapterId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Complete chapter response status:', response.status);
                const result = await response.json();
                console.log('Complete chapter result:', result);
                
                if (response.ok) {
                    alert('Chapter marked as complete!');
                    
                    // Update the chapter in the chapters array
                    const chapterIndex = chapters.findIndex(c => c.id === currentChapterId);
                    if (chapterIndex !== -1) {
                        chapters[chapterIndex].is_completed = true;
                    }
                    
                    // Update enrollment progress display
                    if (currentEnrollment) {
                        currentEnrollment.progress_percentage = result.progress_percentage;
                    }
                    
                    // Refresh chapters list to update lock status
                    displayChapters();
                    
                    // Increment completed chapters count and trigger security verification
                    chaptersCompletedCount++;
                    console.log(`Chapter completed. Total chapters completed: ${chaptersCompletedCount}`);
                    
                    // Trigger security verification after chapter completion
                    triggerSecurityAfterChapter();
                    
                    // Load next chapter if available
                    const currentIndex = chapters.findIndex(c => c.id === currentChapterId);
                    if (currentIndex < chapters.length - 1) {
                        const nextChapter = chapters[currentIndex + 1];
                        setTimeout(() => {
                            selectChapter(nextChapter.id);
                        }, 500);
                    }
                } else {
                    console.error('Failed to complete chapter:', result);
                    alert('Failed to complete chapter: ' + (result.message || result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error completing chapter:', error);
                alert('Failed to complete chapter: ' + error.message);
            }
        }
        
        function displayQuestions() {
            const container = document.getElementById('quiz-section');
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Chapter Quiz - ${currentQuestions.length} Questions</h5>
                        <button onclick="hideQuiz()" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-arrow-left"></i> Back to Content
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Answer all questions and click "Submit Quiz" to complete this chapter.
                        </div>
                        <form id="quiz-form">
                            ${currentQuestions.map((q, index) => {
                                let options;
                                if (Array.isArray(q.options)) {
                                    options = q.options;
                                } else if (typeof q.options === 'object') {
                                    options = Object.values(q.options);
                                } else {
                                    options = JSON.parse(q.options);
                                }
                                return `
                                    <div class="mb-4 question-item" data-question-id="${q.id}" data-correct="${q.correct_answer}">
                                        <h6>${index + 1}. ${q.question_text}</h6>
                                        ${options.map((opt, optIndex) => `
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="question_${q.id}" id="q${q.id}_opt${optIndex}" value="${opt}">
                                                <label class="form-check-label" for="q${q.id}_opt${optIndex}">
                                                    ${opt}
                                                </label>
                                            </div>
                                        `).join('')}
                                    </div>
                                `;
                            }).join('')}
                        </form>
                        <div class="mt-4 text-center">
                            <button onclick="submitQuizAndComplete()" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle"></i> Submit Quiz & Complete Chapter
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function hideQuiz() {
            // Show content and hide quiz
            document.querySelector('.chapter-text').style.display = 'block';
            document.querySelector('#action-button-container').style.display = 'block';
            document.querySelector('#quiz-section').style.display = 'none';
        }
        
        async function checkExistingQuizResult(chapterId) {
            try {
                const response = await fetch(`/api/chapters/${chapterId}/quiz-result`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.quiz_result) {
                        // User has already taken this quiz, show the result
                        const actionContainer = document.getElementById('action-button-container');
                        actionContainer.innerHTML = `
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle"></i> You've already completed this chapter quiz with a score of <strong>${result.quiz_result.percentage}%</strong>
                            </div>
                            <button onclick="showQuiz()" class="btn btn-warning btn-lg me-3">
                                <i class="fas fa-redo"></i> Retake Quiz
                            </button>
                            <button onclick="completeChapter()" class="btn btn-success btn-lg">
                                <i class="fas fa-arrow-right"></i> Continue to Next Chapter
                            </button>
                        `;
                    }
                }
            } catch (error) {
                console.error('Error checking quiz result:', error);
            }
        }
        
        async function submitQuizAndComplete() {
            const form = document.getElementById('quiz-form');
            if (!form) {
                completeChapter();
                return;
            }
            
            // Check if all questions are answered
            const unanswered = currentQuestions.filter(q => {
                return !document.querySelector(`input[name="question_${q.id}"]:checked`);
            });
            
            if (unanswered.length > 0) {
                alert(`Please answer all questions before completing. ${unanswered.length} question(s) remaining.`);
                return;
            }
            
            // Collect answers
            const results = currentQuestions.map(q => {
                const selected = document.querySelector(`input[name="question_${q.id}"]:checked`);
                const userAnswer = selected ? selected.value : null;
                const isCorrect = userAnswer === q.correct_answer;
                
                return {
                    question_id: q.id,
                    question_text: q.question_text,
                    user_answer: userAnswer,
                    correct_answer: q.correct_answer,
                    is_correct: isCorrect,
                    explanation: q.explanation || ''
                };
            });
            
            const correctCount = results.filter(r => r.is_correct).length;
            const wrongCount = results.length - correctCount;
            const percentage = ((correctCount / results.length) * 100).toFixed(2);
            
            // Save results to database
            let quizSaveResponse = null;
            try {
                const response = await fetch('/api/chapter-quiz-results', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        chapter_id: currentChapterId,
                        enrollment_id: enrollmentId,
                        total_questions: results.length,
                        correct_answers: correctCount,
                        wrong_answers: wrongCount,
                        percentage: percentage,
                        answers: results
                    })
                });
                quizSaveResponse = await response.json();
                console.log('Quiz results saved:', quizSaveResponse);
                
                // Handle Delaware quiz rotation
                if (quizSaveResponse.delaware_quiz_rotation && quizSaveResponse.switch_to_quiz_set === 2) {
                    // Student failed Quiz Set 1, show Quiz Set 2
                    alert(quizSaveResponse.message || 'Quiz Set 1 failed. You will now see Quiz Set 2 questions.');
                    
                    // Reload questions for Quiz Set 2
                    await loadChapterQuestions(currentChapterId);
                    
                    // Reset quiz form and show it again
                    document.getElementById('quiz-form').reset();
                    showQuiz();
                    return; // Don't show results popup, let them try Quiz Set 2
                }
                
            } catch (error) {
                console.error('Error saving quiz results:', error);
            }
            
            // Show results popup
            showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse);
        }
        
        function showQuizResults(results, correctCount, wrongCount, percentage, quizSaveResponse) {
            // Check if there's a next chapter
            const currentIndex = chapters.findIndex(c => c.id === currentChapterId);
            const hasNextChapter = currentIndex !== -1 && currentIndex < chapters.length - 1;
            const buttonText = hasNextChapter ? 'Continue to Next Chapter' : 'Complete Course';
            const buttonIcon = hasNextChapter ? 'fas fa-arrow-right' : 'fas fa-trophy';
            
            // Get quiz average if available
            const quizAverage = quizSaveResponse && quizSaveResponse.quiz_average ? quizSaveResponse.quiz_average : null;
            
            const modalHtml = `
                <div class="modal fade show" id="quizResultsModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Quiz Results</h5>
                                <button type="button" class="btn-close" onclick="closeQuizResults()"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <div class="card text-center border-success">
                                            <div class="card-body">
                                                <h2 class="text-success">${correctCount}</h2>
                                                <p class="mb-0">Correct</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-danger">
                                            <div class="card-body">
                                                <h2 class="text-danger">${wrongCount}</h2>
                                                <p class="mb-0">Wrong</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card text-center border-primary">
                                            <div class="card-body">
                                                <h2 class="text-primary">${percentage}%</h2>
                                                <p class="mb-0">Chapter Score</p>
                                            </div>
                                        </div>
                                    </div>
                                    ${quizAverage ? `
                                        <div class="col-md-3">
                                            <div class="card text-center border-info">
                                                <div class="card-body">
                                                    <h2 class="text-info">${quizAverage}%</h2>
                                                    <p class="mb-0">Course Average</p>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                                
                                ${quizAverage ? `
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-chart-line"></i> Your overall course quiz average is <strong>${quizAverage}%</strong>
                                    </div>
                                ` : ''}
                                
                                <h6 class="mb-3">Detailed Results:</h6>
                                ${results.map((r, i) => `
                                    <div class="card mb-3 ${r.is_correct ? 'border-success' : 'border-danger'}">
                                        <div class="card-body">
                                            <h6 class="card-title">${i + 1}. ${r.question_text}</h6>
                                            <p class="mb-2">
                                                <strong>Your Answer:</strong> 
                                                <span class="${r.is_correct ? 'text-success' : 'text-danger'}">${r.user_answer}</span>
                                                ${r.is_correct ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i>'}
                                            </p>
                                            ${!r.is_correct ? `
                                                <p class="mb-2">
                                                    <strong>Correct Answer:</strong> 
                                                    <span class="text-success">${r.correct_answer}</span>
                                                </p>
                                            ` : ''}
                                            ${r.explanation ? `
                                                <div class="alert alert-info mt-2 mb-0">
                                                    <strong>Explanation:</strong> ${r.explanation}
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-lg" onclick="closeQuizResults()">
                                    <i class="${buttonIcon}"></i> ${buttonText}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Don't auto-complete chapter here - let closeQuizResults handle it
        }
        
        function closeQuizResults() {
            const modal = document.getElementById('quizResultsModal');
            if (modal) {
                modal.remove();
            }
            
            // Complete the current chapter first
            completeChapter().then(() => {
                // Find current chapter index and load next chapter
                const currentIndex = chapters.findIndex(c => c.id === currentChapterId);
                
                if (currentIndex !== -1 && currentIndex < chapters.length - 1) {
                    const nextChapter = chapters[currentIndex + 1];
                    // Small delay to ensure chapter completion is processed
                    setTimeout(() => {
                        selectChapter(nextChapter.id);
                    }, 500);
                } else {
                    // This was the last chapter
                    alert('🎉 Congratulations! You have completed all chapters in this course!');
                }
            }).catch(error => {
                console.error('Error completing chapter:', error);
                alert('There was an error completing the chapter. Please try again.');
            });
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
        
        async function checkChapterTimer(chapterId) {
            try {
                console.log('🔒 Starting strict timer for chapter:', chapterId);
                
                if (!window.strictTimer) {
                    console.warn('⚠️ StrictTimer not initialized, attempting to initialize...');
                    if (typeof StrictTimer !== 'undefined') {
                        window.strictTimer = new StrictTimer();
                        console.log('✅ StrictTimer initialized in checkChapterTimer');
                    } else {
                        console.error('❌ StrictTimer class not available!');
                        hideTimerDisplay();
                        return { success: false, error: 'StrictTimer not initialized' };
                    }
                }
                
                // Get chapter duration
                const chapter = chapters.find(c => String(c.id) === String(chapterId));
                const chapterDuration = chapter ? chapter.duration : null;
                
                console.log('📖 Chapter duration:', chapterDuration, 'minutes');
                
                // Pass enrollment ID and chapter duration to the timer
                const result = await window.strictTimer.startTimer(chapterId, enrollmentId, chapterDuration);
                
                if (result.timer_required) {
                    console.log('✅ Strict timer activated');
                    // Timer display is handled by the StrictTimer class
                } else {
                    console.log('ℹ️ No timer required for this chapter');
                    hideTimerDisplay();
                }
                
                return result;
            } catch (error) {
                console.error('❌ Error starting strict timer:', error);
                hideTimerDisplay();
                return { success: false, error: error.message };
            }
        }
        
        function showTimerDisplay() {
            const timerDisplay = document.getElementById('timer-display');
            if (timerDisplay) {
                timerDisplay.style.display = 'block';
                document.getElementById('required-time').textContent = Math.floor(timerRequired / 60);
                updateTimerDisplay();
            }
        }
        
        function hideTimerDisplay() {
            const timerDisplay = document.getElementById('timer-display');
            if (timerDisplay) {
                timerDisplay.style.display = 'none';
            }
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
        }
        
        function startTimerCountdown() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            timerStartTime = Date.now();
            timerRunning = true;
            
            timerInterval = setInterval(() => {
                timerElapsed = Math.floor((Date.now() - timerStartTime) / 1000);
                timeRemaining = Math.max(0, timerRequired - timerElapsed);
                updateTimerDisplay();
                
                // Check if timer is complete
                if (timerElapsed >= timerRequired) {
                    timerRunning = false;
                    document.getElementById('timer-status').textContent = 'Complete';
                    document.getElementById('timer-status').classList.remove('bg-warning');
                    document.getElementById('timer-status').classList.add('bg-success');
                    
                    // Update button if strict duration is enabled
                    if (strictDurationEnabled) {
                        displayActionButtons();
                    }
                }
            }, 1000);
        }
        
        function updateTimerDisplay() {
            const timerDisplay = document.getElementById('timer-display');
            
            // Show timer display if strict duration is enabled
            if (strictDurationEnabled) {
                timerDisplay.style.display = 'block';
            }
            
            const minutes = Math.floor(timerElapsed / 60);
            const seconds = timerElapsed % 60;
            const timeText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            document.getElementById('timer-text').textContent = timeText;
            
            // Update progress bar
            const progress = Math.min((timerElapsed / timerRequired) * 100, 100);
            document.getElementById('timer-progress').style.width = progress + '%';
            
            // Update status
            if (timerElapsed >= timerRequired) {
                document.getElementById('timer-status').textContent = 'Complete';
                document.getElementById('timer-status').classList.remove('bg-warning');
                document.getElementById('timer-status').classList.add('bg-success');
            } else {
                document.getElementById('timer-status').textContent = 'In Progress';
                document.getElementById('timer-status').classList.remove('bg-success');
                document.getElementById('timer-status').classList.add('bg-warning');
            }
        }
        
        // Show fallback and load course data if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app course-player');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadCourseData();
            }
        }, 1000);
        
        // Old timer function - now handled by checkChapterTimer
        async function startChapterTimer(chapterId) {
            // This is now handled by checkChapterTimer in selectChapter
            console.log('Timer check for chapter:', chapterId);
        }
        
        // Final Exam Functions
        let finalExamQuestions = [];
        let finalExamAttempts = 0;
        let maxFinalExamAttempts = 2;
        
        async function loadFinalExam() {
            try {
                // Check previous attempts
                const attemptsResponse = await fetch(`/api/final-exam/attempts/${enrollmentId}`);
                const attemptsData = await attemptsResponse.json();
                finalExamAttempts = attemptsData.attempts || 0;
                maxFinalExamAttempts = attemptsData.max_attempts || 2;
                
                // Show final exam interface
                document.getElementById('chapter-content').innerHTML = `
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Final Exam</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5>Final Exam Instructions</h5>
                                <ul>
                                    <li>25 randomly selected questions</li>
                                    <li>You need 80% (20 correct answers) to pass</li>
                                    <li>Maximum ${maxFinalExamAttempts} attempts allowed</li>
                                    <li>Different questions on retry</li>
                                </ul>
                                ${finalExamAttempts > 0 ? `<p><strong>Attempts used: ${finalExamAttempts}/${maxFinalExamAttempts}</strong></p>` : ''}
                            </div>
                            
                            ${finalExamAttempts >= maxFinalExamAttempts ? 
                                '<div class="alert alert-danger">You have used all attempts for the final exam. Contact support if you need additional attempts.</div>' :
                                '<button class="btn btn-primary btn-lg" onclick="startFinalExam()">Start Final Exam</button>'
                            }
                        </div>
                    </div>
                `;
                
                // Update chapter title
                document.getElementById('chapter-title').textContent = 'Final Exam';
                
            } catch (error) {
                console.error('Error loading final exam:', error);
                document.getElementById('chapter-content').innerHTML = '<div class="alert alert-danger">Error loading final exam</div>';
            }
        }
        
        async function startFinalExam() {
            try {
                // Get enrollment ID from current enrollment
                const enrollmentId = currentEnrollment ? currentEnrollment.id : null;
                
                // First, check how many questions are available
                const checkResponse = await fetch(`/api/final-exam/count?enrollment_id=${enrollmentId}`);
                const countData = await checkResponse.json();
                const availableQuestions = countData.count || 0;
                
                console.log(`Available final exam questions: ${availableQuestions}`);
                
                // Determine how many questions to request (minimum 20, maximum 25)
                let questionsToRequest = Math.min(25, availableQuestions);
                
                if (questionsToRequest < 20) {
                    alert(`Not enough questions available for final exam. Need at least 20 questions, but only ${availableQuestions} are available.`);
                    return;
                }
                
                // Get the questions
                const response = await fetch(`/api/final-exam/random/${questionsToRequest}?enrollment_id=${enrollmentId}`);
                finalExamQuestions = await response.json();
                
                if (finalExamQuestions.length < questionsToRequest) {
                    alert(`Not enough questions available for final exam. Expected ${questionsToRequest}, got ${finalExamQuestions.length}.`);
                    return;
                }
                
                console.log(`Starting final exam with ${finalExamQuestions.length} questions`);
                displayFinalExam();
                
            } catch (error) {
                console.error('Error starting final exam:', error);
                alert('Error starting final exam');
            }
        }
        
        function displayFinalExam() {
            document.getElementById('chapter-content').innerHTML = `
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Final Exam - Attempt ${finalExamAttempts + 1}</h4>
                        <small>25 Questions | 80% Required to Pass</small>
                    </div>
                    <div class="card-body">
                        <form id="final-exam-form">
                            ${finalExamQuestions.map((q, index) => `
                                <div class="mb-4 question-item" data-question-id="${q.id}">
                                    <h6>${index + 1}. ${q.question_text}</h6>
                                    ${Object.entries(q.options).map(([key, value]) => `
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_${q.id}" id="q${q.id}_${key}" value="${key}">
                                            <label class="form-check-label" for="q${q.id}_${key}">
                                                ${key}. ${value}
                                            </label>
                                        </div>
                                    `).join('')}
                                </div>
                            `).join('')}
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-success btn-lg" onclick="submitFinalExam()">
                                    Submit Final Exam
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }
        
        async function submitFinalExam() {
            // Check if all questions are answered
            const unanswered = finalExamQuestions.filter(q => {
                return !document.querySelector(`input[name="question_${q.id}"]:checked`);
            });
            
            if (unanswered.length > 0) {
                alert(`Please answer all questions. ${unanswered.length} question(s) remaining.`);
                return;
            }
            
            // Collect answers
            const answers = finalExamQuestions.map(q => {
                const selected = document.querySelector(`input[name="question_${q.id}"]:checked`);
                return {
                    question_id: q.id,
                    user_answer: selected.value,
                    correct_answer: q.correct_answer,
                    is_correct: selected.value === q.correct_answer
                };
            });
            
            const correctCount = answers.filter(a => a.is_correct).length;
            const percentage = Math.round((correctCount / 25) * 100);
            const passed = percentage >= 80;
            
            try {
                // Save final exam result
                await fetch('/api/final-exam/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        enrollment_id: enrollmentId,
                        answers: answers,
                        score: percentage,
                        passed: passed,
                        attempt: finalExamAttempts + 1
                    })
                });
                
                showFinalExamResults(correctCount, percentage, passed);
                
            } catch (error) {
                console.error('Error submitting final exam:', error);
                alert('Error submitting final exam');
            }
        }
        
        function showFinalExamResults(correctCount, percentage, passed) {
            const resultClass = passed ? 'success' : 'danger';
            const resultText = passed ? 'PASSED' : 'FAILED';
            
            document.getElementById('chapter-content').innerHTML = `
                <div class="card">
                    <div class="card-header bg-${resultClass} text-white">
                        <h4 class="mb-0">Final Exam Results</h4>
                    </div>
                    <div class="card-body text-center">
                        <h2 class="text-${resultClass}">${resultText}</h2>
                        <h3>${correctCount}/25 Correct (${percentage}%)</h3>
                        
                        ${passed ? 
                            '<div class="alert alert-success"><h5>🎉 Congratulations!</h5><p>You have successfully completed the course!</p></div>' :
                            `<div class="alert alert-danger">
                                <h5>You need 80% to pass</h5>
                                ${finalExamAttempts < maxFinalExamAttempts - 1 ? 
                                    '<p>You have more attempts available with different questions.</p><button class="btn btn-warning" onclick="loadFinalExam()">Try Again</button>' :
                                    '<p>You have used all attempts. Contact support if you need additional attempts.</p>'
                                }
                            </div>`
                        }
                        
                        <button class="btn btn-secondary mt-3" onclick="loadCourseData()">Back to Course</button>
                    </div>
                </div>
            `;
        }
        
        // Security Verification System
        let securityTimer = null;
        let securitySessionId = null;
        let securityVerificationActive = false;
        let chaptersCompletedCount = 0; // Track completed chapters for sequential questions
        
        function initializeSecurityVerification() {
            // Show security verification immediately on page load
            setTimeout(() => {
                showSecurityVerification();
            }, 2000); // Small delay to let page load
            
            // No longer schedule random intervals - questions will be triggered after chapter completion
        }
        
        // Remove the random scheduling function - we'll trigger after chapter completion
        function triggerSecurityAfterChapter() {
            // Trigger security verification after chapter completion
            if (!securityVerificationActive) {
                setTimeout(() => {
                    showSecurityVerification();
                }, 1000); // Small delay after chapter completion
            }
        }
        
        async function showSecurityVerification() {
            if (securityVerificationActive) {
                return; // Already showing
            }
            
            securityVerificationActive = true;
            
            try {
                // Send chapter completion count to get sequential questions
                const response = await fetch('/api/security/questions', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        chapter_count: chaptersCompletedCount
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load security questions');
                }
                
                const data = await response.json();
                securitySessionId = data.session_id;
                
                console.log(`Showing security questions for chapter completion count: ${chaptersCompletedCount}`);
                displaySecurityQuestions(data.questions);
                
                // Show the modal with fallback
                showModal('securityModal');
                
            } catch (error) {
                console.error('Error loading security questions:', error);
                securityVerificationActive = false;
            }
        }
        
        function showModal(modalId) {
            const modalElement = document.getElementById(modalId);
            
            // Try Bootstrap modal first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                    return;
                } catch (e) {
                    console.warn('Bootstrap modal failed, using fallback:', e);
                }
            }
            
            // Fallback: manually show modal
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            modalElement.setAttribute('aria-hidden', 'false');
            
            // Add backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            backdrop.id = modalId + '-backdrop';
            document.body.appendChild(backdrop);
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
        
        function hideModal(modalId) {
            const modalElement = document.getElementById(modalId);
            
            // Try Bootstrap modal first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                try {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                        return;
                    }
                } catch (e) {
                    console.warn('Bootstrap modal hide failed, using fallback:', e);
                }
            }
            
            // Fallback: manually hide modal
            modalElement.style.display = 'none';
            modalElement.classList.remove('show');
            modalElement.setAttribute('aria-hidden', 'true');
            
            // Remove backdrop
            const backdrop = document.getElementById(modalId + '-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Restore body scroll
            document.body.style.overflow = '';
        }
        
        function displaySecurityQuestions(questions) {
            const container = document.getElementById('securityQuestions');
            let html = '<form id="securityForm">';
            
            questions.forEach((question, index) => {
                html += `
                    <div class="mb-3">
                        <label for="answer_${question.id}" class="form-label">
                            <strong>Question ${index + 1}:</strong> ${question.question}
                        </label>
                        <input type="text" class="form-control" id="answer_${question.id}" name="${question.id}" required>
                    </div>
                `;
            });
            
            html += '</form>';
            container.innerHTML = html;
            
            // Clear any previous errors
            document.getElementById('securityErrors').classList.add('d-none');
        }
        
        async function submitSecurityAnswers() {
            const form = document.getElementById('securityForm');
            const formData = new FormData(form);
            const answers = {};
            
            for (let [key, value] of formData.entries()) {
                answers[key] = value.trim();
            }
            
            try {
                const response = await fetch('/api/security/verify', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ answers: answers })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // All answers correct - close modal and continue
                    hideModal('securityModal');
                    securityVerificationActive = false;
                    
                    // Show success message briefly
                    showNotification('Security verification successful!', 'success');
                } else {
                    // Show errors
                    displaySecurityErrors(data.errors);
                }
                
            } catch (error) {
                console.error('Error verifying security answers:', error);
                showNotification('Error verifying answers. Please try again.', 'error');
            }
        }
        
        function displaySecurityErrors(errors) {
            const errorContainer = document.getElementById('securityErrors');
            let html = '<strong>Please correct the following:</strong><ul>';
            
            errors.forEach(error => {
                html += `<li>${error.message}</li>`;
            });
            
            html += '</ul>';
            errorContainer.innerHTML = html;
            errorContainer.classList.remove('d-none');
        }
        
        function showNotification(message, type = 'info') {
            // Create a temporary notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure StrictTimer is initialized
            if (!window.strictTimer) {
                console.warn('⚠️ StrictTimer not yet initialized, attempting to initialize...');
                if (typeof StrictTimer !== 'undefined') {
                    window.strictTimer = new StrictTimer();
                    console.log('✅ StrictTimer initialized in DOMContentLoaded');
                } else {
                    console.error('❌ StrictTimer class not available');
                }
            } else {
                console.log('✅ StrictTimer already initialized');
            }
            
            // Load pagination settings from localStorage
            loadPaginationSettings();
            
            // Security questions will only be shown after chapter completion
            // No longer showing on page load
            
            // Add event listener for security form submission
            document.getElementById('submitSecurityAnswers').addEventListener('click', submitSecurityAnswers);
            
            // Handle Enter key in security form
            document.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && document.getElementById('securityModal').classList.contains('show')) {
                    e.preventDefault();
                    submitSecurityAnswers();
                }
            });
        });
        
        // Clean up timer when page unloads
        window.addEventListener('beforeunload', function() {
            if (securityTimer) {
                clearTimeout(securityTimer);
            }
        });
        
    </script>
    </div>
    
    <!-- Security Verification Modal -->
    <div class="modal fade" id="securityModal" tabindex="-1" aria-labelledby="securityModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="securityModalLabel">
                        <i class="fas fa-shield-alt me-2"></i>Security Verification Required
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Identity Verification:</strong> Please answer the following security questions to continue with your course. These are the same answers you provided during registration.
                    </div>
                    
                    <div id="securityQuestions">
                        <!-- Questions will be loaded here -->
                    </div>
                    
                    <div id="securityErrors" class="alert alert-danger d-none">
                        <!-- Error messages will appear here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="submitSecurityAnswers">
                        <i class="fas fa-check me-2"></i>Verify Answers
                    </button>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
