<?php
session_start();
require_once '../public/includes/db_connect.php';

$error = '';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        // Check if username exists
        $sql = "SELECT id, username, password FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $row['password'])) {
                // Password is correct, start a new session
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                
                // Redirect to admin dashboard
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Invalid username";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Timeless Elegance Admin Login" />
    <title>Admin Login - Timeless Elegance</title>
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
    <!-- Admin Dashboard Styles -->
    <link href="static/css/admin-dashboard.css" rel="stylesheet" />
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <span class="login-brand">Timeless Elegance</span>
            <span class="login-subtitle">Admin Dashboard</span>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p>Return to <a href="../public/index.php">Store Front</a></p>
        </div>
    </div>
</body>
</html>