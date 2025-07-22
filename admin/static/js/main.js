/**
 * Timeless Elegance Admin Panel - Main JavaScript File
 * This file consolidates all admin JavaScript functionality
 */

// =============================================================================
// CORE ADMIN FUNCTIONALITY
// =============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Apply sidebar state from localStorage
    const savedSidebarState = localStorage.getItem('eshopAdminSidebarState');
    if (savedSidebarState === 'open') {
        document.body.classList.add('sidebar-active');
    } else {
        // Default is closed, ensure class is not present if state is 'closed' or null
        document.body.classList.remove('sidebar-active'); 
    }

    // Initialize admin components
    initializeAdminComponents();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize modals if needed
    initializeModals();
});

/**
 * Initialize all admin UI components
 */
function initializeAdminComponents() {
    // Initialize datepickers if they exist
    if (document.querySelector('.datepicker')) {
        initializeDatepickers();
    }
    
    // Initialize rich text editors if they exist
    if (document.querySelector('.rich-text-editor')) {
        initializeRichTextEditors();
    }
}

/**
 * Setup event listeners for admin functionality
 */
function setupEventListeners() {

    // Add sidebar toggle listener
    const sidebarToggleButton = document.getElementById('sidebarToggle');
    if (sidebarToggleButton) {
        sidebarToggleButton.addEventListener('click', function() {
            // Toggle class on the body element as expected by the CSS
            document.body.classList.toggle('sidebar-active'); 

            // Save the new state to localStorage
            const currentState = document.body.classList.contains('sidebar-active') ? 'open' : 'closed';
            localStorage.setItem('eshopAdminSidebarState', currentState);
            document.cookie = 'eshopAdminSidebarState=' + currentState + '; path=/';
        });
    }
}

// =============================================================================
// MODAL FUNCTIONALITY
// =============================================================================

/**
 * Initialize modals based on page context
 */
function initializeModals() {
    // Category Modal
    const categoryModalElement = document.getElementById('categoryModal');
    if (categoryModalElement) {
        var categoryModal = new bootstrap.Modal(categoryModalElement);

        // Handle clicks on Edit buttons for categories
        document.querySelectorAll('.admin-table .btn-edit[data-bs-target="#categoryModal"]').forEach(button => {
            button.addEventListener('click', function(event) {
                const categoryId = this.getAttribute('data-category-id');
                const categoryName = this.getAttribute('data-category-name');
                const categoryDescription = this.getAttribute('data-category-description');

                const modalTitle = categoryModalElement.querySelector('.modal-title');
                const categoryIdInput = categoryModalElement.querySelector('input[name="category_id"]');
                const categoryNameInput = categoryModalElement.querySelector('input[name="categoryName"]');
                const descriptionTextarea = categoryModalElement.querySelector('textarea[name="description"]');
                const submitButton = categoryModalElement.querySelector('button[type="submit"]');

                if (modalTitle) modalTitle.textContent = 'Edit Category';
                if (categoryIdInput) categoryIdInput.value = categoryId;
                if (categoryNameInput) categoryNameInput.value = categoryName;
                if (descriptionTextarea) descriptionTextarea.value = categoryDescription;
                if (submitButton) submitButton.textContent = 'Update Category';

                // Ensure hidden input exists if it doesn't (for safety)
                if (!categoryIdInput && categoryModalElement.querySelector('form')) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'category_id';
                    hiddenInput.value = categoryId;
                    categoryModalElement.querySelector('form').prepend(hiddenInput);
                }
            });
        });

        // Reset modal when Add New Category button is clicked
        document.querySelector('button[data-bs-target="#categoryModal"]:not(.btn-edit)').addEventListener('click', function() {
            const modalTitle = categoryModalElement.querySelector('.modal-title');
            const categoryIdInput = categoryModalElement.querySelector('input[name="category_id"]');
            const categoryNameInput = categoryModalElement.querySelector('input[name="categoryName"]');
            const descriptionTextarea = categoryModalElement.querySelector('textarea[name="description"]');
            const submitButton = categoryModalElement.querySelector('button[type="submit"]');

            if (modalTitle) modalTitle.textContent = 'Add New Category';
            if (categoryIdInput) categoryIdInput.remove(); // Remove hidden ID field for adding
            if (categoryNameInput) categoryNameInput.value = '';
            if (descriptionTextarea) descriptionTextarea.value = '';
            if (submitButton) submitButton.textContent = 'Add Category';
        });
    }
    
    // Product Modal
    const productModalElement = document.getElementById('productModal');
    if (productModalElement) {
        var productModal = new bootstrap.Modal(productModalElement);
        
        // Show modal if edit parameter is present in URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('edit')) {
            productModal.show();
        }
    }
}

// =============================================================================
// REPORTS CHART FUNCTIONALITY
// =============================================================================

/**
 * Initialize sales chart with provided data
 */
function initSalesChart(dates, salesData) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) {
        return;
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Sales ($)',
                    data: salesData,
                    backgroundColor: 'rgba(193, 154, 107, 0.2)',
                    borderColor: 'rgba(193, 154, 107, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Sales ($)'
                    }
                }
            }
        }
    });
}

/**
 * Initialize orders chart with provided data
 */
function initOrdersChart(dates, ordersData) {
    const ctx = document.getElementById('ordersChart');
    if (!ctx) {
        return;
    }
    
    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Orders',
                    data: ordersData,
                    backgroundColor: 'rgba(75, 85, 99, 0.2)',
                    borderColor: 'rgba(75, 85, 99, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Number of Orders'
                    },
                    ticks: {
                        stepSize: 1,
                        beginAtZero: true
                    }
                }
            }
        }
    });
}