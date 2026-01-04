/**
 * SETTINGS MODULE JAVASCRIPT
 * Handles tab switching, confirmation modals, AJAX submissions, and toast notifications
 */

// ============================================
// TAB SWITCHING
// ============================================
function switchTab(tabName) {
    // Update URL parameter
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);

    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.classList.remove('active');
    });

    // Show selected tab content
    const selectedContent = document.getElementById(`${tabName}-tab`);
    if (selectedContent) {
        selectedContent.classList.add('active');
    }

    // Add active class to selected tab button
    const selectedTab = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
}

// Initialize tabs on page load
document.addEventListener('DOMContentLoaded', function () {
    // Get tab from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'profile';

    // Switch to active tab
    switchTab(activeTab);

    // Add click handlers to tab buttons
    document.querySelectorAll('.tab-item:not(.disabled)').forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
});

// ============================================
// CONFIRMATION MODAL
// ============================================
let confirmationCallback = null;

function showConfirmationModal(title, message, confirmText, callback) {
    const modal = document.getElementById('confirmationModal');
    const modalTitle = document.getElementById('confirmationTitle');
    const modalMessage = document.getElementById('confirmationMessage');
    const confirmBtn = document.getElementById('confirmButton');

    modalTitle.textContent = title;
    modalMessage.textContent = message;
    confirmBtn.textContent = confirmText;

    confirmationCallback = callback;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    confirmationCallback = null;
}

function confirmAction() {
    if (confirmationCallback) {
        confirmationCallback();
    }
    closeConfirmationModal();
}

// Close modal on backdrop click
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('confirmation-modal')) {
        closeConfirmationModal();
    }
});

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================
// PROFILE UPDATE WITH CONFIRMATION
// ============================================
document.getElementById('profileForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    showConfirmationModal(
        'Save profile changes?',
        'Your profile information will be updated.',
        'Yes, Save Changes',
        () => submitProfileForm()
    );
});

function submitProfileForm() {
    const form = document.getElementById('profileForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Profile updated successfully', 'success');
                // Reload page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
}

// ============================================
// PASSWORD CHANGE WITH CONFIRMATION
// ============================================
document.getElementById('passwordForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    // Client-side validation
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword.length < 8) {
        showToast('Password must be at least 8 characters', 'error');
        return;
    }

    if (!/[0-9]/.test(newPassword)) {
        showToast('Password must contain at least 1 number', 'error');
        return;
    }

    if (!/[a-zA-Z]/.test(newPassword)) {
        showToast('Password must contain at least 1 letter', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }

    showConfirmationModal(
        'Change Password?',
        'Are you sure you want to update your password? You will be logged out and need to sign in again.',
        'Yes, Change Password',
        () => submitPasswordForm()
    );
});

function submitPasswordForm() {
    const form = document.getElementById('passwordForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Redirect to login after 2 seconds
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
}

// Password strength indicator
document.getElementById('new_password')?.addEventListener('input', function (e) {
    const password = e.target.value;
    const indicator = document.getElementById('passwordStrength');

    if (!indicator) return;

    let strength = 0;
    let text = '';
    let className = '';

    if (password.length >= 8) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    if (password.length === 0) {
        indicator.style.display = 'none';
        return;
    }

    indicator.style.display = 'block';

    if (strength <= 2) {
        text = 'Weak';
        className = 'weak';
    } else if (strength <= 3) {
        text = 'Medium';
        className = 'medium';
    } else {
        text = 'Strong';
        className = 'strong';
    }

    indicator.textContent = `Password strength: ${text}`;
    indicator.className = `password-strength ${className}`;
});

// ============================================
// NOTIFICATION PREFERENCES
// ============================================
document.querySelectorAll('.notification-toggle').forEach(toggle => {
    toggle.addEventListener('change', function () {
        const preference = this.getAttribute('data-preference');
        const value = this.checked;

        // Save immediately
        saveNotificationPreference(preference, value);
    });
});

function saveNotificationPreference(preference, value) {
    const formData = new FormData();
    formData.append(preference, value);

    fetch(BASEURL + '/actions/notification-handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Preference updated', 'success');
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
}

// ============================================
// APP SETTINGS
// ============================================
document.querySelectorAll('.app-setting-select').forEach(select => {
    select.addEventListener('change', function () {
        saveAppSettings();
    });
});

document.getElementById('auto_location')?.addEventListener('change', function () {
    const manualLocationGroup = document.getElementById('manualLocationGroup');
    if (this.checked) {
        manualLocationGroup.style.display = 'none';
    } else {
        manualLocationGroup.style.display = 'block';
    }
    saveAppSettings();
});

function saveAppSettings() {
    const formData = new FormData();
    const currentLang = document.getElementById('app_language')?.value || 'id';
    const previousLang = document.getElementById('app_language')?.dataset.previousLang || currentLang;

    formData.append('language', currentLang);
    formData.append('theme', document.getElementById('app_theme')?.value || 'auto');
    formData.append('distance_unit', document.getElementById('app_distance_unit')?.value || 'km');
    formData.append('auto_location', document.getElementById('auto_location')?.checked || false);
    formData.append('manual_location', document.getElementById('manual_location')?.value || '');

    fetch(BASEURL + '/actions/app-settings-handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Settings updated', 'success');

                // If language changed, reload page to apply translations
                if (currentLang !== previousLang) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
}

// ============================================
// SUPPORT & LEGAL MODALS
// ============================================
function openSupportModal(type) {
    const modal = document.getElementById('supportModal');
    const title = document.getElementById('supportModalTitle');
    const content = document.getElementById('supportModalContent');

    const contentMap = {
        'faq': {
            title: 'Frequently Asked Questions',
            content: getFAQContent()
        },
        'privacy': {
            title: 'Privacy Policy',
            content: getPrivacyContent()
        },
        'terms': {
            title: 'Terms of Service',
            content: getTermsContent()
        },
        'about': {
            title: 'About SPARK',
            content: getAboutContent()
        }
    };

    const data = contentMap[type];
    if (data) {
        title.textContent = data.title;
        content.innerHTML = data.content;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeSupportModal() {
    const modal = document.getElementById('supportModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Content functions (placeholder - can be expanded)
function getFAQContent() {
    return `
        <div class="faq-section">
            <h3>How do I book a parking spot?</h3>
            <p>Navigate to Find Parking, select your desired location, choose a time slot, and complete the booking.</p>
            
            <h3>How do I cancel a booking?</h3>
            <p>Go to My Ticket, find your active booking, and click the cancel button.</p>
            
            <h3>What payment methods are accepted?</h3>
            <p>We accept credit cards, debit cards, e-wallets, and bank transfers.</p>
        </div>
    `;
}

function getPrivacyContent() {
    return `
        <div class="legal-content">
            <p><strong>Last updated:</strong> January 2026</p>
            <h3>Information We Collect</h3>
            <p>We collect information you provide directly to us, including name, email, phone number, and payment information.</p>
            
            <h3>How We Use Your Information</h3>
            <p>We use your information to provide, maintain, and improve our services, process transactions, and communicate with you.</p>
            
            <h3>Data Security</h3>
            <p>We implement appropriate security measures to protect your personal information.</p>
        </div>
    `;
}

function getTermsContent() {
    return `
        <div class="legal-content">
            <p><strong>Last updated:</strong> January 2026</p>
            <h3>Acceptance of Terms</h3>
            <p>By using SPARK, you agree to these terms and conditions.</p>
            
            <h3>User Responsibilities</h3>
            <p>You are responsible for maintaining the confidentiality of your account and password.</p>
            
            <h3>Booking and Cancellation</h3>
            <p>Bookings are subject to availability. Cancellation policies apply as stated at the time of booking.</p>
        </div>
    `;
}

function getAboutContent() {
    return `
        <div class="about-content">
            <h3>SPARK Parking Management System</h3>
            <p>SPARK is a modern parking management platform designed to make finding and booking parking spots easy and convenient.</p>
            
            <p><strong>Version:</strong> 1.0.0</p>
            <p><strong>Developed by:</strong> SPARK Team</p>
            <p><strong>Contact:</strong> support@spark-parking.com</p>
        </div>
    `;
}
