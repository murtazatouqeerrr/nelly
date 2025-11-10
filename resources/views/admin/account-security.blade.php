<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Security</title>
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
        <h2>Account Security</h2>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm">
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" required>
                                    <div id="passwordStrength" class="mt-2"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                                <button type="button" onclick="changePassword()" class="btn btn-primary">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Security Settings</h5>
                        </div>
                        <div class="card-body" id="securitySettings">
                            <p>Loading security settings...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Login History</h5>
                        </div>
                        <div class="card-body">
                            <div id="loginHistory">
                                <p>Loading login history...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let passwordStrengthTimer;

        async function loadSecuritySettings() {
            try {
                const response = await fetch('/web/account/security-settings', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const settings = await response.json();
                
                document.getElementById('securitySettings').innerHTML = `
                    <div class="mb-3">
                        <label class="form-label">Two-Factor Authentication</label>
                        <div>
                            <span class="badge ${settings.two_factor_enabled ? 'bg-success' : 'bg-secondary'}">
                                ${settings.two_factor_enabled ? 'Enabled' : 'Disabled'}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Password Change</label>
                        <div>${formatDate(settings.last_password_change)}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Active Sessions</label>
                        <div>${settings.active_sessions}</div>
                    </div>
                `;
                
            } catch (error) {
                console.error('Failed to load security settings:', error);
                document.getElementById('securitySettings').innerHTML = '<p class="text-danger">Error loading settings</p>';
            }
        }

        async function loadLoginHistory() {
            try {
                const response = await fetch('/web/account/login-history', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const history = await response.json();
                
                if (history.length === 0) {
                    document.getElementById('loginHistory').innerHTML = '<p>No login history found.</p>';
                    return;
                }

                const table = `
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Status</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${history.map(attempt => `
                                    <tr>
                                        <td>${formatDate(attempt.attempted_at)}</td>
                                        <td>
                                            <span class="badge ${attempt.successful ? 'bg-success' : 'bg-danger'}">
                                                ${attempt.successful ? 'Success' : 'Failed'}
                                            </span>
                                        </td>
                                        <td>${attempt.ip_address}</td>
                                        <td class="text-truncate" style="max-width: 200px;" title="${attempt.user_agent}">
                                            ${attempt.user_agent}
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('loginHistory').innerHTML = table;
                
            } catch (error) {
                console.error('Failed to load login history:', error);
                document.getElementById('loginHistory').innerHTML = '<p class="text-danger">Error loading login history</p>';
            }
        }

        async function changePassword() {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match');
                return;
            }

            try {
                const response = await fetch('/web/account/password', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword,
                        new_password_confirmation: confirmPassword
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Password changed successfully');
                    document.getElementById('passwordForm').reset();
                    document.getElementById('passwordStrength').innerHTML = '';
                    loadSecuritySettings();
                } else {
                    alert(result.error || 'Failed to change password');
                }
            } catch (error) {
                console.error('Password change error:', error);
                alert('Error changing password');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('newPassword').value;
            
            if (!password) {
                document.getElementById('passwordStrength').innerHTML = '';
                return;
            }

            let score = 0;
            let feedback = [];

            // Length check
            if (password.length >= 8) score += 20;
            else feedback.push('At least 8 characters');

            // Character variety
            if (/[a-z]/.test(password)) score += 20;
            else feedback.push('Lowercase letter');

            if (/[A-Z]/.test(password)) score += 20;
            else feedback.push('Uppercase letter');

            if (/[0-9]/.test(password)) score += 20;
            else feedback.push('Number');

            if (/[^A-Za-z0-9]/.test(password)) score += 20;
            else feedback.push('Special character');

            let strengthClass, strengthText;
            if (score < 40) {
                strengthClass = 'bg-danger';
                strengthText = 'Weak';
            } else if (score < 80) {
                strengthClass = 'bg-warning';
                strengthText = 'Fair';
            } else {
                strengthClass = 'bg-success';
                strengthText = 'Strong';
            }

            const strengthHtml = `
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar ${strengthClass}" style="width: ${score}%"></div>
                </div>
                <small class="text-muted">Strength: ${strengthText}</small>
                ${feedback.length ? `<div class="small text-muted mt-1">Missing: ${feedback.join(', ')}</div>` : ''}
            `;

            document.getElementById('passwordStrength').innerHTML = strengthHtml;
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }

        // Add event listener for password strength checking
        document.getElementById('newPassword').addEventListener('input', function() {
            clearTimeout(passwordStrengthTimer);
            passwordStrengthTimer = setTimeout(checkPasswordStrength, 300);
        });

        loadSecuritySettings();
        loadLoginHistory();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
