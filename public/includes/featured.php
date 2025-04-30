<section class="py-5">
    <div class="container px-4 px-lg-5 mt-5" id="showcase" style="display:block">
        <h2 class="text-center section-title" style="margin-bottom: 40px;">FEATURED COLLECTION</h2>
        <div class="row gx-4 gx-lg-5 row-cols-2 row-cols-md-3 row-cols-xl-4 justify-content-center">
            
            <?php
                $sql = "SELECT * FROM product WHERE feature = '1'";
                $result = mysqli_query($conn, $sql);
                $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
                
                mysqli_free_result($result);
                mysqli_close($conn);
                shuffle($items);
                
                foreach($items as $item){ 
            ?>
                <div class="col mb-5">
                    <div class="card">
                        <!-- Product image-->
                        <div class="card-img-container">
                            <img class="card-img" src="https://placehold.co/400x300?text=<?php echo urlencode($item['productName']); ?>" alt="<?php echo htmlspecialchars($item['productName']); ?>" />
                            <?php if($item['salePrice'] != 0){ ?>
                                <span class="card-tag">SALE</span>
                            <?php } ?>
                        </div>
                        <!-- Product details-->
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
                                <a href="#" class="btn main-btn add-to-cart-category" data-product="<?php echo htmlspecialchars($item['productName']); ?>" data-price="<?php echo htmlspecialchars($item['salePrice'] != 0 ? $item['salePrice'] : $item['price']); ?>">Add to Cart</a>
                                <a href="../product_details.php?prod=<?php echo htmlspecialchars($item['productName'])?>" class="btn secondary-btn">Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>