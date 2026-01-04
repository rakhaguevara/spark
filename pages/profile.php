<?php
// pages/profile.php
// Settings → Profile page

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../functions/auth.php';

startSession();

// Check authentication
if (!isLoggedIn()) {
    header('Location: ' . BASEURL . '/pages/login.php');
    exit;
}

// Get current user data
$user = getCurrentUser();
$pdo = getDBConnection();

// Initialize translation system
require_once __DIR__ . '/../functions/translate.php';
$userLang = isset($user['app_language']) ? $user['app_language'] : 'id';
Translator::init($userLang);

// Helper function to get first 2 words of name
function getShortName($fullName) {
    $words = explode(' ', trim($fullName));
    $shortName = implode(' ', array_slice($words, 0, 2));
    return $shortName;
}

// Get success/error messages
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Settings - Profile | SPARK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="<?= BASEURL ?>/assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/dashboard-user.css">
    <link rel="stylesheet" href="<?= BASEURL ?>/assets/css/profile.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="dashboard-navbar">
        <a href="<?= BASEURL ?>/pages/dashboard.php" class="brand-wrapper">
            <img src="<?= BASEURL ?>/assets/img/logo.png" alt="Spark Logo">
            SPARK
        </a>

        <div class="search-bar">
            <input type="text" placeholder="Search settings...">
        </div>

        <div class="user-actions">
            <button class="icon-btn" title="Notifications"><i class="fas fa-bell"></i></button>
            <div class="profile-chip">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?= BASEURL ?>/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?= strtoupper(substr($user['nama_pengguna'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <span><?= htmlspecialchars(getShortName($user['nama_pengguna'] ?? 'User')) ?></span>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTAINER -->
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <aside class="dashboard-sidebar" id="sidebar">
            <!-- Toggle Button -->
            <button class="sidebar-toggle-edge" id="sidebarToggle" aria-label="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>

            <!-- Main Navigation -->
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li>
                        <a href="<?= BASEURL ?>/pages/dashboard.php" data-tooltip="Find Parking">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Find Parking</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/my-ticket.php" data-tooltip="My Ticket">
                            <i class="fas fa-ticket-alt"></i>
                            <span>My Ticket</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/history.php" data-tooltip="History">
                            <i class="fas fa-history"></i>
                            <span>History</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASEURL ?>/pages/wallet.php" data-tooltip="Wallet">
                            <i class="fas fa-wallet"></i>
                            <span>Wallet</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Bottom Section -->
            <div class="sidebar-bottom">
                <a href="<?= BASEURL ?>/pages/profile.php" class="active" data-tooltip="Settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="<?= BASEURL ?>/logout.php" class="logout-link" data-tooltip="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="dashboard-main">
            <div class="settings-container">
                
                <!-- Page Header -->
                <div class="settings-header">
                    <h1><?= t('settings') ?></h1>
                </div>

                <!-- Tab Navigation -->
                <div class="settings-tabs">
                    <a href="#" class="tab-item" data-tab="profile"><?= t('profile') ?></a>
                    <a href="#" class="tab-item" data-tab="password"><?= t('password') ?></a>
                    <a href="#" class="tab-item" data-tab="notification"><?= t('notification') ?></a>
                    <a href="#" class="tab-item" data-tab="app-settings"><?= t('app_settings') ?></a>
                </div>

                <!-- Scrollable Content Wrapper -->
                <div class="settings-content-wrapper">
                
                <!-- Alert Messages -->
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($successMessage) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($errorMessage) ?></span>
                    </div>
                <?php endif; ?>

                <!-- PROFILE TAB -->
                <div id="profile-tab" class="tab-content">
                <!-- Profile Card -->
                <div class="settings-card">
                    <div class="settings-section-header">
                        <h2>Profile</h2>
                        <p>Update your photo and personal details here.</p>
                    </div>

                    <!-- Profile Picture Section -->
                    <form action="<?= BASEURL ?>/actions/profile-handler.php" method="POST" enctype="multipart/form-data" id="imageForm">
                        <input type="hidden" name="action" value="upload_image">
                        <div class="profile-picture-section">
                            <div class="profile-avatar-large" id="avatarPreview">
                                <?php if (!empty($user['profile_image'])): ?>
                                    <img src="<?= BASEURL ?>/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Picture">
                                <?php else: ?>
                                    <?= strtoupper(substr($user['nama_pengguna'] ?? 'U', 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="profile-picture-info">
                                <div class="profile-picture-actions">
                                    <button type="button" class="btn-upload" onclick="document.getElementById('profileImageInput').click()">
                                        <i class="fas fa-upload"></i>
                                        Upload image
                                    </button>
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <button type="button" class="btn-remove" onclick="removeProfileImage()">
                                            <i class="fas fa-trash"></i>
                                            Remove
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <p class="profile-picture-hint">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <input type="file" id="profileImageInput" name="profile_image" accept="image/png,image/jpeg,image/jpg,image/gif" onchange="handleImageUpload(this)">
                    </form>

                    <!-- Profile Form -->
                    <form action="<?= BASEURL ?>/actions/profile-handler.php" method="POST" id="profileForm">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-grid">
                            <!-- Full Name -->
                            <div class="form-group">
                                <label for="nama_pengguna"><?= t('full_name') ?></label>
                                <input 
                                    type="text" 
                                    id="nama_pengguna" 
                                    name="nama_pengguna" 
                                    value="<?= htmlspecialchars($user['nama_pengguna'] ?? '') ?>"
                                    required
                                >
                                <span class="form-error">Full name is required</span>
                            </div>

                            <!-- Username / Role (Read-only) -->
                            <div class="form-group">
                                <label for="role">Username / Role</label>
                                <input 
                                    type="text" 
                                    id="role" 
                                    value="<?= htmlspecialchars($user['nama_role'] ?? 'User') ?>"
                                    readonly
                                    disabled
                                >
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label for="noHp_pengguna"><?= t('phone_number') ?></label>
                                <input 
                                    type="tel" 
                                    id="noHp_pengguna" 
                                    name="noHp_pengguna" 
                                    value="<?= htmlspecialchars($user['noHp_pengguna'] ?? '') ?>"
                                    required
                                >
                                <span class="form-error">Phone number is required</span>
                            </div>

                            <!-- Email (Read-only) -->
                            <div class="form-group">
                                <label for="email_pengguna"><?= t('email') ?></label>
                                <input 
                                    type="email" 
                                    id="email_pengguna" 
                                    value="<?= htmlspecialchars($user['email_pengguna'] ?? '') ?>"
                                    readonly
                                    disabled
                                >
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <button type="button" class="btn-secondary" onclick="resetForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                </div><!-- End Profile Tab -->

                <!-- PASSWORD TAB -->
                <div id="password-tab" class="tab-content">
                    <div class="settings-card">
                        <div class="settings-section-header">
                            <h2><?= t('change_password') ?></h2>
                            <p><?= t('change_password_desc') ?></p>
                        </div>

                        <form id="passwordForm" action="<?= BASEURL ?>/actions/password-handler.php" method="POST">
                            <div class="form-group">
                                <label for="current_password"><?= t('current_password') ?></label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>

                            <div class="form-group">
                                <label for="new_password"><?= t('new_password') ?></label>
                                <input type="password" id="new_password" name="new_password" required>
                                <div id="passwordStrength" class="password-strength"></div>
                                <small style="color: #888; font-size: 13px;"><?= t('password_hint') ?></small>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password"><?= t('confirm_password') ?></label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn-danger">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div><!-- End Password Tab -->

                <!-- NOTIFICATION TAB -->
                <div id="notification-tab" class="tab-content">
                    <div class="settings-card">
                        <div class="settings-section-header">
                            <h2>Notification Preferences</h2>
                            <p>Manage how you receive notifications from SPARK.</p>
                        </div>

                        <?php
                        // Get notification preferences (safe fallback if column doesn't exist)
                        $notifPrefs = [];
                        try {
                            if (isset($user['notification_preferences'])) {
                                $notifPrefs = json_decode($user['notification_preferences'] ?? '{}', true) ?? [];
                            }
                        } catch (Exception $e) {
                            // Column doesn't exist yet, use defaults
                            $notifPrefs = [];
                        }
                        $emailNotif = $notifPrefs['email_notifications'] ?? true;
                        $bookingReminders = $notifPrefs['booking_reminders'] ?? true;
                        $profileUpdates = $notifPrefs['profile_updates'] ?? true;
                        $passwordChanges = true; // Always true, cannot be disabled
                        ?>

                        <div class="settings-section">
                            <h3>Email Notifications</h3>
                            
                            <div class="toggle-group">
                                <span class="toggle-label">Email notifications</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-preference="email_notifications" <?= $emailNotif ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="toggle-group">
                                <span class="toggle-label">Booking reminders</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-preference="booking_reminders" <?= $bookingReminders ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="toggle-group">
                                <span class="toggle-label">Profile update alerts</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-preference="profile_updates" <?= $profileUpdates ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="toggle-group">
                                <span class="toggle-label">Password change alerts <small style="color: #888;">(Required for security)</small></span>
                                <label class="toggle-switch">
                                    <input type="checkbox" class="notification-toggle" data-preference="password_changes" checked disabled>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div><!-- End Notification Tab -->

                <!-- APP SETTINGS TAB -->
                <div id="app-settings-tab" class="tab-content">
                    <div class="settings-card">
                        <div class="settings-section-header">
                            <h2><?= t('app_settings_title') ?></h2>
                            <p><?= t('app_settings_desc') ?></p>
                        </div>

                        <?php
                        // Get app settings (safe fallback if columns don't exist)
                        $appLanguage = isset($user['app_language']) ? $user['app_language'] : 'id';
                        $appTheme = isset($user['app_theme']) ? $user['app_theme'] : 'auto';
                        $appDistanceUnit = isset($user['app_distance_unit']) ? $user['app_distance_unit'] : 'km';
                        $appAutoLocation = isset($user['app_auto_location']) ? $user['app_auto_location'] : 1;
                        $appManualLocation = isset($user['app_manual_location']) ? $user['app_manual_location'] : '';
                        ?>

                        <!-- Language -->
                        <div class="settings-section">
                            <h3><?= t('language') ?></h3>
                            <div class="form-group">
                                <label for="app_language"><?= t('preferred_language') ?></label>
                                <select id="app_language" class="form-select app-setting-select" data-previous-lang="<?= $appLanguage ?>">
                                    <option value="id" <?= $appLanguage === 'id' ? 'selected' : '' ?>><?= t('indonesian') ?></option>
                                    <option value="en" <?= $appLanguage === 'en' ? 'selected' : '' ?>><?= t('english') ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Location & Map -->
                        <div class="settings-section">
                            <h3>Location & Map</h3>
                            <div class="toggle-group">
                                <span class="toggle-label">Auto-detect location (GPS)</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="auto_location" <?= $appAutoLocation ? 'checked' : '' ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="form-group" id="manualLocationGroup" style="<?= $appAutoLocation ? 'display: none;' : '' ?>">
                                <label for="manual_location">Manual Location</label>
                                <input type="text" id="manual_location" placeholder="e.g., Jakarta, Indonesia" value="<?= htmlspecialchars($appManualLocation) ?>">
                                <small style="color: #888; font-size: 13px;">Enter your city or region</small>
                            </div>
                        </div>

                        <!-- Application -->
                        <div class="settings-section">
                            <h3>Application</h3>
                            <div class="form-group">
                                <label for="app_theme">Theme</label>
                                <select id="app_theme" class="form-select app-setting-select">
                                    <option value="auto" <?= $appTheme === 'auto' ? 'selected' : '' ?>>Auto (System)</option>
                                    <option value="light" <?= $appTheme === 'light' ? 'selected' : '' ?>>Light</option>
                                    <option value="dark" <?= $appTheme === 'dark' ? 'selected' : '' ?>>Dark</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="app_distance_unit">Distance Unit</label>
                                <select id="app_distance_unit" class="form-select app-setting-select">
                                    <option value="km" <?= $appDistanceUnit === 'km' ? 'selected' : '' ?>>Kilometers (km)</option>
                                    <option value="miles" <?= $appDistanceUnit === 'miles' ? 'selected' : '' ?>>Miles</option>
                                </select>
                            </div>
                        </div>

                        <!-- Support & Legal -->
                        <div class="support-section">
                            <h3>Support & Legal</h3>
                            <div class="support-links">
                                <a href="javascript:void(0)" class="support-link" onclick="openSupportModal('faq')">
                                    <i class="fas fa-question-circle"></i>
                                    FAQ
                                </a>
                                <a href="javascript:void(0)" class="support-link" onclick="openSupportModal('privacy')">
                                    <i class="fas fa-shield-alt"></i>
                                    Privacy Policy
                                </a>
                                <a href="javascript:void(0)" class="support-link" onclick="openSupportModal('terms')">
                                    <i class="fas fa-file-contract"></i>
                                    Terms of Service
                                </a>
                                <a href="javascript:void(0)" class="support-link" onclick="openSupportModal('about')">
                                    <i class="fas fa-info-circle"></i>
                                    About SPARK
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!-- End App Settings Tab -->

                </div><!-- End settings-content-wrapper -->

            </div><!-- End settings-container -->
        </main>
    </div>

    <!-- Remove Image Form (Hidden) -->
    <form id="removeImageForm" action="<?= BASEURL ?>/actions/profile-handler.php" method="POST" style="display: none;">
        <input type="hidden" name="action" value="remove_image">
    </form>

    <script src="<?= BASEURL ?>/assets/js/sidebar-toggle.js"></script>
    <script>
        // Handle image upload preview
        function handleImageUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size exceeds 10MB limit');
                    input.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only PNG, JPG, and GIF are allowed');
                    input.value = '';
                    return;
                }

                // Submit form automatically
                document.getElementById('imageForm').submit();
            }
        }

        // Remove profile image
        function removeProfileImage() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                document.getElementById('removeImageForm').submit();
            }
        }

        // Reset form to original values
        function resetForm() {
            document.getElementById('profileForm').reset();
        }

        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            let isValid = true;
            const nama = document.getElementById('nama_pengguna');
            const noHp = document.getElementById('noHp_pengguna');

            // Clear previous errors
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('has-error');
            });

            // Validate full name
            if (!nama.value.trim()) {
                nama.closest('.form-group').classList.add('has-error');
                isValid = false;
            }

            // Validate phone number
            if (!noHp.value.trim()) {
                noHp.closest('.form-group').classList.add('has-error');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="confirmationTitle">Confirm Action</h3>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeConfirmationModal()">Cancel</button>
                <button type="button" class="btn-primary" id="confirmButton" onclick="confirmAction()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Support Modal -->
    <div id="supportModal" class="support-modal">
        <div class="modal-dialog">
            <button class="modal-close" onclick="closeSupportModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-header">
                <h3 id="supportModalTitle">Support</h3>
            </div>
            <div class="modal-content" id="supportModalContent">
                <!-- Content will be dynamically loaded -->
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <!-- Settings JavaScript -->
    <script>
        // Define BASEURL for JavaScript
        const BASEURL = '<?= BASEURL ?>';
    </script>
    <script src="<?= BASEURL ?>/assets/js/settings.js"></script>
</body>
</html>
