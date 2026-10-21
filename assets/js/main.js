/**
 * PawConnect - Master Client-side JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Auto dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // 2. Favorite button toggle
    const favButtons = document.querySelectorAll('.pet-fav-btn');
    favButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();
            const petId = btn.getAttribute('data-pet-id');
            if (!petId) return;

            try {
                const response = await fetch('/favorites.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `action=toggle&pet_id=${encodeURIComponent(petId)}`
                });

                if (response.status === 401) {
                    window.location.href = '/login.php';
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    const icon = btn.querySelector('i');
                    if (data.is_favorite) {
                        btn.classList.add('active');
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                    } else {
                        btn.classList.remove('active');
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                    }
                }
            } catch (err) {
                console.error('Error toggling favorite:', err);
            }
        });
    });

    // 3. Image URL preview handler
    const imgUrlInput = document.getElementById('image_url');
    const imgPreview = document.getElementById('image_preview');
    if (imgUrlInput && imgPreview) {
        const updatePreview = () => {
            const val = imgUrlInput.value.trim();
            if (val) {
                imgPreview.src = val;
                imgPreview.style.display = 'block';
            }
        };
        imgUrlInput.addEventListener('input', updatePreview);
        imgUrlInput.addEventListener('change', updatePreview);
    }

    // 4. File input preview handler
    const imgFileInput = document.getElementById('image_file');
    if (imgFileInput && imgPreview) {
        imgFileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    imgPreview.src = event.target.result;
                    imgPreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 5. Password visibility toggle
    const togglePassBtns = document.querySelectorAll('.toggle-password');
    togglePassBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                const isPass = targetInput.getAttribute('type') === 'password';
                targetInput.setAttribute('type', isPass ? 'text' : 'password');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                }
            }
        });
    });

    // 6. Confirm prompt for destructive actions
    const confirmActions = document.querySelectorAll('[data-confirm]');
    confirmActions.forEach(elem => {
        elem.addEventListener('click', (e) => {
            const msg = elem.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
