<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Corrected path to admin login
    exit;
}

$success_message = '';
$error_message = '';
?>

<?php
  $sidebarState = (isset($_COOKIE['eshopAdminSidebarState']) && $_COOKIE['eshopAdminSidebarState']==='open')
                  ? ' sidebar-active'
                  : '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Timeless Elegance Admin Dashboard" />
    <title><?php echo isset($page_title) ? $page_title : 'Admin Dashboard'; ?> - Timeless Elegance</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="../assets/favicon.png" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Custom Styles -->
    <link href="static/css/styles.css" rel="stylesheet" />
    <link href="static/css/responsive.css" rel="stylesheet" />
    <style>
    /* Animation for flash messages */
    .alert {
        transition: opacity 0.5s ease-out;
    }
    .fade-out {
        opacity: 0;
    }
    </style>
    <?php if(isset($extra_css)) echo $extra_css; ?>
    <!-- Admin Scripts -->
    
    <script>
    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        if (alerts.length > 0) {
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    // Add fade-out class for smooth transition
                    alert.classList.add('fade-out');
                    // Remove the element after animation completes
                    setTimeout(function() {
                        alert.remove();
                    }, 500); // 500ms for fade animation
                }, 5000); // 5 seconds
            });
        }
    });

    
    </script>
</head>
<body class="admin-body<?php echo $sidebarState; ?>">
    <!-- Sidebar Toggle Button (Styled with CSS) -->
    <button class="sidebar-toggle" id="sidebarToggle">
    </button>

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-brand">Timeless Elegance</a>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="bi bi-box-seam"></i> Products</a></li>
                <li><a href="categories.php"><i class="bi bi-tags"></i> Categories</a></li>
                <li><a href="orders.php"><i class="bi bi-receipt"></i> Orders</a></li>
                <li><a href="customers.php"><i class="bi bi-people"></i> Customers</a></li>
                <li><a href="reports.php"><i class="bi bi-bar-chart-line"></i> Reports</a></li>
                <li><a href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
            </ul>
            <ul style="margin-top: auto; padding-bottom: 20px;"> 
                <li><a href="../public/index.php" target="_blank"><i class="bi bi-shop"></i> Visit Store</a></li>
                <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <div class="admin-wrapper">

        <!-- Main Content -->
        <main class="admin-main" id="adminMain"> <!-- Add sidebar-active class dynamically -->
            <!-- Header -->
            <header class="admin-header"> <!-- Add sidebar-active class dynamically -->
                <div class="admin-header-title">
                    <h1><?php echo isset($page_title) ? $page_title : 'Admin Dashboard'; ?></h1>
                </div>
               
            </header>

            <!-- Content -->
            <div class="admin-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>