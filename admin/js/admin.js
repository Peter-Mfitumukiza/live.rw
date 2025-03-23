// Admin JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown toggle
    const profileDropdown = document.getElementById('profileDropdown');
    const profileMenu = document.getElementById('profileMenu');
    
    if (profileDropdown && profileMenu) {
        profileDropdown.addEventListener('click', function() {
            profileMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!profileDropdown.contains(event.target) && !profileMenu.contains(event.target)) {
                profileMenu.classList.remove('show');
            }
        });
    }
    
    // Image preview functionality
    const imageInputs = document.querySelectorAll('.image-input');
    
    imageInputs.forEach(input => {
        const previewContainer = document.getElementById(input.dataset.preview);
        
        if (previewContainer) {
            input.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        // Clear previous preview
                        previewContainer.innerHTML = '';
                        
                        // Create image element
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        
                        // Add image to preview container
                        previewContainer.appendChild(img);
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    });
    
    // Confirm delete
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Date-time picker initialization
    const datetimeInputs = document.querySelectorAll('.datetime-input');
    
    if (typeof flatpickr !== 'undefined') {
        datetimeInputs.forEach(input => {
            flatpickr(input, {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true
            });
        });
    }
});