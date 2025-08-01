<?php
$page_title = 'Customer Management';
require_once 'includes/header.php';

// Form for adding or editing a customer
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
                // Insert new customer
                $sql = "INSERT INTO customer (firstName, lastName, email, phone, address, city, postalCode, country) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssssss", $firstName, $lastName, $email, $phone, $address, $city, $postalCode, $country);
            }

            if (mysqli_stmt_execute($stmt)) {
                $success_message = $customer_id ? "Customer updated successfully!" : "Customer added successfully!";
            } else {
                $error_message = $customer_id ? "Error updating customer." : "Error adding customer.";
            }
        }
    }
}

// Form for deleting a customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer'])) {
    $delete_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);

    if ($delete_id) {
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
            $error_message = "Cannot delete customer because they have {$order_count} order(s)";
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
    } else {
        $error_message = "Invalid customer ID.";
    }
}

$customers = [];
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

?>

<!-- Content -->
<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-12 mb-4">
        <button type="button" class="btn primary-btn" data-bs-toggle="modal" data-bs-target="#customerModal">
            <i class="bi bi-plus-circle me-2"></i> Add New Customer
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
                                        <button type="button" class="edit-btn btn-sm me-1"
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
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <form method="post" action="customers.php" class="delete-customer-form" style="display:inline;">
                                            <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">
                                            <input type="hidden" name="delete_customer" value="1">
                                            <button
                                                type="button"
                                                class="delete-btn me-1"
                                                data-customer-name="<?= htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName']) ?>"
                                                data-order-count="<?= (int)$customer['order_count'] ?>">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>

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

<!-- Add/Edit  Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="customers.php" id="customerForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add/Edit Customer</h5>
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

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_customer" class="btn primary-btn">Save Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCustomerModal" tabindex="-1" aria-labelledby="deleteCustomerLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCustomerLabel">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete
                <strong id="deleteCustomerName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn primary-btn" id="confirmDeleteCustomerBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Cannot Delete Modal -->
<div class="modal fade" id="cannotDeleteCustomerModal" tabindex="-1" aria-labelledby="cannotDeleteCustomerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cannotDeleteCustomerLabel">Cannot Delete Customer</h5>
            </div>
            <div class="modal-body" id="cannotDeleteCustomerBody">
                <!-- Filled by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn primary-btn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<?php require_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ----- Add/Edit Logic -----
        var customerModal = document.getElementById('customerModal');
        customerModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var modalTitle = customerModal.querySelector('.modal-title');
            var customerForm = document.getElementById('customerForm');
            var customerIdInput = document.getElementById('customerId');

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
            } else {
                // Add mode
                modalTitle.textContent = 'Add New Customer';
                customerForm.reset(); // Clear the form
                customerIdInput.value = ''; // Ensure customer ID is empty
            }
        });

        // ----- Delete Logic -----
        const deleteCustModalEl = document.getElementById('deleteCustomerModal');
        const deleteCustModal = new bootstrap.Modal(deleteCustModalEl);
        const cannotDelCustModalEl = document.getElementById('cannotDeleteCustomerModal');
        const cannotDelCustModal = new bootstrap.Modal(cannotDelCustModalEl);

        const deleteCustNameEl = document.getElementById('deleteCustomerName');
        const cannotDelCustBodyEl = document.getElementById('cannotDeleteCustomerBody');
        const confirmDeleteBtn = document.getElementById('confirmDeleteCustomerBtn');

        let formToDeleteCust = null;

        // Handle all delete‑button clicks
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                const name = this.dataset.customerName;
                const count = parseInt(this.dataset.orderCount, 10);

                if (count > 0) {
                    // Show “cannot delete” if they have orders
                    cannotDelCustBodyEl.textContent =
                        `Cannot delete "${name}" because they have ${count} order(s).`;
                    cannotDelCustModal.show();
                } else {
                    // Otherwise show the confirmation dialog
                    formToDeleteCust = this.closest('form');
                    deleteCustNameEl.textContent = name;
                    deleteCustModal.show();
                }
            });
        });

        // On confirm click, submit the form
        confirmDeleteBtn.addEventListener('click', function() {
            if (formToDeleteCust) {
                deleteCustModal.hide();
                formToDeleteCust.submit();
            }
        });
    });
</script>