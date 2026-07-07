// assets/js/sweetalert-helper.js

// Success Toast Notification
function showSuccessToast(message, title = 'Success!') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: 'success',
        title: title,
        text: message
    });
}

// Error Toast Notification
function showErrorToast(message, title = 'Error!') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: 'error',
        title: title,
        text: message
    });
}

// Warning Toast Notification
function showWarningToast(message, title = 'Warning!') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: 'warning',
        title: title,
        text: message
    });
}

// Info Toast Notification
function showInfoToast(message, title = 'Info!') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: 'info',
        title: title,
        text: message
    });
}

// Confirm Delete Dialog
function confirmDelete(url, title = 'Are you sure?', text = 'This action cannot be undone!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, delete it!',
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        reverseButtons: true,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                window.location.href = url;
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

// Confirm Action Dialog
function confirmAction(url, title = 'Confirm Action', text = 'Are you sure you want to proceed?', confirmText = 'Yes, proceed!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-check"></i> ' + confirmText,
        cancelButtonText: '<i class="fas fa-times"></i> Cancel',
        reverseButtons: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                window.location.href = url;
                resolve();
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    });
}

// Custom Dialog
function showCustomDialog(options) {
    const defaultOptions = {
        title: 'Confirm',
        text: 'Are you sure?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-check"></i> Yes',
        cancelButtonText: '<i class="fas fa-times"></i> No'
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    return Swal.fire(mergedOptions);
}

// Success Dialog with Redirect
function showSuccessRedirect(message, redirectUrl, title = 'Success!', timer = 2000) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        timer: timer,
        timerProgressBar: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="fas fa-arrow-right"></i> Continue'
    }).then((result) => {
        if (result.isConfirmed || result.isDismissed) {
            window.location.href = redirectUrl;
        }
    });
}

// Error Dialog
function showErrorDialog(message, title = 'Error!') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-times"></i> OK'
    });
}

// Form Success Dialog
function showFormSuccess(message, redirectUrl = null, title = 'Success!') {
    Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonColor: '#28a745',
        confirmButtonText: '<i class="fas fa-check"></i> OK',
        timer: 3000,
        timerProgressBar: true
    }).then(() => {
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    });
}

// Form Error Dialog
function showFormError(message, title = 'Error!') {
    Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonColor: '#d33',
        confirmButtonText: '<i class="fas fa-times"></i> OK'
    });
}

// Loading Dialog
function showLoading(message = 'Please wait...') {
    Swal.fire({
        title: 'Loading...',
        text: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Close Loading Dialog
function closeLoading() {
    Swal.close();
}

// Warning Dialog
function showWarningDialog(message, title = 'Warning!') {
    Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonColor: '#f39c12',
        confirmButtonText: '<i class="fas fa-check"></i> OK'
    });
}

// Info Dialog
function showInfoDialog(message, title = 'Information') {
    Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonColor: '#3498db',
        confirmButtonText: '<i class="fas fa-check"></i> OK'
    });
}

// Auto-close Toast
function showAutoToast(message, icon = 'success', title = '', timer = 3000) {
    Swal.fire({
        icon: icon,
        title: title,
        text: message,
        timer: timer,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

// Export all functions
window.showSuccessToast = showSuccessToast;
window.showErrorToast = showErrorToast;
window.showWarningToast = showWarningToast;
window.showInfoToast = showInfoToast;
window.confirmDelete = confirmDelete;
window.confirmAction = confirmAction;
window.showCustomDialog = showCustomDialog;
window.showSuccessRedirect = showSuccessRedirect;
window.showErrorDialog = showErrorDialog;
window.showFormSuccess = showFormSuccess;
window.showFormError = showFormError;
window.showLoading = showLoading;
window.closeLoading = closeLoading;
window.showWarningDialog = showWarningDialog;
window.showInfoDialog = showInfoDialog;
window.showAutoToast = showAutoToast;