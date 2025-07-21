<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'includes/header.php' ?>
    <style>

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

                                <!-- Credit Card -->
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

                                <!-- Paypal -->
                                <div class="payment-method" data-method="paypal">
                                    <div class="payment-method-header">
                                        <div class="payment-method-title">
                                            <i class="bi bi-paypal"></i> PayPal
                                        </div>
                                        <input type="radio" name="payment-method" value="paypal">
                                    </div>
                                    <div class="payment-method-details">
                                        <p>You’ll be redirected to PayPal to complete your purchase securely.</p>
                                    </div>
                                </div>

                                <!-- Bank Transfer -->
                                <div class="payment-method" data-method="bank-transfer">
                                    <div class="payment-method-header">
                                        <div class="payment-method-title">
                                            <i class="bi bi-bank"></i> Bank Transfer
                                        </div>
                                        <input type="radio" name="payment-method" value="bank-transfer">
                                    </div>
                                    <div class="payment-method-details">
                                        <p>Instructions for bank transfer will be provided on the confirmation page.</p>
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
                <div class="checkout-card order-summary-card">
                    <div id="order-summary-items"></div>   
                    <br><br>                 
                        <h5>Order Summary</h5>
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
</body>

</html>