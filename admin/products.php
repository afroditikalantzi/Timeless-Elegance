<?php
$page_title = 'Product Management';

require_once 'includes/header.php';

// Initialize
$edit_product = null;
$products     = [];
$categories   = [];

// Form for adding or updating a product
if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST['add_product']) || isset($_POST['update_product']))) {
    // Gather inputs
    $productName = trim($_POST['productName']);
    $description = trim($_POST['description']);
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $salePrice   = filter_input(INPUT_POST, 'salePrice', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? 0.0;
    $category    = $_POST['category'];
    $feature     = isset($_POST['feature']) ? 1 : 0;
    $product_id  = isset($_POST['product_id']) ? filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) : null;

    // If editing: fetch existing image path
    $existing_image = null;
    if ($product_id) {
        $img_sql  = "SELECT image FROM product WHERE id = ?";
        $img_stmt = mysqli_prepare($conn, $img_sql);
        mysqli_stmt_bind_param($img_stmt, "i", $product_id);
        mysqli_stmt_execute($img_stmt);
        $res = mysqli_stmt_get_result($img_stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            $existing_image = $row['image'];
        }
        mysqli_free_result($res);
        mysqli_stmt_close($img_stmt);
    }

    // Basic validation
    if (empty($productName) || empty($description) || $price === false || $price <= 0 || empty($category)) {
        $error_message = "Please fill in all required fields correctly.";
    } else {

        // Check for duplicate name
        $check_sql = "SELECT id FROM product WHERE productName = ?";
        if ($product_id) {
            $check_sql .= " AND id != ?";
            $chk = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($chk, "si", $productName, $product_id);
        } else {
            $chk = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($chk, "s", $productName);
        }
        mysqli_stmt_execute($chk);
        $dup = mysqli_stmt_get_result($chk);
        if (mysqli_fetch_assoc($dup)) {
            $error_message = "A product with this name already exists.";
        }
        mysqli_free_result($dup);
        mysqli_stmt_close($chk);

        if (empty($error_message)) {
            $image_path = $existing_image;

            // Handle upload
            if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../public/static/images/products/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($ext, $allowed, true)) {
                    // Random filename
                    $newfile     = uniqid('prod_', true) . '.' . $ext;
                    $target_file = $upload_dir . $newfile;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        // Delete old image if editing
                        if ($product_id && $existing_image) {
                            $old = __DIR__ . '/../public/' . ltrim($existing_image, '/.');
                            if (file_exists($old)) unlink($old);
                        }
                        $image_path = 'static/images/products/' . $newfile;
                    } else {
                        $error_message = "Error uploading image.";
                    }
                } else {
                    $error_message = "Invalid image type.";
                }
            }

            // INSERT or UPDATE
            if (empty($error_message)) {
                if ($product_id) {
                    // UPDATE
                    $sql = "UPDATE product
                                   SET productName = ?, description = ?, price = ?, salePrice = ?,
                                       category = ?, image = ?, feature = ?
                                 WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssddssii",
                        $productName,
                        $description,
                        $price,
                        $salePrice,
                        $category,
                        $image_path,
                        $feature,
                        $product_id
                    );
                } else {
                    // INSERT
                    $sql = "INSERT INTO product
                                    (productName, description, price, salePrice, category, image, feature)
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssddssi",
                        $productName,
                        $description,
                        $price,
                        $salePrice,
                        $category,
                        $image_path,
                        $feature
                    );
                }

                if (mysqli_stmt_execute($stmt)) {
                    $success_message = $product_id
                        ? "Product updated successfully!"
                        : "Product added successfully!";
                    $edit_product = null;
                } else {
                    $error_message = $product_id
                        ? "Error updating product."
                        : "Error adding product.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Form for deleting a product
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_product'], $_POST['product_id'])) {
    $del_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if ($del_id) {
        // Fetch image to delete
        $chk = mysqli_prepare($conn, "SELECT image FROM product WHERE id = ?");
        mysqli_stmt_bind_param($chk, "i", $del_id);
        mysqli_stmt_execute($chk);
        $res = mysqli_stmt_get_result($chk);
        $prd = mysqli_fetch_assoc($res);
        mysqli_free_result($res);
        mysqli_stmt_close($chk);

        if ($prd) {
            // Delete row
            $del = mysqli_prepare($conn, "DELETE FROM product WHERE id = ?");
            mysqli_stmt_bind_param($del, "i", $del_id);
            if (mysqli_stmt_execute($del)) {
                // Delete file
                $img = $prd['image'];
                if ($img) {
                    $path = __DIR__ . '/../public/' . ltrim($img, '/.');
                    if (file_exists($path)) unlink($path);
                }
                $success_message = "Product deleted successfully!";
            } else {
                $error_message = "Error deleting product.";
            }
            mysqli_stmt_close($del);
        } else {
            $error_message = "Product not found.";
        }
    }
}

// Form for editing a product
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $eid  = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM product WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $eid);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($res) === 1) {
        $edit_product = mysqli_fetch_assoc($res);
    } else {
        $error_message = "Product not found.";
    }
    mysqli_free_result($res);
    mysqli_stmt_close($stmt);
}

$items_per_page = get_items_per_page($conn);
$current_page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset         = ($current_page - 1) * $items_per_page;

// Count
$cnt_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM product");
$total   = mysqli_fetch_assoc($cnt_res)['total'];
mysqli_free_result($cnt_res);
$total_pages = ceil($total / $items_per_page);

// Page data
$stmt = mysqli_prepare($conn, "SELECT * FROM product ORDER BY id DESC LIMIT ?, ?");
mysqli_stmt_bind_param($stmt, "ii", $offset, $items_per_page);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$products = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_free_result($res);
mysqli_stmt_close($stmt);

// Categories
$cat_res   = mysqli_query($conn, "SELECT categoryName FROM category ORDER BY categoryName ASC");
$categories = mysqli_fetch_all($cat_res, MYSQLI_ASSOC);
mysqli_free_result($cat_res);
?>

<!-- Content -->
<div>
    <?php if ($error_message): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-12">
        <button type="button" class="btn primary-btn" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bi bi-plus-circle me-2"></i> Add New Product
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
                                    src="<?php echo htmlspecialchars(
                                                '../public/' . ltrim($product['image'], '/.')
                                            ); ?>"
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
                            <td>
                                <?php echo $product['feature']
                                    ? '<i class="bi bi-check-circle-fill text-success"></i>'
                                    : '<i class="bi bi-x-circle-fill text-danger"></i>'; ?>
                            </td>
                            <td>
                                <button type="button" class="edit-btn me-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#productModal"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['productName']); ?>"
                                    data-product-description="<?php echo htmlspecialchars($product['description']); ?>"
                                    data-product-price="<?php echo htmlspecialchars($product['price']); ?>"
                                    data-product-saleprice="<?php echo htmlspecialchars($product['salePrice']); ?>"
                                    data-product-category="<?php echo htmlspecialchars($product['category']); ?>"
                                    data-product-image="<?php echo htmlspecialchars('../public/' . ltrim($product['image'], '/.')); ?>"
                                    data-product-feature="<?php echo htmlspecialchars($product['feature']); ?>">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>

                                <form method="post" action="products.php" class="delete-form" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                    <input type="hidden" name="delete_product" value="1">
                                    <button
                                        type="button"
                                        class="delete-btn me-1"
                                        data-product-name="<?php echo htmlspecialchars($product['productName']); ?>">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No products found</td>
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


<?php
require_once 'includes/footer.php';
?>

<!-- Add/Edit Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="products.php" id="productForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
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
                                <option value="" disabled selected hidden>Select Category</option>
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
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/png, image/jpeg, image/gif">
                            <div id="currentImage" class="mt-2" style="display: none;">
                                <small class="form-text text-muted">
                                    Current:
                                    <img id="imagePreview" src="" alt="Image" style="max-height: 30px; vertical-align: middle;">
                                    - Leave blank to keep.
                                </small>
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
                    <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_product" id="saveProductBtn" class="btn primary-btn">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete
                <strong id="deleteItemName"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn primary-btn" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ----- Add/Edit Logic -----
        const productModalEl = document.getElementById('productModal');
        const productForm = document.getElementById('productForm');
        const productIdInput = document.getElementById('productId');
        const nameInput = document.getElementById('productName');
        const descInput = document.getElementById('description');
        const priceInput = document.getElementById('price');
        const salePriceInput = document.getElementById('salePrice');
        const categorySelect = document.getElementById('category');
        const featureCheckbox = document.getElementById('feature');
        const currentImageEl = document.getElementById('currentImage');
        const imagePreviewEl = document.getElementById('imagePreview');
        const saveProductBtn = document.getElementById('saveProductBtn');

        if (productModalEl) {
            productModalEl.addEventListener('show.bs.modal', event => {
                const triggerBtn = event.relatedTarget;
                const modalTitle = productModalEl.querySelector('.modal-title');
                const prodId = triggerBtn.dataset.productId;

                if (prodId) {
                    // Edit Mode 
                    modalTitle.textContent = 'Edit Product';
                    productIdInput.value = prodId;
                    nameInput.value = triggerBtn.dataset.productName || '';
                    descInput.value = triggerBtn.dataset.productDescription || '';
                    priceInput.value = triggerBtn.dataset.productPrice || '';
                    salePriceInput.value = triggerBtn.dataset.productSaleprice || '';

                    // select category
                    const catVal = triggerBtn.dataset.productCategory;
                    Array.from(categorySelect.options).forEach((opt, i) => {
                        if (opt.value === catVal) categorySelect.selectedIndex = i;
                    });

                    // feature checkbox
                    featureCheckbox.checked = triggerBtn.dataset.productFeature === '1';

                    // image preview
                    const imgPath = triggerBtn.dataset.productImage;
                    if (imgPath) {
                        currentImageEl.style.display = 'block';
                        imagePreviewEl.src = imgPath;
                    } else {
                        currentImageEl.style.display = 'none';
                    }

                    saveProductBtn.textContent = 'Update Product';
                    saveProductBtn.name = 'update_product';
                } else {
                    // Add Mode 
                    modalTitle.textContent = 'Add New Product';
                    productForm.reset();
                    productIdInput.value = '';
                    currentImageEl.style.display = 'none';
                    saveProductBtn.textContent = 'Add Product';
                    saveProductBtn.name = 'add_product';
                }
            });
        }

        // ----- Delete Logic -----
        const deleteModalEl = document.getElementById('deleteConfirmModal');
        const deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
        const deleteItemNameEl = document.getElementById('deleteItemName');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let formToDelete = null;

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                formToDelete = btn.closest('form');
                if (deleteItemNameEl) deleteItemNameEl.textContent = btn.dataset.productName || '';
                if (deleteModal) deleteModal.show();
            });
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                if (formToDelete) formToDelete.submit();
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>