<?php
// Include database connection
require_once 'includes/db_connect.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Function to sanitize input data
function sanitize_input($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Check if the request is POST and contains JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json_data = file_get_contents('php://input');

    // Decode the JSON data
    $order_data = json_decode($json_data, true);

    // Check if JSON was valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit;
    }

    // Validate required data
    if (
        !isset($order_data['shipping']) || !isset($order_data['items']) ||
        !isset($order_data['paymentMethod']) || !isset($order_data['orderTotal'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Missing required order data']);
        exit;
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // 1. Insert or get customer data
        $shipping = $order_data['shipping'];

        // Check if customer already exists
        $email = sanitize_input($shipping['email']);
        $customer_query = "SELECT id FROM customer WHERE email = '$email'";
        $customer_result = mysqli_query($conn, $customer_query);

        if (mysqli_num_rows($customer_result) > 0) {
            // Customer exists, get ID
            $customer_row = mysqli_fetch_assoc($customer_result);
            $customer_id = $customer_row['id'];

            // Update customer information
            $update_customer = "UPDATE customer SET 
                firstName = '" . sanitize_input($shipping['firstName']) . "',
                lastName = '" . sanitize_input($shipping['lastName']) . "',
                phone = '" . sanitize_input($shipping['phone']) . "',
                address = '" . sanitize_input($shipping['address']) . "',
                city = '" . sanitize_input($shipping['city']) . "',
                postalCode = '" . sanitize_input($shipping['postalCode']) . "',
                country = '" . sanitize_input($shipping['country']) . "'
                WHERE id = $customer_id";

            mysqli_query($conn, $update_customer);
        } else {
            // Insert new customer
            $insert_customer = "INSERT INTO customer (firstName, lastName, email, phone, address, city, postalCode, country) 
                VALUES (
                    '" . sanitize_input($shipping['firstName']) . "',
                    '" . sanitize_input($shipping['lastName']) . "',
                    '" . sanitize_input($shipping['email']) . "',
                    '" . sanitize_input($shipping['phone']) . "',
                    '" . sanitize_input($shipping['address']) . "',
                    '" . sanitize_input($shipping['city']) . "',
                    '" . sanitize_input($shipping['postalCode']) . "',
                    '" . sanitize_input($shipping['country']) . "'
                )";

            mysqli_query($conn, $insert_customer);
            $customer_id = mysqli_insert_id($conn);
        }

        // 2. Create order
        $payment_method = sanitize_input($order_data['paymentMethod']);
        $total_amount = floatval($order_data['orderTotal']);

        $insert_order = "INSERT INTO orders (customer_id, total_amount, status, shipping_address, shipping_city, 
                                           shipping_postal_code, shipping_country, payment_method) 
                        VALUES (
                            $customer_id,
                            $total_amount,
                            'pending',
                            '" . sanitize_input($shipping['address']) . "',
                            '" . sanitize_input($shipping['city']) . "',
                            '" . sanitize_input($shipping['postalCode']) . "',
                            '" . sanitize_input($shipping['country']) . "',
                            '$payment_method'
                        )";

        mysqli_query($conn, $insert_order);
        $order_id = mysqli_insert_id($conn);

        // 3. Insert order items
        foreach ($order_data['items'] as $item) {
            $product_name = sanitize_input($item['name']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);
            $product_id = isset($item['id']) ? intval($item['id']) : 'NULL';

            $size = isset($item['size']) ? sanitize_input($item['size']) : NULL;
            $color = isset($item['color']) ? sanitize_input($item['color']) : NULL;

            // Check if size and color columns exist in the order_items table
            $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM order_items LIKE 'size'");
            $has_size_column = mysqli_num_rows($check_columns) > 0;

            $check_columns = mysqli_query($conn, "SHOW COLUMNS FROM order_items LIKE 'color'");
            $has_color_column = mysqli_num_rows($check_columns) > 0;

            // Prepare the SQL query based on column existence
            if ($has_size_column && $has_color_column) {
                // Both size and color columns exist
                $size_value = $size ? "'$size'" : "NULL";
                $color_value = $color ? "'$color'" : "NULL";

                $insert_item = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size, color) 
                                VALUES ($order_id, $product_id, '$product_name', $quantity, $price, $size_value, $color_value)";
            } else {
                // Size and/or color columns don't exist
                $insert_item = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                                VALUES ($order_id, $product_id, '$product_name', $quantity, $price)";
            }

            mysqli_query($conn, $insert_item);
        }

        // Commit transaction
        mysqli_commit($conn);

        // Return success response without order ID
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Not a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close database connection
mysqli_close($conn);
