# Multi-Language Implementation Guide untuk SPARK

## Rekomendasi Library untuk PHP Multi-Language

### Opsi 1: **PHP Gettext** (RECOMMENDED - Native PHP)

**Kelebihan:**
- ✅ Built-in di PHP (tidak perlu install library)
- ✅ Industry standard untuk i18n
- ✅ Performa tinggi
- ✅ Support plural forms
- ✅ Tools lengkap (Poedit untuk translate)

**Cara Implementasi:**

#### 1. Setup Struktur Folder
```
spark/
├── locale/
│   ├── id_ID/
│   │   └── LC_MESSAGES/
│   │       ├── messages.po
│   │       └── messages.mo
│   └── en_US/
│       └── LC_MESSAGES/
│           ├── messages.po
│           └── messages.mo
```

#### 2. Install Gettext Extension (jika belum ada)
```bash
# Cek apakah sudah terinstall
php -m | grep gettext

# Jika belum, enable di php.ini
# Uncomment line: extension=gettext
```

#### 3. Create Helper Function
**File: `functions/i18n.php`**
```php
<?php
function initLanguage($lang = 'id') {
    $locale = $lang === 'id' ? 'id_ID' : 'en_US';
    
    putenv("LC_ALL=$locale");
    setlocale(LC_ALL, $locale);
    
    $domain = 'messages';
    bindtextdomain($domain, __DIR__ . '/../locale');
    bind_textdomain_codeset($domain, 'UTF-8');
    textdomain($domain);
}

// Shorthand function
function __($text) {
    return gettext($text);
}

// Plural function
function _n($singular, $plural, $count) {
    return ngettext($singular, $plural, $count);
}
?>
```

#### 4. Update profile.php
```php
<?php
require_once __DIR__ . '/../functions/i18n.php';

// Get user language preference
$userLang = $user['app_language'] ?? 'id';
initLanguage($userLang);
?>

<!-- Usage in HTML -->
<h1><?= __('Settings') ?></h1>
<p><?= __('Manage your account settings') ?></p>
```

#### 5. Create Translation Files

**locale/id_ID/LC_MESSAGES/messages.po:**
```po
msgid "Settings"
msgstr "Pengaturan"

msgid "Manage your account settings"
msgstr "Kelola pengaturan akun Anda"

msgid "Profile"
msgstr "Profil"

msgid "Password"
msgstr "Kata Sandi"
```

**locale/en_US/LC_MESSAGES/messages.po:**
```po
msgid "Settings"
msgstr "Settings"

msgid "Manage your account settings"
msgstr "Manage your account settings"
```

#### 6. Compile .po to .mo
```bash
# Install gettext tools
# Windows: Download from https://mlocati.github.io/articles/gettext-iconv-windows.html

# Compile
msgfmt locale/id_ID/LC_MESSAGES/messages.po -o locale/id_ID/LC_MESSAGES/messages.mo
msgfmt locale/en_US/LC_MESSAGES/messages.po -o locale/en_US/LC_MESSAGES/messages.mo
```

---

### Opsi 2: **Custom PHP Array** (Simple & Fast)

**Kelebihan:**
- ✅ Sangat simple
- ✅ Tidak perlu compile
- ✅ Easy to maintain
- ✅ Cocok untuk project kecil-menengah

**Cara Implementasi:**

#### 1. Create Language Files

**File: `lang/id.php`**
```php
<?php
return [
    // Settings Page
    'settings' => 'Pengaturan',
    'settings_desc' => 'Kelola pengaturan akun Anda',
    
    // Tabs
    'profile' => 'Profil',
    'password' => 'Kata Sandi',
    'notification' => 'Notifikasi',
    'app_settings' => 'Pengaturan Aplikasi',
    
    // Profile Tab
    'update_photo' => 'Perbarui foto dan detail personal Anda',
    'full_name' => 'Nama Lengkap',
    'phone_number' => 'Nomor Telepon',
    'email' => 'Email',
    'save_changes' => 'Simpan Perubahan',
    
    // Password Tab
    'change_password' => 'Ubah Kata Sandi',
    'current_password' => 'Kata Sandi Saat Ini',
    'new_password' => 'Kata Sandi Baru',
    'confirm_password' => 'Konfirmasi Kata Sandi',
    
    // Notifications
    'email_notifications' => 'Notifikasi Email',
    'booking_reminders' => 'Pengingat Booking',
    'profile_updates' => 'Update Profil',
    'password_changes' => 'Perubahan Kata Sandi',
    
    // App Settings
    'language' => 'Bahasa',
    'preferred_language' => 'Bahasa Pilihan',
    'theme' => 'Tema',
    'distance_unit' => 'Satuan Jarak',
    
    // Messages
    'profile_updated' => 'Profil berhasil diperbarui',
    'password_changed' => 'Kata sandi berhasil diubah',
    'settings_saved' => 'Pengaturan tersimpan',
];
?>
```

**File: `lang/en.php`**
```php
<?php
return [
    // Settings Page
    'settings' => 'Settings',
    'settings_desc' => 'Manage your account settings',
    
    // Tabs
    'profile' => 'Profile',
    'password' => 'Password',
    'notification' => 'Notification',
    'app_settings' => 'App Settings',
    
    // Profile Tab
    'update_photo' => 'Update your photo and personal details',
    'full_name' => 'Full Name',
    'phone_number' => 'Phone Number',
    'email' => 'Email',
    'save_changes' => 'Save Changes',
    
    // ... (same keys, English values)
];
?>
```

#### 2. Create Translation Helper

**File: `functions/translate.php`**
```php
<?php
class Translator {
    private static $translations = [];
    private static $currentLang = 'id';
    
    public static function init($lang = 'id') {
        self::$currentLang = $lang;
        $langFile = __DIR__ . "/../lang/{$lang}.php";
        
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        }
    }
    
    public static function get($key, $default = null) {
        return self::$translations[$key] ?? $default ?? $key;
    }
}

// Shorthand function
function t($key, $default = null) {
    return Translator::get($key, $default);
}
?>
```

#### 3. Usage in profile.php

```php
<?php
require_once __DIR__ . '/../functions/translate.php';

// Initialize with user's language
$userLang = $user['app_language'] ?? 'id';
Translator::init($userLang);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= t('settings') ?> - SPARK</title>
</head>
<body>
    <h1><?= t('settings') ?></h1>
    <p><?= t('settings_desc') ?></p>
    
    <!-- Tabs -->
    <div class="settings-tabs">
        <a href="#" data-tab="profile"><?= t('profile') ?></a>
        <a href="#" data-tab="password"><?= t('password') ?></a>
        <a href="#" data-tab="notification"><?= t('notification') ?></a>
        <a href="#" data-tab="app-settings"><?= t('app_settings') ?></a>
    </div>
    
    <!-- Form -->
    <label><?= t('full_name') ?></label>
    <input type="text" name="nama">
    
    <button><?= t('save_changes') ?></button>
</body>
</html>
```

---

### Opsi 3: **i18next.js** (Client-Side JavaScript)

**Kelebihan:**
- ✅ Dynamic language switching tanpa reload
- ✅ Powerful features (interpolation, plurals, etc)
- ✅ Popular & well-maintained

**Implementasi:**
```html
<!-- Include i18next -->
<script src="https://cdn.jsdelivr.net/npm/i18next@latest/i18next.min.js"></script>

<script>
// Initialize
i18next.init({
    lng: 'id', // default language
    resources: {
        id: {
            translation: {
                "settings": "Pengaturan",
                "profile": "Profil",
                "save_changes": "Simpan Perubahan"
            }
        },
        en: {
            translation: {
                "settings": "Settings",
                "profile": "Profile",
                "save_changes": "Save Changes"
            }
        }
    }
});

// Usage
document.getElementById('title').textContent = i18next.t('settings');

// Change language dynamically
function changeLanguage(lang) {
    i18next.changeLanguage(lang, () => {
        updatePageContent();
    });
}
</script>
```

---

## Rekomendasi untuk SPARK

**Gunakan Opsi 2 (Custom PHP Array)** karena:

✅ **Simple & Cepat** - Tidak perlu compile atau setup kompleks
✅ **Easy Maintenance** - Edit langsung file PHP
✅ **Performa Baik** - Array di-cache oleh PHP opcache
✅ **Cocok untuk SPARK** - Project size menengah
✅ **No Dependencies** - Pure PHP

**Struktur yang Direkomendasikan:**
```
spark/
├── lang/
│   ├── id.php (Indonesian)
│   └── en.php (English)
├── functions/
│   └── translate.php (Helper)
└── pages/
    └── profile.php (Use t() function)
```

**Next Steps:**
1. Create `lang/id.php` dan `lang/en.php`
2. Create `functions/translate.php`
3. Update semua pages untuk gunakan `t()` function
4. Test language switching

Mau saya buatkan implementasi lengkapnya?
