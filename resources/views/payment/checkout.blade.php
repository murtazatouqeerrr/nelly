<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - {{ $course->title }}</title>
    <script src="https://js.stripe.com/v3/"></script>
    @if(config('payment.paypal.client_id'))
    <script src="https://www.paypal.com/sdk/js?client-id={{ config('payment.paypal.client_id') }}&currency=USD"></script>
    @endif
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .payment-card { background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .course-info { border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .course-title { font-size: 24px; font-weight: bold; color: #333; margin-bottom: 10px; }
        .course-price { font-size: 32px; font-weight: bold; color: #2563eb; }
        .payment-methods { margin-bottom: 30px; }
        .payment-method { border: 2px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s; }
        .payment-method:hover, .payment-method.active { border-color: #2563eb; background: #f8faff; }
        .payment-method h3 { margin-bottom: 10px; }
        .stripe-form, .paypal-form { display: none; margin-top: 20px; }
        .stripe-form.active, .paypal-form.active { display: block; }
        #card-element { padding: 15px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 20px; }
        .btn { padding: 15px 30px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; }
        .loading { display: none; }
        .error { color: #dc2626; margin-top: 10px; }
        .order-summary { background: #f9fafb; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-card">
            <div class="course-info">
                <h1 class="course-title">{{ $course->title }}</h1>
                <p>{{ $course->description ?? 'Complete your enrollment to access this course.' }}</p>
                <div class="course-price">${{ number_format($course->price, 2) }}</div>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                    <span>Course Fee:</span>
                    <span>${{ number_format($course->price, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                    <span>Total:</span>
                    <span>${{ number_format($course->price, 2) }}</span>
                </div>
            </div>

            <div class="payment-methods">
                <h3>Select Payment Method</h3>
                
                <div class="payment-method" onclick="selectPaymentMethod('authorizenet')">
                    <h3>💳 Credit/Debit Card</h3>
                    <p>Pay securely with your credit or debit card</p>
                </div>

                <div class="payment-method" onclick="selectPaymentMethod('dummy')">
                    <h3>🧪 Test Payment (Dummy)</h3>
                    <p>Use this for testing purposes only</p>
                </div>
            </div>

            <div class="stripe-form" id="authorizenet-form">
                <div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #0369a1;">Test Card Information (Sandbox)</h4>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>Card Number:</strong> 4007000000027</p>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>Expiry:</strong> Any future date (e.g., 12/2025)</p>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>CVV:</strong> Any 3 digits (e.g., 123)</p>
                </div>

                <h4>Billing Information</h4>
                <input type="text" id="authnet-billing-address" placeholder="Address" required style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <input type="text" id="authnet-billing-city" placeholder="City" required style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" id="authnet-billing-state" placeholder="State" required style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                    <input type="text" id="authnet-billing-zipcode" placeholder="Zip Code" required style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <input type="text" id="authnet-billing-country" placeholder="Country" required value="USA" style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
                
                <h4>Card Details</h4>
                <input type="text" id="authnet-card-number" placeholder="Card Number" required maxlength="16" style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" id="authnet-expiry-month" placeholder="MM" required maxlength="2" style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                    <input type="text" id="authnet-expiry-year" placeholder="YYYY" required maxlength="4" style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                    <input type="text" id="authnet-cvv" placeholder="CVV" required maxlength="4" style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <div id="authnet-errors" class="error"></div>
                <button class="btn btn-primary" onclick="processAuthorizenetPayment()">
                    <span class="loading">Processing...</span>
                    <span class="btn-text">Pay ${{ number_format($course->price, 2) }}</span>
                </button>
            </div>

            <div class="stripe-form" id="dummy-form">
                <div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #0369a1;">🧪 Test Payment</h4>
                    <p style="margin: 5px 0; font-size: 14px;">This is a dummy payment method for testing purposes only.</p>
                    <p style="margin: 5px 0; font-size: 14px;">Click the button below to complete the test payment.</p>
                </div>
                <button class="btn btn-success" onclick="processDummyPayment()">
                    <span class="loading">Processing...</span>
                    <span class="btn-text">Complete Test Payment - ${{ number_format($course->price, 2) }}</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        let selectedMethod = null;
        let stripe = null;
        let cardElement = null;

        function selectPaymentMethod(method) {
            selectedMethod = method;
            
            // Update UI
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            event.target.closest('.payment-method').classList.add('active');
            
            document.querySelectorAll('.stripe-form, .paypal-form').forEach(el => el.classList.remove('active'));
            document.getElementById(method + '-form').classList.add('active');

            if (method === 'stripe' && !stripe) {
                initializeStripe();
            }
        }

        async function processAuthorizenetPayment() {
            const button = event.target;
            button.disabled = true;
            button.querySelector('.loading').style.display = 'inline';
            button.querySelector('.btn-text').style.display = 'none';

            // Validate inputs
            const cardNumber = document.getElementById('authnet-card-number').value.replace(/\s/g, '');
            const expiryMonth = document.getElementById('authnet-expiry-month').value;
            const expiryYear = document.getElementById('authnet-expiry-year').value;
            const cvv = document.getElementById('authnet-cvv').value;
            const address = document.getElementById('authnet-billing-address').value;
            const city = document.getElementById('authnet-billing-city').value;
            const state = document.getElementById('authnet-billing-state').value;
            const zipcode = document.getElementById('authnet-billing-zipcode').value;
            const country = document.getElementById('authnet-billing-country').value;

            if (!cardNumber || !expiryMonth || !expiryYear || !cvv || !address || !city || !state || !zipcode) {
                document.getElementById('authnet-errors').textContent = 'Please fill in all required fields';
                button.disabled = false;
                button.querySelector('.loading').style.display = 'none';
                button.querySelector('.btn-text').style.display = 'inline';
                return;
            }

            try {
                const response = await fetch('/payment/authorizenet', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        enrollment_id: {{ $enrollment->id }},
                        card_number: cardNumber,
                        expiry_month: expiryMonth,
                        expiry_year: expiryYear,
                        cvv: cvv,
                        address: address,
                        city: city,
                        state: state,
                        country: country,
                        zipcode: zipcode
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    document.getElementById('authnet-errors').textContent = data.error || 'Payment failed';
                    button.disabled = false;
                    button.querySelector('.loading').style.display = 'none';
                    button.querySelector('.btn-text').style.display = 'inline';
                }
            } catch (error) {
                document.getElementById('authnet-errors').textContent = 'Error: ' + error.message;
                button.disabled = false;
                button.querySelector('.loading').style.display = 'none';
                button.querySelector('.btn-text').style.display = 'inline';
            }
        }

        async function processDummyPayment() {
            const button = event.target;
            button.disabled = true;
            button.querySelector('.loading').style.display = 'inline';
            button.querySelector('.btn-text').style.display = 'none';

            try {
                const response = await fetch('/payment/dummy', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        enrollment_id: {{ $enrollment->id }},
                        amount: {{ $course->price }}
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/payment/success?enrollment_id={{ $enrollment->id }}';
                } else {
                    alert('Payment failed: ' + data.error);
                    button.disabled = false;
                    button.querySelector('.loading').style.display = 'none';
                    button.querySelector('.btn-text').style.display = 'inline';
                }
            } catch (error) {
                alert('Error: ' + error.message);
                button.disabled = false;
                button.querySelector('.loading').style.display = 'none';
                button.querySelector('.btn-text').style.display = 'inline';
            }
        }
    </script>
</body>
</html>
