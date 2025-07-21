<!DOCTYPE html>
<html lang="en">
    <head>
        <?php require 'includes/header.php'?>
    </head>
        
    <body>
        <!-- Connection to the database -->
        <?php require_once 'includes/db_connect.php'; ?>

        <!-- Navigation-->
        <?php require 'includes/navbar.php' ?>

        <!-- About Us Section -->
        <section>
            <div class="container px-4 px-lg-5 mt-5">
                <h2 class="text-center section-title mb-5">ABOUT US</h2>
                
                <!-- You could reduce the number of sections if desired -->
                <div class="row gx-2 mb-5 align-items-center">
                    <div class="col-lg-6">
                    <img class="img-fluid rounded mb-5 mb-lg-0 w-50 mx-auto mx-lg-0"  src="static/images/about_page/story.png" alt="Our Story" />
                    </div>
                    <div class="col-lg-6">
                        <h3 class="mb-3">Our Story</h3>
                        <p>Founded in 2010, Timeless Elegance began with a simple vision: to create premium menswear that combines classic sophistication with modern sensibilities. Our founder, a third-generation tailor, established the brand with an unwavering commitment to quality craftsmanship and attention to detail.</p>
                        <p>Over the years, we've grown from a small boutique in Paris to an internationally recognized brand, while staying true to our core values of excellence, integrity, and timeless style.</p>
                    </div>
                </div>
                
                <div class="row gx-2 mb-5 align-items-center flex-lg-row-reverse">
                    <div class="col-lg-6">
                    <img class="img-fluid rounded mb-5 mb-lg-0 w-50 mx-auto mx-lg-0 ms-lg-auto d-block" src="static/images/about_page/philosophy.png" alt="Our Philosophy" />
                    </div>
                    <div class="col-lg-6">
                        <h3 class="mb-3">Our Philosophy</h3>
                        <p>At Timeless Elegance, we believe that true style transcends trends. We create garments that are designed to last, both in terms of quality and aesthetic appeal. Each piece in our collection is crafted with precision using the finest materials sourced from around the world.</p>
                        <p>We're committed to sustainable and ethical practices throughout our production process, ensuring that our garments not only look exceptional but are created with respect for people and the planet.</p>
                    </div>
                </div>
                
                <div class="row gx-2 mb-5 align-items-center">
                    <div class="col-lg-6">
                    <img class="img-fluid rounded mb-5 mb-lg-0 w-50 mx-auto mx-lg-0" src="static/images/about_page/craftsmanship.png" alt="Our Craftsmanship" />
                    </div>
                    <div class="col-lg-6">
                        <h3 class="mb-3">Our Craftsmanship</h3>
                        <p>Every Timeless Elegance garment represents the perfect harmony between traditional tailoring techniques and contemporary design. Our master craftsmen bring decades of experience to each piece, ensuring impeccable fit, comfort, and durability.</p>
                        <p>We believe in the art of slow fashion - taking the time to create garments that are built to last. From hand-stitched details to meticulously selected fabrics, every element of our clothing reflects our dedication to excellence.</p>
                    </div>
                </div>
                
            </div>
        </section>
        
        <!-- Footer-->
        <?php require 'includes/footer.php' ?>

    </body>
</html>