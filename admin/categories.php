<?php
// Set page title for header
$page_title = 'Category Management';

// Include header file
require_once 'includes/header.php'; // This should include db_connect.php which defines $conn

// Initialize variables
$edit_category = null;
$categories = [];
$error_message = '';
$success_message = '';

// Process form submission for adding/editing a category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category']) || isset($_POST['update_category'])) {
        $categoryName = trim($_POST['categoryName']);
        $description = trim($_POST['description']);
        $category_id = isset($_POST['category_id']) ? filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) : null;

        // Basic Validation
        if (empty($categoryName)) {
            $error_message = "Category name cannot be empty.";
        } else {
            try {
                // Check if category name already exists (excluding the current category if editing)
                if ($category_id) {
                    $check_sql = "SELECT id FROM category WHERE categoryName = ? AND id != ?";
                    $check_stmt = mysqli_prepare($conn, $check_sql);
                    mysqli_stmt_bind_param($check_stmt, "si", $categoryName, $category_id);
                } else {
                    $check_sql = "SELECT id FROM category WHERE categoryName = ?";
                    $check_stmt = mysqli_prepare($conn, $check_sql);
                    mysqli_stmt_bind_param($check_stmt, "s", $categoryName);
                }

                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $error_message = "A category with this name already exists.";
                } else {
                    if ($category_id) {
                        // Update existing category
                        $sql = "UPDATE category SET categoryName = ?, description = ? WHERE id = ?";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "ssi", $categoryName, $description, $category_id);
                    } else {
                        // Insert new category
                        $sql = "INSERT INTO category (categoryName, description) VALUES (?, ?)";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "ss", $categoryName, $description);
                    }

                    if (mysqli_stmt_execute($stmt)) {
                        $success_message = $category_id ? "Category updated successfully!" : "Category added successfully!";
                        $edit_category = null; // Clear edit form
                    } else {
                        $error_message = $category_id ? "Error updating category." : "Error adding category.";
                    }

                    mysqli_stmt_close($stmt);
                }

                mysqli_stmt_close($check_stmt);
            } catch (Exception $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    // Handle delete request
    elseif (isset($_POST['delete_category']) && isset($_POST['category_id'])) {
        $delete_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        if ($delete_id) {
            try {
                // Check if category is used by any products
                $check_sql = "SELECT COUNT(*) FROM product WHERE category = (SELECT categoryName FROM category WHERE id = ?)";
                $check_stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($check_stmt, "i", $delete_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_bind_result($check_stmt, $product_count);
                mysqli_stmt_fetch($check_stmt);
                mysqli_stmt_close($check_stmt);

                if ($product_count > 0) {
                    $error_message = "Cannot delete category because it is assigned to " . $product_count . " product(s).";
                } else {
                    // Delete the category
                    $delete_sql = "DELETE FROM category WHERE id = ?";
                    $delete_stmt = mysqli_prepare($conn, $delete_sql);
                    mysqli_stmt_bind_param($delete_stmt, "i", $delete_id);

                    if (mysqli_stmt_execute($delete_stmt)) {
                        $success_message = "Category deleted successfully!";
                    } else {
                        $error_message = "Error deleting category.";
                    }

                    mysqli_stmt_close($delete_stmt);
                }
            } catch (Exception $e) {
                $error_message = "Database error deleting category: " . $e->getMessage();
            }
        }
    }
}

// Get all categories for display using mysqli
try {
    // Get items per page setting
    $items_per_page = get_items_per_page($conn);

    // Set up pagination
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Get total category count for pagination
    $count_sql = "SELECT COUNT(*) FROM category";
    $count_result = mysqli_query($conn, $count_sql);
    $total_categories = mysqli_fetch_row($count_result)[0];
    $total_pages = ceil($total_categories / $items_per_page);
    mysqli_free_result($count_result);

    // Get categories for current page with product count
    $sql = "SELECT c.*, COUNT(p.id) as product_count 
            FROM category c 
            LEFT JOIN product p ON c.categoryName = p.category 
            GROUP BY c.id, c.categoryName, c.description 
            ORDER BY c.id DESC 
            LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $offset, $items_per_page);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
} catch (Exception $e) {
    $error_message = "Database error fetching categories: " . $e->getMessage();
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
            <button type="button" class="btn primary-btn" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="bi bi-plus-circle me-2"></i> Add New Category
            </button>
        </div>
    </div>

    <!-- Category List -->
    <div class="admin-card">
        <h2 class="admin-card-title">Category List</h2>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Product Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['id']); ?></td>
                                <td><?php echo htmlspecialchars($category['categoryName']); ?></td>
                                <td><?php echo htmlspecialchars($category['description'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($category['product_count']); ?></td>
                                <td>
                                    <button type="button" class="edit-btn btn-sm me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#categoryModal"
                                        data-category-id="<?php echo $category['id']; ?>"
                                        data-category-name="<?php echo htmlspecialchars($category['categoryName']); ?>"
                                        data-category-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <form method="post" action="categories.php" class="delete-category-form" style="display:inline;">
  <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['id']) ?>">
  <input type="hidden" name="delete_category" value="1">

  <button
    type="button"
    class="delete-btn btn-sm me-1"
    data-category-name="<?= htmlspecialchars($category['categoryName']) ?>"
    data-product-count="<?= (int)$category['product_count'] ?>"
  >
    <i class="bi bi-trash me-1"></i> Delete
  </button>
</form>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No categories found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container mt-4">
            <?php echo generate_pagination($current_page, $total_pages, 'categories.php?'); ?>
        </div>
    <?php endif; ?>
</div> <!-- Closing admin-content -->

<?php
// Include footer
require_once 'includes/footer.php';
?>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="categories.php" id="categoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="category_id" id="categoryId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="categoryName" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <input type="text" class="form-control" id="description" name="description">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_category" id="saveCategoryBtn" class="btn primary-btn">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteCategoryLabel">Confirm Delete</h5>
      </div>
      <div class="modal-body">
        Are you sure you want to delete
        <strong id="deleteCategoryName"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn primary-btn" id="confirmDeleteCategoryBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<!-- Cannot Delete Category Modal -->
<div class="modal fade" id="cannotDeleteModal" tabindex="-1" aria-labelledby="cannotDeleteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cannotDeleteLabel">Cannot Delete Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="cannotDeleteModalBody">
        <!-- Filled dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn primary-btn" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var categoryModal = document.getElementById('categoryModal');
        categoryModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var modalTitle = categoryModal.querySelector('.modal-title');
            var categoryForm = document.getElementById('categoryForm');
            var categoryIdInput = document.getElementById('categoryId');
            var saveButton = document.getElementById('saveCategoryBtn');

            // Check if the button has category data (meaning it's an edit)
            var categoryId = button.getAttribute('data-category-id');

            if (categoryId) {
                // Edit mode
                modalTitle.textContent = 'Edit Category';
                categoryIdInput.value = categoryId;
                document.getElementById('categoryName').value = button.getAttribute('data-category-name');
                document.getElementById('description').value = button.getAttribute('data-category-description') || '';

                // Change button text
                saveButton.textContent = 'Update Category';
                saveButton.name = 'update_category';
            } else {
                // Add mode
                modalTitle.textContent = 'Add New Category';
                categoryForm.reset(); // Clear the form
                categoryIdInput.value = ''; // Ensure category ID is empty
                saveButton.textContent = 'Add Category';
                saveButton.name = 'add_category';
            }
        });

        
    });

    document.addEventListener('DOMContentLoaded', function() {
  // confirm‑delete modal
  const deleteCatModalEl    = document.getElementById('deleteCategoryModal');
  const deleteCatModal      = new bootstrap.Modal(deleteCatModalEl);
  const deleteCatNameEl     = document.getElementById('deleteCategoryName');
  const confirmDeleteCatBtn = document.getElementById('confirmDeleteCategoryBtn');
  let   formToDeleteCat     = null;

  // cannot‑delete modal
  const cannotDeleteModalEl  = document.getElementById('cannotDeleteModal');
  const cannotDeleteModal    = new bootstrap.Modal(cannotDeleteModalEl);
  const cannotDeleteBodyEl   = document.getElementById('cannotDeleteModalBody');

  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopImmediatePropagation();

      const name  = this.dataset.categoryName;
      const count = parseInt(this.dataset.productCount, 10);

      if (count > 0) {
        // show error
        cannotDeleteBodyEl.textContent =
          `Cannot delete "${name}" because it is assigned to ${count} product(s).`;
        cannotDeleteModal.show();
        return;
      }

      // otherwise show the normal confirm
      formToDeleteCat  = this.closest('form');
      deleteCatNameEl.textContent = name;
      deleteCatModal.show();
    }, true);
  });

  confirmDeleteCatBtn.addEventListener('click', function() {
    if (!formToDeleteCat) return;
    deleteCatModal.hide();
    formToDeleteCat.submit();
  });
});
</script>