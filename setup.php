<?php
/**
 * Master Setup File for eShop
 * This file creates all database tables and inserts sample data
 */

// Display all errors for debugging during setup
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    echo "<div class='setup-success'>Database created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating database: " . mysqli_error($conn) . "</div>";
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
    image VARCHAR(255),
    feature TINYINT(1) DEFAULT 0
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='setup-success'>Table product created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
}

// Create category table
$sql = "CREATE TABLE IF NOT EXISTS category (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoryName VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='setup-success'>Table category created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
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
    echo "<div class='setup-success'>Table customer created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
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
    echo "<div class='setup-success'>Table orders created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
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
    echo "<div class='setup-success'>Table admin created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
}

// Create settings table
$sql = "CREATE TABLE IF NOT EXISTS settings (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "<div class='setup-success'>Table settings created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
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
    echo "<div class='setup-success'>Table order_items created successfully</div>";
} else {
    echo "<div class='setup-error'>Error creating table: " . mysqli_error($conn) . "</div>";
}


//INSERT DATA TO TABLES


// Check if product data already exists
$check = mysqli_query($conn, "SELECT * FROM product LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    $sql = "INSERT INTO product (productName, description, price, salePrice, category, image, feature) 
        VALUES 
        ('Tailored Oxford Shirt', 'Crafted from premium Egyptian cotton with mother-of-pearl buttons. A timeless addition to any gentleman\'s wardrobe.', 120.00, 0, 'Shirts', 'static/images/products/tailored-oxford-shirt.png', 1),
        ('Italian Linen Shirt', 'Lightweight and breathable Italian linen, perfect for warm weather occasions. Features a modern slim fit with subtle texture.', 95.00, 85.00, 'Shirts', 'static/images/products/italian-linen-shirt.png', 0),
        ('French Cuff Dress Shirt', 'Elegant dress shirt with French cuffs, ideal for formal events. Made from 120-thread count cotton with a subtle herringbone pattern.', 110.00, 0, 'Shirts', 'static/images/products/french-cuff-dress-shirt.png', 0),
        
        ('Cashmere Wool Blazer', 'Luxurious cashmere-wool blend blazer with hand-stitched details and horn buttons. Perfect for both formal and smart-casual occasions.', 450.00, 0, 'Blazers', 'static/images/products/cashmere-wool-blazer.png', 1),
        ('Italian Silk Blazer', 'Lightweight summer blazer crafted from the finest Italian silk. Features a half-canvas construction and mother-of-pearl buttons.', 380.00, 320.00, 'Blazers', 'static/images/products/italian-silk-blazer.png', 0),
        ('Herringbone Tweed Blazer', 'Classic herringbone pattern in premium British tweed. Fully lined with custom paisley print and genuine leather elbow patches.', 395.00, 0, 'Blazers', 'static/images/products/herringbone-tweed-blazer.png', 0),
        
        ('Merino Wool Trousers', 'Tailored from superfine merino wool with a natural stretch for comfort. Features side adjusters and a clean, flat front design.', 180.00, 0, 'Trousers', 'static/images/products/merino-wool-trousers.png', 1),
        ('Italian Cotton Chinos', 'Premium cotton chinos with a subtle texture. Garment-dyed for rich color and pre-washed for a comfortable feel from the first wear.', 140.00, 120.00, 'Trousers', 'static/images/products/italian-cotton-chinos.png', 0),
        ('Tailored Flannel Trousers', 'Luxurious flannel trousers with a classic fit. Perfect for cooler weather with a soft hand feel and elegant drape.', 165.00, 0, 'Trousers', 'static/images/products/tailored-flannel-trousers.png', 0),
        
        ('Limited Edition Linen Suit', 'Exclusive summer collection featuring a lightweight linen suit in a distinctive sand tone. Limited quantities available.', 580.00, 0, 'Seasonal', 'static/images/products/limited-edition-linen-suit.png', 1),
        ('Autumn Cashmere Sweater', 'Seasonal pure cashmere sweater with a contemporary fit. Perfect layering piece for the autumn months.', 220.00, 190.00, 'Seasonal', 'static/images/products/autumn-cashmere-sweater.png', 0),
        ('Winter Collection Overcoat', 'Premium wool-blend overcoat from our winter collection. Features a tailored silhouette and luxurious satin lining.', 495.00, 0, 'Seasonal', 'static/images/products/winter-collection-overcoat.png', 0)

       ";
    
    if (mysqli_query($conn, $sql)) {
        echo "<div class='setup-success'>Sample products added successfully</div>";
    } else {
        echo "<div class='setup-error'>Error adding sample products: " . mysqli_error($conn) . "</div>";
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
        echo "<div class='setup-success'>Admin account created successfully</div>";
        echo "<div class='setup-credentials'><strong>Login credentials:</strong> Username: <code>$admin_username</code>, Password: <code>$admin_password</code></div>";
    } else {
        echo "<div class='setup-error'>Error creating admin account: " . mysqli_error($conn) . "</div>";
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
        echo "<div class='setup-success'>Default settings created successfully</div>";
    } else {
        echo "<div class='setup-error'>Error creating default settings: " . mysqli_error($conn) . "</div>";
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
        echo "<div class='setup-success'>Sample categories added successfully</div>";
    } else {
        echo "<div class='setup-error'>Error adding sample categories: " . mysqli_error($conn) . "</div>";
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
    <link rel="icon" type="image/x-icon" href="public/static/static/images/products//favicon.png" />
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
            background-color: #f9f7f5;
        }

        .setup-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e8e4e0;
        }

        .setup-title {
            font-family: 'Cormorant Garamond', serif;
            color: #333f4d;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .setup-title::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background-color: #c19a6b;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .setup-messages {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            max-height: 450px;
            overflow-y: auto;
            border: 1px solid #e8e4e0;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            line-height: 1.8;
            color: #333;
        }
        
        .setup-success {
            color: #2e7d32;
            background-color: #e8f5e9;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .setup-error {
            color: #c62828;
            background-color: #ffebee;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .setup-credentials {
            background-color: #fff8e1;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }
        
        .setup-credentials code {
            background-color: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #d81b60;
            font-weight: bold;
        }
        
        .setup-messages br {
            display: block;
            margin: 8px 0;
            content: "";
        }
        
        .setup-card h1 {
            margin-bottom: 1.5rem;
        }
        
        .setup-card .btn {
            font-weight: 500;
            letter-spacing: 0.5px;
            padding: 10px 25px;
            transition: all 0.3s ease;
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
                        <h1 class="setup-title">Timeless Elegance Shop Setup</h1>
                        <p class="text-center mb-4" style="color: #666; font-family: 'Montserrat', sans-serif;">The database and required tables have been created successfully.</p>
                        <div class="setup-messages">
                            <?php echo $setup_messages; ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="public/index.php" class="btn btn-primary me-3" style="background-color: #c19a6b; border-color: #c19a6b; font-family: 'Montserrat', sans-serif; font-weight: 500; letter-spacing: 0.5px; padding: 12px 25px;">
                                <i class="bi bi-shop me-2"></i>Go to Store
                            </a>
                            <a href="admin/login.php" class="btn btn-outline-secondary" style="color: #333f4d; border-color: #e8e4e0; font-family: 'Montserrat', sans-serif; font-weight: 500; letter-spacing: 0.5px; padding: 12px 25px;">
                                <i class="bi bi-gear me-2"></i>Admin Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer-->
    <footer class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <p class="m-0 text-white-50 small copyright">© 2025 Timeless Elegance. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="public/static/js/main.js"></script>
</body>
</html>