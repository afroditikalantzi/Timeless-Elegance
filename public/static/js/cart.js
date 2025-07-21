// Add to cart function
function addToCart() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('prod');
      const productName = document.querySelector('.product-title').textContent;
    
    // Get the selected product attributes
    const rawColor = document.querySelector('input[name="productColor"]:checked').value;
    const color    = rawColor.charAt(0).toUpperCase() + rawColor.slice(1).toLowerCase();
    
    const rawSize = document.querySelector('input[name="productSize"]:checked').value;
    const size    = rawSize.toUpperCase();
    
    const quantity = parseInt(document.getElementById('inputQuantity').value);
    
    
    // Get the price from the product details page
    let priceText = '';
    const priceContainer = document.querySelector('.product-price');
    
    if (priceContainer) {
        // First check for sale price 
        const salePriceElement = priceContainer.querySelector('.sale-price');
        if (salePriceElement && salePriceElement.textContent.trim()) {
            priceText = salePriceElement.textContent;
        } 
        // Then check for regular price
        else {
            const regularPriceElement = priceContainer.querySelector('.regular-price');
            if (regularPriceElement && regularPriceElement.textContent.trim()) {
                priceText = regularPriceElement.textContent;
            } 
            // Finally, check for any non-original price span
            else {
                const allSpans = priceContainer.querySelectorAll('span');
                for (const span of allSpans) {
                    // Skip original price (crossed-out price)
                    if (!span.classList.contains('original-price')) {
                        const spanText = span.textContent.trim();
                        if (spanText) {
                            priceText = spanText;
                            break;
                        }
                    }
                }
                
                // If still no price found, use any direct text in the container
                if (!priceText) {
                    const directText = priceContainer.textContent.trim();
                    if (directText) {
                        // Try to extract a price-like pattern from the text
                        const priceMatch = directText.match(/\d+([.,]\d+)?\s*€/);
                        if (priceMatch) {
                            priceText = priceMatch[0];
                        } else {
                            priceText = directText;
                        }
                    }
                }
            }
        }
    }
    
    // If we still don't have a price, try other selectors (for category pages)
    if (!priceText) {
        const salePriceElement = document.querySelector('.sale-price');
        if (salePriceElement) {
            priceText = salePriceElement.textContent;
        } else {
            const regularPriceElement = document.querySelector('.regular-price');
            if (regularPriceElement) {
                priceText = regularPriceElement.textContent;
            } else {
                const cardPriceElement = document.querySelector('.card-price');
                if (cardPriceElement) {
                    priceText = cardPriceElement.textContent;
                }
            }
        }
    }
    
    // Clean the price text by removing the Euro symbol and any whitespace
    const cleanPriceText = priceText.replace('€', '').trim();
    // Parse the clean price text to a float
    const price = parseFloat(cleanPriceText);
    
    // Validate the price
    if (isNaN(price) || price <= 0) {
        console.error('Invalid price detected:', priceText);
        alert('Error: Could not determine product price. Please try again.');
        return;
    }
    
    // Create cart item object
    const item = {
        id: parseInt(productId, 10),
        name: productName,
        price: price,
        color: color,
        size: size,
        quantity: quantity
    };
    
    // Get existing cart or initialize new one
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(cartItem => 
        cartItem.name === item.name && 
        cartItem.color === item.color && 
        cartItem.size === item.size
    );
    
    if (existingItemIndex !== -1) {
        // Update quantity if item exists
        const newQuantity = cart[existingItemIndex].quantity + quantity;
        if (newQuantity <= 10) {
            cart[existingItemIndex].quantity = newQuantity;
        } else {
            alert('Maximum quantity of 10 items reached for this product');
            return;
        }
    } else {
        // Add new item if it doesn't exist
        cart.push(item);
    }

    // Save updated cart
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart badge
    updateCartBadge();
    
    // Show success modal
    showAddToCartModal(item);
}

// Show add to cart success modal
function showAddToCartModal(item) {
    const modalHTML = `
        <div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
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
                            <h3 class="mt-4 animate-fade-in" style="font-family: var(--heading-font); color: var(--primary-color); letter-spacing: 0.5px; font-weight: 600;">Item Added to Cart</h3>
                        </div>
                        <div class="d-flex justify-content-between gap-3 animate-fade-in mt-4">
                            <button type="button" class="btn continue-btn" style="background-color: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.8rem 1.5rem; transition: all 0.3s ease; font-weight: 500; flex: 1;" data-bs-dismiss="modal">
                                Continue Shopping
                            </button>
                            <a href="cart.php" class="btn view-cart-btn" style="background-color: var(--secondary-color); color: white; border: none; border-radius: 8px; padding: 0.8rem 1.5rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 500; transition: all 0.3s ease; flex: 1; text-align: center;">
                                View Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('addToCartModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Initialize and show modal
    const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
    modal.show();
}

// Calculate cart total
function calculateCartTotal() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Get total number of items in cart
function getCartItemCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    return cart.reduce((total, item) => total + item.quantity, 0);
}

// Update cart count in navbar (now uses distinct items count)
function updateCartCount() {
    // Call updateCartBadge to show distinct items count
    updateCartBadge();
}

// Update cart badge with number of distinct items
function updateCartBadge() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    // Get number of distinct items in cart
    const distinctItems = cart.length;
    
    // Update cart badge with count
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge) {
        cartBadge.textContent = distinctItems;
        
        if (distinctItems > 0) {
            cartBadge.classList.add('show');
        } else {
            cartBadge.classList.remove('show');
        }
    }
}

// Load cart contents
function loadCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartItems = document.getElementById('cartItems');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (cart.length === 0) {
        emptyCartMessage.style.display = 'block';
        checkoutBtn.disabled = true;
        updateOrderSummary(0);
        return;
    }

    emptyCartMessage.style.display = 'none';
    checkoutBtn.disabled = false;
    
    let cartHTML = '<div class="cart-items-container">';
    let subtotal = 0;

    cart.forEach((item, index) => {        
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        // Removed estimated delivery date calculation for a more minimal design

        cartHTML += `
            <div class="cart-item" data-index="${index}">
                <div class="cart-item-content">
                    <div class="cart-item-image-container">
                        <img src="https://placehold.co/200x200?text=${encodeURIComponent(item.name)}" 
                             class="cart-item-image" alt="${item.name}">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-header">
                            <h5 class="cart-item-title">${item.name}</h5>
                            <div class="cart-item-price-mobile">
                                <span>${item.price.toFixed(2)}€</span>
                            </div>
                        </div>
                        <div class="cart-item-meta">
                            <div class="cart-item-attributes">
                                <span class="cart-item-color"><i class="bi bi-circle-fill" style="color: ${getColorCode(item.color)}; font-size: 0.8rem; margin-right: 4px;"></i> ${item.color}</span>
                                <span class="cart-item-size"><i class="bi bi-rulers" style="font-size: 0.8rem; margin-right: 4px;"></i> ${item.size}</span>
                            </div>
                            <div class="cart-item-price-desktop">
                                <span>${item.price.toFixed(2)}€</span>
                            </div>
                        </div>
                        <!-- Removed delivery and stock information for a more minimal design -->
                    </div>
                    <div class="cart-item-actions">
                        <div class="cart-item-quantity">
                            <label class="quantity-label">Qty</label>
                            <div class="quantity-control">
                                <button class="quantity-btn decrement-btn" data-index="${index}">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="10" data-index="${index}">
                                <button class="quantity-btn increment-btn" data-index="${index}">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="cart-item-total">
                            <div class="total-label">Total</div>
                            <span>${(item.price * item.quantity).toFixed(2)}€</span>
                        </div>
                        <div class="cart-item-remove">
                            <button class="remove-item-btn" data-index="${index}" title="Remove item">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    cartHTML += '</div>';
    cartItems.innerHTML = cartHTML;
    updateOrderSummary(subtotal);
    
    // Add event listeners for quantity controls and remove buttons
    attachCartEventListeners();
}

// Helper function to get a color code based on color name
function getColorCode(colorName) {
    const colorMap = {
        'Black': '#000000',
        'White': '#FFFFFF',
        'Blue': '#0000FF',
    };
    
    return colorMap[colorName] || '#CCCCCC'; // Default to gray if color not found
}

// Update a specific cart item without reloading the entire cart
function updateCartItem(index, cart) {
    if (!cart) {
        cart = JSON.parse(localStorage.getItem('cart')) || [];
    }
    
    if (index >= cart.length) return;
    
    const item = cart[index];
    const itemElement = document.querySelector(`.cart-item[data-index="${index}"]`);
    
    if (!itemElement) return;
    
    // Update quantity input
    const quantityInput = itemElement.querySelector('.quantity-input');
    if (quantityInput) {
        quantityInput.value = item.quantity;
    }
    
    // Update item total price
    const totalElement = itemElement.querySelector('.cart-item-total span');
    if (totalElement) {
        totalElement.textContent = `${(item.price * item.quantity).toFixed(2)}€`;
    }
    
    // Update order summary
    updateOrderSummary(calculateSubtotal(cart));
}

// Calculate subtotal from cart
function calculateSubtotal(cart) {
    if (!cart) {
        cart = JSON.parse(localStorage.getItem('cart')) || [];
    }
    
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Update order summary
function updateOrderSummary(subtotal) {
    const shipping = 0; // Fixed to 0 instead of conditional 5.00
    const total = subtotal + shipping;

    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + '€';
    document.getElementById('shipping').textContent = shipping.toFixed(2) + '€';
    document.getElementById('total').textContent = total.toFixed(2) + '€';
    
    // Update checkout button state
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        if (subtotal === 0) {
            checkoutBtn.setAttribute('disabled', 'disabled');
            checkoutBtn.classList.add('disabled');
            checkoutBtn.style.pointerEvents = 'none';
            checkoutBtn.style.opacity = '0.65';
            checkoutBtn.style.cursor = 'not-allowed';
            checkoutBtn.style.boxShadow = 'none';
        } else {
            checkoutBtn.removeAttribute('disabled');
            checkoutBtn.classList.remove('disabled');
            checkoutBtn.style.pointerEvents = 'auto';
        }
    }
}

// Attach event listeners to cart elements
function attachCartEventListeners() {
    // Increment quantity buttons
    document.querySelectorAll('.increment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            incrementQuantity(index);
        });
    });
    
    // Decrement quantity buttons
    document.querySelectorAll('.decrement-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            decrementQuantity(index);
        });
    });
    
    // Quantity input fields
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const index = parseInt(this.getAttribute('data-index'));
            updateQuantity(index, this.value);
        });
    });
    
    // Remove item buttons
    document.querySelectorAll('.remove-item-btn').forEach(button => {
        button.addEventListener('click', function() {
            const index = parseInt(this.getAttribute('data-index'));
            removeItem(index);
        });
    });
}

// Increment quantity
function incrementQuantity(index) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart[index].quantity < 10) {
        cart[index].quantity++;
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartItem(index, cart);
        updateCartBadge();
    }
}

// Decrement quantity
function decrementQuantity(index) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartItem(index, cart);
        updateCartBadge();
    }
}

// Update quantity
function updateQuantity(index, value) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    value = parseInt(value);
    if (value >= 1 && value <= 10) {
        cart[index].quantity = value;
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartItem(index, cart);
        updateCartBadge();
    }
}

// Remove item
function removeItem(index) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart badge
    updateCartBadge();
    
    if (cart.length === 0) {
        // Show empty cart message if cart is empty
        const cartItems = document.getElementById('cartItems');
        const checkoutBtn = document.getElementById('checkoutBtn');
        
        // we'll just recreate it directly in the cart items container
        if (cartItems) {
            // Clear the cart items container completely
            cartItems.innerHTML = `
                <div id="emptyCartMessage" class="empty-cart-message" style="display: block !important; background-color: var(--light-color); border-radius: 5px; padding: 3rem;">
                    <i class="bi bi-cart" style="font-size: 3rem; color: var(--secondary-color); "></i>
                    <h4>Your cart is empty</h4>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="index.php" class="btn continue-shopping-btn" style="font-size: 0.9rem">Continue Shopping</a>
                </div>
            `;
            
            // Force a repaint to ensure the message is displayed
            cartItems.offsetHeight;
        } 
        
        // Disable checkout button
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.classList.add('disabled');
            checkoutBtn.style.pointerEvents = 'none';
            checkoutBtn.style.opacity = '0.65';
            checkoutBtn.style.cursor = 'not-allowed';
            checkoutBtn.style.boxShadow = 'none';
        }
        
        updateOrderSummary(0);
    } else {
        // Update the cart items without full reload to maintain current state
        // This prevents the need for a full cart reload which can be disruptive
        const cartItems = document.getElementById('cartItems');
        if (cartItems) {
            // Remove the specific item from the DOM
            const itemToRemove = document.querySelector(`.cart-item[data-index="${index}"]`);
            if (itemToRemove) {
                itemToRemove.remove();
            }
            
            // Update all remaining items' data-index attributes
            const remainingItems = document.querySelectorAll('.cart-item');
            remainingItems.forEach((item, newIndex) => {
                // Update data-index on the item
                item.setAttribute('data-index', newIndex);
                
                // Update data-index on all child elements that need it
                const incrementBtn = item.querySelector('.increment-btn');
                const decrementBtn = item.querySelector('.decrement-btn');
                const quantityInput = item.querySelector('.quantity-input');
                const removeBtn = item.querySelector('.remove-item-btn');
                
                if (incrementBtn) incrementBtn.setAttribute('data-index', newIndex);
                if (decrementBtn) decrementBtn.setAttribute('data-index', newIndex);
                if (quantityInput) quantityInput.setAttribute('data-index', newIndex);
                if (removeBtn) removeBtn.setAttribute('data-index', newIndex);
            });
            
            // Update order summary
            updateOrderSummary(calculateSubtotal(cart));
            
            // Reattach event listeners to the updated elements
            attachCartEventListeners();
        }
    }
    
    updateCartBadge();
}

// Initialize cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to "Add to Cart" button if on product details page
    const addToCartBtn = document.getElementById('addToCartButton');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', addToCart);
    }
    
    // Update cart count
    updateCartCount();
    
    // Check if we're on the cart page
    const cartItemsContainer = document.getElementById('cartItems');
    const emptyCartMessage = document.getElementById('emptyCartMessage');
    
    if (cartItemsContainer && emptyCartMessage) {
        // Load cart contents
        loadCart();
    }
});

// Make loadCart available globally for other scripts
window.loadCart = loadCart;