@extends('layouts.app')

@section('title', 'Question Manager')

@section('content')
<div class="container-fluid py-2" style="margin-left: 10px; padding: 10px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Question Manager - Chapter {{ $chapterId }}</h2>
        <div>
            <button class="btn btn-success" onclick="exportSampleDocx()">
                <i class="fas fa-download"></i> Export Sample DOCX
            </button>
            <button class="btn btn-info" onclick="document.getElementById('importFile').click()">
                <i class="fas fa-upload"></i> Import
            </button>
            <input type="file" id="importFile" accept=".docx" style="display:none;" onchange="importDocx(event)">
            <button class="btn btn-primary" onclick="showCreateModal()">
                <i class="fas fa-plus"></i> Add Question
            </button>
        </div>
    </div>

    <div id="questions-list" class="row justify-content-center">
        <p>Loading questions...</p>
    </div>
</div>

<!-- Question Modal -->
<div class="modal fade" id="questionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="questionForm">
                    <input type="hidden" id="questionId">
                    <div class="mb-3">
                        <label class="form-label">Question Text</label>
                        <textarea class="form-control" id="questionText" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question Type</label>
                        <select class="form-control" id="questionType" onchange="updateOptionsFields()" required>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                        </select>
                    </div>
                    <div id="optionsContainer">
                        <div class="mb-3">
                            <label class="form-label">Options (one per line)</label>
                            <textarea class="form-control" id="options" rows="4" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D"></textarea>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correct Answer</label>
                        <input type="text" class="form-control" id="correctAnswer" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Explanation (optional)</label>
                        <textarea class="form-control" id="explanation" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points</label>
                        <input type="number" class="form-control" id="points" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order</label>
                        <input type="number" class="form-control" id="orderIndex" value="1" min="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveQuestion()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
const chapterId = {{ $chapterId }};
let editingQuestionId = null;

async function loadQuestions() {
    try {
        const response = await fetch(`/api/chapters/${chapterId}/questions`);
        const questions = await response.json();
        displayQuestions(questions);
    } catch (error) {
        console.error('Error loading questions:', error);
        document.getElementById('questions-list').innerHTML = '<p class="text-danger">Failed to load questions</p>';
    }
}

function displayQuestions(questions) {
    const container = document.getElementById('questions-list');
    
    if (questions.length === 0) {
        container.innerHTML = '<p>No questions yet. Click "Add Question" to create one.</p>';
        return;
    }
    
    container.innerHTML = questions.map((q, index) => `
        <div class="col-md-9 mb-2">
            <div class="card" style="padding: 10px;">
                <div class="card-body" style="padding: 8px;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex: 1; min-width: 0;">
                            <h6 style="margin: 0; font-size: 14px;">${index + 1}. ${q.question_text}</h6>
                            <small class="text-muted" style="display: block; margin-top: 4px;"><strong>Type:</strong> ${q.question_type}</small>
                            ${q.options && Array.isArray(q.options) && q.options.length > 0 ? `<small class="text-muted" style="display: block;"><strong>Options:</strong> ${q.options.join(', ')}</small>` : ''}
                            <small class="text-muted" style="display: block;"><strong>Answer:</strong> ${q.correct_answer}</small>
                            ${q.explanation ? `<small class="text-muted" style="display: block;"><strong>Exp:</strong> ${q.explanation}</small>` : ''}
                            <small class="text-muted" style="display: block;"><strong>Points:</strong> ${q.points}</small>
                        </div>
                        <div style="margin-left: 10px; white-space: nowrap;">
                            <button class="btn btn-sm btn-outline-primary" onclick="editQuestion(${q.id})" style="padding: 4px 8px; font-size: 12px;">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(${q.id})" style="padding: 4px 8px; font-size: 12px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function showCreateModal() {
    editingQuestionId = null;
    document.getElementById('modalTitle').textContent = 'Add Question';
    document.getElementById('questionForm').reset();
    document.getElementById('questionId').value = '';
    new bootstrap.Modal(document.getElementById('questionModal')).show();
}

async function editQuestion(id) {
    try {
        const response = await fetch(`/api/questions/${id}`);
        const question = await response.json();
        
        console.log('Edit question data:', question);
        console.log('Options data:', question.options);
        
        editingQuestionId = id;
        document.getElementById('modalTitle').textContent = 'Edit Question';
        document.getElementById('questionId').value = question.id;
        document.getElementById('questionText').value = question.question_text || '';
        document.getElementById('questionType').value = question.question_type || 'multiple_choice';
        document.getElementById('correctAnswer').value = question.correct_answer || '';
        document.getElementById('explanation').value = question.explanation || '';
        document.getElementById('points').value = question.points || 1;
        document.getElementById('orderIndex').value = question.order_index || 1;
        
        // Update options fields first to set up the UI
        updateOptionsFields();
        
        // Then set the options value after the UI is ready
        setTimeout(() => {
            let optionsText = '';
            if (question.options && Array.isArray(question.options)) {
                optionsText = question.options.join('\n');
            }
            
            const optionsField = document.getElementById('options');
            if (optionsField) {
                optionsField.value = optionsText;
                console.log('Set options field to:', optionsText);
            }
        }, 100);
        
        new bootstrap.Modal(document.getElementById('questionModal')).show();
    } catch (error) {
        console.error('Error loading question:', error);
        alert('Failed to load question');
    }
}

function updateOptionsFields() {
    const type = document.getElementById('questionType').value;
    const container = document.getElementById('optionsContainer');
    
    if (type === 'true_false') {
        container.innerHTML = '<input type="hidden" id="options" value="[&quot;True&quot;,&quot;False&quot;]">';
    } else {
        container.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Options (one per line)</label>
                <textarea class="form-control" id="options" rows="4" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D"></textarea>
            </div>
        `;
    }
}

async function saveQuestion() {
    const data = {
        question_text: document.getElementById('questionText').value,
        question_type: document.getElementById('questionType').value,
        options: document.getElementById('questionType').value === 'true_false' 
            ? JSON.stringify(['True', 'False'])
            : JSON.stringify(document.getElementById('options').value.split('\n').filter(o => o.trim())),
        correct_answer: document.getElementById('correctAnswer').value,
        explanation: document.getElementById('explanation').value,
        points: parseInt(document.getElementById('points').value),
        order_index: parseInt(document.getElementById('orderIndex').value)
    };
    
    try {
        const url = editingQuestionId 
            ? `/api/questions/${editingQuestionId}`
            : `/api/chapters/${chapterId}/questions`;
        const method = editingQuestionId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
            loadQuestions();
            alert(editingQuestionId ? 'Question updated!' : 'Question created!');
        } else {
            alert('Error saving question');
        }
    } catch (error) {
        console.error('Error saving question:', error);
        alert('Error saving question');
    }
}

async function deleteQuestion(id) {
    if (!confirm('Delete this question?')) return;
    
    try {
        const response = await fetch(`/api/questions/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        if (response.ok) {
            loadQuestions();
            alert('Question deleted!');
        } else {
            alert('Error deleting question');
        }
    } catch (error) {
        console.error('Error deleting question:', error);
        alert('Error deleting question');
    }
}

loadQuestions();

async function exportSampleDocx() {
    try {
        const response = await fetch(`/api/chapters/${chapterId}/questions/export-sample`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `questions_sample_chapter_${chapterId}.docx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } else {
            alert('Error exporting sample');
        }
    } catch (error) {
        console.error('Error exporting sample:', error);
        alert('Error exporting sample');
    }
}

async function importDocx(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    console.log('Starting import with file:', file.name);
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
        const response = await fetch(`/api/chapters/${chapterId}/questions/import`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        const result = await response.json();
        console.log('Import response:', result);
        
        if (response.ok) {
            console.log(`Successfully imported ${result.count} questions`);
            console.log('Debug info:', result.debug);
            alert(`Successfully imported ${result.count} questions!\nDebug: ${JSON.stringify(result.debug)}`);
            loadQuestions();
            document.getElementById('importFile').value = '';
        } else {
            console.error('Import error:', result);
            alert('Error importing: ' + (result.message || 'Unknown error') + '\n' + (result.trace || ''));
        }
    } catch (error) {
        console.error('Error importing:', error);
        alert('Error importing file: ' + error.message);
    }
}
</script>
@endsection
