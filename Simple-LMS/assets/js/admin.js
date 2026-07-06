// assets/js/admin.js

document.addEventListener('DOMContentLoaded', function () {
    // Add confirmation for delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Handle course form validation
    const courseForm = document.getElementById('courseForm');
    if (courseForm) {
        courseForm.addEventListener('submit', function (e) {
            let isValid = true;

            const title = document.getElementById('title');
            if (!title.value.trim()) {
                showError('title', 'Course title is required.');
                isValid = false;
            }

            const description = document.getElementById('description');
            if (!description.value.trim()) {
                showError('description', 'Course description is required.');
                isValid = false;
            }

            // Image validation
            const image = document.getElementById('image');
            if (image && image.files.length > 0) {
                const file = image.files[0];
                const result = validateImage(file);
                if (!result.valid) {
                    showError('image', result.message);
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Handle lesson form validation
    const lessonForm = document.getElementById('lessonForm');
    if (lessonForm) {
        lessonForm.addEventListener('submit', function (e) {
            let isValid = true;

            const title = document.getElementById('title');
            if (!title.value.trim()) {
                showError('title', 'Lesson title is required.');
                isValid = false;
            }

            const content = document.getElementById('content');
            if (!content.value.trim()) {
                showError('content', 'Lesson content is required.');
                isValid = false;
            }

            const order = document.getElementById('order_number');
            if (!order.value || isNaN(order.value) || parseInt(order.value) <= 0) {
                showError('order_number', 'Order number must be a positive number.');
                isValid = false;
            }

            const videoUrl = document.getElementById('video_url');
            if (videoUrl.value && !videoUrl.value.match(/^https?:\/\/.+/)) {
                showError('video_url', 'Please enter a valid URL.');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});