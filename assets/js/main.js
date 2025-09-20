document.addEventListener('DOMContentLoaded', function() {
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const mobileNavClose = document.querySelector('.mobile-nav-close');
    const mobileNavContainer = document.querySelector('.mobile-nav-container');
    const navOverlay = document.querySelector('.nav-overlay');
    let lastFocusedElement; // To store the element that opened the nav
    let focusableElements;
    const focusableElementsSelector = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

    function openNav() {
        lastFocusedElement = document.activeElement; // Store focus before opening
        mobileNavContainer.classList.add('open');
        navOverlay.classList.add('open');
        document.body.style.overflow = 'hidden'; // Prevent background scroll

        // Get all focusable elements inside the nav
        focusableElements = Array.from(mobileNavContainer.querySelectorAll(focusableElementsSelector));
        
        // Focus the first focusable element (the close button)
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
        document.addEventListener('keydown', trapFocus);
    }

    function closeNav() {
        mobileNavContainer.classList.remove('open');
        navOverlay.classList.remove('open');
        document.body.style.overflow = ''; // Restore background scroll
        document.removeEventListener('keydown', trapFocus);
        
        if (lastFocusedElement) {
            lastFocusedElement.focus(); // Return focus to the element that opened the nav
        }
    }

    // Focus trapping logic for the mobile menu
    function trapFocus(e) {
        if (e.key !== 'Tab' || !focusableElements) return;

        const firstFocusableElement = focusableElements[0];
        const lastFocusableElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) { // if shift + tab
            if (document.activeElement === firstFocusableElement) {
                lastFocusableElement.focus();
                e.preventDefault();
            }
        } else { // if tab
            if (document.activeElement === lastFocusableElement) {
                firstFocusableElement.focus();
                e.preventDefault();
            }
        }
    }

    if (mobileNavToggle) {
        mobileNavToggle.addEventListener('click', openNav);
    }
    if (mobileNavClose) {
        mobileNavClose.addEventListener('click', closeNav);
    }
    if (navOverlay) {
        navOverlay.addEventListener('click', closeNav);
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileNavContainer.classList.contains('open')) {
            closeNav();
        }
    });

    const mobileSearchToggle = document.querySelector('.mobile-search-toggle');
    const searchCloseBtn = document.querySelector('.search-close-btn');
    const topNav = document.querySelector('.top-nav');

    if (mobileSearchToggle && searchCloseBtn && topNav) {
        mobileSearchToggle.addEventListener('click', function() {
            topNav.classList.add('search-active');
            topNav.querySelector('.search-input').focus();
        });

        searchCloseBtn.addEventListener('click', function() {
            topNav.classList.remove('search-active');
        });
    }
});

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.innerHTML = `<span>${message}</span><button class="notification-close">&times;</button>`;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto remove after 5 seconds
    const autoRemoveTimeout = setTimeout(() => {
        hideNotification(notification);
    }, 5000);
    
    // Close button event
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
        clearTimeout(autoRemoveTimeout); // Prevent trying to remove it again
        hideNotification(notification);
    });
}

function hideNotification(notification) {
    if (!notification) return;
    notification.classList.remove('show');
    // Wait for fade out animation to complete before removing
    setTimeout(() => {
        notification.remove();
    }, 300);
}