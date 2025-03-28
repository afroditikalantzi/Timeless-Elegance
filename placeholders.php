<!DOCTYPE html>
<html lang="en" class="h-100">
    <head>
        <?php require 'header.php' ?>
        <style>
            body {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }
            
            main {
                flex: 1 0 auto;
            }
            
            footer {
                flex-shrink: 0;
            }
        </style>
    </head>
    <body>
        <!-- Navigation -->
        <?php require 'navbar.php'; ?>
        
        <!-- Under Construction Section -->
        <main class="py-5 my-5">
            <div class="container px-4 px-lg-5">
                <div class="row gx-4 gx-lg-5 justify-content-center">
                    <div class="col-md-8 text-center">
                        <div style="margin-bottom: 40px;">
                            <i class="bi bi-tools" style="font-size: 4rem; color: var(--secondary-color);"></i>
                        </div>
                        <h1 class="section-title mb-4" style="font-family: 'Playfair Display', serif; color: var(--primary-color);">Page Under Construction</h1>
                        <p class="lead mb-5" style="color: var(--text-color); font-weight: 300; line-height: 1.8;">
                            We're currently crafting this section with the same attention to detail that goes into all our premium collections. Please check back soon.
                        </p>
                        
                        <div class="row justify-content-center mt-5">
                            <div class="col-md-6">
                                <div style="height: 1px; background-color: rgba(0,0,0,0.05); margin: 30px 0;"></div>
                                <a href="index.php" class="elegant-btn elegant-btn-outline px-4 py-2 mt-3">Return to Homepage</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <?php require 'footer.php' ?>

    </body>
</html>