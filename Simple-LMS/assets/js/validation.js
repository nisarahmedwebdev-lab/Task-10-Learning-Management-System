// assets/js/validation.js

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[0-9]/.test(password) &&
        /[!@#$%^&*(),.?":{}|<>]/.test(password);
}

function validateImage(file) {
    if (!file) return { valid: true };

    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    const maxSize = 2 * 1024 * 1024; // 2MB

    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Only JPG, JPEG, and PNG images are allowed.' };
    }

    if (file.size > maxSize) {
        return { valid: false, message: 'Image size must be 2MB or less.' };
    }

    return { valid: true };
}

function showError(elementId, message) {
    const element = document.getElementById(elementId);
    if (element) {
        // Remove existing error
        const existingError = element.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }

        // Add new error
        const errorSpan = document.createElement('span');
        errorSpan.className = 'error-message';
        errorSpan.textContent = message;
        element.parentElement.appendChild(errorSpan);
    }
}

function clearErrors(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.querySelectorAll('.error-message').forEach(el => el.remove());
    }
}

function validateForm(formId, rules) {
    clearErrors(formId);
    let isValid = true;

    for (const [fieldId, rule] of Object.entries(rules)) {
        const element = document.getElementById(fieldId);
        if (!element) continue;

        const value = element.value.trim();

        if (rule.required && !value) {
            showError(fieldId, rule.requiredMessage || 'This field is required.');
            isValid = false;
            continue;
        }

        if (value) {
            if (rule.type === 'email' && !validateEmail(value)) {
                showError(fieldId, 'Please enter a valid email address.');
                isValid = false;
            }

            if (rule.type === 'password' && !validatePassword(value)) {
                showError(fieldId, 'Password must be at least 8 characters with 1 uppercase, 1 number, and 1 special character.');
                isValid = false;
            }

            if (rule.type === 'number' && isNaN(value)) {
                showError(fieldId, 'Please enter a valid number.');
                isValid = false;
            }

            if (rule.type === 'url' && value && !value.match(/^https?:\/\/.+/)) {
                showError(fieldId, 'Please enter a valid URL.');
                isValid = false;
            }

            if (rule.min && value.length < rule.min) {
                showError(fieldId, `Must be at least ${rule.min} characters.`);
                isValid = false;
            }
        }
    }

    return isValid;
}