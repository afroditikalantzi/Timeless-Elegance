<?php
// Set page title for header
$page_title = 'Product Management';

// Include header file
require_once 'includes/header.php'; // This should include db_connect.php which defines $conn

// Initialize variables
$edit_product = null;
$products = [];
$categories = [];

// Process form submission for adding/editing a product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_product']) || isset($_POST['update_product'])) {
        // Get form data
        $productName = $_POST['productName'];
        $description = $_POST['description'];
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $salePrice = filter_input(INPUT_POST, 'salePrice', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? 0.0;
        $category = $_POST['category'];
        $feature = isset($_POST['feature']) ? 1 : 0;
        $product_id = isset($_POST['product_id']) ? filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) : null;

        // Basic Validation
        if (empty($productName) || empty($description) || $price === false || $price <= 0 || empty($category)) {
            $error_message = "Please fill in all required fields correctly (Product Name, Description, Price > 0, Category).";
        } else {
            try {
                // Check if product name already exists (excluding the current product if editing)
                $check_sql = "SELECT id FROM product WHERE productName = ?";
                if ($product_id) {
                    $check_sql .= " AND id != ?";
                    $check_stmt = mysqli_prepare($conn, $check_sql);
                    mysqli_stmt_bind_param($check_stmt, "si", $productName, $product_id);
                } else {
                    $check_stmt = mysqli_prepare($conn, $check_sql);
                    mysqli_stmt_bind_param($check_stmt, "s", $productName);
                }
                mysqli_stmt_execute($check_stmt);

                $check_result = mysqli_stmt_get_result($check_stmt);
                if (mysqli_fetch_assoc($check_result)) {
                    mysqli_free_result($check_result);
                    mysqli_stmt_close($check_stmt);
                    $error_message = "A product with this name already exists.";
                } else {
                    // Handle file upload (thumbnail)
                    $thumbnail_path = $edit_product['thumbnail'] ?? '../public/static/assets/Placeholder-Image.jpg'; // Keep existing or use placeholder
                    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
                        $upload_dir = '../public/static/assets/products/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        $file_ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
                        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($file_ext, $allowed_ext)) {
                            $new_filename = uniqid('prod_', true) . '.' . $file_ext;
                            $target_file = $upload_dir . $new_filename;
                            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_file)) {
                                // Optionally delete old thumbnail if updating and new one uploaded
                                if ($product_id && $thumbnail_path !== '../public/static/assets/Placeholder-Image.jpg' && file_exists($thumbnail_path)) {
                                    // Be careful with file deletion - ensure path is correct
                                    // unlink($thumbnail_path);
                                }
                                $thumbnail_path = $target_file; // Update path to new file
                            } else {
                                $error_message = "Error uploading thumbnail.";
                            }
                        } else {
                            $error_message = "Invalid file type for thumbnail. Allowed types: jpg, jpeg, png, gif.";
                        }
                    }

                    // Proceed only if no upload error occurred
                    if (empty($error_message)) {
                        if ($product_id) {
                            // Update existing product
                            $sql = "UPDATE product SET productName = ?, description = ?, price = ?, salePrice = ?, category = ?, thumbnail = ?, feature = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "ssddssis", $productName, $description, $price, $salePrice, $category, $thumbnail_path, $feature, $product_id);
                        } else {
                            // Insert new product
                            $sql = "INSERT INTO product (productName, description, price, salePrice, category, thumbnail, feature) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "ssddssi", $productName, $description, $price, $salePrice, $category, $thumbnail_path, $feature);
                        }

                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_close($stmt);
                            $success_message = $product_id ? "Product updated successfully!" : "Product added successfully!";
                            $edit_product = null; // Clear edit form after successful operation
                            // Redirect or clear form state here if desired
                        } else {
                            $error_message = $product_id ? "Error updating product." : "Error adding product.";
                        }
                    }
                }
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    // Handle delete request
    elseif (isset($_POST['delete_product']) && isset($_POST['product_id'])) {
        $delete_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($delete_id) {
            try {
                // Optional: Check if product exists before deleting
                $check_sql = "SELECT thumbnail FROM product WHERE id = ?";
                $check_stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($check_stmt, "i", $delete_id);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);
                $product_to_delete = mysqli_fetch_assoc($check_result);
                mysqli_free_result($check_result);
                mysqli_stmt_close($check_stmt);

                if ($product_to_delete) {
                    // Delete product from database
                    $delete_sql = "DELETE FROM product WHERE id = ?";
                    $delete_stmt = mysqli_prepare($conn, $delete_sql);
                    mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);

                    if (mysqli_stmt_execute($delete_stmt)) {
                        mysqli_stmt_close($delete_stmt);
                        // Optionally delete the thumbnail file
                        $thumbnail_to_delete = $product_to_delete['thumbnail'];
                        if ($thumbnail_to_delete && $thumbnail_to_delete !== '../public/static/assets/Placeholder-Image.jpg' && file_exists($thumbnail_to_delete)) {
                            // unlink($thumbnail_to_delete); // Uncomment carefully
                        }
                        $success_message = "Product deleted successfully!";
                    } else {
                        $error_message = "Error deleting product.";
                    }
                } else {
                    $error_message = "Product not found for deletion.";
                }
            } catch (Exception $e) {
                $error_message = "Database error deleting product: " . $e->getMessage();
            }
        }
    }
}

// Handle view/edit request
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    try {
        $edit_sql = "SELECT * FROM product WHERE id = ?";
        $edit_stmt = mysqli_prepare($conn, $edit_sql);
        mysqli_stmt_bind_param($edit_stmt, "i", $edit_id);
        mysqli_stmt_execute($edit_stmt);
        $edit_result = mysqli_stmt_get_result($edit_stmt);

        if (mysqli_num_rows($edit_result) == 1) {
            $edit_product = mysqli_fetch_assoc($edit_result);
        } else {
            $error_message = "Product not found.";
        }
        mysqli_free_result($edit_result);
        mysqli_stmt_close($edit_stmt);
    } catch (Exception $e) {
        $error_message = "Database error fetching product details: " . $e->getMessage();
    }
}

// Get all products and categories for display
try {
    // Get items per page setting
    $items_per_page = get_items_per_page($conn);

    // Set up pagination
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total product count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM product";
    $count_result = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_products = $count_row['total'];
    $total_pages = ceil($total_products / $items_per_page);
    mysqli_free_result($count_result);

    // Get products for current page
    $sql = "SELECT * FROM product ORDER BY id DESC LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $items_per_page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    // Get all categories for dropdown
    $category_sql = "SELECT categoryName FROM category ORDER BY categoryName ASC";
    $cat_result = mysqli_query($conn, $category_sql);
    $categories = mysqli_fetch_all($cat_result, MYSQLI_ASSOC);
    mysqli_free_result($cat_result);
} catch (Exception $e) {
    $error_message = "Database error fetching products or categories: " . $e->getMessage();
    $products = []; // Ensure products is an array even on error
    $categories = []; // Ensure categories is an array even on error
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

    <div class="row">
        <div class="col-12 mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="bi bi-plus-circle"></i> Add New Product
            </button>
        </div>
    </div>

    <!-- Product List -->
    <div class="admin-card">
        <h2 class="admin-card-title">Product List</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Sale Price</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['id']); ?></td>
                                <td>
                                    <img
                                        src="<?php
                                                // pick DB value or placeholder, strip any leading ./ or /, then prepend ../public/
                                                echo htmlspecialchars(
                                                    '../public/' .
                                                        ltrim(
                                                            $product['image'] ?: 'static/assets/Placeholder-Image.jpg',
                                                            '/.'
                                                        )
                                                );
                                                ?>"
                                        alt="<?php echo htmlspecialchars($product['productName']); ?>"
                                        class="product-image-small" />
                                </td>
                                <td><?php echo htmlspecialchars($product['productName']); ?></td>
                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <?php if ($product['salePrice'] > 0): ?>
                                        $<?php echo number_format($product['salePrice'], 2); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['feature'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>'; ?></td>
                                <td>
                                    <button type="button" class="btn-edit btn-sm me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#productModal"
                                        data-product-id="<?php echo $product['id']; ?>"
                                        data-product-name="<?php echo htmlspecialchars($product['productName']); ?>"
                                        data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                                        data-product-price="<?php echo htmlspecialchars($product['price']); ?>"
                                        data-product-saleprice="<?php echo htmlspecialchars($product['salePrice']); ?>"
                                        data-product-category="<?php echo htmlspecialchars($product['category']); ?>"
                                        data-product-image="<?php echo htmlspecialchars($product['image']); ?>"
                                        data-product-feature="<?php echo htmlspecialchars($product['feature']); ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form method="post" action="products.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                        <button type="submit" name="delete_product" class="btn-delete btn-sm">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container mt-4">
            <?php echo generate_pagination($current_page, $total_pages, 'products.php?'); ?>
        </div>
    <?php endif; ?>
</div> <!-- Closing admin-content -->

<?php
// Include footer
require_once 'includes/footer.php';
?>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="products.php" id="productForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="productId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="productName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['categoryName']); ?>">
                                        <?php echo htmlspecialchars($cat['categoryName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price ($)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price" required min="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="salePrice" class="form-label">Sale Price ($) (Optional)</label>
                            <input type="number" step="0.01" class="form-control" id="salePrice" name="salePrice" min="0">
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3">
                            <label for="thumbnail" class="form-label">Thumbnail Image</label>
                            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/png, image/jpeg, image/gif">
                            <div id="currentThumbnail" class="mt-2" style="display: none;">
                                <small class="form-text text-muted">Current: <img id="thumbnailPreview" src="" alt="Thumbnail" style="max-height: 30px; vertical-align: middle;"> - Leave blank to keep.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="feature" name="feature" value="1">
                                <label class="form-check-label" for="feature">Featured Product</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_product" id="saveProductBtn" class="btn-admin">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var productModal = document.getElementById('productModal');
        productModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var modalTitle = productModal.querySelector('.modal-title');
            var productForm = document.getElementById('productForm');
            var productIdInput = document.getElementById('productId');
            var saveButton = document.getElementById('saveProductBtn');
            var currentThumbnail = document.getElementById('currentThumbnail');
            var thumbnailPreview = document.getElementById('thumbnailPreview');

            // Check if the button has product data (meaning it's an edit)
            var productId = button.getAttribute('data-product-id');

            if (productId) {
                // Edit mode
                modalTitle.textContent = 'Edit Product';
                productIdInput.value = productId;
                document.getElementById('productName').value = button.getAttribute('data-product-name');
                document.getElementById('description').value = button.getAttribute('data-product-description');
                document.getElementById('price').value = button.getAttribute('data-product-price');
                document.getElementById('salePrice').value = button.getAttribute('data-product-saleprice') || '';

                // Set category
                var categorySelect = document.getElementById('category');
                var categoryValue = button.getAttribute('data-product-category');
                for (var i = 0; i < categorySelect.options.length; i++) {
                    if (categorySelect.options[i].value === categoryValue) {
                        categorySelect.selectedIndex = i;
                        break;
                    }
                }

                // Set featured checkbox
                document.getElementById('feature').checked = button.getAttribute('data-product-feature') === '1';

                // Show current thumbnail if exists
                var thumbnailPath = button.getAttribute('data-product-thumbnail');
                if (thumbnailPath && thumbnailPath !== '') {
                    currentThumbnail.style.display = 'block';
                    thumbnailPreview.src = thumbnailPath;
                } else {
                    currentThumbnail.style.display = 'none';
                }

                // Change button text
                saveButton.textContent = 'Update Product';
                saveButton.name = 'update_product';
            } else {
                // Add mode
                modalTitle.textContent = 'Add New Product';
                productForm.reset(); // Clear the form
                productIdInput.value = ''; // Ensure product ID is empty
                currentThumbnail.style.display = 'none';
                saveButton.textContent = 'Add Product';
                saveButton.name = 'add_product';
            }
        });
    });
</script>