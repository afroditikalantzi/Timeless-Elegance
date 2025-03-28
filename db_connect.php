<?php
// Database connection parameters
$dbserver = "localhost";
$mysql_username = "root";
$mysql_password = "";
$db_name = "eshop";

// Connect to MySQL server
$conn = mysqli_connect($dbserver, $mysql_username, $mysql_password, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>