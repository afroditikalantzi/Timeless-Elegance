<?php
// Common functions for admin area

/**
 * Check if user is logged in, if not redirect to login page
 */
function check_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Get items per page setting from database
 * @return int Number of items to display per page
 */
function get_items_per_page($conn) {
    try {
        $sql = "SELECT setting_value FROM settings WHERE setting_name = 'items_per_page'";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return (int)$row['setting_value'];
        } else {
            return 10; // Default to 10 if not found
        }
    } catch (Exception $e) {
        // If there's an error, return default value
        return 10;
    }
}

// Format currency values consistently
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

// Generate pagination links
function generate_pagination($current_page, $total_pages, $base_url) {
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    $prev_disabled = ($current_page <= 1) ? 'disabled' : '';
    $prev_url = ($current_page > 1) ? $base_url . '&page=' . ($current_page - 1) : '#';
    $html .= '<li class="page-item ' . $prev_disabled . '"><a class="page-link" href="' . $prev_url . '">&laquo; Previous</a></li>';
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next button
    $next_disabled = ($current_page >= $total_pages) ? 'disabled' : '';
    $next_url = ($current_page < $total_pages) ? $base_url . '&page=' . ($current_page + 1) : '#';
    $html .= '<li class="page-item ' . $next_disabled . '"><a class="page-link" href="' . $next_url . '">Next &raquo;</a></li>';
    
    $html .= '</ul></nav>';
    return $html;
}

// Get order status with appropriate CSS class
function get_order_status_class($status) {
    $status_classes = [
        'Pending' => 'status-pending',
        'Processing' => 'status-processing',
        'Shipped' => 'status-shipped',
        'Delivered' => 'status-delivered',
        'Cancelled' => 'status-cancelled'
    ];
    
    $class = isset($status_classes[$status]) ? $status_classes[$status] : '';
    return ['text' => $status, 'class' => $class];
}

// Generate a random order reference number
function generate_order_reference() {
    return 'ORD-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

// Check if user has admin permissions
function is_admin() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Sanitize input data
function sanitize_input($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

// Log admin activity
function log_admin_activity($action, $details = '', $admin_id = null) {
    global $conn;
    
    if ($admin_id === null && isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
    }
    
    if ($admin_id) {
        $action = sanitize_input($conn, $action);
        $details = sanitize_input($conn, $details);
        
        $sql = "INSERT INTO admin_activity_log (admin_id, action, details, log_date) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $admin_id, $action, $details);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}