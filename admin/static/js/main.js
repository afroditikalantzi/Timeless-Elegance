// ------------------- CORE ADMIN FUNCTIONALITY -------------------

document.addEventListener('DOMContentLoaded', function() {
    // Apply sidebar state from localStorage
    const savedSidebarState = localStorage.getItem('eshopAdminSidebarState');
    if (savedSidebarState === 'open') {
        document.body.classList.add('sidebar-active');
    } else {
        document.body.classList.remove('sidebar-active'); 
    }

    setupEventListeners();
    initializeModals();
});


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


// ------------------- REPORTS CHART FUNCTIONALITY ------------------- //


// Initialize sales chart with provided data
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

//Initialize orders chart with provided data
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