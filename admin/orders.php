<?php
// Set page title
$page_title = "Order Management";

// Add custom CSS for order page
$extra_css = '<style>
    .customer-detail {
        margin-bottom: 5px;
    }
    .customer-label {
        width: 60px;
    }
    .order-items {
        margin-top: 15px;
    }
</style>';

// Include header
require_once 'includes/header.php'; // This should include db_connect.php which defines $conn

// Variables are already set in header.php
// $success_message and $error_message

// No longer need these variables since view functionality is moved to order_details.php

// Update functionality has been moved to order_details.php

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    try {
        // First delete order items
        $delete_items_sql = "DELETE FROM order_items WHERE order_id = ?";
        $delete_items_stmt = mysqli_prepare($conn, $delete_items_sql);
        mysqli_stmt_bind_param($delete_items_stmt, "i", $delete_id);
        mysqli_stmt_execute($delete_items_stmt);
        mysqli_stmt_close($delete_items_stmt);
        
        // Then delete the order
        $delete_order_sql = "DELETE FROM orders WHERE id = ?";
        $delete_order_stmt = mysqli_prepare($conn, $delete_order_sql);
        mysqli_stmt_bind_param($delete_order_stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($delete_order_stmt)) {
            $success_message = "Order #" . $delete_id . " deleted successfully!";
        } else {
            $error_message = "Error deleting order.";
        }
        mysqli_stmt_close($delete_order_stmt);
    } catch (Exception $e) {
        $error_message = "Database error deleting order: " . $e->getMessage();
    }
}

// Get all orders for display using mysqli
$orders = [];
try {
    // Get items per page setting
    $items_per_page = get_items_per_page($conn);
    
    // Set up pagination
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // Get total order count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM orders";
    $count_result = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_orders = $count_row['total'];
    $total_pages = ceil($total_orders / $items_per_page);
    mysqli_free_result($count_result);
    
    // Get orders for current page
    $sql = "SELECT o.*, o.total_amount as total, c.firstName, c.lastName 
            FROM orders o 
            JOIN customer c ON o.customer_id = c.id 
            ORDER BY o.id DESC
            LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $items_per_page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    // Count items in each order using mysqli
    $count_sql = "SELECT COUNT(*) as count, SUM(quantity) as total_items FROM order_items WHERE order_id = ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);

    foreach ($orders as $key => $order) {
        $order_id = $order['id'];
        mysqli_stmt_bind_param($count_stmt, "i", $order_id);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $count_row = mysqli_fetch_assoc($count_result);
        $orders[$key]['item_count'] = $count_row['count'] ?? 0;
        $orders[$key]['total_items'] = $count_row['total_items'] ?? 0;
        mysqli_free_result($count_result);
    }
    mysqli_stmt_close($count_stmt);
} catch (Exception $e) {
    $error_message = "Database error fetching orders: " . $e->getMessage();
    // Optionally display an error message or log the error
}

?>

<!-- Content -->
            <div class="admin-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <?php // The view functionality has been moved to order_details.php ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="admin-card">
                                <h2 class="admin-card-title">Order List</h2>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Customer</th>
                                                <th>Items</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($orders) > 0): ?>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                                        <td><?php echo htmlspecialchars($order['firstName'] . ' ' . $order['lastName']); ?></td>
                                                        <td><?php echo htmlspecialchars($order['total_items']); ?> (<?php echo htmlspecialchars($order['item_count']); ?> products)</td>
                                                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                                                        <td>
                                                            <div class="order-status status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                                                <?php echo htmlspecialchars($order['status']); ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="order_details.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="btn-edit me-2">
                                                                <i class="bi bi-eye"></i> View
                                                            </a>
                                                            <a href="orders.php?delete=<?php echo htmlspecialchars($order['id']); ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                                                                <i class="bi bi-trash"></i> Delete
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="7" class="text-center">No orders found</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php // Removed extra endif here to fix parse error ?>
                
                <!-- Pagination Controls -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-container mt-4">
                    <?php echo generate_pagination($current_page, $total_pages, 'orders.php?'); ?>
                </div>
                <?php endif; ?>
            </div> <!-- Closing admin-content -->
<?php
// Include footer
require_once 'includes/footer.php';
?>