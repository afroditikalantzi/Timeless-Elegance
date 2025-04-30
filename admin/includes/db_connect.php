<?php
// Database connection file using mysqli

// Check if connection already exists to avoid re-declaration
if (!isset($conn)) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "eshop";
    
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    // Check connection
    if (!$conn) {
        // Log the error or handle it more gracefully in production
        error_log('Database Connection Error: ' . mysqli_connect_error());
        // Display a user-friendly error message
        die("Database connection failed. Please try again later or contact support.");
    }
    
    // Set charset
    mysqli_set_charset($conn, 'utf8mb4');
}