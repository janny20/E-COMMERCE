// Landing Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel functionality
    initCarousel();
    
    // Initialize countdown timer
    initCountdown();
    
    // Initialize number counting animation
    initNumberCounting();
    
    // Initialize scroll animations
    initLandingHeaderScroll();
});

// Carousel functionality
function initCarousel() {
    const carousel = document.querySelector('.carousel-images');
    const items = document.querySelectorAll('.carousel-item');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');
    const dotsContainer = document.querySelector('.carousel-dots');
    
    let currentIndex = 0;
    let intervalId;
    
    // Create dots for carousel
    items.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('carousel-dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToSlide(index));
        dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.carousel-dot');
    
    // Function to go to specific slide
    function goToSlide(index) {
        currentIndex = index;
        carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // Update active dot
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentIndex);
        });
    }
    
    // Next slide function
    function nextSlide() {
        currentIndex = (currentIndex + 1) % items.length;
        goToSlide(currentIndex);
    }
    
    // Previous slide function
    function prevSlide() {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        goToSlide(currentIndex);
    }
    
    // Event listeners for buttons
    nextBtn.addEventListener('click', () => {
        nextSlide();
        resetInterval();
    });
    
    prevBtn.addEventListener('click', () => {
        prevSlide();
        resetInterval();
    });
    
    // Auto slide every 5 seconds
    function startInterval() {
        intervalId = setInterval(nextSlide, 5000);
    }
    
    // Reset interval on user interaction
    function resetInterval() {
        clearInterval(intervalId);
        startInterval();
    }
    
    // Start the auto slide
    startInterval();
    
    // Pause on hover
    carousel.addEventListener('mouseenter', () => {
        clearInterval(intervalId);
    });
    
    carousel.addEventListener('mouseleave', () => {
        startInterval();
    });
}

// Countdown timer for flash sales
function initCountdown() {
    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const secondsElement = document.getElementById('seconds');
    
    // Set the countdown time (24 hours from now)
    let totalSeconds = 24 * 60 * 60;
    
    function updateCountdown() {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        
        hoursElement.textContent = hours.toString().padStart(2, '0');
        minutesElement.textContent = minutes.toString().padStart(2, '0');
        secondsElement.textContent = seconds.toString().padStart(2, '0');
        
        if (totalSeconds > 0) {
            totalSeconds--;
        } else {
            // Countdown finished
            clearInterval(countdownInterval);
            document.querySelector('.countdown-timer').innerHTML = '<span>Sale Ended</span>';
        }
    }
    
    // Update countdown every second
    const countdownInterval = setInterval(updateCountdown, 1000);
    updateCountdown(); // Initial call
}

// Number counting animation for stats
function initNumberCounting() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-count'));
        const duration = 2000; // 2 seconds
        const increment = target / (duration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                clearInterval(timer);
                stat.textContent = target.toLocaleString();
            } else {
                stat.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    });
}

// Scroll animations
function initLandingHeaderScroll() {
    // Add shadow to header on scroll
    const header = document.querySelector('.landing-header');

    if (header) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
}

// Add to cart animation (for demonstration)
function addToCartAnimation(button) {
    const cart = document.querySelector('.landing-nav .btn-primary');
    const buttonRect = button.getBoundingClientRect();
    const cartRect = cart.getBoundingClientRect();
    
    const animationElement = document.createElement('div');
    animationElement.classList.add('add-to-cart-animation');
    animationElement.innerHTML = '<i class="fas fa-shopping-cart"></i>';
    document.body.appendChild(animationElement);
    
    // Position the animation element at the button
    animationElement.style.position = 'fixed';
    animationElement.style.left = `${buttonRect.left + buttonRect.width / 2}px`;
    animationElement.style.top = `${buttonRect.top}px`;
    animationElement.style.fontSize = '20px';
    animationElement.style.color = '#2d72d9';
    animationElement.style.opacity = '1';
    animationElement.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    
    // Trigger the animation
    setTimeout(() => {
        animationElement.style.left = `${cartRect.left + cartRect.width / 2}px`;
        animationElement.style.top = `${cartRect.top}px`;
        animationElement.style.fontSize = '12px';
        animationElement.style.opacity = '0';
    }, 50);
    
    // Remove the element after animation completes
    setTimeout(() => {
        document.body.removeChild(animationElement);
    }, 1000);
}

// Add event listeners to all "Buy Now" buttons
document.querySelectorAll('.product-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        addToCartAnimation(this);
        
        // Redirect after animation (for demo purposes)
        setTimeout(() => {
            window.location.href = this.href;
        }, 1000);
    });
});