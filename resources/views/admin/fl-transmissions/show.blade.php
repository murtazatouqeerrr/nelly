@extends('layouts.app')
@section('title', 'Transmission Details - ' . $transmission->id)
@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col">
            <a href="{{ route('admin.fl-transmissions.index') }}" class="btn btn-outline-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <h1 class="h3">Transmission Details #{{ $transmission->id }}</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Transmission Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">ID:</th>
                            <td>{{ $transmission->id }}</td>
                        </tr>
                        <tr>
                            <th>State:</th>
                            <td><span class="badge bg-primary">{{ $transmission->state }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($transmission->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($transmission->status === 'success')
                                    <span class="badge bg-success">Success</span>
                                @else
                                    <span class="badge bg-danger">Error</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Retry Count:</th>
                            <td>{{ $transmission->retry_count }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $transmission->created_at->format('M d, Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Sent At:</th>
                            <td>{{ $transmission->sent_at?->format('M d, Y H:i:s') ?? 'Not sent' }}</td>
                        </tr>
                        <tr>
                            <th>Response Code:</th>
                            <td><code>{{ $transmission->response_code ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>Response Message:</th>
                            <td class="{{ $transmission->status === 'error' ? 'text-danger' : '' }}">
                                {{ $transmission->response_message ?? 'N/A' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Name:</th>
                            <td>
                                {{ $transmission->enrollment->user->first_name ?? '' }} 
                                {{ $transmission->enrollment->user->last_name ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $transmission->enrollment->user->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Driver License:</th>
                            <td>{{ $transmission->enrollment->user->driver_license_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Citation Number:</th>
                            <td>{{ $transmission->enrollment->citation_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Court Case Number:</th>
                            <td>{{ $transmission->enrollment->user->court_case_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Course:</th>
                            <td>{{ $transmission->enrollment->course->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Completion Date:</th>
                            <td>{{ $transmission->enrollment->completed_at?->format('M d, Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Certificate Number:</th>
                            <td>{{ $transmission->enrollment->floridaCertificate->dicds_certificate_number ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($transmission->payload_json)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payload Data</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ json_encode($transmission->payload_json, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Actions</h5>
        </div>
        <div class="card-body">
            @if($transmission->status === 'pending')
                <form action="{{ route('admin.fl-transmissions.send', $transmission->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Send this transmission?');">
                        <i class="bi bi-send"></i> Send Now
                    </button>
                </form>
            @endif

            @if($transmission->status === 'error')
                <form action="{{ route('admin.fl-transmissions.retry', $transmission->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Retry this transmission?');">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </form>
            @endif

            <a href="/admin/enrollments/{{ $transmission->enrollment_id }}" class="btn btn-outline-primary" target="_blank">
                <i class="bi bi-pencil"></i> Edit Enrollment
            </a>

            @if(auth()->user()->role === 'super_admin')
                <form action="{{ route('admin.fl-transmissions.destroy', $transmission->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this transmission record?');">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection
