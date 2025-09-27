document.addEventListener('DOMContentLoaded', function() {
    const productForm = document.getElementById('product-form');
    if (!productForm) return;

    const productImageInput = document.getElementById('product-images-input');
    const imagePreviewsContainer = document.getElementById('image-previews');
    const existingImagesHiddenInput = document.getElementById('existing-images-data');
    const submitButton = productForm.querySelector('button[type="submit"]');

    let existingImages = existingImagesHiddenInput ? JSON.parse(existingImagesHiddenInput.value) : [];
    let newFiles = [];

    // --- Image Preview and Management ---
    function renderImagePreviews() {
        imagePreviewsContainer.innerHTML = '';

        // Render existing images
        existingImages.forEach((imageName, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'image-preview-wrapper';
            wrapper.innerHTML = `
                <img src="${BASE_URL}uploads/products/${imageName}" alt="Product Image">
                <button type="button" class="delete-image-btn" data-type="existing" data-index="${index}"><i class="fas fa-times"></i></button>
            `;
            imagePreviewsContainer.appendChild(wrapper);
        });

        // Render new files
        newFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.createElement('div');
                wrapper.className = 'image-preview-wrapper';
                wrapper.innerHTML = `
                    <img src="${e.target.result}" alt="Product Image">
                    <button type="button" class="delete-image-btn" data-type="new" data-index="${index}"><i class="fas fa-times"></i></button>
                `;
                imagePreviewsContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });

        // Update hidden input for existing images
        if (existingImagesHiddenInput) {
            existingImagesHiddenInput.value = JSON.stringify(existingImages);
        }
    }

    // Handle new image selection
    if (productImageInput) {
        productImageInput.addEventListener('change', function(e) {
            Array.from(e.target.files).forEach(file => {
                newFiles.push(file);
            });
            this.value = ''; // Clear input to allow re-selection of same file
            renderImagePreviews();
        });
    }

    // Handle image deletion
    imagePreviewsContainer.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('.delete-image-btn');
        if (deleteBtn) {
            const type = deleteBtn.dataset.type;
            const index = parseInt(deleteBtn.dataset.index);

            if (type === 'existing') {
                if (confirm('Are you sure you want to remove this existing image? It will be permanently deleted from the server upon saving the product.')) {
                    existingImages.splice(index, 1);
                } else {
                    return;
                }
            } else if (type === 'new') {
                newFiles.splice(index, 1);
            }
            renderImagePreviews();
        }
    });

    // Initial render of existing images
    renderImagePreviews();

    // --- Form Validation ---
    const allFields = Array.from(productForm.querySelectorAll('input, select, textarea'));

    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        let error = '';

        if (isRequired && value === '') {
            error = 'This field is required.';
        } else if (field.type === 'number') {
            if (isNaN(value) || parseFloat(value) < 0) {
                error = 'Please enter a valid positive number.';
            }
        }

        const formGroup = field.closest('.form-group');
        const errorElement = formGroup ? formGroup.querySelector('.error-message') : null;

        if (errorElement) {
            if (error) {
                formGroup.classList.add('has-error');
                errorElement.textContent = error;
                return false;
            } else {
                formGroup.classList.remove('has-error');
                errorElement.textContent = '';
                return true;
            }
        }
        return true; // No error element or not required
    }

    allFields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            const formGroup = field.closest('.form-group');
            if (formGroup && formGroup.classList.contains('has-error')) {
                formGroup.classList.remove('has-error');
                formGroup.querySelector('.error-message').textContent = '';
            }
        });
    });

    productForm.addEventListener('submit', function(e) {
        let isFormValid = true;
        allFields.forEach(field => {
            if (!validateField(field)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            showNotification('Please correct the errors in the form.', 'error');
            const firstError = productForm.querySelector('.has-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Append new files to FormData manually
        const formData = new FormData(this);
        newFiles.forEach((file, index) => {
            formData.append(`new_images[]`, file);
        });

        // Disable button and show loading
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // If it's an edit and no redirect, update existing images list
                    if (data.updated_images) {
                        existingImages = data.updated_images;
                        newFiles = []; // Clear new files after successful upload
                        renderImagePreviews();
                    }
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Save Product';
                }
            } else {
                showNotification(data.message || 'An error occurred.', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Product';
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            showNotification('A network error occurred.', 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Save Product';
        });

        e.preventDefault(); // Prevent default form submission as we're using fetch
    });
});