// assets/js/confirm.js

function confirmDelete(url, message) {
    if (confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.')) {
        window.location.href = url;
    }
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Add global confirm delete function
window.confirmDelete = confirmDelete;