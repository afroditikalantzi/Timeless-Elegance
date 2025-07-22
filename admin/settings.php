<?php
$page_title = 'Admin Settings';
require_once 'includes/header.php';

// Initialize variables
$current_password = '';
$new_password = '';
$confirm_password = '';
$maintenance_mode = '';
$items_per_page = '';
$error_message = '';
$success_message = '';
$settings_error_message = '';
$settings_success_message = '';

// Get current settings
    $settings_sql = "SELECT * FROM settings";
    $settings_result = mysqli_query($conn, $settings_sql);
    $settings = [];
    
    if ($settings_result && mysqli_num_rows($settings_result) > 0) {
        while ($row = mysqli_fetch_assoc($settings_result)) {
            $settings[$row['setting_name']] = $row['setting_value'];
        }
    }
    
    $maintenance_mode = $settings['maintenance_mode'] ?? '0';
    $items_per_page = $settings['items_per_page'] ?? '10';
    
    mysqli_free_result($settings_result);

// Form for updating settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted
    if (isset($_POST['update_password'])) {
        // Password update form
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $admin_id = $_SESSION['admin_id'];
        
        // Process password change
        processPasswordChange($conn, $current_password, $new_password, $confirm_password, $admin_id);
    } elseif (isset($_POST['update_settings'])) {
        // Site settings form
        $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
        $items_per_page = $_POST['items_per_page'] ?? '10';
        
        // Validate items per page
        if (!is_numeric($items_per_page) || $items_per_page < 5 || $items_per_page > 100) {
            $settings_error_message = 'Items per page must be a number between 5 and 100.';
        } else {
            // Update settings
            try {
                // Check if settings exist first
                $check_sql = "SELECT COUNT(*) FROM settings WHERE setting_name = ?";
                $check_stmt = mysqli_prepare($conn, $check_sql);
                
                // Check maintenance_mode setting
                mysqli_stmt_bind_param($check_stmt, "s", $setting_name);
                $setting_name = 'maintenance_mode';
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_bind_result($check_stmt, $count);
                mysqli_stmt_fetch($check_stmt);
                $maintenance_exists = $count > 0;
                mysqli_stmt_reset($check_stmt);
                
                // Check items_per_page setting
                $setting_name = 'items_per_page';
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_bind_result($check_stmt, $count);
                mysqli_stmt_fetch($check_stmt);
                $items_exists = $count > 0;
                mysqli_stmt_close($check_stmt);
                
                // Prepare statements based on existence
                if ($maintenance_exists) {
                    $update_maintenance = mysqli_prepare($conn, "UPDATE settings SET setting_value = ? WHERE setting_name = 'maintenance_mode'");
                    mysqli_stmt_bind_param($update_maintenance, "s", $maintenance_mode);
                } else {
                    $update_maintenance = mysqli_prepare($conn, "INSERT INTO settings (setting_name, setting_value) VALUES ('maintenance_mode', ?)");
                    mysqli_stmt_bind_param($update_maintenance, "s", $maintenance_mode);
                }
                
                if ($items_exists) {
                    $update_items = mysqli_prepare($conn, "UPDATE settings SET setting_value = ? WHERE setting_name = 'items_per_page'");
                    mysqli_stmt_bind_param($update_items, "s", $items_per_page);
                } else {
                    $update_items = mysqli_prepare($conn, "INSERT INTO settings (setting_name, setting_value) VALUES ('items_per_page', ?)");
                    mysqli_stmt_bind_param($update_items, "s", $items_per_page);
                }
                
                // Execute the statements
                mysqli_stmt_execute($update_maintenance);
                mysqli_stmt_execute($update_items);
                
                mysqli_stmt_close($update_maintenance);
                mysqli_stmt_close($update_items);
                
                $settings_success_message = 'Settings updated successfully.';
            } catch (Exception $e) {
                $settings_error_message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

function processPasswordChange($conn, $current_password, $new_password, $confirm_password, $admin_id) {
    global $error_message, $success_message, $current_password, $new_password, $confirm_password;
    
    // Basic validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New password and confirmation password do not match.';
    } else {
        // Fetch current password hash from database
            $stmt = mysqli_prepare($conn, "SELECT password FROM admin WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $admin_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $password_hash);
            $found = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($found && password_verify($current_password, $password_hash)) {
                // Current password is correct, hash the new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password in the database
                $update_stmt = mysqli_prepare($conn, "UPDATE admin SET password = ? WHERE id = ?");
                mysqli_stmt_bind_param($update_stmt, "si", $new_password_hash, $admin_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success_message = 'Password updated successfully.';
                    // Clear password fields after successful update
                    $current_password = '';
                    $new_password = '';
                    $confirm_password = '';
                } else {
                    $error_message = 'Failed to update password. Please try again.';
                }
                
                mysqli_stmt_close($update_stmt);
            } else {
                $error_message = 'Incorrect current password.';
            }

    }
}
?>

    
    <div class="row">
        <!-- Site Settings Card -->
        <div class="col-lg-6 mb-4">
            <div class="admin-card">
                <h2 class="admin-card-title">Site Settings</h2>
                <div class="admin-card-body">
                    <?php if (!empty($settings_success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($settings_success_message); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings_error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($settings_error_message); ?></div>
                    <?php endif; ?>
                    
                    <form action="settings.php" method="POST" class="settings-form">
                        <div class="form-group mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" <?php echo $maintenance_mode == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                            </div>
                            <div class="settings-form-text">When enabled, the public storefront will display a maintenance message.</div>
                        </div>
                        
                        <div class="form-group mb-4">
                            <label for="items_per_page" class="settings-form-label">Items Per Page</label>
                            <input type="number" class="form-control" id="items_per_page" name="items_per_page" value="<?php echo htmlspecialchars($items_per_page); ?>" min="5" max="100" required>
                            <div class="settings-form-text">Number of items to display per page in admin listings (products, categories, customers, orders).</div>
                        </div>
                        
                        <button type="submit" name="update_settings" class="btn primary-btn">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Password Change Card -->
        <div class="col-lg-6 mb-4">
            <div class="admin-card">
                <h2 class="admin-card-title">Change Password</h2>
                <div class="admin-card-body">
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    
                    <form action="settings.php" method="POST" class="password-form">
                        <div class="form-group mb-3">
                            <label for="current_password" class="settings-form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="new_password" class="settings-form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="confirm_password" class="settings-form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="update_password" class="btn primary-btn">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>