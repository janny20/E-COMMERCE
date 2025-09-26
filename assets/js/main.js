window.addEventListener('load', function() {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        // Add a short delay to prevent a jarring flash on fast connections
        setTimeout(() => preloader.classList.add('hidden'), 200);
    }
});

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
        mobileNavToggle.classList.add('is-active');
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
        mobileNavToggle.classList.remove('is-active');
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

    // Initialize Back to Top Button
    initBackToTopButton();

    // Initialize Wishlist functionality
    initWishlistButtons();

    // Initialize Move to Cart functionality
    initMoveToCartButtons();

    // Initialize Vendor Order Status functionality
    initVendorOrderStatus();

    // Initialize Cookie Consent
    initCookieConsent();
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

// Helper function to update wishlist page after an item is removed
function updateWishlistStatus() {
    if (!document.body.querySelector('.wishlist-page')) return;

    const wishlistCountEl = document.querySelector('.wishlist-page .products-meta');
    const productGrid = document.querySelector('.wishlist-page .products-grid');
    
    if (wishlistCountEl && productGrid) {
        const currentCount = productGrid.children.length;
        wishlistCountEl.textContent = `You have ${currentCount} item(s) in your wishlist.`;

        if (currentCount === 0) {
            const productsMain = document.querySelector('.wishlist-page .products-main');
            if (productsMain) {
                productsMain.innerHTML = `
                    <div class="products-empty">
                        <div class="products-empty-icon"><i class="far fa-heart"></i></div>
                        <h3 class="products-empty-title">Your Wishlist is Empty</h3>
                        <p class="products-empty-text">Add items you love to your wishlist to save them for later.</p>
                        <a href="${BASE_URL}pages/products.php" class="btn btn-primary">Discover Products</a>
                    </div>
                `;
            }
        }
    }
}

// Wishlist Buttons
function initWishlistButtons() {
    document.body.addEventListener('click', function(e) {
        const wishlistBtn = e.target.closest('.wishlist-btn, .btn-wishlist');
        if (!wishlistBtn) return;

        e.preventDefault();
        
        const productId = wishlistBtn.dataset.productId;
        if (!productId) {
            console.error('Wishlist button is missing data-product-id attribute.');
            return;
        }

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch(`${BASE_URL}ajax/update_wishlist.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.require_login) {
                // Redirect to login, preserving the current page as the redirect destination
                window.location.href = `${BASE_URL}pages/login.php?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                return;
            }

            if (data.success) {
                showNotification(data.message, 'success');

                // If an item was removed and we are on the wishlist page, remove the card from the DOM.
                if (data.action === 'removed' && document.body.querySelector('.wishlist-page')) {
                    const productCard = wishlistBtn.closest('.product-card');
                    if (productCard) {
                        productCard.style.transition = 'opacity 0.3s ease';
                        productCard.style.opacity = '0';                        
                        setTimeout(() => { productCard.remove(); updateWishlistStatus(); }, 300);
                    }
                } else {
                    // Original behavior for all other pages
                    wishlistBtn.classList.toggle('active', data.action === 'added');
                    const icon = wishlistBtn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('far', data.action === 'removed');
                        icon.classList.toggle('fas', data.action === 'added');
                    }
                    // Update the title attribute
                    wishlistBtn.title = data.action === 'added' ? 'Remove from wishlist' : 'Add to wishlist';
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Wishlist Error:', error);
            showNotification('A network error occurred.', 'error');
        });
    });
}

// Move to Cart Buttons (from Wishlist)
function initMoveToCartButtons() {
    document.body.addEventListener('click', function(e) {
        const moveBtn = e.target.closest('.btn-move-to-cart');
        if (!moveBtn) return;

        e.preventDefault();
        
        const productId = moveBtn.dataset.productId;
        if (!productId) {
            console.error('Move to Cart button is missing data-product-id attribute.');
            return;
        }

        moveBtn.disabled = true;
        moveBtn.innerHTML = '<span class="loading"></span> Moving...';

        const formData = new FormData();
        formData.append('product_id', productId);

        fetch(`${BASE_URL}ajax/move_to_cart.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.require_login) {
                window.location.href = `${BASE_URL}pages/login.php?redirect=${encodeURIComponent(window.location.pathname + window.location.search)}`;
                return;
            }

            if (data.success) {
                showNotification(data.message, 'success');

                document.querySelectorAll('.cart-link').forEach(el => {
                    el.innerHTML = `<i class="fas fa-shopping-cart"></i> Cart (${data.cartCount})`;
                });

                const productCard = moveBtn.closest('.product-card');
                if (productCard) {
                    productCard.style.transition = 'opacity 0.3s ease';
                    productCard.style.opacity = '0';
                    setTimeout(() => { productCard.remove(); updateWishlistStatus(); }, 300);
                }
            } else {
                showNotification(data.message, 'error');
                moveBtn.disabled = false;
                moveBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Move to Cart';
            }
        })
        .catch(error => {
            console.error('Move to Cart Error:', error);
            showNotification('A network error occurred.', 'error');
            moveBtn.disabled = false;
            moveBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Move to Cart';
        });
    });
}

// Vendor Order Status Updates
function initVendorOrderStatus() {
    const trackingModal = document.getElementById('trackingModal');
    if (!trackingModal) return; // Only run on pages with the modal

    const trackingForm = document.getElementById('trackingForm');
    const closeBtn = document.getElementById('trackingModalClose');
    const orderItemIdInput = document.getElementById('trackingOrderItemId');

    function openTrackingModal(itemId) {
        orderItemIdInput.value = itemId;
        trackingModal.classList.add('show');
    }

    function closeTrackingModal() {
        trackingModal.classList.remove('show');
        trackingForm.reset();
    }

    closeBtn.addEventListener('click', closeTrackingModal);
    trackingModal.addEventListener('click', (e) => {
        if (e.target === trackingModal) closeTrackingModal();
    });

    document.body.addEventListener('change', function(e) {
        if (e.target.matches('.item-status-select')) {
            const select = e.target;
            const itemId = select.dataset.itemId;
            const newStatus = select.value;
            const currentStatus = select.dataset.currentStatus;

            if (newStatus === currentStatus) return;

            if (newStatus === 'shipped') {
                openTrackingModal(itemId);
                // Revert selection until tracking is submitted
                select.value = currentStatus;
            } else {
                updateItemStatus(itemId, newStatus);
            }
        }
    });

    trackingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const itemId = orderItemIdInput.value;
        const carrier = document.getElementById('shipping_carrier').value;
        const trackingNumber = document.getElementById('tracking_number').value;
        updateItemStatus(itemId, 'shipped', trackingNumber, carrier);
        closeTrackingModal();
    });

    function updateItemStatus(itemId, status, trackingNumber = null, carrier = null) {
        const formData = new FormData();
        formData.append('item_id', itemId);
        formData.append('status', status);
        if (trackingNumber) formData.append('tracking_number', trackingNumber);
        if (carrier) formData.append('shipping_carrier', carrier);

        showNotification('Updating status...', 'info');

        fetch(`${BASE_URL}ajax/update_order_item.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.reload(), 1500); // Reload to see changes
            } else {
                showNotification(data.message || 'Update failed.', 'error');
            }
        })
        .catch(error => showNotification('A network error occurred.', 'error'));
    }
}

// Back to Top Button
function initBackToTopButton() {
    const backToTopBtn = document.querySelector('.back-to-top-btn');

    if (!backToTopBtn) return;

    // Function to show/hide button based on scroll position
    const scrollHandler = () => {
        if (window.scrollY > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    };

    // Function to scroll to top on click
    const clickHandler = (e) => {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    window.addEventListener('scroll', scrollHandler);
    backToTopBtn.addEventListener('click', clickHandler);
}

// Cookie Consent Banner
function initCookieConsent() {
    const banner = document.getElementById('cookie-consent-banner');
    const acceptBtn = document.getElementById('cookie-accept-btn');
    const declineBtn = document.getElementById('cookie-decline-btn');
    const settingsLink = document.getElementById('cookie-settings-link');
    const settingsModal = document.getElementById('cookie-settings-modal');
    const settingsCloseBtn = document.getElementById('cookie-settings-close');
    const saveSettingsBtn = document.getElementById('cookie-save-settings');

    if (!banner) {
        return;
    }

    // Check if consent has already been given
    if (localStorage.getItem('cookie_consent')) {
        return; // Don't show the banner
    }

    // Show the banner immediately on page load
    banner.classList.add('show');

    function handleConsent(consentValue) {
        localStorage.setItem('cookie_consent', consentValue);
        banner.classList.remove('show');
        
        // Optional: remove the banner from the DOM after it hides
        setTimeout(() => { banner.remove(); }, 600);
    }

    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => handleConsent('true'));
    }

    if (declineBtn) {
        declineBtn.addEventListener('click', () => handleConsent('false'));
    }

    // --- Cookie Settings Modal Logic ---
    if (!settingsModal || !settingsLink || !settingsCloseBtn || !saveSettingsBtn) {
        return;
    }

    function openCookieSettings() {
        settingsModal.classList.add('show');
    }

    function closeCookieSettings() {
        settingsModal.classList.remove('show');
    }

    settingsLink.addEventListener('click', function(e) {
        e.preventDefault();
        openCookieSettings();
    });

    settingsCloseBtn.addEventListener('click', closeCookieSettings);

    settingsModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeCookieSettings();
        }
    });

    saveSettingsBtn.addEventListener('click', function() {
        // Here you would save the individual cookie preferences from the toggles
        // For this example, we'll just accept all and close.
        closeCookieSettings();
        handleConsent('true'); // Treat saving as accepting all for now
        showNotification('Your cookie preferences have been saved.', 'success');
    });
}