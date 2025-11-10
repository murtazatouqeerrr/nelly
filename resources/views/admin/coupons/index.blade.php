@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags me-2"></i>Manage Coupons</h2>
        <button class="btn btn-primary" onclick="showCreateForm()">
            <i class="fas fa-plus me-2"></i>Create Coupon
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Existing Coupons</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Expires</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($coupons as $coupon)
                                <tr>
                                    <td><code>{{ $coupon->code }}</code></td>
                                    <td>
                                        @if($coupon->type === 'percentage')
                                            {{ $coupon->amount }}%
                                        @else
                                            ${{ $coupon->amount }}
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($coupon->type) }}</td>
                                    <td>
                                        @if($coupon->is_used)
                                            <span class="badge bg-secondary">Used</span>
                                        @elseif($coupon->is_active && (!$coupon->expires_at || $coupon->expires_at->isFuture()))
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($coupon->expires_at)
                                            {{ $coupon->expires_at->format('M d, Y') }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editCoupon({{ $coupon->id }})">Edit</button>
                                        <form method="POST" action="/admin/coupons/{{ $coupon->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this coupon?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card" id="coupon-form" style="display: none;">
                <div class="card-header">
                    <h5 id="form-title">Create Coupon</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/coupons" id="couponForm">
                        @csrf
                        <input type="hidden" id="coupon-id" name="coupon_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Code (optional - auto-generated if empty)</label>
                            <input type="text" class="form-control" name="code" id="code" maxlength="6" placeholder="Leave empty for auto-generation">
                            <small class="text-muted">Max 6 characters. Leave empty to auto-generate 4-digit codes.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" min="1" max="100" value="1" required>
                            <small class="text-muted">Number of coupons to generate (1-100)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" id="type" required>
                                <option value="fixed">Fixed Amount ($)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="0" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Expires At (optional)</label>
                            <input type="datetime-local" class="form-control" name="expires_at" id="expires_at">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Save</button>
                            <button type="button" class="btn btn-secondary" onclick="hideForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showCreateForm() {
    document.getElementById('form-title').textContent = 'Create Coupon(s)';
    document.getElementById('couponForm').action = '/admin/coupons';
    document.getElementById('couponForm').reset();
    document.getElementById('coupon-id').value = '';
    document.getElementById('quantity').value = '1';
    document.getElementById('coupon-form').style.display = 'block';
}

function editCoupon(id) {
    alert('Edit functionality: Single coupons can only be activated/deactivated once created');
}

function hideForm() {
    document.getElementById('coupon-form').style.display = 'none';
}

// Auto-generate code preview
document.getElementById('code').addEventListener('input', function() {
    const quantity = document.getElementById('quantity').value;
    if (this.value && quantity > 1) {
        document.querySelector('small').textContent = 'Custom code will have numbers appended for multiple quantities (e.g., CODE1, CODE2, ...)';
    } else {
        document.querySelector('small').textContent = 'Max 6 characters. Leave empty to auto-generate 4-digit codes.';
    }
});

document.getElementById('quantity').addEventListener('input', function() {
    const code = document.getElementById('code').value;
    if (code && this.value > 1) {
        document.querySelector('small').textContent = 'Custom code will have numbers appended for multiple quantities (e.g., CODE1, CODE2, ...)';
    }
});
</script>
@endsection
