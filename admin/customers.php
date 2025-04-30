<?php
// Set page title for header
$page_title = 'Customer Management';

// Include header file
require_once 'includes/header.php'; // This includes db_connect.php which defines $conn

// Initialize variables
$edit_customer = null;
$error_message = '';
$success_message = '';

// Process form submission for adding/editing a customer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_customer'])) {
    // Get form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $postalCode = trim($_POST['postalCode']);
    $country = trim($_POST['country']);
    $customer_id = isset($_POST['customer_id']) ? filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT) : null;

    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error_message = "Please fill in all required fields";
    } else {
        try {
            // Check if email already exists (excluding the current customer if editing)
            $check_sql = "SELECT id FROM customer WHERE email = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            
            if ($customer_id) {
                $check_sql = "SELECT id FROM customer WHERE email = ? AND id != ?";
                $check_stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($check_stmt, "si", $email, $customer_id);
            } else {
                mysqli_stmt_bind_param($check_stmt, "s", $email);
            }
            
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error_message = "A customer with this email already exists";
            } else {
                // Check if we're updating an existing customer or adding a new one
                if ($customer_id) {
                    // Update existing customer
                    $sql = "UPDATE customer SET firstName = ?, lastName = ?, email = ?, phone = ?, address = ?, city = ?, postalCode = ?, country = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "ssssssssi", $firstName, $lastName, $email, $phone, $address, $city, $postalCode, $country, $customer_id);
                } else {
                    // For new customers, we need a password
                    $password = password_hash('default123', PASSWORD_DEFAULT); // Default password that should be changed

                    // Insert new customer
                    $sql = "INSERT INTO customer (firstName, lastName, email, phone, address, city, postalCode, country, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "sssssssss", $firstName, $lastName, $email, $phone, $address, $city, $postalCode, $country, $password);
                }

                if (mysqli_stmt_execute($stmt)) {
                    $success_message = $customer_id ? "Customer updated successfully!" : "Customer added successfully!";
                } else {
                    $error_message = $customer_id ? "Error updating customer." : "Error adding customer.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Edit request is now handled by JavaScript via modal data attributes.
// The $edit_customer variable is no longer needed for modal pre-population.
// The form submission logic still handles updates based on $_POST['customer_id'].

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    try {
        // Check if customer has orders
        $check_sql = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $delete_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $row = mysqli_fetch_assoc($result);
        $order_count = $row['order_count'];
        mysqli_stmt_close($check_stmt);

        if ($order_count > 0) {
            $error_message = "Cannot delete customer because they have " . $order_count . " order(s)";
        } else {
            // Delete the customer
            $delete_sql = "DELETE FROM customer WHERE id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);

            if (mysqli_stmt_execute($delete_stmt)) {
                $success_message = "Customer deleted successfully!";
            } else {
                $error_message = "Error deleting customer.";
            }
            mysqli_stmt_close($delete_stmt);
        }
    } catch (Exception $e) {
        $error_message = "Database error deleting customer: " . $e->getMessage();
    }
}

// Get all customers for display
$customers = [];
try {
    // Get items per page setting
    $items_per_page = get_items_per_page($conn);
    
    // Set up pagination
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // Get total customer count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM customer";
    $count_result = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_customers = $count_row['total'];
    $total_pages = ceil($total_customers / $items_per_page);
    mysqli_free_result($count_result);
    
    // Get customers for current page
    $sql = "SELECT c.*, COUNT(o.id) as order_count 
            FROM customer c 
            LEFT JOIN orders o ON c.id = o.customer_id 
            GROUP BY c.id 
            ORDER BY c.id DESC
            LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $items_per_page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    $error_message = "Database error fetching customers: " . $e->getMessage();
}

?>

<?php
// No custom CSS needed, styles are in main styles.css
$extra_css = '';
?>

<!-- Customers Content -->
            <div class="admin-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12 mb-4">
                        <button type="button" class="btn-admin" data-bs-toggle="modal" data-bs-target="#customerModal">
                            <i class="bi bi-plus-circle"></i> Add New Customer
                        </button>
                    </div>
                    <div class="col-12">
                        <div class="admin-card">
                            <h2 class="admin-card-title">Customer List</h2>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Location</th>
                                            <th>Orders</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($customers) > 0): ?>
                                            <?php foreach ($customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName']); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars(($customer['city'] ?? '') . ', ' . ($customer['country'] ?? '')); ?></td>
                                                    <td><?php echo htmlspecialchars($customer['order_count']); ?></td>
                                                    <td>
                                                        <button type="button" class="btn-edit btn-sm me-1" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#customerModal" 
                                                                data-customer-id="<?php echo $customer['id']; ?>" 
                                                                data-customer-firstname="<?php echo htmlspecialchars($customer['firstName']); ?>" 
                                                                data-customer-lastname="<?php echo htmlspecialchars($customer['lastName']); ?>" 
                                                                data-customer-email="<?php echo htmlspecialchars($customer['email']); ?>" 
                                                                data-customer-phone="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" 
                                                                data-customer-address="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>" 
                                                                data-customer-city="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>" 
                                                                data-customer-postalcode="<?php echo htmlspecialchars($customer['postalCode'] ?? ''); ?>" 
                                                                data-customer-country="<?php echo htmlspecialchars($customer['country'] ?? ''); ?>">
                                                            <i class="bi bi-pencil"></i> Edit
                                                        </button>
                                                        <a href="customers.php?delete=<?php echo $customer['id']; ?>" class="btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No customers found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pagination Controls -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-container mt-4">
                    <?php echo generate_pagination($current_page, $total_pages, 'customers.php?'); ?>
                </div>
                <?php endif; ?>
            </div> <!-- Closing admin-content -->

<!-- Add/Edit Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="customers.php" id="customerForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add/Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="customer_id" id="customerId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="postalCode" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="postalCode" name="postalCode">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country">
                        </div>
                    </div>
                    <!-- Password field is only relevant for adding, not editing via admin panel -->
                    <!-- <div class="mb-3" id="passwordField">
                        <label for="password" class="form-label">Password (for new customers)</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank if editing. New customers get a default password.</small>
                    </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_customer" class="btn-admin">Save Customer</button> 
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var customerModal = document.getElementById('customerModal');
    customerModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var modalTitle = customerModal.querySelector('.modal-title');
        var customerForm = document.getElementById('customerForm');
        var customerIdInput = document.getElementById('customerId');
        // var passwordField = document.getElementById('passwordField');

        // Check if the button has customer data (meaning it's an edit)
        var customerId = button.getAttribute('data-customer-id');

        if (customerId) {
            // Edit mode
            modalTitle.textContent = 'Edit Customer';
            customerIdInput.value = customerId;
            document.getElementById('firstName').value = button.getAttribute('data-customer-firstname');
            document.getElementById('lastName').value = button.getAttribute('data-customer-lastname');
            document.getElementById('email').value = button.getAttribute('data-customer-email');
            document.getElementById('phone').value = button.getAttribute('data-customer-phone');
            document.getElementById('address').value = button.getAttribute('data-customer-address');
            document.getElementById('city').value = button.getAttribute('data-customer-city');
            document.getElementById('postalCode').value = button.getAttribute('data-customer-postalcode');
            document.getElementById('country').value = button.getAttribute('data-customer-country');
            // passwordField.style.display = 'none'; // Hide password field when editing
        } else {
            // Add mode
            modalTitle.textContent = 'Add New Customer';
            customerForm.reset(); // Clear the form
            customerIdInput.value = ''; // Ensure customer ID is empty
            // passwordField.style.display = 'block'; // Show password field when adding (though we set default server-side)
        }
    });
});
</script>