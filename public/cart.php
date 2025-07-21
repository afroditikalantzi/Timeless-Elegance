<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'includes/header.php' ?>
</head>

<body>
    <!-- Connection to the database -->
    <?php require_once 'includes/db_connect.php'; ?>

    <!-- Navigation-->
    <?php require 'includes/navbar.php' ?>

    <!-- Cart section-->
    <section class="py-5">
        <div class="container px-4 px-lg-5">
            <h2 class="mb-4" style="font-family: var(--heading-font); color: var(--primary-color); letter-spacing: 0.5px;">Shopping Cart</h2>
            <div class="row">
                <div class="col-lg-8">
                    <div id="cartItems" class="cart-items">
                        <!-- Empty cart message -->
                        <div id="emptyCartMessage" class="empty-cart-message" style="display: block !important; background-color: var(--light-color); border-radius: 5px; padding: 3rem;">
                            <i class="bi bi-cart" style="font-size: 3rem; color: var(--secondary-color); "></i>
                            <h4>Your cart is empty</h4>
                            <p>Looks like you haven't added any items to your cart yet.</p>
                            <a href="index.php" class="btn continue-shopping-btn" style="font-size: 0.9rem">Continue Shopping</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <h5 class="order-summary-title">Order Summary</h5>
                        <div class="order-summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal">0.00€</span>
                        </div>
                        <div class="order-summary-row">
                            <span>Shipping</span>
                            <span id="shipping">0.00€</span>
                        </div>
                        <hr>
                        <div class="order-summary-row order-total">
                            <strong>Total</strong>
                            <strong id="total">0.00€</strong>
                        </div>
                        <a href="checkout.php" id="checkoutBtn" class="checkout-btn btn" style="line-height: 2.5rem; font-size: 1rem; " disabled>Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer-->
    <?php require 'includes/footer.php' ?>
</body>

</html>