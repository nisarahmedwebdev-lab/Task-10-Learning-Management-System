// assets/js/global-sweetalert.js

// Initialize SweetAlert defaults
document.addEventListener('DOMContentLoaded', function() {
    // Set default SweetAlert options
    Swal.defaults({
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-check"></i> OK',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel'
    });
});

// Global function to show flash messages from PHP
function showFlashMessage(type, message) {
    const icons = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    
    const titles = {
        'success': 'Success!',
        'error': 'Error!',
        'warning': 'Warning!',
        'info': 'Information'
    };
    
    Swal.fire({
        icon: icons[type] || 'info',
        title: titles[type] || 'Information',
        text: message,
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: true
    });
}

// Function to handle form submission with loading
function submitFormWithLoading(formId, submitButtonId) {
    const form = document.getElementById(formId);
    const button = document.getElementById(submitButtonId);
    
    if (form && button) {
        form.addEventListener('submit', function(e) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    }
}

// Export functions
window.showFlashMessage = showFlashMessage;
window.submitFormWithLoading = submitFormWithLoading;