@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-clock me-2"></i>Course Timers</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTimerModal">
            <i class="fas fa-plus me-2"></i>Add Timer
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="timers-table"></div>
        </div>
    </div>
</div>

<!-- Add Timer Modal -->
<div class="modal fade" id="addTimerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configure Timer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="timerForm">
                    <div class="mb-3">
                        <label class="form-label">Chapter</label>
                        <select class="form-select" id="chapter_id" required>
                            <option value="">Select Chapter</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Required Time (minutes)</label>
                        <input type="number" class="form-control" id="required_minutes" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_enabled" checked>
                        <label class="form-check-label" for="is_enabled">Enabled</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="allow_pause" checked>
                        <label class="form-check-label" for="allow_pause">Allow Pause</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="bypass_for_admin" checked>
                        <label class="form-check-label" for="bypass_for_admin">Admin Bypass</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveTimer()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadChapters() {
    console.log('Loading chapters...');
    fetch('/api/chapters', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Chapters data:', data);
        const select = document.getElementById('chapter_id');
        select.innerHTML = '<option value="">Select Chapter</option>';
        if (Array.isArray(data)) {
            data.forEach(chapter => {
                const option = document.createElement('option');
                option.value = chapter.id;
                option.textContent = chapter.display_title || chapter.title;
                option.dataset.type = chapter.type || 'chapters';
                select.appendChild(option);
            });
            console.log('Loaded', data.length, 'chapters');
        } else {
            console.error('Data is not an array:', data);
        }
    })
    .catch(error => {
        console.error('Error loading chapters:', error);
        document.getElementById('chapter_id').innerHTML = '<option value="">Error loading chapters</option>';
    });
}

function loadTimers() {
    fetch('/api/timer/list', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('timers-table');
        if (Array.isArray(data) && data.length > 0) {
            let html = '<table class="table table-hover"><thead><tr><th>Chapter</th><th>Required Time</th><th>Status</th><th>Allow Pause</th><th>Admin Bypass</th><th>Actions</th></tr></thead><tbody>';
            data.forEach(timer => {
                html += `<tr>
                    <td>${timer.chapter?.title || 'N/A'}</td>
                    <td>${timer.required_time_minutes || 0} min</td>
                    <td><span class="badge bg-${timer.is_enabled ? 'success' : 'secondary'}">${timer.is_enabled ? 'Enabled' : 'Disabled'}</span></td>
                    <td>${timer.allow_pause ? '✓' : '✗'}</td>
                    <td>${timer.bypass_for_admin ? '✓' : '✗'}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="toggleTimer(${timer.id})">
                            <i class="fas fa-power-off"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTimer(${timer.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-center">No timers configured</p>';
        }
    })
    .catch(error => {
        console.error('Error loading timers:', error);
        document.getElementById('timers-table').innerHTML = '<p class="text-danger">Error loading timers</p>';
    });
}

function saveTimer() {
    const select = document.getElementById('chapter_id');
    const selectedOption = select.options[select.selectedIndex];
    
    const data = {
        chapter_id: select.value,
        chapter_type: selectedOption.dataset.type || 'chapters',
        required_time_minutes: document.getElementById('required_minutes').value,
        is_enabled: document.getElementById('is_enabled').checked,
        allow_pause: document.getElementById('allow_pause').checked,
        bypass_for_admin: document.getElementById('bypass_for_admin').checked
    };
    
    fetch('/api/timer/configure', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById('addTimerModal')).hide();
        loadTimers();
    })
    .catch(error => console.error('Error saving timer:', error));
}

function toggleTimer(id) {
    fetch(`/api/timer/toggle/${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTimers();
        }
    })
    .catch(error => console.error('Error toggling timer:', error));
}

function deleteTimer(id) {
    if (confirm('Delete this timer?')) {
        fetch(`/api/timer/delete/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadTimers();
            }
        })
        .catch(error => console.error('Error deleting timer:', error));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadChapters();
    loadTimers();
});
</script>
@endsection
