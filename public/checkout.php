<!DOCTYPE html>
<html lang="en">
<head>
    <?php require 'includes/header.php' ?>
    <style>
        /* Essential checkout styles */
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--border-color);
            z-index: 1;
        }
        
        .step {
            position: relative;
            z-index: 2;
            background-color: white;
            padding: 0 10px;
            text-align: center;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--light-color);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 600;
            color: var(--text-color);
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        .step-title {
            font-family: var(--heading-font);
            color: var(--text-color);
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .step.active .step-title {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .checkout-section {
            display: none;
            margin-bottom: 2rem;
        }
        
        .checkout-section.active {
            display: block;
        }
        
        .checkout-card {
            background-color: var(--light-color);
            border-radius: 5px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .checkout-card h4 {
            font-family: var(--heading-font);
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 5px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .payment-method {
            border: 1px solid var(--border-color);
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method.selected {
            border-color: var(--secondary-color);
            background-color: rgba(var(--secondary-color-rgb), 0.05);
        }
        
        .payment-method-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .payment-method-title {
            font-weight: 500;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-method-details {
            margin-top: 1rem;
            display: none;
        }
        
        .payment-method.selected .payment-method-details {
            display: block;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .order-item-details {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .order-item-meta {
            font-size: 0.9rem;
            color: var(--text-color);
        }
        
        .order-item-price {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .back-btn {
            background-color: var(--light-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 5px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .next-btn, .place-order-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        /* Order completion popup styles */
        .order-completion-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .order-completion-popup {
            background-color: white;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            max-width: 550px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }
        
        .success-icon {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 2rem;
        }
        
        .order-completion-popup h3 {
            font-family: var(--heading-font);
            color: var(--primary-color);
            margin-bottom: 1.2rem;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .order-completion-popup p {
            color: var(--text-color);
            margin-bottom: 2.5rem;
            font-size: 1.2rem;
            line-height: 1.7;
            padding: 0 1rem;
        }
        
        .order-completion-popup .main-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <!-- Connection to the database -->
    <?php require_once 'includes/db_connect.php'; ?>

    <!-- Navigation-->
    <?php require 'includes/navbar.php' ?>

    <!-- Checkout section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5 checkout-container">
            <h2 class="mb-4" style="font-family: var(--heading-font); color: var(--primary-color); letter-spacing: 0.5px;">Checkout</h2>
            
            <!-- Checkout Steps -->
            <div class="checkout-steps">
                <div class="step active" id="step1">
                    <div class="step-number">1</div>
                    <div class="step-title">Shipping</div>
                </div>
                <div class="step" id="step2">
                    <div class="step-number">2</div>
                    <div class="step-title">Payment</div>
                </div>
                <div class="step" id="step3">
                    <div class="step-number">3</div>
                    <div class="step-title">Review</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Shipping Information Section -->
                    <div class="checkout-section active" id="shipping-section">
                        <div class="checkout-card">
                            <h4>Shipping Information</h4>
                            <form id="shipping-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="firstName" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="firstName" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lastName" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="state" class="form-label">State/Province</label>
                                            <input type="text" class="form-control" id="state" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="zip" class="form-label">Zip/Postal Code</label>
                                            <input type="text" class="form-control" id="zip" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="country" class="form-label">Country</label>
                                    <select class="form-control" id="country" required>
                                        <option value="">Select Country</option>
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="UK">United Kingdom</option>
                                        <option value="FR">France</option>
                                        <option value="DE">Germany</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class="navigation-buttons">
                            <a href="cart.php" class="back-btn">Back to Cart</a>
                            <button class="next-btn" id="shipping-next">Continue to Payment</button>
                        </div>
                    </div>
                    
                    <!-- Payment Method Section -->
                    <div class="checkout-section" id="payment-section">
                        <div class="checkout-card">
                            <h4>Payment Method</h4>
                            <div class="payment-methods">
                                <div class="payment-method" data-method="credit-card">
                                    <div class="payment-method-header">
                                        <div class="payment-method-title">
                                            <i class="bi bi-credit-card"></i> Credit Card
                                        </div>
                                        <input type="radio" name="payment-method" value="credit-card" checked>
                                    </div>
                                    <div class="payment-method-details">
                                        <div class="form-group">
                                            <label for="cardNumber" class="form-label">Card Number</label>
                                            <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="expiryDate" class="form-label">Expiry Date</label>
                                                    <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="cvv" class="form-label">CVV</label>
                                                    <input type="text" class="form-control" id="cvv" placeholder="123">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="nameOnCard" class="form-label">Name on Card</label>
                                            <input type="text" class="form-control" id="nameOnCard">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="navigation-buttons">
                            <button class="back-btn" id="payment-back">Back to Shipping</button>
                            <button class="next-btn" id="payment-next">Continue to Review</button>
                        </div>
                    </div>
                    
                    <!-- Order Review Section -->
                    <div class="checkout-section" id="review-section">
                        <div class="checkout-card">
                            <h4>Review Your Order</h4>
                            <div id="order-items">
                                <!-- Order items will be dynamically loaded here -->
                            </div>
                        </div>
                        <div class="checkout-card">
                            <h4>Shipping Address</h4>
                            <div id="shipping-summary">
                                <!-- Shipping information will be dynamically loaded here -->
                            </div>
                        </div>
                        <div class="checkout-card">
                            <h4>Payment Method</h4>
                            <div id="payment-summary">
                                <!-- Payment information will be dynamically loaded here -->
                            </div>
                        </div>
                        <div class="navigation-buttons">
                            <button class="back-btn" id="review-back">Back to Payment</button>
                            <button class="place-order-btn" id="place-order">Place Order</button>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="checkout-card">
                        <h4>Order Summary</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span id="checkout-subtotal">0.00€</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span id="checkout-shipping">0.00€</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-0">
                            <strong>Total</strong>
                            <strong id="checkout-total">0.00€</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Order Completion Popup -->
    <div class="order-completion-overlay" style="display: none;">
        <div class="order-completion-popup">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h3>Thank You for Your Order!</h3>
            <p>Your order has been successfully placed. You will receive a confirmation email shortly.</p>
            <a href="index.php" class="main-btn">Continue Shopping</a>
        </div>
    </div>

    <!-- Footer-->
    <?php require 'includes/footer.php' ?>
    
    <script src="static/js/checkout.js"></script>
</body>
</html>
