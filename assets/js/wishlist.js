document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('wishlist-items');
    if (!container) return;

    const selectAllCheckbox = document.getElementById('select-all');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkAddToCartBtn = document.getElementById('bulk-add-to-cart');
    const bulkRemoveBtn = document.getElementById('bulk-remove');
    const sortSelect = document.getElementById('sort-wishlist');

    function updateBulkButtons() {
        const selected = document.querySelectorAll('.item-checkbox:checked');
        const canAddToCart = Array.from(selected).some(cb => {
            const item = cb.closest('.wishlist-item');
            return item && !item.querySelector('.btn-move-to-cart').disabled;
        });

        bulkAddToCartBtn.disabled = !canAddToCart;
        bulkRemoveBtn.disabled = selected.length === 0;
    }

    selectAllCheckbox.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkButtons();
    });

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                if (document.querySelectorAll('.item-checkbox:checked').length === itemCheckboxes.length) {
                    selectAllCheckbox.checked = true;
                }
            }
            updateBulkButtons();
        });
    });

    // Sorting functionality
    sortSelect.addEventListener('change', function() {
        const sortBy = this.value;
        const items = Array.from(container.querySelectorAll('.wishlist-item'));

        items.sort((a, b) => {
            let valA, valB;
            switch (sortBy) {
                case 'price-asc':
                    valA = parseFloat(a.dataset.price);
                    valB = parseFloat(b.dataset.price);
                    return valA - valB;
                case 'price-desc':
                    valA = parseFloat(a.dataset.price);
                    valB = parseFloat(b.dataset.price);
                    return valB - valA;
                case 'date-asc':
                    valA = parseInt(a.dataset.dateAdded);
                    valB = parseInt(b.dataset.dateAdded);
                    return valA - valB;
                case 'date-desc':
                default:
                    valA = parseInt(a.dataset.dateAdded);
                    valB = parseInt(b.dataset.dateAdded);
                    return valB - valA;
            }
        });

        // Re-append sorted items
        items.forEach(item => container.appendChild(item));
    });

    function handleBulkAction(action, productIds, button) {
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="loading"></span> Processing...';

        const formData = new FormData();
        formData.append('action', action);
        productIds.forEach(id => formData.append('product_ids[]', id));

        fetch(`${BASE_URL}ajax/bulk_wishlist_action.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                
                // Remove processed items from the DOM
                productIds.forEach(id => {
                    const itemToRemove = document.querySelector(`.wishlist-item[data-product-id="${id}"]`);
                    if (itemToRemove) {
                        itemToRemove.remove();
                    }
                });

                // Update cart count in header if necessary
                if (data.cartCount !== undefined) {
                    document.querySelectorAll('.cart-link').forEach(el => {
                        el.innerHTML = `<i class="fas fa-shopping-cart"></i> Cart (${data.cartCount})`;
                    });
                }

                // Reset checkboxes and buttons
                selectAllCheckbox.checked = false;
                updateBulkButtons();

            } else {
                showNotification(data.message || 'An error occurred.', 'error');
            }
        })
        .catch(error => {
            console.error('Bulk Action Error:', error);
            showNotification('A network error occurred.', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    // Bulk Remove
    bulkRemoveBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        if (selectedCheckboxes.length === 0 || !confirm(`Are you sure you want to remove ${selectedCheckboxes.length} item(s) from your wishlist?`)) {
            return;
        }
        const productIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        handleBulkAction('bulk_remove', productIds, this);
    });

    // Bulk Add to Cart
    bulkAddToCartBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        if (selectedCheckboxes.length === 0) return;

        const productIds = Array.from(selectedCheckboxes)
            .filter(cb => {
                const item = cb.closest('.wishlist-item');
                return item && !item.querySelector('.btn-move-to-cart').disabled;
            })
            .map(cb => cb.value);

        if (productIds.length === 0) {
            showNotification('All selected items are out of stock.', 'info');
            return;
        }
        handleBulkAction('bulk_add_to_cart', productIds, this);
    });
});