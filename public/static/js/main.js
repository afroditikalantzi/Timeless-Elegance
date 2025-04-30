// Main JavaScript file
// This file serves as the entry point for all JavaScript functionality

document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the maintenance page
    if (document.querySelector('.maintenance-body')) {
        console.log('Maintenance page loaded');
        // Any specific maintenance page functionality can be added here
    }
    // Initialize cart badge on page load if the function exists
    if (typeof window.updateCartBadge === 'function') {
        window.updateCartBadge();
    }
    
    // Initialize navbar functionality
    initNavbarToggler();
    initDropdownMenus();
    initNavbarScrollEffect();

    // ------------------- Product Filters ------------------- //
    
    // Initialize price range slider if it exists
    initPriceRangeSlider();
    
    // Initialize size buttons
    initSizeButtons();
});

// Initialize price range slider
function initPriceRangeSlider() {
    const priceSlider = document.getElementById('price-range-slider');
    if (priceSlider) {
        const minPrice = parseInt(document.getElementById('minPrice').value);
        const maxPrice = parseInt(document.getElementById('maxPrice').value);
        
        noUiSlider.create(priceSlider, {
            start: [minPrice, maxPrice],
            connect: true,
            range: {
                'min': minPrice,
                'max': maxPrice
            },
            step: 1,
            format: {
                to: function (value) {
                    return Math.round(value);
                },
                from: function (value) {
                    return Number(value);
                }
            }
        });
        
        // Update price display when slider changes
        priceSlider.noUiSlider.on('update', function (values, handle) {
            document.getElementById('minPriceValue').innerHTML = values[0] + '€';
            document.getElementById('maxPriceValue').innerHTML = values[1] + '€';
        });
        
        // Apply filters button click handler
        const applyFilterBtn = document.querySelector('.filter-apply');
        if (applyFilterBtn) {
            applyFilterBtn.addEventListener('click', applyProductFilters);
        }
        
        // Reset filters button click handler
        const resetFilterBtn = document.querySelector('.filter-reset');
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', resetProductFilters);
        }
    }
}

// Apply product filters
function applyProductFilters() {
    const priceSlider = document.getElementById('price-range-slider');
    if (!priceSlider) return;
    
    // Get price range values
    const priceRange = priceSlider.noUiSlider.get();
    const minFilterPrice = parseInt(priceRange[0]);
    const maxFilterPrice = parseInt(priceRange[1]);
    
    // Get color filters
    const colorFilters = [];
    document.querySelectorAll('.color-options .form-check-input:checked').forEach(function(checkbox) {
        colorFilters.push(checkbox.value);
    });
    
    // Get size filters
    const sizeFilters = [];
    document.querySelectorAll('.size-options .size-btn.active').forEach(function(button) {
        sizeFilters.push(button.getAttribute('data-size'));
    });
    
    // Get availability filters
    const inStock = document.getElementById('inStock').checked;
    const onSale = document.getElementById('onSale').checked;
    
    // Get the product row container
    const productRow = document.querySelector('.row.gx-4.gx-lg-5');
    
    // Apply filters to all product cards
    const cards = document.querySelectorAll('.card');
    let visibleCount = 0;
    
    // Remove center alignment when filtering
    if (productRow) {
        productRow.classList.remove('justify-content-center');
    }
    
    cards.forEach(function(card) {
        const displayPrice = parseFloat(card.dataset.displayPrice);
        const salePrice = parseFloat(card.dataset.salePrice);
        
        // Check if price is in range
        const priceInRange = displayPrice >= minFilterPrice && displayPrice <= maxFilterPrice;
        
        // Check if sale filter matches
        const saleMatch = !onSale || (onSale && salePrice > 0);
        
        // Check if color filter matches (if any colors are selected)
        let colorMatch = true;
        if (colorFilters.length > 0) {
            // In a real implementation, you would have color data in the card dataset
            // For now, we'll assume all products match the color filter
            colorMatch = true;
        }
        
        // Check if size filter matches (if any sizes are selected)
        let sizeMatch = true;
        if (sizeFilters.length > 0) {
            // In a real implementation, you would have size data in the card dataset
            // For now, we'll assume all products match the size filter
            sizeMatch = true;
        }
        
        // Show/hide card based on all filters
        if (priceInRange && saleMatch && colorMatch && sizeMatch) {
            card.closest('.col').style.display = 'block';
            visibleCount++;
        } else {
            card.closest('.col').style.display = 'none';
        }
    });
    
    // Show a message if no products match the filters
    const noResultsMessage = document.getElementById('noResultsMessage');
    if (noResultsMessage) {
        if (visibleCount === 0) {
            noResultsMessage.style.display = 'block';
            // Restore center alignment if no results
            if (productRow) {
                productRow.classList.add('justify-content-center');
            }
        } else {
            noResultsMessage.style.display = 'none';
        }
    }
}

// Reset product filters
function resetProductFilters() {
    const priceSlider = document.getElementById('price-range-slider');
    if (!priceSlider) return;
    
    const minPrice = parseInt(document.getElementById('minPrice').value);
    const maxPrice = parseInt(document.getElementById('maxPrice').value);
    
    // Reset price slider
    priceSlider.noUiSlider.set([minPrice, maxPrice]);
    
    // Reset color checkboxes
    document.querySelectorAll('.color-options .form-check-input').forEach(function(checkbox) {
        checkbox.checked = false;
    });
    
    // Reset size buttons
    document.querySelectorAll('.size-options .size-btn').forEach(function(button) {
        button.classList.remove('active');
    });
    
    // Reset availability checkboxes
    document.getElementById('inStock').checked = true;
    document.getElementById('onSale').checked = false;
    
    // Show all products
    document.querySelectorAll('.card').forEach(function(card) {
        card.closest('.col').style.display = 'block';
    });
    
    // Hide no results message if it exists
    const noResultsMessage = document.getElementById('noResultsMessage');
    if (noResultsMessage) {
        noResultsMessage.style.display = 'none';
    }
}

// Initialize size buttons
function initSizeButtons() {
    // Size buttons click handler
    const sizeButtons = document.querySelectorAll('.size-options .size-btn');
    sizeButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Toggle active class
            this.classList.toggle('active');
        });
    });
}

// ------------------- Product Details Page ------------------- //

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    // If loadCart function exists in cart.js, call it
    if (typeof window.loadCart === 'function') {
        window.loadCart();
    }
    
    // If updateCartBadge function exists in cart.js, call it
    if (typeof window.updateCartBadge === 'function') {
        window.updateCartBadge();
    }
});

// Add to cart functionality for category and related product pages
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all 'Add to Cart' buttons with class 'main-btn'
    // But exclude buttons that should trigger the product options modal
    const addToCartButtons = document.querySelectorAll('.main-btn');
    
    addToCartButtons.forEach(button => {
        // Skip buttons that are already handled by product-options-modal.js
        // These include buttons in the featured section and category page
        if (button.classList.contains('add-to-cart-category') || 
            (button.textContent.trim() === 'Add to Cart' && 
             (button.closest('.featured-section') || 
              button.closest('section').querySelector('.section-title')?.textContent.includes('FEATURED COLLECTION')))) {
            return;
        }
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the product card container
            const card = this.closest('.card');
            if (!card) return;
            
            // Get product details from the card
            const productName = card.querySelector('.card-title').textContent.trim();
            
            // Get the correct price - first try sale price, then regular price
            let price;
            const salePrice = card.querySelector('.sale-price');
            if (salePrice) {
                price = parseFloat(salePrice.textContent.replace('€', '').trim());
            } else {
                // If no sale price, get the regular price
                const regularPrice = card.querySelector('.card-price');
                if (regularPrice) {
                    price = parseFloat(regularPrice.textContent.replace('€', '').trim());
                }
            }
            
            // Default values for color and size
            const color = 'Default';
            const size = 'One Size';
            const quantity = 1;
            
            // Create cart item object
            const item = {
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
                cart[existingItemIndex].quantity += quantity;
            } else {
                // Add new item if it doesn't exist
                cart.push(item);
            }
            
            // Save updated cart
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Show a nicer success message instead of an alert
            const successMessage = document.createElement('div');
            successMessage.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
            successMessage.style.zIndex = '9999';
            successMessage.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i>Item added to cart!`;
            document.body.appendChild(successMessage);
            
            // Remove the message after 3 seconds
            setTimeout(() => {
                successMessage.classList.add('fade');
                setTimeout(() => {
                    document.body.removeChild(successMessage);
                }, 500);
            }, 2500);
            
            // Update cart count
            updateCartBadge();
        });
    });
});

// Function to update cart count in navbar
function updateCartCount() {
    updateCartBadge();
}

// Function to update cart badge across all pages
function updateCartBadge() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    // Get number of distinct items in cart
    const distinctItems = cart.length;
    // Get total quantity of items in cart
    const totalQuantity = cart.reduce((total, item) => total + item.quantity, 0);
    
    // Update cart badge with count
    const cartBadge = document.getElementById('cartBadge');
    if (cartBadge) {
        cartBadge.textContent = totalQuantity;
        
        if (distinctItems > 0) {
            cartBadge.classList.add('show');
        } else {
            cartBadge.classList.remove('show');
        }
    }
}

// ------------------- Navbar Functionality ------------------- //

// Initialize navbar toggler
function initNavbarToggler() {
    const navbarToggler = document.querySelector('.navbar-toggler');

    if (navbarToggler) {
        // Add click handler ONLY for the animated icon toggle
        // Let Bootstrap handle the actual collapse functionality via data attributes
        navbarToggler.addEventListener('click', function() {
            const animatedIcon = this.querySelector('.animated-icon2');
            if (animatedIcon) {
                // Check the state *after* Bootstrap potentially toggles the collapse
                // Use setTimeout to allow Bootstrap's event handlers to run first
                setTimeout(() => {
                    const targetSelector = this.getAttribute('data-bs-target');
                    const target = document.querySelector(targetSelector);
                    if (target) {
                        // Toggle 'open' class based on whether the collapse element is shown
                        if (target.classList.contains('show')) {
                            animatedIcon.classList.add('open');
                        } else {
                            animatedIcon.classList.remove('open');
                        }
                    }
                }, 0);
            }
        });

        // Ensure the icon state is correct on page load if the menu starts open (e.g., on larger screens)
        const targetSelector = navbarToggler.getAttribute('data-bs-target');
        const target = document.querySelector(targetSelector);
        const animatedIcon = navbarToggler.querySelector('.animated-icon2');
        if (target && target.classList.contains('show') && animatedIcon) {
            animatedIcon.classList.add('open');
        }

    } else {
        // Log error if the toggler element itself is not found
        console.error('Navbar toggler element (.navbar-toggler) not found.');
    }
}

// Initialize dropdown menus
function initDropdownMenus() {
    // Fix for mobile dropdown menus
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            // Only apply custom handling on mobile
            if (window.innerWidth < 992) {
                e.preventDefault();
                
                // Get the dropdown menu
                const dropdownMenu = this.nextElementSibling;
                
                // Toggle the aria-expanded attribute
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                
                // Toggle the show class on the dropdown menu
                if (isExpanded) {
                    dropdownMenu.classList.remove('show');
                } else {
                    dropdownMenu.classList.add('show');
                }
            }
        });
    });
}

// Initialize navbar scroll effect
function initNavbarScrollEffect() {
    // Add navbar scrolling effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        }
    });
}

// ------------------- Index Page Specific JavaScript ------------------- //

// Set current year in footer copyright (Moved from index.js)
document.addEventListener('DOMContentLoaded', function() {
    const currentYearElement = document.getElementById('currentYear');
    if (currentYearElement) {
        currentYearElement.textContent = new Date().getFullYear();
    }
});

// ------------------- Product Options Modal (Moved from product-options-modal.js) ------------------- //

document.addEventListener('DOMContentLoaded', function() {
    // Only add modal if it doesn't already exist
    if (!document.getElementById('productOptionsModal')) {
        // Add modal HTML to the page
        const modalHTML = `
        <div class="modal fade" id="productOptionsModal" tabindex="-1" aria-labelledby="productOptionsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="productOptionsModalLabel">Choose Product Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="product-info mb-3">
                            <h6 class="product-name"></h6>
                            <p class="product-price"></p>
                        </div>
                        <form id="productOptionsForm">
                            <!-- Size Selection -->
                            <div class="mb-3">
                                <label class="form-label">Size</label>
                                <div class="size-options d-flex">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="productSize" id="sizeS" value="S">
                                        <label class="form-check-label" for="sizeS">S</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="productSize" id="sizeM" value="M" checked>
                                        <label class="form-check-label" for="sizeM">M</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="productSize" id="sizeL" value="L">
                                        <label class="form-check-label" for="sizeL">L</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="productSize" id="sizeXL" value="XL">
                                        <label class="form-check-label" for="sizeXL">XL</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Color Selection -->
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <div class="color-options d-flex">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="productColor" id="colorBlack" value="Black" checked>
                                        <label class="form-check-label" for="colorBlack">Black</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="productColor" id="colorWhite" value="White">
                                        <label class="form-check-label" for="colorWhite">White</label>
                                    </div>
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" name="productColor" id="colorBlue" value="Blue">
                                        <label class="form-check-label" for="colorBlue">Blue</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="productColor" id="colorBeige" value="Beige">
                                        <label class="form-check-label" for="colorBeige">Beige</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quantity Selection -->
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <select class="form-select" id="productQuantity" name="productQuantity" style="width: 100px;">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn main-btn" id="addToCartBtn">Add to Cart</button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        // Append modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    // Initialize modal if the element exists
    const modalElement = document.getElementById('productOptionsModal');
    if (modalElement) {
        const productOptionsModal = new bootstrap.Modal(modalElement);
        
        // Store current product data
        let currentProduct = {
            name: '',
            price: 0,
            element: null
        };
        
        // Add event listeners for category page Add to Cart buttons
        const addToCartCategoryButtons = document.querySelectorAll('.add-to-cart-category');
        if (addToCartCategoryButtons.length > 0) {
            addToCartCategoryButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Store product data
                    currentProduct.name = this.getAttribute('data-product');
                    currentProduct.price = parseFloat(this.getAttribute('data-price'));
                    currentProduct.element = this;
                    
                    // Update modal with product info
                    document.querySelector('#productOptionsModal .product-name').textContent = currentProduct.name;
                    document.querySelector('#productOptionsModal .product-price').textContent = currentProduct.price + '€';
                    
                    // Show the modal
                    productOptionsModal.show();
                });
            });
        }
        
        // Add event listeners for featured page Add to Cart buttons
        const addToCartFeaturedButtons = document.querySelectorAll('.featured-section .main-btn');
        if (addToCartFeaturedButtons.length > 0) {
            addToCartFeaturedButtons.forEach(button => {
                if (button.textContent.trim() === 'Add to Cart') {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Get product info from the card
                        const card = this.closest('.card');
                        const productName = card.querySelector('.card-title').textContent;
                        let productPrice = 0;
                        
                        // Try to get sale price first
                        const salePrice = card.querySelector('.sale-price');
                        if (salePrice) {
                            productPrice = parseFloat(salePrice.textContent.replace('€', ''));
                        } else {
                            // If no sale price, get regular price
                            const regularPrice = card.querySelector('.card-price');
                            if (regularPrice) {
                                productPrice = parseFloat(regularPrice.textContent.replace('€', ''));
                            }
                        }
                        
                        // Store product data
                        currentProduct.name = productName;
                        currentProduct.price = productPrice;
                        currentProduct.element = this;
                        
                        // Update modal with product info
                        document.querySelector('#productOptionsModal .product-name').textContent = currentProduct.name;
                        document.querySelector('#productOptionsModal .product-price').textContent = currentProduct.price + '€';
                        
                        // Show the modal
                        productOptionsModal.show();
                    });
                }
            });
        }
        
        // Add event listeners for quantity buttons
        const quantityInput = document.getElementById('productQuantity');
        
        // No need for increment/decrement buttons with the dropdown selection
        
        // Add to Cart button in modal
        const addToCartBtn = document.getElementById('addToCartBtn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                // Get selected options
                const sizeElement = document.querySelector('input[name="productSize"]:checked');
                const colorElement = document.querySelector('input[name="productColor"]:checked');
                const quantityElement = document.getElementById('productQuantity');

                if (!sizeElement || !colorElement || !quantityElement) {
                    console.error('Could not find size, color, or quantity elements in the modal.');
                    return; // Exit if elements are missing
                }

                const size = sizeElement.value;
                const color = colorElement.value;
                const quantity = parseInt(quantityElement.value);
                
                // Create cart item object
                const item = {
                    name: currentProduct.name,
                    price: currentProduct.price,
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
                
                // Update cart badge if function exists
                if (typeof updateCartBadge === 'function') {
                    updateCartBadge();
                }
                
                // Hide the options modal
                productOptionsModal.hide();
                
                // Show a nicer success message
                showAddToCartSuccessMessage(item);
            });
        }
    }
    
    // Function to show success message after adding to cart
    function showAddToCartSuccessMessage(item) {
        // Create a modal similar to the one in product_details page
        const successModalHTML = `
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
        document.body.insertAdjacentHTML('beforeend', successModalHTML);
        
        // Initialize and show modal
        const modal = new bootstrap.Modal(document.getElementById('addToCartModal'));
        modal.show();
    }
});