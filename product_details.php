<!DOCTYPE html>
<html lang="en">
    <head>
        <?php require 'header.php' ?>        
    </head>
    <body>
        <!-- Connection to the database -->
        <?php require_once 'db_connect.php'; ?>

        <!-- Navigation-->
        <?php require 'navbar.php' ?>

        <!-- Product section-->
        <?php
            $product = $_GET['prod'];
            $sql = "SELECT * FROM product WHERE productName ='$product'";
            $result = mysqli_query($conn, $sql);
            $items = mysqli_fetch_all($result , MYSQLI_ASSOC);
            $category ="";

            mysqli_free_result($result);           
                
        ?> 
        <?php foreach($items as $item){ ?>

        <section class="py-5 product-details-section">
            <div class="container px-4 px-lg-5 my-5">
                <div class="row gx-4 gx-lg-5 align-items-center">
                    <div class="col-md-6">
                        <div class="product-image-container">
                            <img class="product-detail-img" src="https://placehold.co/600x700?text=<?php echo urlencode($item['productName']); ?>" alt="<?php echo htmlspecialchars($item['productName']); ?>" />
                            <?php if($item['salePrice'] != 0){ ?>
                                <span class="card-tag product-tag">SALE</span>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="product-info">
                            <h1 class="product-title"><?php echo htmlspecialchars($item['productName']) ?></h1>
                            
                            <div class="product-price mb-4">
                                <?php if($item['salePrice'] != 0){ ?>
                                    <span class="original-price"><?php echo htmlspecialchars($item['price'])?>€</span>
                                    <span class="sale-price"><?php echo htmlspecialchars($item['salePrice'])?>€</span>
                                <?php } else { ?>
                                    <span class="regular-price"><?php echo htmlspecialchars($item['price'])?>€</span>
                                <?php } ?>
                            </div>
                            
                            <div class="product-category mb-3">
                                <span class="category-label">Collection:</span>
                                <span class="category-value"><?php echo htmlspecialchars($item['category'])?></span>
                            </div>
                            
                            <p class="product-description"><?php echo htmlspecialchars($item['description'])?></p>
                            
                            <!-- Add color selection -->
                            <div class="product-options mt-4">
                                <div class="color-selection mb-3">
                                    <h4 class="option-title">Color</h4>
                                    <div class="color-options">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="productColor" id="colorBlack" value="black" checked>
                                            <label class="form-check-label" for="colorBlack">Black</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="productColor" id="colorWhite" value="white">
                                            <label class="form-check-label" for="colorWhite">White</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="productColor" id="colorBlue" value="blue">
                                            <label class="form-check-label" for="colorBlue">Blue</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="productColor" id="colorBeige" value="beige">
                                            <label class="form-check-label" for="colorBeige">Beige</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Add size selection -->
                                <div class="size-selection mb-4">
                                    <h4 class="option-title">Size</h4>
                                    <div class="size-options">
                                        <div class="btn-group" role="group" aria-label="Size selection">
                                            <input type="radio" class="btn-check" name="productSize" id="sizeS" value="S" autocomplete="off" checked>
                                            <label class="btn btn-size" for="sizeS">S</label>
                                            
                                            <input type="radio" class="btn-check" name="productSize" id="sizeM" value="M" autocomplete="off">
                                            <label class="btn btn-size" for="sizeM">M</label>
                                            
                                            <input type="radio" class="btn-check" name="productSize" id="sizeL" value="L" autocomplete="off">
                                            <label class="btn btn-size" for="sizeL">L</label>
                                            
                                            <input type="radio" class="btn-check" name="productSize" id="sizeXL" value="XL" autocomplete="off">
                                            <label class="btn btn-size" for="sizeXL">XL</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="product-actions mt-4">
                                <div class="quantity-selector">
                                    <span class="quantity-label">Quantity</span>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="decrementQuantity()">-</button>
                                        <input class="quantity-input" id="inputQuantity" type="number" value="1" min="1" max="10" />
                                        <button class="quantity-btn" onclick="incrementQuantity()">+</button>
                                    </div>
                                </div>
                                
                                <button class="add-to-cart-btn" type="button">
                                    <i class="bi-cart-fill me-1"></i>
                                    Add to Cart
                                </button>
                            </div>
                            
                            <div class="product-meta mt-5">
                                <div class="meta-item">
                                    <i class="bi bi-truck"></i>
                                    <span>Free shipping on orders over 50€</span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-arrow-repeat"></i>
                                    <span>30-day returns policy</span>
                                </div>
                                <div class="meta-item">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Quality guarantee</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php } ?>
        
        <!-- Related items section-->
        <section class="py-5 relProd">
            <div class="container px-4 px-lg-5 mt-5">
                <h2 class="text-center section-title" style="margin-bottom: 40px;">You May Also Like</h2>
                <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
                
                <?php
                    // Get related products in the same category
                    $category = $item['category'];
                    $currentProduct = $item['productName'];
                    
                    $sql = "SELECT * FROM product WHERE category = '$category' AND productName != '$currentProduct' LIMIT 4";
                    $result = mysqli_query($conn, $sql);
                    $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    
                    foreach($items as $item){ 
                        // Calculate display price (sale price if available, otherwise regular price)
                        $displayPrice = ($item['salePrice'] != 0) ? $item['salePrice'] : $item['price'];
                ?>
                    <div class="col mb-5">
                        <div class="card" data-color="" data-size="" data-display-price="<?php echo htmlspecialchars($displayPrice); ?>" data-sale-price="<?php echo htmlspecialchars($item['salePrice']); ?>">
                            <div class="card-img-container">
                                <img class="card-img" src="https://placehold.co/400x300?text=<?php echo urlencode($item['productName']); ?>" alt="<?php echo htmlspecialchars($item['productName']); ?>" />
                                <?php if($item['salePrice'] != 0){ ?>
                                    <div class="card-badge">SALE</div>
                                <?php } ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['productName']); ?></h5>
                                <div class="card-price">
                                    <?php if($item['salePrice'] != 0){ ?>
                                        <span class="original-price"><?php echo htmlspecialchars($item['price']); ?>€</span>
                                        <span class="discount-price"><?php echo htmlspecialchars($item['salePrice']); ?>€</span>
                                    <?php } else { ?>
                                        <span><?php echo htmlspecialchars($item['price']); ?>€</span>
                                    <?php } ?>
                                </div>
                                <div class="card-actions">
                                    <a href="#" class="btn main-btn">Add to Cart</a>
                                    <a href="product_details.php?prod=<?php echo htmlspecialchars($item['productName']); ?>" class="btn secondary-btn">Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
        </section>
        
        <!-- Footer-->
        <?php require 'footer.php' ?>

    </body>
</html>


