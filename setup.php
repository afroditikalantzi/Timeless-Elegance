<?php
// Database connection parameters
$dbserver = "localhost";
$mysql_username = "root";
$mysql_password = "";

// Connect to MySQL server
$conn = mysqli_connect($dbserver, $mysql_username, $mysql_password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
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
    feauture TINYINT(1) DEFAULT 0
)";

if (mysqli_query($conn, $sql)) {
    echo "Table 'product' created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

// Insert sample data
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

// Check if data already exists before inserting
$check = mysqli_query($conn, "SELECT * FROM product LIMIT 1");
if (mysqli_num_rows($check) == 0) {
    if (mysqli_query($conn, $sql)) {
        echo "Sample data inserted successfully<br>";
    } else {
        echo "Error inserting sample data: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Data already exists, skipping sample data insertion<br>";
}

echo "<p>Setup complete! <a href='index.php'>Go to homepage</a></p>";

mysqli_close($conn);
?>