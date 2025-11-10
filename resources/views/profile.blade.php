<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    @include('components.navbar')

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <div id="app">
            <user-profile></user-profile>
        </div>
        
        <!-- Fallback content -->
        <div id="fallback-content">
            <h2>User Profile</h2>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>Profile Information</h5>
                        </div>
                        <div class="card-body" id="profile-info">
                            <p>Loading profile...</p>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Update Profile</h5>
                        </div>
                        <div class="card-body">
                            <form id="profile-form">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        async function loadProfile() {
            try {
                const response = await fetch('/web/user', {
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
                    throw new Error('Failed to load profile');
                }
                
                const user = await response.json();
                
                // Display profile info
                document.getElementById('profile-info').innerHTML = `
                    <p><strong>Name:</strong> ${user.first_name} ${user.last_name}</p>
                    <p><strong>Email:</strong> ${user.email}</p>
                    <p><strong>Role:</strong> ${user.role?.name || 'N/A'}</p>
                    <p><strong>Status:</strong> ${user.status}</p>
                    <p><strong>Member Since:</strong> ${new Date(user.created_at).toLocaleDateString()}</p>
                `;
                
                // Fill form
                document.getElementById('first_name').value = user.first_name;
                document.getElementById('last_name').value = user.last_name;
                document.getElementById('email').value = user.email;
                
            } catch (error) {
                console.error('Error loading profile:', error);
                document.getElementById('profile-info').innerHTML = '<p class="text-danger">Error loading profile.</p>';
            }
        }
        
        document.getElementById('profile-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/web/user', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }
                    throw new Error('Failed to update profile');
                }
                
                alert('Profile updated successfully!');
                loadProfile(); // Reload profile data
                
            } catch (error) {
                console.error('Error updating profile:', error);
                alert('Failed to update profile');
            }
        });
        
        // Show fallback and load profile if Vue doesn't load
        setTimeout(() => {
            const vueApp = document.querySelector('#app user-profile');
            if (!vueApp || vueApp.children.length === 0) {
                document.getElementById('fallback-content').style.display = 'block';
                loadProfile();
            }
        }, 1000);
    </script>
    
    @vite(['resources/js/app.js'])
    <x-footer />
</body>
</html>
