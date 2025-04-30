<?php
// Database connection
$conn = mysqli_connect('localhost', 'root', '', 'eshop');

// Check connection
if(!$conn){
    echo 'Connection error: ' . mysqli_connect_error();
}

// Check if maintenance mode is enabled
$maintenance_check = mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_name = 'maintenance_mode'");
if ($maintenance_check && $row = mysqli_fetch_assoc($maintenance_check)) {
    $maintenance_mode = $row['setting_value'];
    
    // If maintenance mode is enabled and user is not in admin area
    if ($maintenance_mode == '1' && strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
        // Only allow access to maintenance page
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page !== 'maintenance.php') {
            header('Location: maintenance.php');
            exit;
        }
    }
}
?>