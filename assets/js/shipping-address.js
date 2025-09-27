document.addEventListener('DOMContentLoaded', function() {
    const addressModal = document.getElementById('addressModal');
    if (!addressModal) return;

    const modalTitle = document.getElementById('addressModalTitle');
    const form = document.getElementById('addressForm');
    const actionInput = document.getElementById('addressAction');
    const addressIdInput = document.getElementById('addressId');

    const openModal = () => addressModal.classList.add('show');
    const closeModal = () => {
        addressModal.classList.remove('show');
        form.reset();
    };

    document.getElementById('addNewAddressBtn').addEventListener('click', () => {
        modalTitle.textContent = 'Add New Address';
        actionInput.value = 'add';
        addressIdInput.value = '';
        form.reset();
        openModal();
    });

    document.getElementById('addressModalClose').addEventListener('click', closeModal);
    addressModal.addEventListener('click', e => {
        if (e.target === addressModal) closeModal();
    });

    document.getElementById('addressList').addEventListener('click', e => {
        const target = e.target;
        const addressCard = target.closest('.address-card');
        if (!addressCard) return;

        const id = addressCard.dataset.addressId;

        if (target.classList.contains('btn-edit-address')) {
            fetch(`${BASE_URL}ajax/manage_address.php?action=get&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        modalTitle.textContent = 'Edit Address';
                        actionInput.value = 'update';
                        addressIdInput.value = data.data.id;
                        for (const key in data.data) {
                            const field = form.querySelector(`[name="${key}"]`);
                            if (field) {
                                if (field.type === 'checkbox') {
                                    field.checked = !!parseInt(data.data[key]);
                                } else {
                                    field.value = data.data[key];
                                }
                            }
                        }
                        openModal();
                    } else {
                        showNotification(data.message, 'error');
                    }
                });
        }

        if (target.classList.contains('btn-delete-address')) {
            if (confirm('Are you sure you want to delete this address?')) {
                handleAddressAction('delete', { address_id: id });
            }
        }

        if (target.classList.contains('btn-set-default')) {
            handleAddressAction('set_default', { address_id: id });
        }
    });

    form.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(form);
        const action = actionInput.value;
        handleAddressAction(action, formData, true);
    });

    function handleAddressAction(action, data, isFormData = false) {
        const body = isFormData ? data : new FormData();
        if (!isFormData) {
            body.append('action', action);
            for (const key in data) {
                body.append(key, data[key]);
            }
        }

        fetch(`${BASE_URL}ajax/manage_address.php`, {
            method: 'POST',
            body: body
        })
        .then(res => res.json())
        .then(data => {
            showNotification(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                setTimeout(() => window.location.reload(), 1000);
            }
        })
        .catch(err => {
            console.error(err);
            showNotification('A network error occurred.', 'error');
        });
    }
});