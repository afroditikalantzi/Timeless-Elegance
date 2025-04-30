/**
 * Combined Checkout and Credit Card Validation
 * Implements validation for shipping information, payment methods, and credit card details
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize validation for the checkout form
    initFormValidation();
    
    // Initialize credit card validation
    initCreditCardValidation();
    
    // Replace default alert with custom styled popup
    customizeOrderCompletionPopup();
});

// ===== SHIPPING VALIDATION FUNCTIONS =====

function initFormValidation() {
    // Get form elements
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const phoneInput = document.getElementById('phone');
    const zipInput = document.getElementById('zip');
    const shippingNextBtn = document.getElementById('shipping-next');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    
    // Add validation event listeners
    if (firstNameInput) {
        firstNameInput.addEventListener('input', function() {
            validateName(this, 'First name should not contain numbers');
        });
    }
    
    if (lastNameInput) {
        lastNameInput.addEventListener('input', function() {
            validateName(this, 'Last name should not contain numbers');
        });
    }
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            validatePhoneNumber(this);
        });
    }
    
    if (zipInput) {
        zipInput.addEventListener('input', function() {
            validateZipCode(this);
        });
    }
    
    if (cityInput) {
        cityInput.addEventListener('input', function() {
            validateName(this, 'City should not contain numbers');
        });
    }
    if (stateInput) {
        stateInput.addEventListener('input', function() {
            validateName(this, 'State/Province should not contain numbers');
        });
    }
    
    // Override the shipping form validation
    if (shippingNextBtn) {
        shippingNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateShippingFormCustom()) {
                goToSection('payment-section');
            }
        });
    }
}

// Validate name fields (no numbers allowed)
function validateName(inputElement, errorMessage) {
    const value = inputElement.value;
    const hasNumbers = /\d/.test(value);
    
    if (hasNumbers) {
        showError(inputElement, errorMessage);
        return false;
    } else {
        clearError(inputElement);
        return true;
    }
}

// Validate phone number (only numbers and + sign allowed)
function validatePhoneNumber(inputElement) {
    const value = inputElement.value;
    const isValid = /^\+?[0-9]+$/.test(value);
    
    if (!isValid && value !== '') {
        showError(inputElement, 'Phone number should only contain numbers and optionally a + sign');
        return false;
    } else {
        clearError(inputElement);
        return true;
    }
}

// Validate zip/postal code (only numbers allowed)
function validateZipCode(inputElement) {
    const value = inputElement.value;
    const isValid = /^[0-9]+$/.test(value);
    
    if (!isValid && value !== '') {
        showError(inputElement, 'Zip/Postal code should only contain numbers');
        return false;
    } else {
        clearError(inputElement);
        return true;
    }
}

// Custom validation for the shipping form
function validateShippingFormCustom() {
    const firstName = document.getElementById('firstName');
    const lastName = document.getElementById('lastName');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const address = document.getElementById('address');
    const city = document.getElementById('city');
    const state = document.getElementById('state');
    const zip = document.getElementById('zip');
    const country = document.getElementById('country');
    
    let isValid = true;
    
    // Validate required fields
    if (!firstName.value) {
        showError(firstName, 'First name is required');
        isValid = false;
    } else if (!/^[^\d]+$/.test(firstName.value)) {
        showError(firstName, 'First name should not contain numbers');
        isValid = false;
    }
    
    if (!lastName.value) {
        showError(lastName, 'Last name is required');
        isValid = false;
    } else if (!/^[^\d]+$/.test(lastName.value)) {
        showError(lastName, 'Last name should not contain numbers');
        isValid = false;
    }
    
    if (!email.value) {
        showError(email, 'Email is required');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        showError(email, 'Please enter a valid email address');
        isValid = false;
    }
    
    if (!phone.value) {
        showError(phone, 'Phone number is required');
        isValid = false;
    } else if (!/^\+?[0-9]+$/.test(phone.value)) {
        showError(phone, 'Phone number should only contain numbers and optionally a + sign');
        isValid = false;
    }
    
    if (!address.value) {
        showError(address, 'Address is required');
        isValid = false;
    }
    
    if (!city.value) {
        showError(city, 'City is required');
        isValid = false;
    } else if (/\d/.test(city.value)) {
        showError(city, 'City should not contain numbers');
        isValid = false;
    } else {
        clearError(city);
    }

    if (!state.value) {
        showError(state, 'State/Province is required');
        isValid = false;
    } else if (/\d/.test(state.value)) {
        showError(state, 'State/Province should not contain numbers');
        isValid = false;
    } else {
        clearError(state);
    }

    if (!zip.value) {
        showError(zip, 'Zip/Postal code is required');
        isValid = false;
    } else if (!/^[0-9]+$/.test(zip.value)) {
        showError(zip, 'Zip/Postal code should only contain numbers');
        isValid = false;
    }
    
    if (!country.value) {
        showError(country, 'Country is required');
        isValid = false;
    }
    
    return isValid;
}

// Replace the default alert with a custom styled popup
function customizeOrderCompletionPopup() {
    // We don't need to override placeOrder function anymore
    // as it's now handled directly in checkout.php with proper order processing
    // The showOrderCompletionPopup function will be called after successful order processing
}

function showOrderCompletionPopup(orderId) {
    // Create popup container
    const popupOverlay = document.createElement('div');
    popupOverlay.className = 'order-completion-overlay';
    
    const popupContent = document.createElement('div');
    popupContent.className = 'order-completion-popup';
    
    // Add success icon
    const successIcon = document.createElement('div');
    successIcon.className = 'success-icon';
    successIcon.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
    
    // Add message
    const title = document.createElement('h3');
    title.textContent = 'Thank You!';
    
    const message = document.createElement('p');
    message.textContent = orderId 
        ? `Your order #${orderId} has been placed successfully!` 
        : 'Your order has been placed successfully!';
    
    // Add button
    const button = document.createElement('button');
    button.className = 'main-btn';
    button.textContent = 'Continue Shopping';
    button.addEventListener('click', function() {
        // Remove popup and redirect
        document.body.removeChild(popupOverlay);
        window.location.href = 'index.php';
    });
    
    // Assemble popup
    popupContent.appendChild(successIcon);
    popupContent.appendChild(title);
    popupContent.appendChild(message);
    popupContent.appendChild(button);
    popupOverlay.appendChild(popupContent);
    
    // Add to body
    document.body.appendChild(popupOverlay);
    
    // Add animation class after a small delay (for animation to work)
    setTimeout(function() {
        popupContent.classList.add('show');
    }, 10);
}

// ===== CREDIT CARD VALIDATION FUNCTIONS =====

function initCreditCardValidation() {
    // Get credit card form elements
    const cardNumberInput = document.getElementById('cardNumber');
    const expiryDateInput = document.getElementById('expiryDate');
    const cvvInput = document.getElementById('cvv');
    const nameOnCardInput = document.getElementById('nameOnCard');
    const paymentNextBtn = document.getElementById('payment-next');

    // Add validation event listeners for credit card fields
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            // Format card number as user types
            this.value = formatCardNumber(this.value);
            // Validate card number (basic check for numbers only)
            validateCardNumber(this);
        });
    }

    if (expiryDateInput) {
        expiryDateInput.addEventListener('input', function() {
            // Basic format MM/YY
            let value = this.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            this.value = value;
            validateExpiryDate(this);
        });
    }

    if (cvvInput) {
        cvvInput.addEventListener('input', function() {
            // Allow only numbers and limit length
            this.value = this.value.replace(/\D/g, '').substring(0, 3);
            validateCVV(this);
        });
    }

    if (nameOnCardInput) {
        nameOnCardInput.addEventListener('input', function() {
            validateNameOnCard(this);
        });
    }

    // Only override the payment form validation
    if (paymentNextBtn) {
        paymentNextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Check if the payment form is valid before proceeding
            if (validatePaymentFormEnhanced()) {
                // Ensure updateReviewSection exists before calling
                if (typeof updateReviewSection === 'function') {
                    updateReviewSection();
                } else {
                    console.error('updateReviewSection function not found.');
                }
                goToSection('review-section');
            }
        });
    }
}

// Format card number with spaces (4 digits groups)
function formatCardNumber(value) {
    const regex = /^(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,})$/g;
    const onlyNumbers = value.replace(/[^\d]/g, '');

    return onlyNumbers.replace(regex, (regex, $1, $2, $3, $4, $5) => {
        let output = '';
        if ($1) output += $1;
        if ($2) output += ' ' + $2;
        if ($3) output += ' ' + $3;
        if ($4) output += ' ' + $4;
        // Removed $5 to prevent more than 16 digits visually with spaces
        return output.trim();
    }).substring(0, 19); // Limit visual length with spaces
}

// Validate credit card number (only numbers)
function validateCardNumber(inputElement) {
    const value = inputElement.value.replace(/\s+/g, ''); // Remove spaces for validation

    // Check if empty
    if (!value) {
        showError(inputElement, 'Card number is required');
        return false;
    }

    // Check if it contains only numbers
    if (!/^[0-9]+$/.test(value)) {
        showError(inputElement, 'Card number should only contain numbers');
        return false;
    }

    // Optional: Basic length check (e.g., at least 13 digits)
    // if (value.length < 13) {
    //     showError(inputElement, 'Card number seems too short');
    //     return false;
    // }

    clearError(inputElement);
    return true;
}

// Luhn algorithm implementation (kept for potential future use, but not used in validateCardNumber now)
function luhnCheck(cardNumber) {
    let sum = 0;
    let shouldDouble = false;
    
    // Loop through values starting from the rightmost digit
    for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i));
        
        if (shouldDouble) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        
        sum += digit;
        shouldDouble = !shouldDouble;
    }
    
    return (sum % 10) === 0;
}

// Validate expiry date (must be in future and MM/YY format)
function validateExpiryDate(inputElement) {
    const value = inputElement.value;

    // Check if empty
    if (!value) {
        showError(inputElement, 'Expiry date is required');
        return false;
    }

    // Check format (MM/YY)
    if (!/^\d{2}\/\d{2}$/.test(value)) {
        showError(inputElement, 'Expiry date should be in MM/YY format');
        return false;
    }

    // Parse month and year
    const parts = value.split('/');
    const month = parseInt(parts[0], 10);
    const year = parseInt('20' + parts[1], 10); // Convert to 4-digit year

    // Create date objects for validation
    // Use month - 1 because Date object months are 0-indexed
    const expiryDate = new Date(year, month - 1);
    const today = new Date();
    // Set today's date to the beginning of the month for comparison
    today.setDate(1);
    today.setHours(0, 0, 0, 0);


    // Check if month is valid (1-12)
    if (month < 1 || month > 12) {
        showError(inputElement, 'Month should be between 01-12');
        return false;
    }

    // Check if date is in the future (compare year first, then month)
    if (expiryDate < today) {
        showError(inputElement, 'Card has expired or expires this month');
        return false;
    }

    clearError(inputElement);
    return true;
}

// Validate CVV (exactly 3 digits)
function validateCVV(inputElement) {
    const value = inputElement.value;

    // Check if empty
    if (!value) {
        showError(inputElement, 'CVV is required');
        return false;
    }

    // Check length (exactly 3 digits)
    if (value.length !== 3) {
        showError(inputElement, 'CVV should be exactly 3 digits');
        return false;
    }

    // Check if it contains only numbers (already handled by input listener, but good practice)
    if (!/^[0-9]+$/.test(value)) {
        showError(inputElement, 'CVV should only contain numbers');
        return false;
    }


    clearError(inputElement);
    return true;
}

// Remove all card validation helpers (formatCardNumber, validateCardNumber, luhnCheck, validateExpiryDate, validateCVV, validateNameOnCard)
// ^^^ This comment is now inaccurate, we are keeping and using these helpers ^^^

// Enhanced payment form validation
function validatePaymentFormEnhanced() {
    const selectedMethod = document.querySelector('.payment-method.selected');
    if (!selectedMethod) {
        // Should not happen if one is selected by default, but good to check
        alert('Please select a payment method.');
        return false;
    }
    const paymentMethod = selectedMethod.getAttribute('data-method');

    if (paymentMethod === 'credit-card') {
        // Validate all credit card fields
        const isCardNumberValid = validateCardNumber(document.getElementById('cardNumber'));
        const isExpiryDateValid = validateExpiryDate(document.getElementById('expiryDate'));
        const isCvvValid = validateCVV(document.getElementById('cvv'));
        const isNameOnCardValid = validateNameOnCard(document.getElementById('nameOnCard'));

        // Check if Name on Card is empty (as it's required)
        const nameOnCardInput = document.getElementById('nameOnCard');
        if (!nameOnCardInput.value) {
             showError(nameOnCardInput, 'Name on Card is required');
             // Need to return false here as well
             return false;
        }


        // Return true only if all fields are valid
        return isCardNumberValid && isExpiryDateValid && isCvvValid && isNameOnCardValid && nameOnCardInput.value;
    }

    // For other payment methods, no specific validation needed here
    return true;
}

// ===== SHARED UTILITY FUNCTIONS =====

// Show error message
function showError(inputElement, message) {
    // Clear any existing error
    clearError(inputElement);
    
    // Add error class to input
    inputElement.classList.add('is-invalid');
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    // Insert error message after input
    inputElement.parentNode.appendChild(errorDiv);
}

// Clear error message
function clearError(inputElement) {
    inputElement.classList.remove('is-invalid');
    
    // Remove any existing error messages
    const existingError = inputElement.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

// Helper function to access the goToSection function from the main checkout script
function goToSection(sectionId) {
    // If the original function exists in the global scope, use it
    if (typeof window.goToSection === 'function') {
        window.goToSection(sectionId);
    } else {
        // Fallback implementation
        // Hide all sections
        const sections = document.querySelectorAll('.checkout-section');
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show the selected section
        document.getElementById(sectionId).classList.add('active');
        
        // Update steps
        const steps = document.querySelectorAll('.step');
        steps.forEach(step => {
            step.classList.remove('active', 'completed');
        });
        
        if (sectionId === 'shipping-section') {
            document.getElementById('step1').classList.add('active');
        } else if (sectionId === 'payment-section') {
            document.getElementById('step1').classList.add('completed');
            document.getElementById('step2').classList.add('active');
        } else if (sectionId === 'review-section') {
            document.getElementById('step1').classList.add('completed');
            document.getElementById('step2').classList.add('completed');
            document.getElementById('step3').classList.add('active');
        }
    }
}

// Add this new function: // <-- This comment is slightly misleading now, it was already added
function validateNameOnCard(inputElement) {
    const value = inputElement.value;

    // Check if empty - Added check for required field
    if (!value) {
        showError(inputElement, 'Name on Card is required');
        return false;
    }

    if (/\d/.test(value)) {
        showError(inputElement, 'Name on Card should not contain numbers');
        return false;
    } else {
        clearError(inputElement);
        return true;
    }
}