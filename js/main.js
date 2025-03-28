// ------------------- Navbar ------------------- //

// Animated hamburger menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            // The aria-expanded attribute will be handled by Bootstrap
            // We're just ensuring the animation works correctly
        });
    }
    
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
    
    // Add navbar scrolling effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    // ------------------- Product Filters ------------------- //
    
    // Initialize price range slider if it exists
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
            applyFilterBtn.addEventListener('click', function() {
                // Get price range values
                const priceRange = priceSlider.noUiSlider.get();
                const minFilterPrice = parseInt(priceRange[0]);
                const maxFilterPrice = parseInt(priceRange[1]);
                
                // Get color filters
                const colorFilters = [];
                document.querySelectorAll('.color-options .form-check-input:checked').forEach(function(checkbox) {
                    colorFilters.push(checkbox.value);
                });
                
                // Get size filters - Updated selector to match the new styling
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
            });
        }
        
        // Size buttons click handler
        const sizeButtons = document.querySelectorAll('.size-options .size-btn');
        sizeButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Toggle active class
                this.classList.toggle('active');
            });
        });
        
        // Reset filters button click handler
        const resetFilterBtn = document.querySelector('.filter-reset');
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', function() {
                // Reset price slider
                priceSlider.noUiSlider.set([minPrice, maxPrice]);
                
                // Reset color checkboxes
                document.querySelectorAll('.color-options .form-check-input').forEach(function(checkbox) {
                    checkbox.checked = false;
                });
                
                // Reset size buttons with the correct selector
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
                
                // Get the product row container and restore center alignment
                const productRow = document.querySelector('.row.gx-4.gx-lg-5');
                if (productRow) {
                    productRow.classList.add('justify-content-center');
                }
            });
        }
    }
});