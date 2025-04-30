<?php
/**
 * Master Setup File for eShop
 * This file creates all database tables and inserts sample data
 */

// Display all errors for debugging during setup
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to capture all messages
ob_start();

// Database connection parameters
$dbserver = "localhost";
$mysql_username = "root";
$mysql_password = "";

// Connect to MySQL server without selecting a database
$conn = mysqli_connect($dbserver, $mysql_username, $mysql_password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS eshop";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "eshop");

// Create product table
$sql = "CREATE TABLE IF NOT EXISTS product (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    salePrice DECIMAL(10,2) DEFAULT 0,
    category VARCHAR(50),
    thumbnail VARCHAR(255),
    feature TINYINT(1) DEFAULT 0
)";

if (mysqli_query($conn, $sql)) {
    echo "Table product created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create category table
$sql = "CREATE TABLE IF NOT EXISTS category (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
)";

if (mysqli_query($conn, $sql)) {
    echo "Table category created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create customer table
$sql = "CREATE TABLE IF NOT EXISTS customer (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    postalCode VARCHAR(20),
    country VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table customer created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(6) UNSIGNED,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    shipping_city VARCHAR(50),
    shipping_postal_code VARCHAR(20),
    shipping_country VARCHAR(50),
    payment_method VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Table orders created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create admin table
$sql = "CREATE TABLE IF NOT EXISTS admin (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table admin created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Table settings created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Create order_items table for order details
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT(6) UNSIGNED,
    product_id INT(6) UNSIGNED,
    product_name VARCHAR(100) NOT NULL,
    quantity INT(3) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    size VARCHAR(20) DEFAULT NULL,
    color VARCHAR(30) DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE SET NULL
)";

if (mysqli_query($conn, $sql)) {
    echo "Table order_items created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}


//INSERT DATA TO TABLES


// Check if product data already exists
$check = mysqli_query($conn, "SELECT * FROM product LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    $sql = "INSERT INTO product (productName, description, price, salePrice, category, thumbnail, feature) 
        VALUES 
        ('Tailored Oxford Shirt', 'Crafted from premium Egyptian cotton with mother-of-pearl buttons. A timeless addition to any gentleman\'s wardrobe.', 120.00, 0, 'Shirts', 'assets/Placeholder-Image.jpg', 1),
        ('Italian Linen Shirt', 'Lightweight and breathable Italian linen, perfect for warm weather occasions. Features a modern slim fit with subtle texture.', 95.00, 85.00, 'Shirts', 'assets/Placeholder-Image.jpg', 0),
        ('French Cuff Dress Shirt', 'Elegant dress shirt with French cuffs, ideal for formal events. Made from 120-thread count cotton with a subtle herringbone pattern.', 110.00, 0, 'Shirts', 'assets/Placeholder-Image.jpg', 0),
        
        ('Cashmere Wool Blazer', 'Luxurious cashmere-wool blend blazer with hand-stitched details and horn buttons. Perfect for both formal and smart-casual occasions.', 450.00, 0, 'Blazers', 'assets/Placeholder-Image.jpg', 1),
        ('Italian Silk Blazer', 'Lightweight summer blazer crafted from the finest Italian silk. Features a half-canvas construction and mother-of-pearl buttons.', 380.00, 320.00, 'Blazers', 'assets/Placeholder-Image.jpg', 0),
        ('Herringbone Tweed Blazer', 'Classic herringbone pattern in premium British tweed. Fully lined with custom paisley print and genuine leather elbow patches.', 395.00, 0, 'Blazers', 'assets/Placeholder-Image.jpg', 0),
        
        ('Merino Wool Trousers', 'Tailored from superfine merino wool with a natural stretch for comfort. Features side adjusters and a clean, flat front design.', 180.00, 0, 'Trousers', 'assets/Placeholder-Image.jpg', 1),
        ('Italian Cotton Chinos', 'Premium cotton chinos with a subtle texture. Garment-dyed for rich color and pre-washed for a comfortable feel from the first wear.', 140.00, 120.00, 'Trousers', 'assets/Placeholder-Image.jpg', 0),
        ('Tailored Flannel Trousers', 'Luxurious flannel trousers with a classic fit. Perfect for cooler weather with a soft hand feel and elegant drape.', 165.00, 0, 'Trousers', 'assets/Placeholder-Image.jpg', 0),
        
        ('Limited Edition Linen Suit', 'Exclusive summer collection featuring a lightweight linen suit in a distinctive sand tone. Limited quantities available.', 580.00, 0, 'Seasonal', 'assets/Placeholder-Image.jpg', 1),
        ('Autumn Cashmere Sweater', 'Seasonal pure cashmere sweater with a contemporary fit. Perfect layering piece for the autumn months.', 220.00, 190.00, 'Seasonal', 'assets/Placeholder-Image.jpg', 0),
        ('Winter Collection Overcoat', 'Premium wool-blend overcoat from our winter collection. Features a tailored silhouette and luxurious satin lining.', 495.00, 0, 'Seasonal', 'assets/Placeholder-Image.jpg', 0)

       ";
    
    if (mysqli_query($conn, $sql)) {
        echo "Data inside the product table inserted successfully<br>";
    } else {
        echo "Error inserting sample products: " . mysqli_error($conn) . "<br>";
    }
}

// Check if admin data already exists
$check = mysqli_query($conn, "SELECT * FROM admin LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    $admin_username = 'admin';
    $admin_password = 'password123'; 
    $admin_email = 'admin@example.com';
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO admin (username, password, email) VALUES 
            ('$admin_username', '$hashed_password', '$admin_email')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Admin created successfully with this credentials (username: $admin_username, password: $admin_password)<br>";
    } else {
        echo "Error creating admin: " . mysqli_error($conn) . "<br>";
    }
}

// Check if default settings exist
$check = mysqli_query($conn, "SELECT * FROM settings LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    // Insert default settings
    $sql = "INSERT INTO settings (setting_name, setting_value) VALUES 
            ('maintenance_mode', '0'),
            ('items_per_page', '10')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Default settings created successfully<br>";
    } else {
        echo "Error creating default settings: " . mysqli_error($conn) . "<br>";
    }
}

// Check if category data already exists
$check = mysqli_query($conn, "SELECT * FROM category LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    // Insert sample categories
    $sql = "INSERT INTO category (categoryName, description) VALUES 
            ('Shirts', 'All types of shirts including dress shirts, casual shirts, and formal shirts'),
            ('Blazers', 'Premium blazers and suit jackets for all occasions'),
            ('Trousers', 'Formal and casual trousers including chinos and dress pants'),
            ('Seasonal', 'Limited edition seasonal collections')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Data inside the category table inserted successfully<br>";
    } else {
        echo "Error inserting sample categories: " . mysqli_error($conn) . "<br>";
    }
}

// Get all the output messages
$setup_messages = ob_get_clean();

// Close database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timeless Elegance - Setup</title>
    <!-- Include header meta tags and links with adjusted paths -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Timeless Elegance - Premium Men's Clothing" />
    <meta name="author" content="Timeless Elegance" />
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="public/static/assets/favicon.png" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <link href="public/static/css/styles.css" rel="stylesheet" />
    <link href="public/static/css/responsive.css" rel="stylesheet" />
    
    <!-- Additional setup-specific styles -->
    <style>
        .setup-container {
            flex: 1;
            padding: 3rem 0;
        }

        .setup-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .setup-title {
            font-family: var(--heading-font);
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 2px;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
            text-align: center;
        }

        .setup-title::after {
            content: '';
            position: absolute;
            width: 60px;
            height: 2px;
            background-color: var(--secondary-color);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .setup-messages {
            background-color: var(--light-color);
            border-radius: 5px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg">
        <div class="container px-4 px-lg-5">
            <a class="brand" href="#">
                <span class="brand-text">Timeless Elegance</span>
            </a>
        </div>
    </nav>

    <!-- Setup Content -->
    <section class="setup-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="setup-card">
                        <h1 class="setup-title">Eshop Setup</h1>
                        <div class="setup-messages">
                            <?php echo $setup_messages; ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="public/index.php" class="btn-primary me-3">Go to Store</a>
                            <a href="admin/login.php" class="btn-primary">Admin Dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer-->
    <footer class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <h6 class="text-white mb-2">Timeless Elegance</h6>
                    <p class="text-white-50 small mb-0">Crafting premium menswear with exceptional attention to detail since 2010.</p>
                </div>
                <div class="col-md-3 mb-3">
                    <h6 class="text-white mb-2">Collections</h6>
                    <ul class="list-unstyled text-white-50 small mb-0">
                        <li class="mb-1"><a href="public/category.php?categ=Shirts" class="text-white-50 text-decoration-none">Designer Shirts</a></li>
                        <li class="mb-1"><a href="public/category.php?categ=Blazers" class="text-white-50 text-decoration-none">Premium Blazers</a></li>
                        <li class="mb-1"><a href="public/category.php?categ=Trousers" class="text-white-50 text-decoration-none">Tailored Trousers</a></li>
                        <li class="mb-1"><a href="public/category.php?categ=Seasonal" class="text-white-50 text-decoration-none">Limited Edition</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h6 class="text-white mb-2">Client Services</h6>
                    <ul class="list-unstyled text-white-50 small mb-0">
                        <li class="mb-1"><a href="#" class="text-white-50 text-decoration-none">Shipping & Returns</a></li>
                        <li class="mb-1"><a href="#" class="text-white-50 text-decoration-none">Store Policy</a></li>
                        <li class="mb-1"><a href="#" class="text-white-50 text-decoration-none">Payment Methods</a></li>
                        <li class="mb-1"><a href="#" class="text-white-50 text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-3">
                    <h6 class="text-white mb-2">Connect With Us</h6>
                    <p class="text-white-50 small mb-1">15 Avenue des Champs-Élysées, Paris</p>
                    <p class="text-white-50 small mb-2">Email: <a class="text-white-50 text-decoration-none" href="mailto:contact@timelesselegance.com">contact@timelesselegance.com</a></p>
                    <div class="d-flex">
                        <a href="#" class="text-white-50 me-3 social-icon"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white-50 me-3 social-icon"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white-50 me-3 social-icon"><i class="bi bi-pinterest"></i></a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="m-0 text-white-50 small copyright">© <?php echo date('Y'); ?> Timeless Elegance. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="m-0 text-white-50 small copyright">
                        <a href="#" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                        <a href="#" class="text-white-50 text-decoration-none">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
        <!-- Bootstrap JS Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <!-- Custom JS -->
        <script src="public/static/js/main.js"></script>
    </footer>
</body>
</html>