<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Certificates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2 class="mb-4">My Certificates</h2>

        <div id="certificates-container">
            <div class="row" id="certificates-list">
                <!-- Certificates will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Download Form Modal -->
    <div class="modal fade" id="downloadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Certificate Information</h5>
                    <button type="button" class="btn-close" onclick="closeModal()"></button>
                </div>
                <div class="modal-body">
                    <form id="downloadForm">
                        <div class="mb-3">
                            <label class="form-label">Driver License Number *</label>
                            <input type="text" class="form-control" id="driver_license" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Citation Number</label>
                            <input type="text" class="form-control" id="citation_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Citation County</label>
                            <input type="text" class="form-control" id="citation_county">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Traffic School Due Date</label>
                            <input type="date" class="form-control" id="due_date">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Student Address *</label>
                            <textarea class="form-control" id="student_address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="date_of_birth" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Court Name</label>
                            <input type="text" class="form-control" id="court_name">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="downloadCertificate()">Download PDF</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadCertificates() {
            try {
                console.log('Starting to load certificates...');
                
                const response = await fetch('/api/my-certificates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    credentials: 'same-origin'
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                if (!response.ok) {
                    throw new Error('Failed to load certificates');
                }
                
                const data = await response.json();
                console.log('API Response:', data);
                
                // Handle debug response format
                const certificates = data.certificates || data;
                console.log('Certificates array:', certificates);
                console.log('Is array?', Array.isArray(certificates));
                console.log('Length:', certificates.length);
                
                const container = document.getElementById('certificates-list');
                
                if (!Array.isArray(certificates) || certificates.length === 0) {
                    console.log('No certificates found, showing empty message');
                    container.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> You don't have any certificates yet. Complete a course to earn your first certificate!
                            </div>
                        </div>
                    `;
                    return;
                }
                
                console.log('Rendering certificates...');
                container.innerHTML = certificates.map(cert => `
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-certificate text-primary"></i> ${cert.course_name || 'Certificate'}
                                </h5>
                                <p class="card-text">
                                    <strong>Certificate Number:</strong> ${cert.dicds_certificate_number || 'N/A'}<br>
                                    <strong>Completion Date:</strong> ${cert.completion_date ? new Date(cert.completion_date).toLocaleDateString() : 'N/A'}<br>
                                    <strong>Final Score:</strong> ${cert.final_exam_score || 'N/A'}%<br>
                                    <strong>Status:</strong> <span class="badge bg-success">Completed</span>
                                </p>
                                <div class="d-flex gap-2">
                                    <button onclick="showDownloadForm(${cert.id})" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Download PDF
                                    </button>
                                    <a href="/certificates/verify/${cert.verification_hash}" class="btn btn-secondary btn-sm" target="_blank">
                                        <i class="fas fa-check-circle"></i> Verify
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
                
            } catch (error) {
                console.error('Error loading certificates:', error);
                document.getElementById('certificates-list').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Unable to load certificates at this time. Please try again later.
                        </div>
                    </div>
                `;
            }
        }

        let currentCertificateId = null;

        function showDownloadForm(certificateId) {
            currentCertificateId = certificateId;
            document.getElementById('downloadModal').style.display = 'block';
            document.getElementById('downloadModal').classList.add('show');
            document.body.classList.add('modal-open');
        }

        function closeModal() {
            document.getElementById('downloadModal').style.display = 'none';
            document.getElementById('downloadModal').classList.remove('show');
            document.body.classList.remove('modal-open');
        }

        async function downloadCertificate() {
            const form = document.getElementById('downloadForm');
            const formData = new FormData();
            
            formData.append('driver_license_number', document.getElementById('driver_license').value);
            formData.append('citation_number', document.getElementById('citation_number').value);
            formData.append('citation_county', document.getElementById('citation_county').value);
            formData.append('traffic_school_due_date', document.getElementById('due_date').value);
            formData.append('student_address', document.getElementById('student_address').value);
            formData.append('student_date_of_birth', document.getElementById('date_of_birth').value);
            formData.append('court_name', document.getElementById('court_name').value);
            
            try {
                const response = await fetch(`/api/certificates/${currentCertificateId}/download`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData,
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `certificate-${currentCertificateId}.html`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    // Close modal and reset form
                    closeModal();
                    form.reset();
                } else {
                    alert('Error downloading certificate. Please try again.');
                }
            } catch (error) {
                console.error('Download error:', error);
                alert('Error downloading certificate. Please try again.');
            }
        }

        loadCertificates();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <x-footer />
</body>
</html>
