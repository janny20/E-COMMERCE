// Main JavaScript for the e-commerce platform

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize dropdowns
    initDropdowns();
    
    // Initialize quantity buttons
    initQuantityButtons();
    
    // Initialize image sliders
    initImageSliders();
});

// Tooltip initialization
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Dropdown initialization
function initDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');

    // Function to close all dropdowns
    const closeAllDropdowns = (exceptThisOne = null) => {
        const allDropdowns = document.querySelectorAll('.dropdown');
        allDropdowns.forEach(d => {
            if (d !== exceptThisOne) {
                d.querySelector('.dropdown-content')?.classList.remove('show');
                d.querySelector('.dropdown-toggle')?.setAttribute('aria-expanded', 'false');
            }
        });
    };

    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const content = dropdown.querySelector('.dropdown-content');

        if (toggle && content) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent document click listener from closing it immediately
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                
                // Close other dropdowns before opening a new one
                closeAllDropdowns(dropdown);

                toggle.setAttribute('aria-expanded', !isExpanded);
                content.classList.toggle('show');
            });
        }
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        closeAllDropdowns();
    });

    // Keyboard navigation for dropdowns
    document.addEventListener('keydown', (e) => {
        const openDropdown = document.querySelector('.dropdown-content.show');
        if (!openDropdown) return;

        const toggle = openDropdown.parentElement.querySelector('.dropdown-toggle');
        const items = Array.from(openDropdown.querySelectorAll('a'));
        const activeElement = document.activeElement;

        if (e.key === 'Escape') {
            openDropdown.classList.remove('show');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }

        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (items.length === 0) return;
            
            const currentIndex = items.indexOf(activeElement);
            let nextIndex;

            if (e.key === 'ArrowDown') {
                nextIndex = (currentIndex + 1) % items.length;
            } else { // ArrowUp
                nextIndex = (currentIndex - 1 + items.length) % items.length;
            }
            items[nextIndex].focus();
        }
    });
}

// Quantity buttons initialization
function initQuantityButtons() {
    const quantityContainers = document.querySelectorAll('.quantity-container');
    
    quantityContainers.forEach(container => {
        const minusBtn = container.querySelector('.quantity-minus');
        const plusBtn = container.querySelector('.quantity-plus');
        const input = container.querySelector('.quantity-input');
        
        if (minusBtn && plusBtn && input) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                input.value = value + 1;
            });
        }
    });
}

// Image slider initialization
function initImageSliders() {
    const sliders = document.querySelectorAll('.image-slider');
    
    sliders.forEach(slider => {
        const mainImage = slider.querySelector('.slider-main-image');
        const thumbnails = slider.querySelectorAll('.slider-thumbnail');
        
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                // Update main image
                mainImage.src = this.src;
                
                // Update active thumbnail
                thumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
}

// Add to cart functionality
function addToCart(productId, quantity = 1) {
    // Check if user is logged in
    const isLoggedIn = document.body.getAttribute('data-user-loggedin') === 'true';
    
    if (!isLoggedIn) {
        window.location.href = 'login.php';
        return;
    }
    
    // AJAX request to add to cart
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax/add_to_cart.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Update cart count
                updateCartCount(response.cartCount);
                
                // Show success message
                showNotification('Product added to cart successfully!', 'success');
            } else {
                showNotification('Error adding product to cart: ' + response.message, 'error');
            }
        }
    };
    xhr.send('product_id=' + productId + '&quantity=' + quantity);
}

// Update cart count
function updateCartCount(count) {
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type;
    notification.innerHTML = '<span>' + message + '</span><button class="notification-close">&times;</button>';
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        hideNotification(notification);
    }, 5000);
    
    // Close button event
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
        hideNotification(notification);
    });
}

function hideNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            highlightError(input, 'This field is required');
        } else {
            removeHighlight(input);
            
            // Email validation
            if (input.type === 'email') {
                if (!isValidEmail(input.value)) {
                    isValid = false;
                    highlightError(input, 'Please enter a valid email address');
                }
            }
            
            // Password validation
            if (input.type === 'password' && input.id === 'password') {
                if (input.value.length < 6) {
                    isValid = false;
                    highlightError(input, 'Password must be at least 6 characters');
                }
            }
            
            // Confirm password validation
            if (input.id === 'confirm_password') {
                const password = form.querySelector('#password');
                if (password && input.value !== password.value) {
                    isValid = false;
                    highlightError(input, 'Passwords do not match');
                }
            }
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function highlightError(input, message) {
    input.classList.add('error');
    
    // Remove existing error message
    const existingError = input.nextElementSibling;
    if (existingError && existingError.classList.contains('error-message')) {
        existingError.remove();
    }
    
    // Add error message
    const errorMessage = document.createElement('div');
    errorMessage.className = 'error-message';
    errorMessage.textContent = message;
    input.parentNode.appendChild(errorMessage);
}

function removeHighlight(input) {
    input.classList.remove('error');
    
    // Remove error message
    const errorMessage = input.nextElementSibling;
    if (errorMessage && errorMessage.classList.contains('error-message')) {
        errorMessage.remove();
    }
}

// Search functionality
function initSearch() {
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('.search-input');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                highlightError(searchInput, 'Please enter a search term');
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
});