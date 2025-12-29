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
        .btn-success { background: #516425; color: white; }
        .btn-success:hover { background: #3d4b1c; }
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

            <!-- Coupon Section -->
            <div style="background: #f4f6f0; border: 2px dashed #516425; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3 style="margin: 0 0 15px 0; color: #516425;">🎟️ Have a Coupon Code?</h3>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="couponCode" placeholder="Enter coupon code" maxlength="6" style="flex: 1; padding: 12px; border: 1px solid #516425; border-radius: 4px; text-transform: uppercase; font-weight: bold;">
                    <button type="button" class="btn btn-success" onclick="applyCoupon()" style="padding: 12px 24px;">
                        Apply
                    </button>
                </div>
                <div id="couponMessage" style="margin-top: 10px;"></div>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                    <span>Course Fee:</span>
                    <span id="originalPriceDisplay">${{ number_format($course->price, 2) }}</span>
                </div>
                <div id="discountRow" style="display: none; justify-content: space-between; margin-top: 10px; color: #516425;">
                    <span>Discount:</span>
                    <span>-$<span id="discountAmount">0.00</span></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                    <span>Total:</span>
                    <span id="finalPriceDisplay">${{ number_format($course->price, 2) }}</span>
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
        const originalPrice = {{ $course->price }};
        let appliedCoupon = null;

        function selectPaymentMethod(method) {
            selectedMethod = method;
            
            // Update UI safely
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            
            const clickedElement = event.target?.closest('.payment-method');
            if (clickedElement) {
                clickedElement.classList.add('active');
            }
            
            // Hide all forms first
            document.querySelectorAll('.stripe-form, .paypal-form').forEach(el => el.classList.remove('active'));
            
            // Show selected form
            const targetForm = document.getElementById(method + '-form');
            if (targetForm) {
                targetForm.classList.add('active');
            }

            if (method === 'stripe' && !stripe) {
                initializeStripe();
            }
        }

        async function processAuthorizenetPayment() {
            const button = event.target;
            if (!button) return;
            
            button.disabled = true;
            
            // Safely handle loading state
            const loadingSpan = button.querySelector('.loading');
            const btnTextSpan = button.querySelector('.btn-text');
            
            if (loadingSpan) loadingSpan.style.display = 'inline';
            if (btnTextSpan) btnTextSpan.style.display = 'none';

            // Validate inputs
            const cardNumber = document.getElementById('authnet-card-number')?.value?.replace(/\s/g, '') || '';
            const expiryMonth = document.getElementById('authnet-expiry-month')?.value || '';
            const expiryYear = document.getElementById('authnet-expiry-year')?.value || '';
            const cvv = document.getElementById('authnet-cvv')?.value || '';
            const address = document.getElementById('authnet-billing-address')?.value || '';
            const city = document.getElementById('authnet-billing-city')?.value || '';
            const state = document.getElementById('authnet-billing-state')?.value || '';
            const zipcode = document.getElementById('authnet-billing-zipcode')?.value || '';
            const country = document.getElementById('authnet-billing-country')?.value || '';

            if (!cardNumber || !expiryMonth || !expiryYear || !cvv || !address || !city || !state || !zipcode) {
                const errorDiv = document.getElementById('authnet-errors');
                if (errorDiv) errorDiv.textContent = 'Please fill in all required fields';
                resetButton();
                return;
            }

            const finalAmount = appliedCoupon ? 
                (originalPrice - (appliedCoupon.type === 'percentage' ? 
                    (originalPrice * appliedCoupon.amount / 100) : 
                    Math.min(appliedCoupon.amount, originalPrice))) : 
                originalPrice;

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
                        zipcode: zipcode,
                        amount: finalAmount,
                        original_amount: originalPrice,
                        coupon_code: appliedCoupon ? appliedCoupon.code : null,
                        discount_amount: appliedCoupon ? (originalPrice - finalAmount) : 0
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    const errorDiv = document.getElementById('authnet-errors');
                    if (errorDiv) errorDiv.textContent = data.error || 'Payment failed';
                    resetButton();
                }
            } catch (error) {
                console.error('Payment error:', error);
                const errorDiv = document.getElementById('authnet-errors');
                if (errorDiv) errorDiv.textContent = 'Error: ' + error.message;
                resetButton();
            }
            
            function resetButton() {
                if (button) {
                    button.disabled = false;
                    if (loadingSpan) loadingSpan.style.display = 'none';
                    if (btnTextSpan) btnTextSpan.style.display = 'inline';
                }
            }
        }

        async function applyCoupon() {
            const couponCode = document.getElementById('couponCode').value.trim().toUpperCase();
            const messageDiv = document.getElementById('couponMessage');
            
            if (!couponCode) {
                showCouponMessage('Please enter a coupon code', 'error');
                return;
            }
            
            // Show loading state
            const applyBtn = event.target;
            const originalText = applyBtn.innerHTML;
            applyBtn.innerHTML = 'Applying...';
            applyBtn.disabled = true;
            
            try {
                const response = await fetch('/api/coupons/apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: couponCode,
                        amount: originalPrice
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.valid) {
                    appliedCoupon = data.coupon;
                    updatePriceDisplay(data.discount, data.final_amount);
                    showCouponMessage(`Coupon applied! You saved $${data.discount.toFixed(2)}`, 'success');
                    
                    // Change button to "Remove"
                    applyBtn.innerHTML = 'Remove';
                    applyBtn.onclick = removeCoupon;
                    applyBtn.style.background = '#dc2626';
                } else {
                    showCouponMessage(data.error || 'Invalid coupon code', 'error');
                }
            } catch (error) {
                console.error('Coupon error:', error);
                showCouponMessage('Error applying coupon. Please try again.', 'error');
            } finally {
                if (!appliedCoupon) {
                    applyBtn.innerHTML = originalText;
                }
                applyBtn.disabled = false;
            }
        }
        
        function removeCoupon() {
            appliedCoupon = null;
            updatePriceDisplay(0, originalPrice);
            showCouponMessage('', '');
            
            // Reset button
            const applyBtn = event.target;
            applyBtn.innerHTML = 'Apply';
            applyBtn.onclick = applyCoupon;
            applyBtn.style.background = '#516425';
            
            // Clear coupon code
            document.getElementById('couponCode').value = '';
        }
        
        function updatePriceDisplay(discount, finalAmount) {
            const discountRow = document.getElementById('discountRow');
            const discountAmountSpan = document.getElementById('discountAmount');
            const finalPriceDisplay = document.getElementById('finalPriceDisplay');
            
            if (discount > 0) {
                if (discountRow) discountRow.style.display = 'flex';
                if (discountAmountSpan) discountAmountSpan.textContent = discount.toFixed(2);
                if (finalPriceDisplay) finalPriceDisplay.textContent = '$' + finalAmount.toFixed(2);
                
                // Update button text safely
                document.querySelectorAll('.btn-text').forEach(btn => {
                    if (btn && (btn.textContent.includes('Pay $') || btn.textContent.includes('Complete Test Payment'))) {
                        btn.textContent = btn.textContent.replace(/\$[\d,]+\.?\d*/, '$' + finalAmount.toFixed(2));
                    }
                });
            } else {
                if (discountRow) discountRow.style.display = 'none';
                if (finalPriceDisplay) finalPriceDisplay.textContent = '$' + originalPrice.toFixed(2);
                
                // Reset button text safely
                document.querySelectorAll('.btn-text').forEach(btn => {
                    if (btn && (btn.textContent.includes('Pay $') || btn.textContent.includes('Complete Test Payment'))) {
                        btn.textContent = btn.textContent.replace(/\$[\d,]+\.?\d*/, '$' + originalPrice.toFixed(2));
                    }
                });
            }
        }
        
        function showCouponMessage(message, type) {
            const messageDiv = document.getElementById('couponMessage');
            if (message) {
                const color = type === 'success' ? '#516425' : '#dc2626';
                const bgColor = type === 'success' ? '#f4f6f0' : '#fef2f2';
                messageDiv.innerHTML = `<div style="padding: 10px; background: ${bgColor}; color: ${color}; border-radius: 4px; font-size: 14px;">${message}</div>`;
            } else {
                messageDiv.innerHTML = '';
            }
        }

        async function processDummyPayment() {
            const button = event.target;
            if (!button) return;
            
            button.disabled = true;
            
            // Safely handle loading state
            const loadingSpan = button.querySelector('.loading');
            const btnTextSpan = button.querySelector('.btn-text');
            
            if (loadingSpan) loadingSpan.style.display = 'inline';
            if (btnTextSpan) btnTextSpan.style.display = 'none';

            const finalAmount = appliedCoupon ? 
                (originalPrice - (appliedCoupon.calculateDiscount ? appliedCoupon.calculateDiscount(originalPrice) : 
                    (appliedCoupon.type === 'percentage' ? 
                        (originalPrice * appliedCoupon.amount / 100) : 
                        Math.min(appliedCoupon.amount, originalPrice)))) : 
                originalPrice;

            try {
                const response = await fetch('/payment/dummy', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        enrollment_id: {{ $enrollment->id }},
                        amount: finalAmount,
                        original_amount: originalPrice,
                        coupon_code: appliedCoupon ? appliedCoupon.code : null,
                        discount_amount: appliedCoupon ? (originalPrice - finalAmount) : 0
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    window.location.href = '/payment/success?enrollment_id={{ $enrollment->id }}';
                } else {
                    alert('Payment failed: ' + (data.error || 'Unknown error'));
                    resetButton();
                }
            } catch (error) {
                console.error('Payment error:', error);
                alert('Error: ' + error.message);
                resetButton();
            }
            
            function resetButton() {
                if (button) {
                    button.disabled = false;
                    if (loadingSpan) loadingSpan.style.display = 'none';
                    if (btnTextSpan) btnTextSpan.style.display = 'inline';
                }
            }
        }

        // Auto-uppercase coupon code input
        document.getElementById('couponCode').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
        
        // Allow Enter key to apply coupon
        document.getElementById('couponCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCoupon();
            }
        });
    </script>
</body>
</html>
