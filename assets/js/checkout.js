document.addEventListener('DOMContentLoaded', function() {
    const checkoutForm = document.getElementById('checkout-form');
    if (!checkoutForm) return;

    // --- Payment Method Interaction ---
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Remove active class from all parents
            document.querySelectorAll('.payment-method').forEach(pm => pm.classList.remove('active'));
            // Add active class to the selected one
            this.closest('.payment-method').classList.add('active');

            // Toggle required attributes for validation
            updateRequiredFields(this.value);
        });
    });
    // Trigger change on initial load for the checked one
    const defaultMethod = document.querySelector('input[name="payment_method"]:checked');
    if (defaultMethod) {
        defaultMethod.dispatchEvent(new Event('change'));
    }

    // --- Coupon Toggle ---
    const couponToggle = document.querySelector('.coupon-toggle');
    const couponForm = document.querySelector('.checkout-sidebar .coupon-form');
    if (couponToggle && couponForm) {
        couponToggle.addEventListener('click', function() {
            couponForm.classList.toggle('active');
            this.querySelector('.fa-chevron-down').classList.toggle('active');
        });
    }

    // --- Form Validation ---
    const placeOrderBtn = document.querySelector('.btn-place-order');
    const allFields = Array.from(checkoutForm.querySelectorAll('input, select, textarea'));

    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required');
        let error = '';

        if (isRequired && value === '') {
            error = 'This field is required.';
        } else if (value !== '') {
            switch (field.type) {
                case 'email':
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) error = 'Please enter a valid email address.';
                    break;
                case 'tel':
                    if (!/^\+?[0-9\s-()]{7,}$/.test(value)) error = 'Please enter a valid phone number.';
                    break;
            }
            switch (field.id) {
                case 'card_number':
                    if (!/^\d{4}\s\d{4}\s\d{4}\s\d{4}$/.test(value)) error = 'Please enter a valid 16-digit card number.';
                    break;
                case 'card_expiry':
                    if (!/^(0[1-9]|1[0-2])\s?\/\s?([0-9]{2})$/.test(value)) {
                        error = 'Enter a valid date in MM/YY format.';
                    } else {
                        const [month, year] = value.split('/').map(s => parseInt(s.trim()));
                        const currentYear = new Date().getFullYear() % 100;
                        const currentMonth = new Date().getMonth() + 1;
                        if (year < currentYear || (year === currentYear && month < currentMonth)) {
                            error = 'Card has expired.';
                        }
                    }
                    break;
                case 'card_cvv':
                    if (!/^[0-9]{3,4}$/.test(value)) error = 'Enter a valid CVV.';
                    break;
            }
        }

        const formGroup = field.closest('.form-group');
        const errorElement = formGroup.querySelector('.error-message');
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

    function updateRequiredFields(paymentMethod) {
        const cardFields = ['card_number', 'card_name', 'card_expiry', 'card_cvv'];
        const momoFields = ['momo_number', 'momo_network'];

        cardFields.forEach(id => {
            const field = document.getElementById(id);
            if (field) field.required = (paymentMethod === 'card');
        });
        momoFields.forEach(id => {
            const field = document.getElementById(id);
            if (field) field.required = (paymentMethod === 'mobile_money');
        });

        // Clear validation on fields that are no longer required
        allFields.forEach(field => {
            if (!field.required) {
                const formGroup = field.closest('.form-group');
                if (formGroup) {
                    formGroup.classList.remove('has-error');
                    formGroup.querySelector('.error-message').textContent = '';
                }
            }
        });
    }

    allFields.forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => {
            // Clear error on input for better UX
            const formGroup = field.closest('.form-group');
            if (formGroup.classList.contains('has-error')) {
                formGroup.classList.remove('has-error');
                formGroup.querySelector('.error-message').textContent = '';
            }
        });
    });

    checkoutForm.addEventListener('submit', function(e) {
        let isFormValid = true;
        allFields.forEach(field => {
            // Only validate visible and required fields
            if (field.offsetParent !== null && field.hasAttribute('required')) {
                if (!validateField(field)) {
                    isFormValid = false;
                }
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            showNotification('Please correct the errors before proceeding.', 'error');
            const firstError = checkoutForm.querySelector('.has-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
    });

    // --- Input Formatting ---
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value.substring(0, 19);
        });
    }

    const expiryInput = document.getElementById('card_expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value.substring(0, 5);
        });
    }
});