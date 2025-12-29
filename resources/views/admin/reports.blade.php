@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container-fluid py-4" style="margin-left: 300px; max-width: calc(100% - 320px); padding: 20px;">
        <h2>Reports & Analytics</h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Generate Report</h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <form id="report-form" class="flex-grow-1">
                            <div class="mb-3">
                                <label>Report Type</label>
                                <select name="report_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="enrollment">Enrollment Report</option>
                                    <option value="revenue">Revenue Report</option>
                                    <option value="completion">Completion Report</option>
                                    <option value="compliance">Compliance Report</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>State (Optional)</label>
                                <select name="state_code" class="form-select">
                                    <option value="">All States</option>
                                    <option value="FL">Florida</option>
                                    <option value="CA">California</option>
                                    <option value="TX">Texas</option>
                                    <option value="NY">New York</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary mt-auto">Generate Report</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Report Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="report-results">
                            <p class="text-muted">Select report parameters and click "Generate Report" to view results.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Saved Reports</h5>
                    </div>
                    <div class="card-body">
                        <div id="saved-reports">
                            <p>Loading saved reports...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
        }
        
        document.getElementById('report-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            
            try {
                document.getElementById('report-results').innerHTML = '<p>Generating report...</p>';
                
                const response = await fetch(`/web/admin/reports/generate?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayReportResults(data, formData.get('report_type'));
                } else {
                    document.getElementById('report-results').innerHTML = '<p class="text-danger">Error generating report.</p>';
                }
            } catch (error) {
                console.error('Error generating report:', error);
                document.getElementById('report-results').innerHTML = '<p class="text-danger">Error generating report.</p>';
            }
        });
        
        function displayReportResults(response, reportType) {
            const container = document.getElementById('report-results');
            
            // Handle both array and object responses
            const data = Array.isArray(response) ? response : (response.detailed_data || response.data || response.enrollments || []);
            const summary = response.summary || null;
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p>No data found for the selected criteria.</p>';
                return;
            }
            
            let html = `<h6>${reportType.charAt(0).toUpperCase() + reportType.slice(1)} Report (${data.length} records)</h6>`;
            
            // Display summary if available
            if (summary) {
                html += `
                    <div class="alert alert-info mb-3">
                        <strong>Summary:</strong><br>
                        ${Object.entries(summary).map(([key, value]) => 
                            `${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}: ${value}`
                        ).join('<br>')}
                    </div>
                `;
            }
            
            if (reportType === 'enrollment') {
                html += `
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Enrolled</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(item => `
                                    <tr>
                                        <td>${item.student_name || (item.user?.first_name + ' ' + item.user?.last_name) || 'N/A'}</td>
                                        <td>${item.student_email || item.user?.email || 'N/A'}</td>
                                        <td>${item.course_title || item.course?.title || 'N/A'}</td>
                                        <td>${item.enrolled_date || (item.enrolled_at ? new Date(item.enrolled_at).toLocaleDateString() : 'N/A')}</td>
                                        <td><span class="badge bg-${item.status === 'completed' ? 'success' : 'primary'}">${item.status || 'N/A'}</span></td>
                                        <td>${item.progress || item.progress_percentage || 0}%</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else if (reportType === 'revenue') {
                const totalRevenue = data.reduce((sum, item) => sum + parseFloat(item.amount_paid || item.amount || 0), 0);
                html += `
                    <div class="mb-3">
                        <strong>Total Revenue: $${totalRevenue.toFixed(2)}</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(item => `
                                    <tr>
                                        <td>${item.student_name || (item.user?.first_name + ' ' + item.user?.last_name) || 'N/A'}</td>
                                        <td>${item.course_title || item.course?.title || 'N/A'}</td>
                                        <td>$${item.amount_paid || item.amount || 0}</td>
                                        <td>${new Date(item.enrolled_at).toLocaleDateString()}</td>
                                        <td><span class="badge bg-success">${item.payment_status}</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                html += `<div class="table-responsive"><table class="table table-sm"><tbody>`;
                data.forEach(item => {
                    html += `<tr><td>${JSON.stringify(item)}</td></tr>`;
                });
                html += `</tbody></table></div>`;
            }
            
            container.innerHTML = html;
        }
        
        async function loadSavedReports() {
            try {
                const response = await fetch('/web/admin/reports', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const reports = Array.isArray(data) ? data : (data.data || []);
                    const container = document.getElementById('saved-reports');
                    
                    if (reports.length === 0) {
                        container.innerHTML = '<p>No saved reports found.</p>';
                        return;
                    }
                    
                    container.innerHTML = reports.map(report => `
                        <div class="border rounded p-2 mb-2">
                            <strong>${report.name || 'Unnamed Report'}</strong> - ${report.type || 'N/A'}
                            <small class="text-muted d-block">Created by ${report.creator?.first_name || 'Unknown'} ${report.creator?.last_name || ''}</small>
                        </div>
                    `).join('');
                } else {
                    document.getElementById('saved-reports').innerHTML = '<p class="text-danger">Error loading saved reports.</p>';
                }
            } catch (error) {
                console.error('Error loading saved reports:', error);
                document.getElementById('saved-reports').innerHTML = '<p class="text-danger">Error loading saved reports.</p>';
            }
        }
        
        // Load saved reports on page load
        loadSavedReports();
    </script>
</div>
@endsection
