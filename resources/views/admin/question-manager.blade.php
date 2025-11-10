@extends('layouts.app')

@section('title', 'Question Manager')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Question Manager - Chapter {{ $chapterId }}</h2>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <i class="fas fa-plus"></i> Add Question
        </button>
    </div>

    <div id="questions-list" class="row">
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
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5>${index + 1}. ${q.question_text}</h5>
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="editQuestion(${q.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(${q.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <p class="mb-2"><strong>Type:</strong> ${q.question_type}</p>
                    ${q.options ? `<p class="mb-2"><strong>Options:</strong> ${JSON.parse(q.options).join(', ')}</p>` : ''}
                    <p class="mb-2"><strong>Correct Answer:</strong> ${q.correct_answer}</p>
                    ${q.explanation ? `<p class="mb-2"><strong>Explanation:</strong> ${q.explanation}</p>` : ''}
                    <p class="mb-0"><strong>Points:</strong> ${q.points}</p>
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
        
        editingQuestionId = id;
        document.getElementById('modalTitle').textContent = 'Edit Question';
        document.getElementById('questionId').value = question.id;
        document.getElementById('questionText').value = question.question_text;
        document.getElementById('questionType').value = question.question_type;
        document.getElementById('options').value = question.options ? JSON.parse(question.options).join('\n') : '';
        document.getElementById('correctAnswer').value = question.correct_answer;
        document.getElementById('explanation').value = question.explanation || '';
        document.getElementById('points').value = question.points;
        document.getElementById('orderIndex').value = question.order_index;
        
        updateOptionsFields();
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
</script>
@endsection
