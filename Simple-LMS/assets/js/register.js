// assets/js/register.js

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        let isValid = true;

        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(el => el.remove());

        // Full Name validation
        const fullName = document.getElementById('full_name');
        if (!fullName.value.trim()) {
            showError('full_name', 'Full name is required.');
            isValid = false;
        }

        // Email validation
        const email = document.getElementById('email');
        if (!email.value.trim()) {
            showError('email', 'Email is required.');
            isValid = false;
        } else if (!validateEmail(email.value.trim())) {
            showError('email', 'Please enter a valid email address.');
            isValid = false;
        }

        // Password validation
        const password = document.getElementById('password');
        if (!password.value) {
            showError('password', 'Password is required.');
            isValid = false;
        } else if (!validatePassword(password.value)) {
            showError('password', 'Password must be at least 8 characters with 1 uppercase, 1 number, and 1 special character.');
            isValid = false;
        }

        // Confirm password validation
        const confirmPassword = document.getElementById('confirm_password');
        if (password.value && password.value !== confirmPassword.value) {
            showError('confirm_password', 'Passwords do not match.');
            isValid = false;
        }

        // Image validation
        const imageInput = document.getElementById('profile_image');
        if (imageInput && imageInput.files.length > 0) {
            const file = imageInput.files[0];
            const result = validateImage(file);
            if (!result.valid) {
                showError('profile_image', result.message);
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
});