<?php
// Set page title
$page_title = "Sales Reports";



// Include header
require_once 'includes/header.php'; // This should include db_connect.php which defines $conn

// Get date range and status filter for reports
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));
$status_filter = 'all'; // Default to all statuses

if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
}
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
}
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_filter = $_GET['status'];
}

// Define possible order statuses (adjust if needed based on your system)
$possible_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

// Display versions of statuses (for UI display)
$display_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled', 'Refunded'];

// Get sales data
$sales_sql = "SELECT DATE(order_date) as date, COUNT(*) as order_count, SUM(total_amount) as total_sales 
            FROM orders 
            WHERE order_date BETWEEN ? AND ?";
$params = [$start_date];
$types = "s";

$end_date_adj = date('Y-m-d', strtotime($end_date . ' +1 day')); // Include end date fully
$params[] = $end_date_adj;
$types .= "s";

if ($status_filter !== 'all') {
    $sales_sql .= " AND status = ?";
    $params[] = $status_filter;
    $types .= "s";
} else {
    // Optionally exclude cancelled from default 'all' view if desired
    // $sales_sql .= " AND status != 'Cancelled'"; 
}

$sales_sql .= " GROUP BY DATE(order_date) ORDER BY date ASC";

try {
    $sales_stmt = mysqli_prepare($conn, $sales_sql);
    // Bind parameters dynamically based on the types string
    if (!empty($params)) {
        mysqli_stmt_bind_param($sales_stmt, $types, ...$params);
    }
    mysqli_stmt_execute($sales_stmt);
    $sales_result = mysqli_stmt_get_result($sales_stmt);
    $sales_data = mysqli_fetch_all($sales_result, MYSQLI_ASSOC);
    mysqli_free_result($sales_result);
    mysqli_stmt_close($sales_stmt);
} catch (Exception $e) {
    error_log("Database error fetching sales data: " . $e->getMessage());
    $sales_data = [];
    $error_message = "Error fetching sales data.";
}

// Get total sales in period
$total_sql = "SELECT COUNT(*) as order_count, SUM(total_amount) as total_sales 
            FROM orders 
            WHERE order_date BETWEEN ? AND ?";
$params_total = [$start_date, $end_date_adj];
$types_total = "ss";

if ($status_filter !== 'all') {
    $total_sql .= " AND status = ?";
    $params_total[] = $status_filter;
    $types_total .= "s";
} else {
     // Optionally exclude cancelled from default 'all' view if desired
    // $total_sql .= " AND status != 'Cancelled'"; 
}

try {
    $total_stmt = mysqli_prepare($conn, $total_sql);
    // Bind parameters dynamically based on the types string
    if (!empty($params_total)) {
        mysqli_stmt_bind_param($total_stmt, $types_total, ...$params_total);
    }
    mysqli_stmt_execute($total_stmt);
    $total_result = mysqli_stmt_get_result($total_stmt);
    $total_data = mysqli_fetch_assoc($total_result);
    mysqli_free_result($total_result);
    mysqli_stmt_close($total_stmt);
} catch (Exception $e) {
    error_log("Database error fetching total sales: " . $e->getMessage());
    $total_data = ['order_count' => 0, 'total_sales' => 0]; 
    $error_message = "Error fetching total sales data.";
}

// Get top selling products
$products_sql = "SELECT oi.product_name as productName, SUM(oi.quantity) as total_quantity, SUM(oi.quantity * oi.price) as total_sales 
                FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE o.order_date BETWEEN ? AND ? ";
$params_prod = [$start_date, $end_date_adj];
$types_prod = "ss";

if ($status_filter !== 'all') {
    $products_sql .= " AND o.status = ?";
    $params_prod[] = $status_filter;
    $types_prod .= "s";
} else {
     // Optionally exclude cancelled from default 'all' view if desired
    // $products_sql .= " AND o.status != 'Cancelled'"; 
}

$products_sql .= " GROUP BY oi.product_name ORDER BY total_quantity DESC LIMIT 10";

try {
    $products_stmt = mysqli_prepare($conn, $products_sql);
    // Bind parameters dynamically based on the types string
    if (!empty($params_prod)) {
        mysqli_stmt_bind_param($products_stmt, $types_prod, ...$params_prod);
    }
    mysqli_stmt_execute($products_stmt);
    $products_result = mysqli_stmt_get_result($products_stmt);
    $top_products = mysqli_fetch_all($products_result, MYSQLI_ASSOC);
    mysqli_free_result($products_result);
    mysqli_stmt_close($products_stmt);
} catch (Exception $e) {
    error_log("Database error fetching top products: " . $e->getMessage());
    $top_products = [];
    $error_message = "Error fetching top products data.";
}

// Get top customers
$customers_sql = "SELECT c.id, c.firstName, c.lastName, c.email, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent 
                FROM orders o 
                JOIN customer c ON o.customer_id = c.id 
                WHERE o.order_date BETWEEN ? AND ?";
$params_cust = [$start_date, $end_date_adj];
$types_cust = "ss";

if ($status_filter !== 'all') {
    $customers_sql .= " AND o.status = ?";
    $params_cust[] = $status_filter;
    $types_cust .= "s";
} else {
     // Optionally exclude cancelled from default 'all' view if desired
    // $customers_sql .= " AND o.status != 'Cancelled'"; 
}

$customers_sql .= " GROUP BY c.id ORDER BY total_spent DESC LIMIT 10";

try {
    $customers_stmt = mysqli_prepare($conn, $customers_sql);
    // Bind parameters dynamically based on the types string
    if (!empty($params_cust)) {
        mysqli_stmt_bind_param($customers_stmt, $types_cust, ...$params_cust);
    }
    mysqli_stmt_execute($customers_stmt);
    $customers_result = mysqli_stmt_get_result($customers_stmt);
    $top_customers = mysqli_fetch_all($customers_result, MYSQLI_ASSOC);
    mysqli_free_result($customers_result);
    mysqli_stmt_close($customers_stmt);
} catch (Exception $e) {
    error_log("Database error fetching top customers: " . $e->getMessage());
    $top_customers = [];
    $error_message = "Error fetching top customers data.";
}

// Get recent orders
$orders_sql = "SELECT o.id, o.order_date, o.status, o.total_amount, c.firstName, c.lastName 
            FROM orders o 
            JOIN customer c ON o.customer_id = c.id 
            WHERE o.order_date BETWEEN ? AND ?";
$params_orders = [$start_date, $end_date_adj];
$types_orders = "ss";

if ($status_filter !== 'all') {
    $orders_sql .= " AND o.status = ?";
    $params_orders[] = $status_filter;
    $types_orders .= "s";
}

$orders_sql .= " ORDER BY o.order_date DESC LIMIT 10";

try {
    $orders_stmt = mysqli_prepare($conn, $orders_sql);
    // Bind parameters dynamically based on the types string
    if (!empty($params_orders)) {
        mysqli_stmt_bind_param($orders_stmt, $types_orders, ...$params_orders);
    }
    mysqli_stmt_execute($orders_stmt);
    $orders_result = mysqli_stmt_get_result($orders_stmt);
    $recent_orders = mysqli_fetch_all($orders_result, MYSQLI_ASSOC);
    mysqli_free_result($orders_result);
    mysqli_stmt_close($orders_stmt);
} catch (Exception $e) {
    error_log("Database error fetching recent orders: " . $e->getMessage());
    $recent_orders = [];
    $error_message = "Error fetching recent orders data.";
}

// Prepare data for charts
$dates = [];
$sales = [];
$orders = [];

foreach ($sales_data as $data) {
    $dates[] = date('M j', strtotime($data['date']));
    $sales[] = $data['total_sales'];
    $orders[] = $data['order_count'];
}

// Convert to JSON for JavaScript
$dates_json = json_encode($dates);
$sales_json = json_encode($sales);
$orders_json = json_encode($orders);
?>

<!-- Content -->
<div class="admin-content">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="filters-section mb-4">
                    <form method="get" action="reports.php" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Order Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>All Statuses</option>
                                <?php foreach ($possible_statuses as $key => $status): ?>
                                    <option value="<?php echo htmlspecialchars($status); ?>" <?php echo ($status_filter == $status) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($display_statuses[$key]); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="report-card">
                            <h3 class="report-card-title">Total Sales</h3>
                            <div class="report-value">$<?php echo number_format($total_data['total_sales'] ?? 0, 2); ?></div>
                            <div class="report-label">For selected period</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="report-card">
                            <h3 class="report-card-title">Total Orders</h3>
                            <div class="report-value"><?php echo $total_data['order_count'] ?? 0; ?></div>
                            <div class="report-label">For selected period</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="report-card">
                            <h3 class="report-card-title">Average Order Value</h3>
                            <div class="report-value">
                                $<?php 
                                    $avg = 0;
                                    if (($total_data['order_count'] ?? 0) > 0) {
                                        $avg = ($total_data['total_sales'] ?? 0) / $total_data['order_count'];
                                    }
                                    echo number_format($avg, 2); 
                                ?>
                            </div>
                            <div class="report-label">For selected period</div>
                        </div>
                    </div>
                </div>

                <!-- Sales and Orders Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="admin-card h-100">
                            <h2 class="admin-card-title">Sales Trend ($)</h2>
                            <div class="chart-container">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-card h-100">
                            <h2 class="admin-card-title">Order Volume</h2>
                            <div class="chart-container">
                                <canvas id="ordersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Products and Customers -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="admin-card">
                            <h2 class="admin-card-title">Top Selling Products</h2>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($top_products) > 0): ?>
                                            <?php foreach ($top_products as $product): ?>
                                                <tr>
                                                    <td><?php echo $product['productName']; ?></td>
                                                    <td><?php echo $product['total_quantity']; ?></td>
                                                    <td>$<?php echo number_format($product['total_sales'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="admin-card">
                            <h2 class="admin-card-title">Top Customers</h2>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Orders</th>
                                            <th>Total Spent</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($top_customers) > 0): ?>
                                            <?php foreach ($top_customers as $customer): ?>
                                                <tr>
                                                    <td><?php echo $customer['firstName'] . ' ' . $customer['lastName']; ?></td>
                                                    <td><?php echo $customer['order_count']; ?></td>
                                                    <td>$<?php echo number_format($customer['total_spent'], 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No data available</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="admin-card mt-4">
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_orders) > 0): ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><a href="orders.php?view=<?php echo $order['id']; ?>">#<?php echo $order['id']; ?></a></td>
                                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                            <td><?php echo $order['firstName'] . ' ' . $order['lastName']; ?></td>
                                            <td>$<?php echo number_format($order['total_amount'] ?? 0, 2); ?></td>
                                            <td>
                                                <div class="order-status status-<?php echo $order['status']; ?>">
                                                    <?php 
                                                    // Find the display version of the status
                                                    $status_key = array_search($order['status'], $possible_statuses);
                                                    echo $display_statuses[$status_key]; 
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn-edit">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 text-end">
                        <!-- Updated to match CHANGE PASSWORD button style -->
                        <a href="orders.php" class="btn btn-primary">View All Orders</a> 
                    </div>
                </div>
            <?php
// Add Chart.js library
echo "<script src='https://cdn.jsdelivr.net/npm/chart.js'></script>";

// Define chart initialization functions
echo "<script>
function initSalesChart(dates, salesData) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Sales ($)',
                data: salesData,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
}

function initOrdersChart(dates, ordersData) {
    const ctx = document.getElementById('ordersChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Orders',
                data: ordersData,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}
</script>";

// Add chart initialization scripts and output them
$sales_chart_init = "<script>document.addEventListener('DOMContentLoaded', function() { initSalesChart($dates_json, $sales_json); });</script>";
$orders_chart_init = "<script>document.addEventListener('DOMContentLoaded', function() { initOrdersChart($dates_json, $orders_json); });</script>";

echo $sales_chart_init;
echo $orders_chart_init;

require_once 'includes/footer.php';
?>