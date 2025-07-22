<?php
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
        error_log('Database Connection Error: ' . mysqli_connect_error());
        die("Database connection failed. Please try again later");
    }

    // Set charset
    mysqli_set_charset($conn, 'utf8mb4');
}
