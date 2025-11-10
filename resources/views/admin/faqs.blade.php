@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-question-circle me-2"></i>FAQs Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFaqModal">
            <i class="fas fa-plus me-2"></i>Add FAQ
        </button>
    </div>
    <div id="faqs"></div>
</div>

<!-- Add FAQ Modal -->
<div class="modal fade" id="addFaqModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="faqForm">
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Question</label>
                        <input type="text" class="form-control" id="question" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Answer</label>
                        <textarea class="form-control" id="answer" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveFaq()">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadFaqs() {
    fetch('/api/faq', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('faqs');
        if (Object.keys(data).length > 0) {
            let html = '';
            for (const [category, faqs] of Object.entries(data)) {
                html += `<h4>${category || 'Uncategorized'}</h4><div class="accordion mb-4">`;
                faqs.forEach((faq, index) => {
                    html += `
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq${faq.id}">
                                    ${faq.question}
                                </button>
                            </h2>
                            <div id="faq${faq.id}" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    ${faq.answer}
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-danger" onclick="deleteFaq(${faq.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                html += '</div>';
            }
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p>No FAQs found.</p>';
        }
    })
    .catch(error => {
        document.getElementById('faqs').innerHTML = '<p class="text-danger">Error loading FAQs</p>';
    });
}

function saveFaq() {
    const data = {
        category: document.getElementById('category').value,
        question: document.getElementById('question').value,
        answer: document.getElementById('answer').value
    };
    
    fetch('/api/faqs', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(() => {
        bootstrap.Modal.getInstance(document.getElementById('addFaqModal')).hide();
        document.getElementById('faqForm').reset();
        loadFaqs();
    })
    .catch(error => console.error('Error saving FAQ:', error));
}

function deleteFaq(id) {
    if (confirm('Delete this FAQ?')) {
        fetch(`/api/faqs/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(() => loadFaqs())
        .catch(error => console.error('Error deleting FAQ:', error));
    }
}

document.addEventListener('DOMContentLoaded', loadFaqs);
</script>
@endsection
