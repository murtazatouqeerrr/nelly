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
                
                <div class="payment-method" onclick="selectPaymentMethod('stripe')">
                    <h3>💳 Credit/Debit Card</h3>
                    <p>Pay securely with your credit or debit card via Stripe</p>
                </div>

                <div class="payment-method" onclick="selectPaymentMethod('dummy')">
                    <h3>🧪 Test Payment (Dummy)</h3>
                    <p>Use this for testing purposes only</p>
                </div>
            </div>

            <div class="stripe-form" id="stripe-form">
                <div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: #0369a1;">Test Card Information</h4>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>Card Number:</strong> 4242 4242 4242 4242</p>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>Expiry:</strong> Any future date (e.g., 12/34)</p>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>CVC:</strong> Any 3 digits (e.g., 123)</p>
                    <p style="margin: 5px 0; font-size: 14px;"><strong>ZIP:</strong> Any 5 digits (e.g., 12345)</p>
                </div>

                <h4>Billing Information</h4>
                <input type="text" id="billing-address" placeholder="Address" required style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <input type="text" id="billing-city" placeholder="City" required style="width: 100%; padding: 12px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" id="billing-state" placeholder="State" required style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                    <input type="text" id="billing-zipcode" placeholder="Zip Code" required style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                <input type="text" id="billing-country" placeholder="Country" required value="USA" style="width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px;">
                
                <h4>Card Details</h4>
                <div id="card-element"></div>
                <div id="card-errors" class="error"></div>
                <button class="btn btn-primary" onclick="processStripePayment()">
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

        function initializeStripe() {
            const stripeKey = '{{ config("payment.stripe.public_key") ?: config("services.stripe.key") ?: env("STRIPE_KEY") }}';
            
            if (!stripeKey || stripeKey.includes('your_stripe')) {
                document.getElementById('card-errors').textContent = 'Stripe is not configured. Please contact support.';
                return;
            }
            
            stripe = Stripe(stripeKey);
            const elements = stripe.elements();
            
            cardElement = elements.create('card', {
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#424770',
                        '::placeholder': { color: '#aab7c4' }
                    }
                }
            });
            
            cardElement.mount('#card-element');
            cardElement.on('change', ({error}) => {
                const displayError = document.getElementById('card-errors');
                displayError.textContent = error ? error.message : '';
            });
        }

        async function processStripePayment() {
            if (!stripe || !cardElement) return;

            const button = event.target;
            button.disabled = true;
            button.querySelector('.loading').style.display = 'inline';
            button.querySelector('.btn-text').style.display = 'none';

            const {paymentMethod, error} = await stripe.createPaymentMethod({
                type: 'card',
                card: cardElement,
            });

            if (error) {
                document.getElementById('card-errors').textContent = error.message;
                button.disabled = false;
                button.querySelector('.loading').style.display = 'none';
                button.querySelector('.btn-text').style.display = 'inline';
                return;
            }

            // Send to server
            fetch('/payment/stripe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    enrollment_id: {{ $enrollment->id }},
                    payment_method_id: paymentMethod.id,
                    address: document.getElementById('billing-address').value,
                    city: document.getElementById('billing-city').value,
                    state: document.getElementById('billing-state').value,
                    country: document.getElementById('billing-country').value,
                    zipcode: document.getElementById('billing-zipcode').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    document.getElementById('card-errors').textContent = data.error;
                    button.disabled = false;
                    button.querySelector('.loading').style.display = 'none';
                    button.querySelector('.btn-text').style.display = 'inline';
                }
            });
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
