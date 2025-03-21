// Slideshow variables
let slideIndex = 1;
let slideInterval;

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // ------ NAVBAR FUNCTIONALITY ------
    initializeNavbar();
    
    // ------ SLIDESHOW FUNCTIONALITY ------
    initializeSlideshow();

    // Initialize reminders functionality
    initializeReminders();
});

// Initialize all navbar functionality
function initializeNavbar() {
    // Mobile menu toggle functionality
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navItems = document.querySelector('.nav-items');
    
    if (mobileMenuToggle && navItems) {
        mobileMenuToggle.addEventListener('click', function() {
            navItems.classList.toggle('active');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInsideNav = navItems.contains(event.target);
            const isClickOnToggle = mobileMenuToggle.contains(event.target);
            
            if (navItems.classList.contains('active') && !isClickInsideNav && !isClickOnToggle) {
                navItems.classList.remove('active');
            }
        });
    }
    
    // Active navigation link handler
    const navLinks = document.querySelectorAll('.nav-items a');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            navLinks.forEach(item => {
                item.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Close mobile menu after link click
            if (window.innerWidth <= 768) {
                navItems.classList.remove('active');
            }
        });
    });
    
    // Search input functionality
    const searchInput = document.querySelector('.search-bar input');
    
    if (searchInput) {
        searchInput.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        searchInput.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    }
}

// Initialize all slideshow functionality
function initializeSlideshow() {
    // Initialize slideshow
    showSlides(slideIndex);
    
    // Auto slide every 5 seconds
    startSlideInterval();
    
    // Pause auto-sliding when mouse enters the slideshow
    const slideshow = document.querySelector('.slideshow-container');
    if (slideshow) {
        slideshow.addEventListener('mouseenter', function() {
            clearInterval(slideInterval);
        });
        
        slideshow.addEventListener('mouseleave', function() {
            startSlideInterval();
        });
        
        // Handle slide swipe gestures for mobile devices
        let touchStartX = 0;
        let touchEndX = 0;
        
        slideshow.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        slideshow.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
        
        function handleSwipe() {
            // Detect left or right swipe
            if (touchEndX < touchStartX - 50) {
                // Swipe left - show next slide
                changeSlide(1);
            } else if (touchEndX > touchStartX + 50) {
                // Swipe right - show previous slide
                changeSlide(-1);
            }
        }
    }
}

// Start automatic slide interval
function startSlideInterval() {
    clearInterval(slideInterval);
    slideInterval = setInterval(function() {
        changeSlide(1);
    }, 5000);
}

// Change slide when clicking on arrows
function changeSlide(n) {
    showSlides(slideIndex += n);
}

// Change slide when clicking on dots
function currentSlide(n) {
    showSlides(slideIndex = n);
}

// Show the current slide
function showSlides(n) {
    let i;
    let slides = document.getElementsByClassName("slide");
    let dots = document.getElementsByClassName("dot");
    
    // Check if elements exist
    if (!slides.length || !dots.length) return;
    
    // Reset to first slide if we go past the end
    if (n > slides.length) {
        slideIndex = 1;
    }
    
    // Go to last slide if we go before the first
    if (n < 1) {
        slideIndex = slides.length;
    }
    
    // Hide all slides
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
        slides[i].classList.remove("active");
    }
    
    // Remove active class from all dots
    for (i = 0; i < dots.length; i++) {
        dots[i].classList.remove("active");
    }
    
    // Show the current slide and activate the current dot
    slides[slideIndex - 1].style.display = "block";
    slides[slideIndex - 1].classList.add("active");
    dots[slideIndex - 1].classList.add("active");
    
    // Reset the auto-slide timer when manually changing slides
    startSlideInterval();
}

// Initialize reminder buttons
function initializeReminders() {
    const reminderButtons = document.querySelectorAll('.btn-remind');
    
    if (reminderButtons) {
        reminderButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const icon = this.querySelector('i');
                const matchId = this.getAttribute('data-match-id');
                
                // Toggle icon between filled and outline bell
                if (icon.classList.contains('far')) {
                    // Set reminder
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    showNotification('Reminder set! We\'ll notify you before the match starts.');
                } else {
                    // Remove reminder
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    showNotification('Reminder removed.');
                }
                
                console.log(`Toggled reminder for match ID: ${matchId}`);
            });
        });
    }
}

function showNotification(message) {
    // Create notification element if it doesn't exist
    let notification = document.querySelector('.notification-popup');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification-popup';
        document.body.appendChild(notification);
    }
    
    // Set message and show notification
    notification.textContent = message;
    notification.classList.add('show');
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}