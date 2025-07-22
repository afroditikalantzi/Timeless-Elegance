<?php
$page_title = 'Admin Dashboard';

require_once 'includes/header.php';

// Initialize variables
$products = [];
$product_count = 0;
$customer_count = 0;
$order_count = 0;
$total_revenue = 0;
$recent_orders = [];

// Initialize messages
$error_message = '';
$success_message = '';

// Get all products for display
$sql = "SELECT * FROM product ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
$product_count = count($products);
mysqli_free_result($result);

// Get customer count
$customer_sql = "SELECT COUNT(*) as count FROM customer";
$customer_result = mysqli_query($conn, $customer_sql);
$customer_row = mysqli_fetch_assoc($customer_result);
$customer_count = $customer_row['count'];
mysqli_free_result($customer_result);

// Get order count
$order_sql = "SELECT COUNT(*) as count FROM orders";
$order_result = mysqli_query($conn, $order_sql);
$order_row = mysqli_fetch_assoc($order_result);
$order_count = $order_row['count'];
mysqli_free_result($order_result);

// Get total revenue
$revenue_sql = "SELECT SUM(total_amount) as total FROM orders";
$revenue_result = mysqli_query($conn, $revenue_sql);
$revenue_row = mysqli_fetch_assoc($revenue_result);
$total_revenue = $revenue_row['total'] ?? 0;
mysqli_free_result($revenue_result);

// Get recent orders
$recent_orders_sql = "SELECT o.*, c.firstName, c.lastName 
                          FROM orders o 
                          JOIN customer c ON o.customer_id = c.id 
                          ORDER BY o.order_date DESC 
                          LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_sql);
$recent_orders = mysqli_fetch_all($recent_orders_result, MYSQLI_ASSOC);
mysqli_free_result($recent_orders_result);
?>

<!-- Dashboard Stats -->
<div class="row mb-4 mt-3">
    <div class="col-md-3">
        <div class="admin-card report-card">
            <h3 class="report-card-title">Total Products</h3>
            <div class="report-value"><?php echo $product_count; ?></div>
            <div class="report-label">Available in store</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card report-card">
            <h3 class="report-card-title">Total Customers</h3>
            <div class="report-value"><?php echo $customer_count; ?></div>
            <div class="report-label">Registered users</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card report-card">
            <h3 class="report-card-title">Total Orders</h3>
            <div class="report-value"><?php echo $order_count; ?></div>
            <div class="report-label">All time orders</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="admin-card report-card">
            <h3 class="report-card-title">Total Revenue</h3>
            <div class="report-value">$<?php echo number_format($total_revenue, 2); ?></div>
            <div class="report-label">All time sales</div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-12">
        <div class="admin-card">
            <h2 class="admin-card-title">Recent Orders</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_orders) > 0): ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo date('j M Y', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['firstName'] . ' ' . $order['lastName']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <div class="order-status status-<?php echo strtolower(htmlspecialchars($order['status'])); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="order_details.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="view-btn">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No recent orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>