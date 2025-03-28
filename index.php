<!DOCTYPE html>
<html lang="en">
    <head>
        <?php require 'header.php'?>
    </head>
        
    <body>
        <!-- Connection to the database -->
        <?php require_once 'db_connect.php'; ?>

        <!-- Navigation-->
        <?php require 'navbar.php' ?>

        
        <header class="bg-carouzel py-5" >
            <div class="container px-4 px-lg-5 my-5">
                <div class="text-center text-white">
                <?php require 'carouzel.php'; ?>
                </div>
            </div>
        </header>
        
        <?php require 'feautured.php';?>
        
        <!-- Footer-->
        <?php require 'footer.php' ?>

    </body>
</html>

