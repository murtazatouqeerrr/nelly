<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All State Transmissions - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --accent: #4a90e2;
            --border: #404040;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --transition: all 0.3s ease;
        }

        body {
            background: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            background: var(--bg-secondary) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
        }

        .table-dark {
            --bs-table-bg: var(--bg-secondary);
            --bs-table-border-color: var(--border);
        }

        .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        .btn-success {
            background: var(--success) !important;
            border-color: var(--success) !important;
        }

        .btn-warning {
            background: var(--warning) !important;
            border-color: var(--warning) !important;
            color: #000 !important;
        }

        .btn-danger {
            background: var(--danger) !important;
            border-color: var(--danger) !important;
        }

        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }

        .status-pending { background-color: var(--warning) !important; color: #000 !important; }
        .status-success { background-color: var(--success) !important; }
        .status-error { background-color: var(--danger) !important; }

        .system-flhsmv { background-color: #ff6b35 !important; }
        .system-tvcc { background-color: #4ecdc4 !important; }
        .system-ntsa { background-color: #45b7d1 !important; }
        .system-ccs { background-color: #96ceb4 !important; }
        .system-ctsi { background-color: #feca57 !important; color: #000 !important; }

        .stats-card {
            background: linear-gradient(135deg, var(--accent), #357abd);
            border: none !important;
            color: white !important;
        }

        .filter-form {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .form-control, .form-select {
            background: var(--bg-primary) !important;
            border: 1px solid var(--border) !important;
            color: var(--text-primary) !important;
        }

        .form-control:focus, .form-select:focus {
            background: var(--bg-primary) !important;
            border-color: var(--accent) !important;
            color: var(--text-primary) !important;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25) !important;
        }

        .pagination .page-link {
            background: var(--bg-secondary) !important;
            border-color: var(--border) !important;
            color: var(--text-primary) !important;
        }

        .pagination .page-link:hover {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        .pagination .page-item.active .page-link {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }
    </style>
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-globe-americas me-2"></i>All State Transmissions</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        <small>Total Transmissions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="background: var(--warning) !important; color: #000 !important;">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                        <small>Pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="background: var(--success) !important;">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['success'] }}</h3>
                        <small>Successful</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card" style="background: var(--danger) !important;">
                    <div class="card-body text-center">
                        <h3 class="mb-0">{{ $stats['error'] }}</h3>
                        <small>Failed</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Breakdown -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Transmissions by State & System</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($stats['by_state'] as $stat)
                                <div class="col-md-3 mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="badge system-{{ strtolower($stat->system) }} me-2">
                                            {{ $stat->state }}-{{ $stat->system }}
                                        </span>
                                        <span>{{ $stat->count }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filter-form">
            <div class="row">
                <div class="col-md-2">
                    <label for="state" class="form-label">State</label>
                    <select name="state" id="state" class="form-select">
                        <option value="">All States</option>
                        <option value="FL" {{ request('state') === 'FL' ? 'selected' : '' }}>Florida</option>
                        <option value="CA" {{ request('state') === 'CA' ? 'selected' : '' }}>California</option>
                        <option value="NV" {{ request('state') === 'NV' ? 'selected' : '' }}>Nevada</option>
                        <option value="TX" {{ request('state') === 'TX' ? 'selected' : '' }}>Texas</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="system" class="form-label">System</label>
                    <select name="system" id="system" class="form-select">
                        <option value="">All Systems</option>
                        <option value="FLHSMV" {{ request('system') === 'FLHSMV' ? 'selected' : '' }}>FLHSMV</option>
                        <option value="TVCC" {{ request('system') === 'TVCC' ? 'selected' : '' }}>TVCC</option>
                        <option value="NTSA" {{ request('system') === 'NTSA' ? 'selected' : '' }}>NTSA</option>
                        <option value="CCS" {{ request('system') === 'CCS' ? 'selected' : '' }}>CCS</option>
                        <option value="CTSI" {{ request('system') === 'CTSI' ? 'selected' : '' }}>CTSI</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Email, name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.state-transmissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>

        <!-- Transmissions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">State Transmissions</h5>
                <small class="text-muted">{{ $transmissions->total() }} total records</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Course</th>
                                <th>State</th>
                                <th>System</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Sent</th>
                                <th>Retries</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transmissions as $transmission)
                                <tr>
                                    <td>{{ $transmission->id }}</td>
                                    <td>
                                        @if($transmission->enrollment && $transmission->enrollment->user)
                                            <div>
                                                <strong>{{ $transmission->enrollment->user->first_name }} {{ $transmission->enrollment->user->last_name }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $transmission->enrollment->user->email }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transmission->enrollment && $transmission->enrollment->course)
                                            <small>{{ $transmission->enrollment->course->title }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $transmission->state }}</span>
                                    </td>
                                    <td>
                                        <span class="badge system-{{ strtolower($transmission->system) }}">
                                            {{ $transmission->system }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-{{ $transmission->status }}">
                                            {{ ucfirst($transmission->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $transmission->created_at->format('M j, Y g:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($transmission->sent_at)
                                            <small>{{ $transmission->sent_at->format('M j, Y g:i A') }}</small>
                                        @else
                                            <span class="text-muted">Not sent</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $transmission->retry_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.state-transmissions.show', $transmission->id) }}" 
                                               class="btn btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($transmission->status === 'pending')
                                                <form method="POST" action="{{ route('admin.state-transmissions.send', $transmission->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Send Now">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($transmission->status === 'error')
                                                <form method="POST" action="{{ route('admin.state-transmissions.retry', $transmission->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-warning" title="Retry">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if(auth()->user()->role->slug === 'super-admin')
                                                <form method="POST" action="{{ route('admin.state-transmissions.destroy', $transmission->id) }}" 
                                                      style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to delete this transmission?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No transmissions found matching your criteria.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($transmissions->hasPages())
                <div class="card-footer">
                    {{ $transmissions->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>