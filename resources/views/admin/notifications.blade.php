<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Push Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2><i class="fas fa-bell"></i> Push Notification System</h2>

        <!-- Send Notification Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Send Real-Time Notification</h5>
            </div>
            <div class="card-body">
                <form id="notificationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">User Email</label>
                                <select class="form-control" id="userEmail" required>
                                    <option value="">Select User</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Notification Type</label>
                                <select class="form-control" id="notificationType">
                                    <option value="info">Info</option>
                                    <option value="success">Success</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="notifTitle" placeholder="Notification title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" id="notifMessage" rows="3" placeholder="Notification message" required></textarea>
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="sendPushNotification()">
                        <i class="fas fa-paper-plane"></i> Send Push Notification
                    </button>
                </form>
            </div>
        </div>

        <!-- Notification History -->
        <div class="card">
            <div class="card-header">
                <h5>Notification History</h5>
            </div>
            <div class="card-body">
                <div id="notificationHistory">
                    <p>No notifications sent yet.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function loadUsers() {
            try {
                const response = await fetch('/web/users', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const users = data.data || data;
                    const userSelect = document.getElementById('userEmail');
                    
                    userSelect.innerHTML = '<option value="">Select User</option>';
                    
                    users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.email;
                        option.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
                        userSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        async function sendPushNotification() {
            const emailEl = document.getElementById('userEmail');
            const typeEl = document.getElementById('notificationType');
            const titleEl = document.getElementById('notifTitle');
            const messageEl = document.getElementById('notifMessage');

            if (!emailEl || !typeEl || !titleEl || !messageEl) {
                alert('Form elements not found');
                return;
            }

            const email = emailEl.value ? emailEl.value.trim() : '';
            const type = typeEl.value ? typeEl.value.trim() : '';
            const title = titleEl.value ? titleEl.value.trim() : '';
            const message = messageEl.value ? messageEl.value.trim() : '';

            console.log('Sending notification:', { email, type, title, message });

            if (!email) {
                alert('Please select a user');
                return;
            }
            if (!title) {
                alert('Please enter notification title');
                return;
            }
            if (!message) {
                alert('Please enter notification message');
                return;
            }

            try {
                const response = await fetch('/api/push-notification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        email: email,
                        type: type,
                        title: title,
                        message: message
                    })
                });

                console.log('Send response status:', response.status);
                const result = await response.json();
                console.log('Send response:', result);

                if (response.ok) {
                    alert('Push notification sent successfully!');
                    document.getElementById('notificationForm').reset();
                    addToHistory(email, type, title, message);
                } else {
                    alert('Failed to send notification: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error sending notification: ' + error.message);
            }
        }

        function addToHistory(email, type, title, message) {
            const history = document.getElementById('notificationHistory');
            const now = new Date().toLocaleString();
            
            const notificationHtml = `
                <div class="alert alert-${type} mb-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${title}</strong><br>
                            <small>To: ${email}</small><br>
                            <span>${message}</span>
                        </div>
                        <small class="text-muted">${now}</small>
                    </div>
                </div>
            `;
            
            if (history.innerHTML.includes('No notifications sent yet')) {
                history.innerHTML = notificationHtml;
            } else {
                history.insertAdjacentHTML('afterbegin', notificationHtml);
            }
        }
        
        // Load users when page loads
        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <x-footer />
</body>
</html>
