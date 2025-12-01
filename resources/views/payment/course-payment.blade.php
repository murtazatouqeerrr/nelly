<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Course Payment - {{ $course->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .payment-body {
            padding: 30px;
        }
        .course-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .price-tag {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .payment-method.selected {
            border-color: #667eea;
            background: #e7f0ff;
        }
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
            width: 100%;
            color: white;
            transition: transform 0.2s;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="payment-card">
        <div class="payment-header">
            <i class="fas fa-graduation-cap fa-3x mb-3"></i>
            <h2>Complete Your Enrollment</h2>
            <p class="mb-0">Secure payment for your course</p>
        </div>
        
        <div class="payment-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            <div class="course-info">
                <h4><i class="fas fa-book text-primary"></i> {{ $course->title }}</h4>
                <p class="text-muted mb-2">{{ $course->description }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock"></i> Duration: {{ $course->duration }} hours</span>
                    <span><i class="fas fa-map-marker-alt"></i> {{ $course->state_code }}</span>
                </div>
            </div>
            
            <div class="text-center">
                <div class="price-tag">
                    ${{ number_format($course->price ?? 29.99, 2) }}
                </div>
            </div>
            
            <form action="{{ route('payment.process') }}" method="POST" id="paymentForm">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}">
                <input type="hidden" name="table" value="{{ $table ?? 'florida_courses' }}">
                <input type="hidden" name="amount" value="{{ $course->price ?? 29.99 }}">
                
                <h5 class="mb-3">Select Payment Method</h5>
                
                <div class="payment-method" onclick="selectPaymentMethod('stripe')">
                    <label class="d-flex align-items-center mb-0">
                        <input type="radio" name="payment_method" value="stripe" required>
                        <div class="flex-grow-1">
                            <strong><i class="fab fa-cc-stripe"></i> Credit/Debit Card</strong>
                            <div class="text-muted small">Pay securely with Stripe</div>
                        </div>
                        <i class="fas fa-credit-card fa-2x text-primary"></i>
                    </label>
                </div>
                
                <div class="payment-method" onclick="selectPaymentMethod('paypal')">
                    <label class="d-flex align-items-center mb-0">
                        <input type="radio" name="payment_method" value="paypal" required>
                        <div class="flex-grow-1">
                            <strong><i class="fab fa-paypal"></i> PayPal</strong>
                            <div class="text-muted small">Pay with your PayPal account</div>
                        </div>
                        <i class="fab fa-paypal fa-2x text-info"></i>
                    </label>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-pay">
                        <i class="fas fa-lock me-2"></i>Complete Payment
                    </button>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> Your payment is secure and encrypted
                    </small>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[value="${method}"]`).checked = true;
        }
        
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedMethod) {
                e.preventDefault();
                alert('Please select a payment method');
            }
        });
    </script>
</body>
</html>
