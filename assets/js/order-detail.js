document.addEventListener('DOMContentLoaded', function() {
    const reviewModal = document.getElementById('reviewModal');
    if (!reviewModal) return;

    const reviewModalClose = document.getElementById('reviewModalClose');
    const reviewForm = document.getElementById('reviewForm');
    const reviewProductIdInput = document.getElementById('reviewProductId');
    const reviewModalTitle = document.getElementById('reviewModalTitle');
    const ratingInput = document.getElementById('reviewRating');
    const stars = document.querySelectorAll('.star-rating i');

    function openModal(productId, productName) {
        reviewModalTitle.textContent = `Reviewing: ${productName}`;
        reviewProductIdInput.value = productId;
        reviewModal.classList.add('show');
    }

    function closeModal() {
        reviewModal.classList.remove('show');
        reviewForm.reset();
        resetStars();
        clearErrors();
    }

    function resetStars() {
        stars.forEach(star => {
            star.classList.remove('fas');
            star.classList.add('far');
        });
        ratingInput.value = '';
    }

    function clearErrors() {
        document.getElementById('ratingError').textContent = '';
        document.getElementById('commentError').textContent = '';
    }

    document.querySelectorAll('.btn-write-review').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            openModal(productId, productName);
        });
    });

    reviewModalClose.addEventListener('click', closeModal);
    reviewModal.addEventListener('click', e => {
        if (e.target === reviewModal) closeModal();
    });

    // Star rating interaction
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.dataset.value;
            stars.forEach(s => {
                s.classList.toggle('fas', s.dataset.value <= rating);
                s.classList.toggle('far', s.dataset.value > rating);
            });
        });

        star.addEventListener('mouseout', function() {
            const currentRating = ratingInput.value;
            stars.forEach(s => {
                s.classList.toggle('fas', s.dataset.value <= currentRating);
                s.classList.toggle('far', s.dataset.value > currentRating);
            });
        });

        star.addEventListener('click', function() {
            ratingInput.value = this.dataset.value;
        });
    });

    // Form submission
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        let isValid = true;

        if (!formData.get('rating')) {
            document.getElementById('ratingError').textContent = 'Please select a rating.';
            isValid = false;
        }
        if (!formData.get('comment').trim()) {
            document.getElementById('commentError').textContent = 'Please write a comment.';
            isValid = false;
        }

        if (!isValid) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading"></span> Submitting...';

        fetch(`${BASE_URL}ajax/submit_review.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                closeModal();
                // Update the button on the page
                const reviewedButton = document.querySelector(`.btn-write-review[data-product-id="${formData.get('product_id')}"]`);
                if (reviewedButton) {
                    reviewedButton.outerHTML = '<button class="btn btn-sm btn-outline-success" disabled><i class="fas fa-check"></i> Reviewed</button>';
                }
            } else {
                showNotification(data.message || 'An error occurred.', 'error');
            }
        })
        .catch(error => {
            console.error('Review submission error:', error);
            showNotification('A network error occurred.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Review';
        });
    });
});