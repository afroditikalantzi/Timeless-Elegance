/**
 * eShop Admin Panel - Main JavaScript File
 * This file consolidates all admin JavaScript functionality
 */

// =============================================================================
// CORE ADMIN FUNCTIONALITY
// =============================================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin DOMContentLoaded - Initializing components'); // Debug log

    // Apply sidebar state from localStorage
    const savedSidebarState = localStorage.getItem('eshopAdminSidebarState');
    if (savedSidebarState === 'open') {
        document.body.classList.add('sidebar-active');
        console.log('Applied sidebar state: open'); // Debug log
    } else {
        // Default is closed, ensure class is not present if state is 'closed' or null
        document.body.classList.remove('sidebar-active'); 
        console.log('Applied sidebar state: closed'); // Debug log
    }

    // Initialize admin components
    initializeAdminComponents();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize sortable tables - REMOVED
    // initSortableTables(); 
    
    // Initialize modals if needed
    initializeModals();
    console.log('Admin DOMContentLoaded - Initialization complete'); // Debug log
});

/**
 * Initialize all admin UI components
 */
function initializeAdminComponents() {
    console.log('Initializing Admin Components'); // Debug log
    // Initialize datepickers if they exist
    if (document.querySelector('.datepicker')) {
        initializeDatepickers();
    }
    
    // Initialize data tables if they exist - REMOVED
    // if (document.querySelector('.admin-table')) {
    //     initializeDataTables();
    // }
    
    // Initialize rich text editors if they exist
    if (document.querySelector('.rich-text-editor')) {
        initializeRichTextEditors();
    }
}

/**
 * Setup event listeners for admin functionality
 */
function setupEventListeners() {
    console.log('Setting up Event Listeners'); // Debug log
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Add sidebar toggle listener
    const sidebarToggleButton = document.getElementById('sidebarToggle'); // Correct ID from header.php
    if (sidebarToggleButton) {
        console.log('Sidebar toggle button found, adding listener.'); // Debug log
        sidebarToggleButton.addEventListener('click', function() {
            console.log('Sidebar toggle button clicked!'); // Debug log
            // Toggle class on the body element as expected by the CSS
            document.body.classList.toggle('sidebar-active'); 
            console.log('Body class toggled.'); // Debug log

            // Save the new state to localStorage
            const currentState = document.body.classList.contains('sidebar-active') ? 'open' : 'closed';
            localStorage.setItem('eshopAdminSidebarState', currentState);
            console.log('Saved sidebar state:', currentState); // Debug log
            
            // Optional: Check if sidebar element exists, but don't toggle its class
            const sidebar = document.getElementById('adminSidebar'); 
            if (!sidebar) {
                console.error('Sidebar element (adminSidebar) not found!'); // Debug log
            }
        });
    } else {
        console.warn('Sidebar toggle button (sidebarToggle) not found!'); // Debug log
    }
}

// =============================================================================
// TABLE SORTING FUNCTIONALITY - REMOVED
// =============================================================================

// The sortable table functionality has been removed.

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

    // Customer Modal
    const customerModalElement = document.getElementById('customerModal');
    if (customerModalElement) {
        var customerModal = new bootstrap.Modal(customerModalElement);

        // Handle clicks on Edit buttons for customers
        document.querySelectorAll('.admin-table .btn-edit[data-bs-target="#customerModal"]').forEach(button => {
            button.addEventListener('click', function(event) {
                // Get customer data from data attributes
                const customerId = this.getAttribute('data-customer-id');
                const firstName = this.getAttribute('data-customer-firstname');
                const lastName = this.getAttribute('data-customer-lastname');
                const email = this.getAttribute('data-customer-email');
                const phone = this.getAttribute('data-customer-phone');
                const address = this.getAttribute('data-customer-address');
                const city = this.getAttribute('data-customer-city');
                const postalCode = this.getAttribute('data-customer-postalcode');
                const country = this.getAttribute('data-customer-country');

                // Get modal elements
                const modalTitle = customerModalElement.querySelector('.modal-title');
                const form = customerModalElement.querySelector('form');
                const customerIdInput = form.querySelector('input[name="customer_id"]');
                const submitButton = customerModalElement.querySelector('button[type="submit"]');

                // Set modal title and button text for editing
                if (modalTitle) modalTitle.textContent = 'Edit Customer';
                if (submitButton) submitButton.textContent = 'Update Customer';

                // Populate form fields
                if (form.elements['firstName']) form.elements['firstName'].value = firstName;
                if (form.elements['lastName']) form.elements['lastName'].value = lastName;
                if (form.elements['email']) form.elements['email'].value = email;
                if (form.elements['phone']) form.elements['phone'].value = phone;
                if (form.elements['address']) form.elements['address'].value = address;
                if (form.elements['city']) form.elements['city'].value = city;
                if (form.elements['postalCode']) form.elements['postalCode'].value = postalCode;
                if (form.elements['country']) form.elements['country'].value = country;

                // Add or update hidden customer_id input
                if (customerIdInput) {
                    customerIdInput.value = customerId;
                } else {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'customer_id';
                    hiddenInput.value = customerId;
                    form.prepend(hiddenInput); // Add to the beginning of the form
                }
            });
        });

        // Reset modal when Add New Customer button is clicked
        const addCustomerButton = document.querySelector('button[data-bs-target="#customerModal"]:not(.btn-edit)');
        if (addCustomerButton) {
            addCustomerButton.addEventListener('click', function() {
                const modalTitle = customerModalElement.querySelector('.modal-title');
                const customerIdInput = customerModalElement.querySelector('input[name="customer_id"]');
                const form = customerModalElement.querySelector('form');
                const submitButton = customerModalElement.querySelector('button[type="submit"]');

                if (modalTitle) modalTitle.textContent = 'Add New Customer';
                if (customerIdInput) customerIdInput.remove(); // Remove hidden ID field for adding
                if (form) form.reset(); // Reset all form fields
                if (submitButton) submitButton.textContent = 'Add Customer';
                
                // Ensure hidden customer_id input (if it exists from a previous edit) is removed
                const existingHiddenInput = form.querySelector('input[name="customer_id"]');
                if (existingHiddenInput) {
                    existingHiddenInput.remove();
                }
            });
        }
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
        console.error('Sales chart canvas not found!');
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
    console.log('Sales chart initialized.'); // Debug log
}

/**
 * Initialize orders chart with provided data
 */
function initOrdersChart(dates, ordersData) {
    const ctx = document.getElementById('ordersChart');
    if (!ctx) {
        console.error('Orders chart canvas not found!');
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
                    ticks: { // Add ticks configuration
                        stepSize: 1, // Ensure only whole numbers are shown
                        beginAtZero: true // Start axis at 0
                    }
                }
            }
        }
    });
    console.log('Orders chart initialized.'); // Debug log
}