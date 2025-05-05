
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

        
        <header class="bg-carouzel py-5" >
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                <?php require 'includes/carouzel.php'; ?>
                </div>
            </div>
        </header>
        
        <?php require 'includes/featured.php';?>
        
        <!-- Footer-->
        <?php require 'includes/footer.php' ?>

    </body>
</html>