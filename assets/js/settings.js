document.addEventListener('DOMContentLoaded', function() {
    
    // --- Password Modal ---
    const passwordModal = document.getElementById('passwordModal');
    if (passwordModal) {
        const openBtn = document.getElementById('openPasswordModalBtn');
        const closeBtn = document.getElementById('passwordModalClose');
        const cancelBtn = document.getElementById('passwordModalCancel');
        const form = document.getElementById('passwordChangeForm');

        const openPasswordModal = () => passwordModal.classList.add('show');
        const closePasswordModal = () => passwordModal.classList.remove('show');

        openBtn.addEventListener('click', openPasswordModal);
        closeBtn.addEventListener('click', closePasswordModal);
        cancelBtn.addEventListener('click', closePasswordModal);
        passwordModal.addEventListener('click', (e) => {
            if (e.target === passwordModal) closePasswordModal();
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            const messageContainer = document.getElementById('password-modal-messages');
            const formData = new FormData(form);
            formData.append('change_password_ajax', '1');

            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="loading"></span> Updating...';
            messageContainer.innerHTML = '';

            fetch(`${BASE_URL}ajax/update_user_settings.php`, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    const alertClass = data.success ? 'alert-success' : 'alert-error';
                    messageContainer.innerHTML = `<div class="alert ${alertClass}">${data.message}</div>`;
                    if (data.success) {
                        form.reset();
                        setTimeout(() => {
                            closePasswordModal();
                            messageContainer.innerHTML = '';
                        }, 2000);
                    }
                })
                .catch(error => {
                    messageContainer.innerHTML = `<div class="alert alert-error">A network error occurred.</div>`;
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Update Password';
                });
        });
    }

    // --- Preference Toggles ---
    document.querySelectorAll('.preference-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const setting = this.dataset.setting;
            const value = this.checked;

            const formData = new FormData();
            formData.append('update_preferences_ajax', '1');
            formData.append('setting', setting);
            formData.append('value', value);

            fetch(`${BASE_URL}ajax/update_user_settings.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                    // Revert the toggle on failure
                    this.checked = !value;
                }
            })
            .catch(error => {
                console.error('Preference update error:', error);
                showNotification('A network error occurred.', 'error');
                this.checked = !value;
            });
        });
    });
});