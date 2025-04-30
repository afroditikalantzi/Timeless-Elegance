<?php
// Set page title
$page_title = "Order Details";



// Include header
require_once 'includes/header.php'; // This includes db_connect.php which defines $conn

// Initialize variables
$order = null;
$order_items = [];
$error_message = '';
$success_message = '';

// Process form submission for updating an order
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    // Get form data
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status']; 
    $notes = $_POST['notes'];   
    
    try {
        // Update order using mysqli prepared statement
        $sql = "UPDATE orders SET status = ?, notes = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $notes, $order_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Order updated successfully!";
        } else {
            $error_message = "Error updating order."; 
        }
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        $error_message = "Database error updating order: " . $e->getMessage();
    }
}

// Check if order ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = intval($_GET['id']);
    try {
        // Get order details with customer information
        $order_sql = "SELECT o.*, c.firstName, c.lastName, c.email, c.phone, c.address, c.city, c.postalCode, c.country 
                    FROM orders o 
                    JOIN customer c ON o.customer_id = c.id 
                    WHERE o.id = ?";
        $order_stmt = mysqli_prepare($conn, $order_sql);
        mysqli_stmt_bind_param($order_stmt, "i", $order_id);
        mysqli_stmt_execute($order_stmt);
        $order_result = mysqli_stmt_get_result($order_stmt);
        
        if (mysqli_num_rows($order_result) == 1) {
            $order = mysqli_fetch_assoc($order_result);
            
            // Get order items using mysqli
            $items_sql = "SELECT oi.*, 
                        COALESCE(p.productName, oi.product_name) as productName, 
                        COALESCE(p.thumbnail, 'assets/Placeholder-Image.jpg') as thumbnail
                        FROM order_items oi 
                        LEFT JOIN product p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?";
            $items_stmt = mysqli_prepare($conn, $items_sql);
            mysqli_stmt_bind_param($items_stmt, "i", $order_id);
            mysqli_stmt_execute($items_stmt);
            $items_result = mysqli_stmt_get_result($items_stmt);
            $order_items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);
            mysqli_stmt_close($items_stmt);
        } else {
             $error_message = "Order not found.";
        }
        mysqli_stmt_close($order_stmt);
    } catch (Exception $e) {
        $error_message = "Database error fetching order details: " . $e->getMessage();
    }
} else {
    $error_message = "No order ID provided.";
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

    <div class="row mb-4">
        <div class="col-12">
            <a href="orders.php" class="btn-admin">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>
    
    <?php if ($order): ?>
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="admin-card">
                    <h4 style="margin-bottom: 10px;">Customer Information</h4>
                    <div class="d-flex flex-wrap" style="gap: 10px;">
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">Name:</span> <span><?php echo htmlspecialchars($order['firstName'] . ' ' . $order['lastName']); ?></span></div>
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">Email:</span> <span><?php echo htmlspecialchars($order['email']); ?></span></div>
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">Phone:</span> <span><?php echo htmlspecialchars($order['phone']); ?></span></div>
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">Address:</span> <span><?php echo htmlspecialchars($order['address']); ?></span></div>
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">City:</span> <span><?php echo htmlspecialchars($order['city']); ?></span></div>
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">Postal:</span> <span><?php echo htmlspecialchars($order['postalCode']); ?></span></div>
                        <div class="customer-detail" style="width: 48%;"><span class="customer-label">Country:</span> <span><?php echo htmlspecialchars($order['country']); ?></span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="admin-card">
                    <h3 class="admin-card-title">Update Order</h3>
                    <form method="post" action="order_details.php?id=<?php echo $order['id']; ?>"> <!-- Keep order ID in URL after update -->
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Order Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending" <?php echo ($order['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Processing" <?php echo ($order['status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="Shipped" <?php echo ($order['status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="Delivered" <?php echo ($order['status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Cancelled" <?php echo ($order['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($order['notes']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_order" class="btn-admin w-100">
                            Update Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">                            
                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Order Items #<?php echo htmlspecialchars($order['id']); ?></h3>
                        <div class="order-status status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </div>
                    </div>
                    <p class="mb-3"><strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                    <div class="order-items">
                        <?php if (!empty($order_items)): ?>
                            <?php foreach ($order_items as $item): ?>
                                <div class="order-item">
                                    <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="<?php echo htmlspecialchars($item['productName']); ?>" class="item-image">
                                    <div class="item-details">
                                        <div class="item-name"><?php echo htmlspecialchars($item['productName']); ?></div>
                                        <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                                        <div class="item-meta">
                                            <?php if (!empty($item['size'])): ?>
                                                <span class="item-size">Size: <?php echo htmlspecialchars($item['size']); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['color'])): ?>
                                                <span class="item-color">Color: <?php echo htmlspecialchars($item['color']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                        <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                    <div class="item-quantity">
                                        <?php echo htmlspecialchars($item['quantity']); ?> x
                                    </div>
                                    <div class="item-total">
                                        $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No items found for this order.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-row total">
                            <div>Total</div>
                            <div>$<?php echo number_format(isset($order['total']) ? $order['total'] : $order['total_amount'], 2); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Order not found or no order ID provided. <a href="orders.php">Return to order list</a>.</div>
    <?php endif; ?>
</div> <!-- Closing admin-content -->

<?php
// Include footer
require_once 'includes/footer.php';
?>