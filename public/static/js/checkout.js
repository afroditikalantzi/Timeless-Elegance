// Initialize checkout on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load cart data from localStorage
    loadCartData();
    
    // Initialize payment method selection
    initPaymentMethods();
    
    // Initialize navigation buttons
    initNavigation();
});

function loadCartData() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    

    
    // Load order summary
    let orderSummaryHTML = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        orderSummaryHTML += `
            <div class="order-summary-row">
                <span>${item.name} x ${item.quantity}</span>
                <span>${itemTotal.toFixed(2)}€</span>
            </div>
        `;
    });
    
    document.getElementById('order-summary-items').innerHTML = orderSummaryHTML;
    
    // Calculate shipping and total
    const shipping = subtotal >= 50 ? 0 : 4.99;
    const total = subtotal + shipping;
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + '€';
    document.getElementById('shipping').textContent = shipping.toFixed(2) + '€';
    document.getElementById('total').textContent = total.toFixed(2) + '€';
    
    // Load order items for review section
    let orderItemsHTML = '';
    
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        
        orderItemsHTML += `
            <div class="order-item">
                <div class="order-item-details">
                    <div class="order-item-name">${item.name} x ${item.quantity}</div>
                    <div class="order-item-meta">
                        <span>Size: ${item.size}</span> • 
                        <span>Color: ${item.color}</span>
                    </div>
                </div>
                <div class="order-item-price">${itemTotal.toFixed(2)}€</div>
            </div>
        `;
    });
    
    document.getElementById('order-items').innerHTML = orderItemsHTML;
}

function initPaymentMethods() {
    const paymentMethods = document.querySelectorAll('.payment-method');
    
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Remove selected class from all methods
            paymentMethods.forEach(m => m.classList.remove('selected'));
            
            // Add selected class to clicked method
            this.classList.add('selected');
            
            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
        });
    });
    
    // Select the first payment method by default
    paymentMethods[0].click();
}

function initNavigation() {
    // Shipping section navigation
    document.getElementById('shipping-next').addEventListener('click', function() {
        if (validateShippingForm()) {
            goToSection('payment-section');
        }
    });
    
    // Payment section navigation
    document.getElementById('payment-back').addEventListener('click', function() {
        goToSection('shipping-section');
    });
    
    document.getElementById('payment-next').addEventListener('click', function() {
        if (validatePaymentForm()) {
            updateReviewSection();
            goToSection('review-section');
        }
    });
    
    // Review section navigation
    document.getElementById('review-back').addEventListener('click', function() {
        goToSection('payment-section');
    });
    
    document.getElementById('place-order').addEventListener('click', function() {
        placeOrder();
    });
}

function validateShippingForm() {
    // This function is now overridden by the custom validation in checkout-validation.js
    // We keep it here for backward compatibility
    if (typeof validateShippingFormCustom === 'function') {
        return validateShippingFormCustom();
    } else {
        const form = document.getElementById('shipping-form');
        
        // Check if form is valid
        if (!form.checkValidity()) {
            // Trigger browser's native form validation
            const submitButton = document.createElement('button');
            submitButton.type = 'submit';
            form.appendChild(submitButton);
            submitButton.click();
            submitButton.remove();
            
            return false;
        }
        
        return true;
    }
}

function validatePaymentForm() {
    const selectedMethod = document.querySelector('.payment-method.selected');
    const paymentMethod = selectedMethod.getAttribute('data-method');
    
    if (paymentMethod === 'credit-card') {
        const cardNumber = document.getElementById('cardNumber').value;
        const expiryDate = document.getElementById('expiryDate').value;
        const cvv = document.getElementById('cvv').value;
        const nameOnCard = document.getElementById('nameOnCard').value;
        
        if (!cardNumber || !expiryDate || !cvv || !nameOnCard) {
            alert('Please fill in all credit card details.');
            return false;
        }
    }
    
    return true;
}

function updateReviewSection() {
    // Update shipping summary
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const address = document.getElementById('address').value;
    const city = document.getElementById('city').value;
    const state = document.getElementById('state').value;
    const zip = document.getElementById('zip').value;
    const country = document.getElementById('country').options[document.getElementById('country').selectedIndex].text;
    
    const shippingSummaryHTML = `
        <p><strong>${firstName} ${lastName}</strong><br>
        ${address}<br>
        ${city}, ${state} ${zip}<br>
        ${country}</p>
    `;
    
    document.getElementById('shipping-summary').innerHTML = shippingSummaryHTML;
    
    // Update payment summary
    const selectedMethod = document.querySelector('.payment-method.selected');
    const paymentMethod = selectedMethod.getAttribute('data-method');

    let paymentSummaryHTML = '';

    if (paymentMethod === 'credit-card') {
        // Show only the method name, not the card number
        paymentSummaryHTML = `
            <p><i class="bi bi-credit-card"></i> Credit Card</p>
        `;
    } else if (paymentMethod === 'paypal') {
        paymentSummaryHTML = `
            <p><i class="bi bi-paypal"></i> PayPal</p>
        `;
    } else if (paymentMethod === 'bank-transfer') {
        paymentSummaryHTML = `
            <p><i class="bi bi-bank"></i> Bank Transfer</p>
        `;
    }

    document.getElementById('payment-summary').innerHTML = paymentSummaryHTML;
}

function goToSection(sectionId) {
    // Hide all sections
    const sections = document.querySelectorAll('.checkout-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Show the selected section
    document.getElementById(sectionId).classList.add('active');
    
    // Update steps
    const steps = document.querySelectorAll('.step');
    steps.forEach(step => {
        step.classList.remove('active', 'completed');
    });
    
    if (sectionId === 'shipping-section') {
        document.getElementById('step1').classList.add('active');
    } else if (sectionId === 'payment-section') {
        document.getElementById('step1').classList.add('completed');
        document.getElementById('step2').classList.add('active');
    } else if (sectionId === 'review-section') {
        document.getElementById('step1').classList.add('completed');
        document.getElementById('step2').classList.add('completed');
        document.getElementById('step3').classList.add('active');
    }
    
    // Scroll to top of the section
    window.scrollTo({
        top: document.querySelector('.checkout-steps').offsetTop - 100,
        behavior: 'smooth'
    });
}

// Function to show the styled order confirmation modal
function showOrderConfirmationModal() {
    const modalHTML = `
        <div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div class="modal-header border-0" style="background-color: var(--light-color);">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4" style="background-color: var(--light-color);">
                        <div class="text-center mb-4 success-animation">
                            <div class="checkmark-circle" style="background-color: var(--secondary-color); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 4px 12px rgba(193, 154, 107, 0.3);">
                                <i class="bi bi-check-lg" style="font-size: 2.2rem; color: white;"></i>
                            </div>
                            <h3 class="mt-4 animate-fade-in" style="font-family: var(--heading-font); color: var(--primary-color); letter-spacing: 0.5px; font-weight: 600;">Thank You! Your Order Has Been Placed Successfully</h3>
                        </div>
                        <div class="d-flex justify-content-between gap-3 animate-fade-in mt-4">
                            <button type="button" class="btn continue-btn" style="background-color: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.8rem 1.5rem; transition: all 0.3s ease; font-weight: 500; flex: 1;" data-bs-dismiss="modal">
                                CONTINUE SHOPPING
                            </button>
                            <a href="cart.php" class="btn view-cart-btn" style="background-color: var(--secondary-color); color: white; border: none; border-radius: 8px; padding: 0.8rem 1.5rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; transition: all 0.3s ease; flex: 1; text-align: center;">
                                VIEW CART
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('orderConfirmationModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show modal
    const modal = new bootstrap.Modal(document.getElementById('orderConfirmationModal'));
    modal.show();
    
    // Add event listener to continue shopping button to redirect to home page
    document.querySelector('.continue-btn').addEventListener('click', function() {
        window.location.href = 'index.php';
    });
}

function placeOrder() {
    // Collect all order data
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }
    
    // Get customer information
    const customerInfo = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        postalCode: document.getElementById('zip').value,
        country: document.getElementById('country').options[document.getElementById('country').selectedIndex].text
    };
    
    // Get payment method
    const selectedMethod = document.querySelector('.payment-method.selected');
    const paymentMethod = selectedMethod.getAttribute('data-method');
    
    // Calculate total
    const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    const shipping = subtotal >= 50 ? 0 : 4.99;
    const orderTotal = subtotal + shipping;
    
    // Prepare order data
    const orderData = {
        shipping: customerInfo, // Changed key from customerInfo to shipping
        items: cart,
        paymentMethod: paymentMethod,
        orderTotal: orderTotal
    };
    
    // Disable place order button to prevent multiple submissions
    document.getElementById('place-order').disabled = true;
    document.getElementById('place-order').textContent = 'Processing...';
    
    // Send order data to server
    fetch('process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear the cart
            localStorage.removeItem('cart');
            
            // Show success message using the styled modal
            showOrderConfirmationModal();
        } else {
            // Show error message
            alert('Error: ' + data.message);
            // Re-enable place order button
            document.getElementById('place-order').disabled = false;
            document.getElementById('place-order').textContent = 'Place Order';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your order. Please try again.');
        // Re-enable place order button
        document.getElementById('place-order').disabled = false;
        document.getElementById('place-order').textContent = 'Place Order';
    });
}