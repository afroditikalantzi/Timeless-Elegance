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

        <!-- Product Category section-->        
        <?php
            $cat = $_GET['categ'];
            $sql = "SELECT * FROM product WHERE category = '$cat'";
            $result = mysqli_query($conn, $sql);
            $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Find min and max prices in the current category
            $minProductPrice = PHP_INT_MAX;
            $maxProductPrice = 0;
            
            foreach($items as $item) {
                $currentPrice = ($item['salePrice'] != 0) ? $item['salePrice'] : $item['price'];
                if ($currentPrice < $minProductPrice) {
                    $minProductPrice = $currentPrice;
                }
                if ($currentPrice > $maxProductPrice) {
                    $maxProductPrice = $currentPrice;
                }
            }
            
            // Round values for slider
            $minProductPrice = floor($minProductPrice);
            $maxProductPrice = ceil($maxProductPrice);
            
            mysqli_free_result($result);
            mysqli_close($conn);
            
            // Map category names to collection titles
            $collection_titles = [
                'Shirts' => 'Designer Shirts Collection',
                'Blazers' => 'Premium Blazers Collection',
                'Trousers' => 'Tailored Trousers Collection',
                'Seasonal' => 'Limited Edition Collection'
            ];
            
            // Get the collection title or use the category name as fallback
            $collection_title = isset($collection_titles[$cat]) ? $collection_titles[$cat] : $cat;
        ?> 

        <section class="py-5">
            <div class="container px-4 px-lg-5 mt-5" id="showcase" style="display:block">
                <h2 class="text-center section-title" style="margin-bottom: 40px;"><?php echo htmlspecialchars($collection_title) ?></h2>
                
                <!-- Mobile filter toggle button (visible only on mobile) -->
                <div class="d-lg-none mb-4">
                    <button class="filter-toggle-btn w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                        <i class="bi bi-funnel-fill"></i> Filter Products
                    </button>
                </div>
                
                <div class="row">
                    <!-- Filter Sidebar -->
                    <div class="col-lg-3 mb-4">
                        <div class="filter-panel collapse d-lg-block" id="filterPanel">
                            <h3 class="filter-title">Refine Selection</h3>
                            
                            <div class="filter-section">
                                <h4 class="filter-section-title">Price Range</h4>
                                <div class="price-slider-container">
                                    <div id="price-range-slider" class="noUi-target noUi-ltr noUi-horizontal" style="margin: 15px 0;"></div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span id="minPriceValue"><?php echo $minProductPrice; ?>€</span>
                                        <span id="maxPriceValue"><?php echo $maxProductPrice; ?>€</span>
                                    </div>
                                    <input type="hidden" id="minPrice" value="<?php echo $minProductPrice; ?>">
                                    <input type="hidden" id="maxPrice" value="<?php echo $maxProductPrice; ?>">
                                </div>
                            </div>
                            
                            <div class="filter-section">
                                <h4 class="filter-section-title">Color</h4>
                                <div class="color-options">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="black" id="colorBlack">
                                        <label class="form-check-label" for="colorBlack">Black</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="white" id="colorWhite">
                                        <label class="form-check-label" for="colorWhite">White</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="blue" id="colorBlue">
                                        <label class="form-check-label" for="colorBlue">Blue</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="beige" id="colorBeige">
                                        <label class="form-check-label" for="colorBeige">Beige</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="filter-section">
                                <h4 class="filter-section-title">Size</h4>
                                <div class="size-options">
                                    <button type="button" class="size-btn" data-size="S">S</button>
                                    <button type="button" class="size-btn" data-size="M">M</button>
                                    <button type="button" class="size-btn" data-size="L">L</button>
                                    <button type="button" class="size-btn" data-size="XL">XL</button>
                                </div>
                            </div>
                            
                            <div class="filter-section">
                                <h4 class="filter-section-title">Availability</h4>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="instock" id="inStock" checked>
                                    <label class="form-check-label" for="inStock">In Stock</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="sale" id="onSale">
                                    <label class="form-check-label" for="onSale">On Sale</label>
                                </div>
                            </div>
                            
                            <div class="filter-actions">
                                <button class="btn filter-btn filter-apply">Apply Filters</button>
                                <button class="btn filter-btn filter-reset">Reset</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Product Grid -->
                    <div class="col-lg-9">
                        <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-md-2 row-cols-xl-3">
                            
                            <?php foreach($items as $item){ 
                                $displayPrice = ($item['salePrice'] != 0) ? $item['salePrice'] : $item['price'];
                            ?>
                            
                            <div class="col mb-5">
                                <div class="card" 
                                     data-price="<?php echo htmlspecialchars($item['price']); ?>" 
                                     data-sale-price="<?php echo htmlspecialchars($item['salePrice']); ?>" 
                                     data-display-price="<?php echo htmlspecialchars($displayPrice); ?>">
                                    <div class="card-img-container">
                                        <img class="card-img" src="https://placehold.co/400x300?text=<?php echo urlencode($item['productName']); ?>" alt="<?php echo htmlspecialchars($item['productName']); ?>" />
                                        <?php if($item['salePrice'] != 0){ ?>
                                            <span class="card-tag">SALE</span>
                                        <?php } ?>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($item['productName'])?></h5>
                                        <?php if($item['salePrice'] != 0){ ?>
                                            <p class="card-price">
                                                <del><?php echo htmlspecialchars($item['price'])?>€</del>
                                                <span class="sale-price"><?php echo htmlspecialchars($item['salePrice'])?>€</span>
                                            </p>
                                        <?php } else { ?>
                                            <p class="card-price"><?php echo htmlspecialchars($item['price'])?>€</p>
                                        <?php } ?>
                                        <div class="card-actions">
                                            <a href="#" class="btn main-btn add-to-cart-category" data-product="<?php echo htmlspecialchars($item['productName']); ?>" data-price="<?php echo htmlspecialchars($displayPrice); ?>">Add to Cart</a>
                                            <a href="/Timeless-Elegance/public/product_details.php?prod=<?php echo htmlspecialchars($item['productName'])?>" class="btn secondary-btn">Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php } ?>
                            
                            <!-- No results message (hidden by default) -->
                            <div id="noResultsMessage" style="display: none;" class="col-12 text-center py-5">
                                <div class="alert alert-info">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    No products match your selected filters. Please try different filter options.
                                </div>
                                <button class="btn filter-btn filter-reset mt-3">Reset Filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>    
       
        <style>
            .noUi-target {
                height: 8px; 
                border: none;
                box-shadow: none;
                background-color: #f0f0f0;
                border-radius: 4px;
                margin: 20px 0;
            }
            
            .noUi-connect {
                background-color: var(--secondary-color); 
            }
            
            .noUi-handle {
                width: 20px !important;
                height: 20px !important;
                border-radius: 50% !important; 
                background-color: white;
                border: 2px solid var(--secondary-color);
                box-shadow: 0 1px 5px rgba(0,0,0,0.15);
                cursor: pointer;
                right: -10px !important; 
                top: -7px !important;
            }
            
            .noUi-handle:before, 
            .noUi-handle:after {
                display: none; 
            }
            
            .noUi-handle:hover {
                border-color: var(--accent-color); 
            }
            
            .price-slider-container {
                padding: 10px 0;
                margin: 15px 0;
            }
            
            #minPriceValue, #maxPriceValue {
                font-weight: 500;
                color: var(--primary-color);
            }
        </style>
        
        <!-- NoUI Slider JS -->
        <script src="https://cdn.jsdelivr.net/npm/nouislider@14.6.3/distribute/nouislider.min.js"></script>
        
        <!-- Footer-->
        <?php require 'includes/footer.php' ?>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize price range slider
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
                    
                    // Size buttons click handler
                    const sizeButtons = document.querySelectorAll('.size-options .size-btn');
                    sizeButtons.forEach(function(button) {
                        button.addEventListener('click', function() {
                            // Toggle active class
                            this.classList.toggle('active');
                        });
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
                            
                            // Get size filters
                            const sizeFilters = [];
                            document.querySelectorAll('.size-options .size-btn.active').forEach(function(button) {
                                sizeFilters.push(button.getAttribute('data-size'));
                            });
                            
                            // Get availability filters
                            const inStock = document.getElementById('inStock').checked;
                            const onSale = document.getElementById('onSale').checked;
                            
                            // Apply filters to all product cards
                            const cards = document.querySelectorAll('.card');
                            let visibleCount = 0;
                            
                            // Get the product row container
                            const productRow = document.querySelector('.row.gx-4.gx-lg-5');
                            
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
                    
                    // Reset filters button click handler
                    const resetFilterBtn = document.querySelectorAll('.filter-reset');
                    resetFilterBtn.forEach(function(button) {
                        button.addEventListener('click', function() {
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
                            const cards = document.querySelectorAll('.card');
                            cards.forEach(function(card) {
                                card.closest('.col').style.display = 'block';
                            });
                            
                            // Hide no results message
                            const noResultsMessage = document.getElementById('noResultsMessage');
                            if (noResultsMessage) {
                                noResultsMessage.style.display = 'none';
                            }
                            
                            // Remove center alignment
                            const productRow = document.querySelector('.row.gx-4.gx-lg-5');
                            if (productRow) {
                                productRow.classList.remove('justify-content-center');
                            }
                        });
                    });
                }
            });
        </script>
    </body>
</html>
        


       