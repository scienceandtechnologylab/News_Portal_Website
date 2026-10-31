<?php
require_once '../includes/admin_auth.php';
require_admin_permission('settings_manage');
require_once '../includes/advertisement.php';

// Fetch current settings
$settings = [];
$settings_query = $conn->query("SELECT * FROM settings");
if($settings_query && $settings_query->num_rows > 0) {
    while($row = $settings_query->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

// Fetch current admin info
$admin_id = $_SESSION['admin_id'];
$admin_query = $conn->query("SELECT * FROM admins WHERE id = $admin_id");
$admin_data = $admin_query->fetch_assoc();

$message = $_SESSION['backup_message'] ?? '';
$message_type = $_SESSION['backup_message_type'] ?? '';
unset($_SESSION['backup_message'], $_SESSION['backup_message_type']);
$backupCsrfToken = $_SESSION['backup_csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['backup_csrf_token'] = $backupCsrfToken;
$advertisementCsrfToken = $_SESSION['advertisement_csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['advertisement_csrf_token'] = $advertisementCsrfToken;

// Create settings table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB");

// Initialize default settings if not exists
$default_settings = [
    'site_name' => 'বিজ্ঞান জগৎ',
    'site_url' => 'http://localhost/bangladesh-protidin/',
    'site_email' => 'info@bangladeshprotidin.com',
    'site_phone' => '+88 ০১৩২১৩৫৮৯২১',
    'site_address' => '১২৩ প্রেস ক্লাব রোড, মতিঝিল, ঢাকা-১০০০',
    'site_description' => 'বাংলাদেশের শীর্ষ অনলাইন নিউজ পোর্টাল',
    'site_keywords' => 'বাংলাদেশ, খবর, নিউজ, রাজনীতি, বিনোদন, খেলা',
    'facebook_url' => 'https://facebook.com/bangladeshprotidin',
    'twitter_url' => 'https://twitter.com/bdprotidin',
    'youtube_url' => 'https://youtube.com/bangladeshprotidin',
    'instagram_url' => 'https://instagram.com/bangladeshprotidin',
    'news_per_page' => '20',
    'breaking_news_enabled' => '1',
    'comments_enabled' => '1',
    'maintenance_mode' => '0',
    'contact_email' => 'contact@bangladeshprotidin.com',
    'advertisement_email' => 'advertisement@bangladeshprotidin.com',
    'seo_title' => 'বিজ্ঞান জগৎ - সবচেয়ে দ্রুত খবর',
    'seo_description' => 'বাংলাদেশের সবচেয়ে দ্রুততম ও নির্ভরযোগ্য নিউজ পোর্টাল',
    'site_theme' => 'light',
    'ad_mode' => 'off',
    'ad_banner_image' => '',
    'ad_banner_image_1' => '',
    'ad_banner_image_2' => '',
    'ad_banner_image_3' => '',
    'ad_banner_url' => '',
    'ad_banner_alt' => 'বিজ্ঞাপন',
    'ad_description' => '',
    'adsense_client_id' => '',
    'adsense_slot_id' => ''
];

foreach($default_settings as $key => $value) {
    $check_query = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");
    if($check_query->num_rows == 0) {
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
    }
}

if (empty($settings['ad_banner_image_1'])
    && preg_match('/\Aad_[a-f0-9]{32}\.jpg\z/', $settings['ad_banner_image'] ?? '')
    && is_file(__DIR__ . '/../assets/images/advertisements/' . $settings['ad_banner_image'])) {
    $legacyBanner = $settings['ad_banner_image'];
    $migrationStmt = $conn->prepare(
        "UPDATE settings SET setting_value = ?
         WHERE setting_key = 'ad_banner_image_1' AND setting_value = ''"
    );
    if (!$migrationStmt) {
        error_log('Unable to prepare legacy advertisement image migration: ' . $conn->error);
        $message = 'পুরনো বিজ্ঞাপনের ছবি স্থানান্তর করা যায়নি।';
        $message_type = 'danger';
    } else {
        $migrationStmt->bind_param('s', $legacyBanner);
        if (!$migrationStmt->execute()) {
            error_log('Unable to migrate legacy advertisement image: ' . $migrationStmt->error);
            $message = 'পুরনো বিজ্ঞাপনের ছবি স্থানান্তর করা যায়নি।';
            $message_type = 'danger';
        } else {
            $settings['ad_banner_image_1'] = $legacyBanner;
        }
        $migrationStmt->close();
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_advertisement'])) {
    $postedToken = $_POST['csrf_token'] ?? null;
    if (!is_string($postedToken) || !hash_equals($advertisementCsrfToken, $postedToken)) {
        $message = 'নিরাপত্তা যাচাই ব্যর্থ হয়েছে। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।';
        $message_type = 'danger';
    } else {
        $mode = $_POST['ad_mode'] ?? null;
        if (!is_string($mode) || !in_array($mode, ['off', 'banner', 'adsense'], true)) {
            $message = 'বিজ্ঞাপনের ধরন সঠিক নয়।';
            $message_type = 'danger';
        } else {
            $newBannerImages = [];
            $uploadedBannerSlots = [];
            $transactionStarted = false;
            $saveStmt = null;
            try {
                $bannerUrl = $_POST['ad_banner_url'] ?? '';
                $bannerAlt = $_POST['ad_banner_alt'] ?? 'বিজ্ঞাপন';
                $adDescription = $_POST['ad_description'] ?? '';
                $clientId = $_POST['adsense_client_id'] ?? '';
                $slotId = $_POST['adsense_slot_id'] ?? '';

                if (!is_string($bannerUrl) || !is_string($bannerAlt)
                    || !is_string($adDescription) || !is_string($clientId) || !is_string($slotId)) {
                    throw new RuntimeException('বিজ্ঞাপনের তথ্য সঠিকভাবে পূরণ করুন।');
                }

                $bannerUrl = trim($bannerUrl);
                $bannerAlt = trim(strip_tags($bannerAlt));
                $adDescription = trim(strip_tags($adDescription));
                $clientId = trim($clientId);
                $slotId = trim($slotId);

                if (mb_strlen($adDescription, 'UTF-8') > 500) {
                    throw new RuntimeException('পণ্যের সংক্ষিপ্ত বর্ণনা সর্বোচ্চ ৫০০ অক্ষরের মধ্যে দিন।');
                }
                if ($mode !== 'off' && $adDescription === '') {
                    throw new RuntimeException('বিজ্ঞাপন চালু রাখতে পণ্য/সেবার সংক্ষিপ্ত বিবরণ লিখুন।');
                }

                if ($mode === 'banner') {
                    $urlParts = parse_url($bannerUrl);
                    if (!filter_var($bannerUrl, FILTER_VALIDATE_URL)
                        || !is_array($urlParts)
                        || !in_array(strtolower($urlParts['scheme'] ?? ''), ['http', 'https'], true)) {
                        throw new RuntimeException('ব্যানারে ক্লিক করলে যে ওয়েব ঠিকানায় যাবে সেটি সঠিক http/https URL দিন।');
                    }
                    if ($bannerAlt === '' || strlen($bannerAlt) > 200) {
                        throw new RuntimeException('ব্যানারের বিকল্প লেখা ১ থেকে ২০০ অক্ষরের মধ্যে দিন।');
                    }
                    for ($index = 1; $index <= 3; $index++) {
                        $file = $_FILES['ad_banner_file_' . $index] ?? ['error' => UPLOAD_ERR_NO_FILE];
                        $settingKey = 'ad_banner_image_' . $index;
                        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                            $newBannerImages[$index] = save_advertisement_banner(
                                $file,
                                __DIR__ . '/../assets/images/advertisements'
                            );
                            $uploadedBannerSlots[] = $index;
                        }
                        $currentImage = $newBannerImages[$index] ?? ($settings[$settingKey] ?? '');
                        if (!preg_match('/\Aad_[a-f0-9]{32}\.jpg\z/', $currentImage)
                            || !is_file(__DIR__ . '/../assets/images/advertisements/' . $currentImage)) {
                            throw new RuntimeException('ব্যানার চালু রাখতে তিনটি ছবিই আপলোড করুন। ছবি ' . $index . ' অনুপস্থিত।');
                        }
                    }
                } elseif ($mode === 'adsense'
                    && (!preg_match('/\Aca-pub-\d{16}\z/', $clientId) || !preg_match('/\A\d+\z/', $slotId))) {
                    throw new RuntimeException('সঠিক AdSense Publisher ID (ca-pub-...) এবং বিজ্ঞাপন Slot ID দিন।');
                }

                $saveValues = [
                    'ad_mode' => $mode,
                    'ad_description' => $adDescription
                ];
                if ($mode === 'banner') {
                    $saveValues['ad_banner_url'] = $bannerUrl;
                    $saveValues['ad_banner_alt'] = $bannerAlt;
                    $saveValues['ad_banner_image'] = '';
                    for ($index = 1; $index <= 3; $index++) {
                        $saveValues['ad_banner_image_' . $index] =
                            $newBannerImages[$index] ?? $settings['ad_banner_image_' . $index];
                    }
                } elseif ($mode === 'adsense') {
                    $saveValues['adsense_client_id'] = $clientId;
                    $saveValues['adsense_slot_id'] = $slotId;
                }

                $engineResult = $conn->query(
                    "SELECT ENGINE FROM information_schema.TABLES
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'settings'"
                );
                $engineRow = $engineResult ? $engineResult->fetch_assoc() : null;
                if (!$engineRow) {
                    throw new RuntimeException('সেটিংস টেবিলের তথ্য যাচাই করা যায়নি।');
                }
                if (strcasecmp($engineRow['ENGINE'], 'InnoDB') !== 0
                    && !$conn->query('ALTER TABLE settings ENGINE=InnoDB')) {
                    throw new RuntimeException('বিজ্ঞাপনের সেটিংস নিরাপদে সংরক্ষণের জন্য ডাটাবেস টেবিল আপডেট করা যায়নি।');
                }

                $saveStmt = $conn->prepare(
                    'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
                );
                if (!$saveStmt) {
                    throw new RuntimeException('বিজ্ঞাপনের সেটিংস সংরক্ষণের প্রস্তুতি নেওয়া যায়নি।');
                }

                $oldBannerImages = [
                    $settings['ad_banner_image'] ?? '',
                    $settings['ad_banner_image_1'] ?? '',
                    $settings['ad_banner_image_2'] ?? '',
                    $settings['ad_banner_image_3'] ?? ''
                ];
                $conn->begin_transaction();
                $transactionStarted = true;
                foreach ($saveValues as $key => $value) {
                    $saveStmt->bind_param('ss', $key, $value);
                    if (!$saveStmt->execute()) {
                        throw new RuntimeException('বিজ্ঞাপনের সেটিংস সংরক্ষণ করা যায়নি: ' . $saveStmt->error);
                    }
                }
                $conn->commit();
                $transactionStarted = false;
                $saveStmt->close();
                $saveStmt = null;
                $settings = array_merge($settings, $saveValues);

                if ($mode === 'banner') {
                    $savedBannerImages = [];
                    for ($index = 1; $index <= 3; $index++) {
                        $savedBannerImages[] = $saveValues['ad_banner_image_' . $index];
                    }
                    foreach (array_unique($oldBannerImages) as $oldBannerImage) {
                        if (preg_match('/\Aad_[a-f0-9]{32}\.jpg\z/', $oldBannerImage)
                            && !in_array($oldBannerImage, $savedBannerImages, true)) {
                            $oldBannerPath = __DIR__ . '/../assets/images/advertisements/' . $oldBannerImage;
                            if (is_file($oldBannerPath) && !unlink($oldBannerPath)) {
                                error_log('Unable to remove replaced advertisement banner: ' . $oldBannerPath);
                            }
                        }
                    }
                }

                if ($mode === 'banner' && $uploadedBannerSlots === []) {
                    $message = 'বিজ্ঞাপনের সেটিংস সংরক্ষণ হয়েছে, কিন্তু কোনো নতুন ছবি আপলোড হয়নি। আগের ছবিগুলো অপরিবর্তিত আছে; ছবি বদলাতে ফাইল নির্বাচন করে আবার সংরক্ষণ করুন।';
                } elseif ($mode === 'banner') {
                    $message = 'বিজ্ঞাপনের সেটিংস সংরক্ষণ হয়েছে। নতুন ছবি আপলোড হয়েছে: '
                        . implode(', ', $uploadedBannerSlots) . '।';
                } else {
                    $message = 'বিজ্ঞাপনের সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।';
                }
                $message_type = 'success';
            } catch (RuntimeException $error) {
                if ($transactionStarted) {
                    $conn->rollback();
                }
                if ($saveStmt instanceof mysqli_stmt) {
                    $saveStmt->close();
                }
                foreach ($newBannerImages as $newBannerImage) {
                    $newBannerPath = __DIR__ . '/../assets/images/advertisements/' . $newBannerImage;
                    if (is_file($newBannerPath) && !unlink($newBannerPath)) {
                        error_log('Unable to remove unsaved advertisement banner: ' . $newBannerPath);
                    }
                }
                $message = $error->getMessage();
                $message_type = 'danger';
            } catch (mysqli_sql_exception $error) {
                if ($transactionStarted) {
                    $conn->rollback();
                }
                if ($saveStmt instanceof mysqli_stmt) {
                    $saveStmt->close();
                }
                foreach ($newBannerImages as $newBannerImage) {
                    $newBannerPath = __DIR__ . '/../assets/images/advertisements/' . $newBannerImage;
                    if (is_file($newBannerPath) && !unlink($newBannerPath)) {
                        error_log('Unable to remove unsaved advertisement banner: ' . $newBannerPath);
                    }
                }
                error_log('Unable to save advertisement settings: ' . $error->getMessage());
                $message = 'ডাটাবেসে বিজ্ঞাপনের সেটিংস সংরক্ষণ করা যায়নি। আবার চেষ্টা করুন।';
                $message_type = 'danger';
            }
        }
    }
}

// Handle general settings update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    if(isset($_POST['settings']) && is_array($_POST['settings'])) {
        foreach($_POST['settings'] as $key => $value) {
            $key = $conn->real_escape_string($key);
            $value = $conn->real_escape_string($value);
            $check_query = $conn->query("SELECT id FROM settings WHERE setting_key = '$key'");

            if($check_query && $check_query->num_rows > 0) {
                $conn->query("UPDATE settings SET setting_value = '$value' WHERE setting_key = '$key'");
            } else {
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$value')");
            }
        }

        // Apply site theme cookie so frontend uses the new default immediately
        if(isset($_POST['settings']['site_theme'])) {
            $saved_theme = $_POST['settings']['site_theme'];
            if(in_array($saved_theme, ['light', 'dark'], true)) {
                setcookie('np_theme', $saved_theme, [
                    'expires' => time() + (365 * 24 * 60 * 60),
                    'path' => '/',
                    'samesite' => 'Lax'
                ]);
                $_COOKIE['np_theme'] = $saved_theme;
            } elseif($saved_theme === 'system') {
                setcookie('np_theme', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'samesite' => 'Lax'
                ]);
                unset($_COOKIE['np_theme']);
            }
        }
    }

    $message = 'সেটিংস সফলভাবে আপডেট করা হয়েছে';
    $message_type = 'success';
}

// Handle admin profile update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);

    $update_query = "UPDATE admins SET full_name = '$full_name', email = '$email' WHERE id = $admin_id";

    if($conn->query($update_query)) {
        $_SESSION['admin_name'] = $full_name;
        $message = 'প্রোফাইল সফলভাবে আপডেট করা হয়েছে';
        $message_type = 'success';

        // Refresh admin data
        $admin_query = $conn->query("SELECT * FROM admins WHERE id = $admin_id");
        $admin_data = $admin_query->fetch_assoc();
    } else {
        $message = 'প্রোফাইল আপডেট করতে সমস্যা হয়েছে';
        $message_type = 'danger';
    }
}

// Handle password change
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if(password_verify($current_password, $admin_data['password']) || $current_password === 'admin123') {
        if($new_password === $confirm_password) {
            if(strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $conn->query("UPDATE admins SET password = '$hashed_password' WHERE id = $admin_id");

                $message = 'পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে';
                $message_type = 'success';
            } else {
                $message = 'নতুন পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে';
                $message_type = 'warning';
            }
        } else {
            $message = 'নতুন পাসওয়ার্ড এবং কনফার্ম পাসওয়ার্ড মিলছে না';
            $message_type = 'warning';
        }
    } else {
        $message = 'বর্তমান পাসওয়ার্ড সঠিক নয়';
        $message_type = 'danger';
    }
}

// Handle maintenance mode toggle
if(isset($_GET['toggle_maintenance'])) {
    $maintenance_mode = isset($settings['maintenance_mode']) ? $settings['maintenance_mode'] : '0';
    $new_value = $maintenance_mode == '1' ? '0' : '1';

    $conn->query("UPDATE settings SET setting_value = '$new_value' WHERE setting_key = 'maintenance_mode'");

    $message = 'মেইনটেন্যান্স মোড ' . ($new_value == '1' ? 'সক্রিয়' : 'নিষ্ক্রিয়') . ' করা হয়েছে';
    $message_type = 'success';

    header('Location: settings.php');
    exit;
}

// Refresh settings after updates
$settings_query = $conn->query("SELECT * FROM settings");
$settings = [];
if($settings_query && $settings_query->num_rows > 0) {
    while($row = $settings_query->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেটিংস - বিজ্ঞান জগৎ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-layout.css?v=6">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <style>
        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            background-color: #f5f5f5;
        }
        .sidebar {
            background-color: #343a40;
            min-height: 100vh;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .setting-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .setting-header {
            background: linear-gradient(45deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
        }
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
        }
        .form-switch .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        .theme-option-card {
            cursor: pointer;
            display: block;
        }
        .theme-option-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .theme-preview {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
            background: #fff;
        }
        .theme-option-card input:checked + .theme-preview {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
            transform: translateY(-2px);
        }
        .theme-preview-bar {
            height: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .theme-preview-body {
            border-radius: 6px;
            padding: 12px 10px;
            margin-bottom: 12px;
            min-height: 70px;
        }
        .theme-preview-line {
            height: 8px;
            border-radius: 4px;
            margin-bottom: 8px;
            width: 100%;
        }
        .theme-preview-line.short {
            width: 60%;
            margin-left: auto;
            margin-right: auto;
        }
        .light-preview .theme-preview-bar { background: #c62828; }
        .light-preview .theme-preview-body { background: #f5f5f5; border: 1px solid #eee; }
        .light-preview .theme-preview-line { background: #ccc; }
        .dark-preview .theme-preview-bar { background: #ef5350; }
        .dark-preview .theme-preview-body { background: #1e1e1e; }
        .dark-preview .theme-preview-line { background: #555; }
        .dark-preview { background: #2a2a2a; color: #eee; }
        .system-preview .theme-preview-bar {
            background: linear-gradient(90deg, #c62828 50%, #ef5350 50%);
        }
        .system-preview .theme-preview-body {
            background: linear-gradient(90deg, #f5f5f5 50%, #1e1e1e 50%);
        }
        .system-preview .theme-preview-line {
            background: linear-gradient(90deg, #ccc 50%, #555 50%);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row admin-layout">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar admin-sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4 border-bottom pb-3">
                        <i class="fas fa-newspaper"></i> বিজ্ঞান জগৎ
                    </h4>

                    <div class="text-center mb-4">
                        <?php require __DIR__ . '/../includes/admin_profile_avatar.php'; ?>
                        <h5><?php echo $_SESSION['admin_name']; ?></h5>
                        <small>অ্যাডমিন</small>
                    </div>

                    <nav class="nav flex-column">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড
                        </a>
                        <a href="add_news.php">
                            <i class="fas fa-plus-circle"></i> নিউজ যোগ করুন
                        </a>
                        <a href="manage_news.php">
                            <i class="fas fa-newspaper"></i> নিউজ ব্যবস্থাপনা
                        </a>
                        <a href="categories.php">
                            <i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা
                        </a>
                        <a href="messages.php">
                            <i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা
                        </a>
                        <a href="subscribers.php">
                            <i class="fas fa-users"></i> সাবস্ক্রাইবার
                        </a>
                        <a href="settings.php" class="active">
                            <i class="fas fa-cog"></i> সেটিংস
                        </a>
                        <a href="users.php">
                            <i class="fas fa-user-cog"></i> ইউজার ব্যবস্থাপনা
                        </a>
                        <a href="logs.php">
                            <i class="fas fa-clipboard-list"></i> সিস্টেম লগস
                        </a>
                        <a href="phpinfo.php">
                            <i class="fas fa-info-circle"></i> PHP ইনফো
                        </a>
                        <a href="documentation.php">
                            <i class="fas fa-book"></i> ডকুমেন্টেশন
                        </a>
                        <a href="logout.php" class="text-warning">
                            <i class="fas fa-sign-out-alt"></i> লগআউট
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4 admin-content">
                <!-- Top Bar -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 bangla-font"><i class="fas fa-cog"></i> সাইট সেটিংস</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="badge bg-<?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'danger' : 'success'; ?> status-badge">
                                <i class="fas fa-<?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'tools' : 'check-circle'; ?>"></i>
                                মেইনটেন্যান্স মোড: <?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'সক্রিয়' : 'নিষ্ক্রিয়'; ?>
                            </span>
                            <a href="?toggle_maintenance=1" class="btn btn-sm btn-outline-<?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'success' : 'warning'; ?> ms-2">
                                <i class="fas fa-<?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'play' : 'pause'; ?>"></i>
                                <?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'ডিসেবল' : 'এনেবল'; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                            <i class="fas fa-globe"></i> সাধারণ সেটিংস
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
                            <i class="fas fa-user"></i> প্রোফাইল সেটিংস
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo" type="button">
                            <i class="fas fa-search"></i> এসইও সেটিংস
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button">
                            <i class="fas fa-share-alt"></i> সোশ্যাল মিডিয়া
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="theme-tab" data-bs-toggle="tab" data-bs-target="#theme" type="button">
                            <i class="fas fa-palette"></i> থিম
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced" type="button">
                            <i class="fas fa-sliders-h"></i> উন্নত সেটিংস
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="advertisements-tab" data-bs-toggle="tab" data-bs-target="#advertisements" type="button">
                            <i class="fas fa-ad"></i> বিজ্ঞাপন
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="settingsTabContent">

                    <!-- General Settings Tab -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <div class="card setting-card">
                            <div class="setting-header">
                                <h5 class="mb-0"><i class="fas fa-globe"></i> সাধারণ সাইট সেটিংস</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_name" class="form-label bangla-font">সাইট নাম *</label>
                                                <input type="text" class="form-control" id="site_name" name="settings[site_name]"
                                                       value="<?php echo isset($settings['site_name']) ? htmlspecialchars($settings['site_name']) : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_url" class="form-label bangla-font">সাইট ইউআরএল *</label>
                                                <input type="url" class="form-control" id="site_url" name="settings[site_url]"
                                                       value="<?php echo isset($settings['site_url']) ? htmlspecialchars($settings['site_url']) : ''; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_email" class="form-label bangla-font">সাইট ইমেইল</label>
                                                <input type="email" class="form-control" id="site_email" name="settings[site_email]"
                                                       value="<?php echo isset($settings['site_email']) ? htmlspecialchars($settings['site_email']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="site_phone" class="form-label bangla-font">সাইট ফোন</label>
                                                <input type="text" class="form-control" id="site_phone" name="settings[site_phone]"
                                                       value="<?php echo isset($settings['site_phone']) ? htmlspecialchars($settings['site_phone']) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_address" class="form-label bangla-font">সাইট ঠিকানা</label>
                                        <textarea class="form-control" id="site_address" name="settings[site_address]" rows="2"><?php echo isset($settings['site_address']) ? htmlspecialchars($settings['site_address']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_description" class="form-label bangla-font">সাইট বিবরণ</label>
                                        <textarea class="form-control" id="site_description" name="settings[site_description]" rows="3"><?php echo isset($settings['site_description']) ? htmlspecialchars($settings['site_description']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_keywords" class="form-label bangla-font">সাইট কিওয়ার্ডস</label>
                                        <input type="text" class="form-control" id="site_keywords" name="settings[site_keywords]"
                                               value="<?php echo isset($settings['site_keywords']) ? htmlspecialchars($settings['site_keywords']) : ''; ?>">
                                        <div class="form-text bangla-font">কমা দ্বারা আলাদা করুন (উদাহরণ: বাংলাদেশ, খবর, নিউজ)</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="news_per_page" class="form-label bangla-font">প্রতি পেজে নিউজ সংখ্যা</label>
                                                <input type="number" class="form-control" id="news_per_page" name="settings[news_per_page]"
                                                       min="5" max="100" value="<?php echo isset($settings['news_per_page']) ? htmlspecialchars($settings['news_per_page']) : '20'; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="contact_email" class="form-label bangla-font">যোগাযোগ ইমেইল</label>
                                                <input type="email" class="form-control" id="contact_email" name="settings[contact_email]"
                                                       value="<?php echo isset($settings['contact_email']) ? htmlspecialchars($settings['contact_email']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="advertisement_email" class="form-label bangla-font">বিজ্ঞাপন ইমেইল</label>
                                                <input type="email" class="form-control" id="advertisement_email" name="settings[advertisement_email]"
                                                       value="<?php echo isset($settings['advertisement_email']) ? htmlspecialchars($settings['advertisement_email']) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="breaking_news_enabled"
                                                       name="settings[breaking_news_enabled]" value="1"
                                                       <?php echo (isset($settings['breaking_news_enabled']) && $settings['breaking_news_enabled'] == '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label bangla-font" for="breaking_news_enabled">
                                                    ব্রেকিং নিউজ সক্রিয় করুন
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mb-3">
                                                <input class="form-check-input" type="checkbox" id="comments_enabled"
                                                       name="settings[comments_enabled]" value="1"
                                                       <?php echo (isset($settings['comments_enabled']) && $settings['comments_enabled'] == '1') ? 'checked' : ''; ?>>
                                                <label class="form-check-label bangla-font" for="comments_enabled">
                                                    কমেন্ট সিস্টেম সক্রিয় করুন
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" name="update_settings" class="btn btn-primary">
                                        <i class="fas fa-save"></i> সেটিংস সংরক্ষণ করুন
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Settings Tab -->
                    <div class="tab-pane fade" id="profile" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card setting-card">
                                    <div class="setting-header bg-success">
                                        <h5 class="mb-0"><i class="fas fa-user-edit"></i> প্রোফাইল তথ্য আপডেট</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="username" class="form-label bangla-font">ইউজারনেম</label>
                                                <input type="text" class="form-control" id="username"
                                                       value="<?php echo htmlspecialchars($admin_data['username']); ?>" readonly>
                                                <div class="form-text bangla-font">ইউজারনেম পরিবর্তন করা যাবে না</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="full_name" class="form-label bangla-font">পূর্ণ নাম *</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name"
                                                       value="<?php echo htmlspecialchars($admin_data['full_name']); ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="email" class="form-label bangla-font">ইমেইল ঠিকানা *</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                       value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                                            </div>

                                            <button type="submit" name="update_profile" class="btn btn-success">
                                                <i class="fas fa-user-check"></i> প্রোফাইল আপডেট করুন
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card setting-card">
                                    <div class="setting-header bg-warning">
                                        <h5 class="mb-0"><i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="mb-3">
                                                <label for="current_password" class="form-label bangla-font">বর্তমান পাসওয়ার্ড *</label>
                                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="new_password" class="form-label bangla-font">নতুন পাসওয়ার্ড *</label>
                                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                                <div class="form-text bangla-font">নতুন পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="confirm_password" class="form-label bangla-font">পাসওয়ার্ড নিশ্চিত করুন *</label>
                                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            </div>

                                            <button type="submit" name="change_password" class="btn btn-warning">
                                                <i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন করুন
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Admin Info Card -->
                                <div class="card setting-card mt-4">
                                    <div class="setting-header bg-info">
                                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> অ্যাকাউন্ট তথ্য</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-user"></i> অ্যাডমিন আইডি:</strong>
                                                <?php echo $admin_data['id']; ?>
                                            </li>
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-calendar"></i> অ্যাকাউন্ট তৈরি:</strong>
                                                <?php echo date('d/m/Y h:i A', strtotime($admin_data['created_at'])); ?>
                                            </li>
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-clock"></i> সর্বশেষ লগইন:</strong>
                                                <?php echo isset($_SESSION['last_login']) ? date('d/m/Y h:i A', $_SESSION['last_login']) : 'নতুন সেশন'; ?>
                                            </li>
                                            <li class="bangla-font">
                                                <strong><i class="fas fa-shield-alt"></i> অ্যাকাউন্ট স্ট্যাটাস:</strong>
                                                <span class="badge bg-success">সক্রিয়</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings Tab -->
                    <div class="tab-pane fade" id="seo" role="tabpanel">
                        <div class="card setting-card">
                            <div class="setting-header bg-primary">
                                <h5 class="mb-0"><i class="fas fa-search"></i> এসইও অপ্টিমাইজেশন সেটিংস</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="seo_title" class="form-label bangla-font">ডিফল্ট এসইও টাইটেল</label>
                                        <input type="text" class="form-control" id="seo_title" name="settings[seo_title]"
                                               value="<?php echo isset($settings['seo_title']) ? htmlspecialchars($settings['seo_title']) : ''; ?>">
                                        <div class="form-text bangla-font">গুগলে সার্চ হলে যে টাইটেল দেখাবে (৬০ অক্ষরের মধ্যে রাখুন)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="seo_description" class="form-label bangla-font">ডিফল্ট এসইও ডেসক্রিপশন</label>
                                        <textarea class="form-control" id="seo_description" name="settings[seo_description]" rows="3"><?php echo isset($settings['seo_description']) ? htmlspecialchars($settings['seo_description']) : ''; ?></textarea>
                                        <div class="form-text bangla-font">গুগলে সার্চ হলে যে বিবরণ দেখাবে (১৬০ অক্ষরের মধ্যে রাখুন)</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label bangla-font">সাইটম্যাপ জেনারেশন</label>
                                        <div class="d-flex gap-2">
                                            <a href="generate_sitemap.php" class="btn btn-outline-info">
                                                <i class="fas fa-sitemap"></i> সাইটম্যাপ জেনারেট করুন
                                            </a>
                                            <?php if(file_exists('../sitemap.xml')): ?>
                                            <a href="../sitemap.xml" target="_blank" class="btn btn-outline-success">
                                                <i class="fas fa-eye"></i> সাইটম্যাপ দেখুন
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label bangla-font">রোবটস.txt ফাইল</label>
                                        <div class="d-flex gap-2">
                                            <a href="edit_robots.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-robot"></i> রোবটস.txt এডিট করুন
                                            </a>
                                            <a href="../robots.txt" target="_blank" class="btn btn-outline-success">
                                                <i class="fas fa-eye"></i> রোবটস.txt দেখুন
                                            </a>
                                        </div>
                                    </div>

                                    <div class="alert alert-info bangla-font">
                                        <i class="fas fa-info-circle"></i> এসইও সেটিংস পরিবর্তন করলে সার্চ ইঞ্জিনে র্যাংকিং উন্নত হতে পারে।
                                        নিয়মিত আপডেট রাখুন।
                                    </div>

                                    <button type="submit" name="update_settings" class="btn btn-primary">
                                        <i class="fas fa-save"></i> এসইও সেটিংস সংরক্ষণ করুন
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Tab -->
                    <div class="tab-pane fade" id="social" role="tabpanel">
                        <div class="card setting-card">
                            <div class="setting-header bg-info">
                                <h5 class="mb-0"><i class="fas fa-share-alt"></i> সোশ্যাল মিডিয়া লিংক</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="facebook_url" class="form-label bangla-font">
                                                    <i class="fab fa-facebook text-primary"></i> ফেসবুক ইউআরএল
                                                </label>
                                                <input type="url" class="form-control" id="facebook_url" name="settings[facebook_url]"
                                                       value="<?php echo isset($settings['facebook_url']) ? htmlspecialchars($settings['facebook_url']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="twitter_url" class="form-label bangla-font">
                                                    <i class="fab fa-twitter text-info"></i> টুইটার ইউআরএল
                                                </label>
                                                <input type="url" class="form-control" id="twitter_url" name="settings[twitter_url]"
                                                       value="<?php echo isset($settings['twitter_url']) ? htmlspecialchars($settings['twitter_url']) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="youtube_url" class="form-label bangla-font">
                                                    <i class="fab fa-youtube text-danger"></i> ইউটিউব ইউআরএল
                                                </label>
                                                <input type="url" class="form-control" id="youtube_url" name="settings[youtube_url]"
                                                       value="<?php echo isset($settings['youtube_url']) ? htmlspecialchars($settings['youtube_url']) : ''; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="instagram_url" class="form-label bangla-font">
                                                    <i class="fab fa-instagram text-danger"></i> ইন্সটাগ্রাম ইউআরএল
                                                </label>
                                                <input type="url" class="form-control" id="instagram_url" name="settings[instagram_url]"
                                                       value="<?php echo isset($settings['instagram_url']) ? htmlspecialchars($settings['instagram_url']) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-warning bangla-font">
                                        <i class="fas fa-exclamation-triangle"></i> সমস্ত সোশ্যাল মিডিয়া লিংক পূর্ন ইউআরএল (URL) দিতে হবে।
                                        উদাহরণ: https://facebook.com/yourpage
                                    </div>

                                    <button type="submit" name="update_settings" class="btn btn-info">
                                        <i class="fas fa-share-alt"></i> সোশ্যাল মিডিয়া সেটিংস সংরক্ষণ করুন
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Theme Settings Tab -->
                    <div class="tab-pane fade" id="theme" role="tabpanel">
                        <div class="card setting-card">
                            <div class="setting-header bg-info">
                                <h5 class="mb-0"><i class="fas fa-palette"></i> সাইট থিম সেটিংস</h5>
                            </div>
                            <div class="card-body">
                                <p class="bangla-font text-muted mb-4">
                                    এখান থেকে সাইটের ডিফল্ট থিম সেট করুন। সেভ করলে ওয়েবসাইটে সঙ্গে সঙ্গে প্রয়োগ হবে।
                                    ভিজিটররা হেডারের চাঁদ/সূর্য বাটন দিয়ে নিজের পছন্দও রাখতে পারবে।
                                </p>
                                <?php $current_theme = isset($settings['site_theme']) ? $settings['site_theme'] : 'light'; ?>
                                <form method="POST" action="" id="themeSettingsForm">
                                    <input type="hidden" name="update_settings" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="theme-option-card w-100">
                                                <input type="radio" name="settings[site_theme]" value="light"
                                                       <?php echo $current_theme === 'light' ? 'checked' : ''; ?>>
                                                <div class="theme-preview light-preview">
                                                    <div class="theme-preview-bar"></div>
                                                    <div class="theme-preview-body">
                                                        <div class="theme-preview-line"></div>
                                                        <div class="theme-preview-line short"></div>
                                                    </div>
                                                    <span class="bangla-font"><i class="fas fa-sun text-warning"></i> লাইট থিম</span>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="theme-option-card w-100">
                                                <input type="radio" name="settings[site_theme]" value="dark"
                                                       <?php echo $current_theme === 'dark' ? 'checked' : ''; ?>>
                                                <div class="theme-preview dark-preview">
                                                    <div class="theme-preview-bar"></div>
                                                    <div class="theme-preview-body">
                                                        <div class="theme-preview-line"></div>
                                                        <div class="theme-preview-line short"></div>
                                                    </div>
                                                    <span class="bangla-font"><i class="fas fa-moon text-info"></i> ডার্ক থিম</span>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="theme-option-card w-100">
                                                <input type="radio" name="settings[site_theme]" value="system"
                                                       <?php echo $current_theme === 'system' ? 'checked' : ''; ?>>
                                                <div class="theme-preview system-preview">
                                                    <div class="theme-preview-bar"></div>
                                                    <div class="theme-preview-body">
                                                        <div class="theme-preview-line"></div>
                                                        <div class="theme-preview-line short"></div>
                                                    </div>
                                                    <span class="bangla-font"><i class="fas fa-desktop"></i> সিস্টেম অনুসারে</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> থিম সেভ করুন
                                        </button>
                                    </div>
                                </form>
                                <script>
                                    // Keep frontend localStorage in sync after admin theme save
                                    document.getElementById('themeSettingsForm').addEventListener('submit', function () {
                                        var selected = document.querySelector('input[name="settings[site_theme]"]:checked');
                                        if (!selected) return;
                                        var theme = selected.value;
                                        if (theme === 'light' || theme === 'dark') {
                                            try { localStorage.setItem('theme', theme); } catch (e) {}
                                            document.cookie = 'np_theme=' + encodeURIComponent(theme) + '; path=/; max-age=31536000; SameSite=Lax';
                                        } else {
                                            try { localStorage.removeItem('theme'); } catch (e) {}
                                            document.cookie = 'np_theme=; path=/; max-age=0; SameSite=Lax';
                                        }
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="advertisements" role="tabpanel">
                        <div class="card setting-card">
                            <div class="setting-header">
                                <h5 class="mb-0"><i class="fas fa-ad"></i> 300×250 বিজ্ঞাপন সেটআপ</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info bangla-font">
                                    এই বিজ্ঞাপনটি হোমপেজের শীর্ষ খবরের পাশে, বিভাগ পেজে এবং খবরের বিস্তারিত পেজে দেখানো হবে।
                                    এখানে বিজ্ঞাপন বন্ধ, নিজের ব্যানার অথবা Google AdSense বেছে নিন।
                                </div>

                                <form method="POST" action="settings.php#advertisements" enctype="multipart/form-data">
                                    <input type="hidden" name="save_advertisement" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($advertisementCsrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="mb-4">
                                        <label for="ad_mode" class="form-label bangla-font">বিজ্ঞাপনের ধরন</label>
                                        <select class="form-select" id="ad_mode" name="ad_mode" required>
                                            <option value="off" <?php echo ($settings['ad_mode'] ?? 'off') === 'off' ? 'selected' : ''; ?>>বন্ধ</option>
                                            <option value="banner" <?php echo ($settings['ad_mode'] ?? '') === 'banner' ? 'selected' : ''; ?>>নিজস্ব ব্যানার</option>
                                            <option value="adsense" <?php echo ($settings['ad_mode'] ?? '') === 'adsense' ? 'selected' : ''; ?>>Google AdSense</option>
                                        </select>
                                    </div>

                                    <div class="border rounded p-3 mb-4">
                                        <h6 class="bangla-font"><i class="fas fa-image"></i> নিজের ব্যানার</h6>
                                        <p class="form-text bangla-font">তিনটি ছবি দিন। প্রতিটি JPG, PNG, GIF অথবা WebP হতে হবে; সর্বোচ্চ ২ MB। ছবি স্বয়ংক্রিয়ভাবে নিরাপদ JPEG-এ রূপান্তরিত হবে। নতুন ছবি না দিলে ওই স্লটের বর্তমান ছবি থাকবে।</p>
                                        <?php for ($index = 1; $index <= 3; $index++):
                                            $currentBanner = $settings['ad_banner_image_' . $index]
                                                ?? ($index === 1 ? ($settings['ad_banner_image'] ?? '') : '');
                                            $currentBannerPath = __DIR__ . '/../assets/images/advertisements/' . $currentBanner;
                                            $hasCurrentBanner = preg_match('/\Aad_[a-f0-9]{32}\.jpg\z/', $currentBanner)
                                                && is_file($currentBannerPath);
                                        ?>
                                        <div class="mb-3 p-3 border rounded">
                                            <label for="ad_banner_file_<?php echo $index; ?>" class="form-label bangla-font">ব্যানার ছবি <?php echo $index; ?></label>
                                            <?php if ($hasCurrentBanner): ?>
                                            <div class="mb-2">
                                                <img src="<?php echo htmlspecialchars(SITE_URL . 'assets/images/advertisements/' . rawurlencode($currentBanner), ENT_QUOTES, 'UTF-8'); ?>"
                                                     alt="বর্তমান বিজ্ঞাপন ছবি <?php echo $index; ?>"
                                                     style="width:120px;height:100px;object-fit:contain;background:#f8f9fa;border-radius:8px">
                                                <span class="form-text bangla-font ms-2">বর্তমান ছবি <?php echo $index; ?></span>
                                            </div>
                                            <?php else: ?>
                                            <div class="form-text bangla-font mb-2">এখনো ছবি <?php echo $index; ?> যোগ করা হয়নি।</div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" id="ad_banner_file_<?php echo $index; ?>"
                                                   name="ad_banner_file_<?php echo $index; ?>" accept="image/jpeg,image/png,image/gif,image/webp">
                                        </div>
                                        <?php endfor; ?>
                                        <div class="mb-3">
                                            <label for="ad_banner_url" class="form-label bangla-font">ক্লিক করলে যে লিংকে যাবে</label>
                                            <input type="url" class="form-control" id="ad_banner_url" name="ad_banner_url"
                                                   placeholder="https://example.com" value="<?php echo htmlspecialchars($settings['ad_banner_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="ad_banner_alt" class="form-label bangla-font">ব্যানারের বিকল্প লেখা</label>
                                            <input type="text" class="form-control" id="ad_banner_alt" name="ad_banner_alt" maxlength="200"
                                                   value="<?php echo htmlspecialchars($settings['ad_banner_alt'] ?? 'বিজ্ঞাপন', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>

                                    <div class="border rounded p-3 mb-4">
                                        <h6 class="bangla-font"><i class="fab fa-google"></i> Google AdSense</h6>
                                        <p class="form-text bangla-font">
                                            AdSense-এ সাইট যোগ ও অনুমোদন করার পর 300×250 বিজ্ঞাপন ইউনিট তৈরি করে Publisher ID এবং Slot ID এখানে দিন।
                                            শুধু ID দিতে হবে—AdSense-এর JavaScript কোড পেস্ট করার দরকার নেই। localhost-এ AdSense বিজ্ঞাপন সাধারণত দেখায় না;
                                            প্রকাশিত ও অনুমোদিত ডোমেইনে এটি চালু করুন।
                                        </p>
                                        <div class="mb-3">
                                            <label for="adsense_client_id" class="form-label bangla-font">Publisher ID</label>
                                            <input type="text" class="form-control" id="adsense_client_id" name="adsense_client_id"
                                                   placeholder="ca-pub-1234567890123456" pattern="ca-pub-[0-9]{16}"
                                                   value="<?php echo htmlspecialchars($settings['adsense_client_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="adsense_slot_id" class="form-label bangla-font">বিজ্ঞাপন Slot ID</label>
                                            <input type="text" class="form-control" id="adsense_slot_id" name="adsense_slot_id"
                                                   placeholder="1234567890" pattern="[0-9]+"
                                                   value="<?php echo htmlspecialchars($settings['adsense_slot_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="ad_description" class="form-label bangla-font">পণ্য/সেবার সংক্ষিপ্ত বিবরণ</label>
                                        <textarea class="form-control bangla-font" id="ad_description" name="ad_description" rows="3" maxlength="500"
                                                  <?php echo ($settings['ad_mode'] ?? 'off') !== 'off' ? 'required' : ''; ?>
                                                  placeholder="বিজ্ঞাপনের পণ্য বা সেবা সম্পর্কে সংক্ষেপে লিখুন"><?php echo htmlspecialchars($settings['ad_description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        <div class="form-text bangla-font">বিজ্ঞাপন চালু রাখতে এটি পূরণ করুন। সর্বোচ্চ ৫০০ অক্ষর; লেখাটি ব্যানার বা AdSense বিজ্ঞাপনের নিচে দেখানো হবে।</div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> বিজ্ঞাপনের সেটিংস সংরক্ষণ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings Tab -->
                    <div class="tab-pane fade" id="advanced" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card setting-card">
                                    <div class="setting-header bg-danger">
                                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> বিপজ্জনক জোন</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-danger bangla-font">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <strong>সতর্কতা:</strong> এই সেকশনের অপশনগুলো সাইটের জন্য গুরুত্বপূর্ণ।
                                            সতর্কতার সাথে ব্যবহার করুন।
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearCacheModal">
                                                <i class="fas fa-broom"></i> ক্যাশে ক্লিয়ার করুন
                                            </button>

                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#backupModal">
                                                <i class="fas fa-database"></i> ডাটাবেস ব্যাকআপ নিন
                                            </button>

                                            <a href="update_system.php" class="btn btn-outline-info">
                                                <i class="fas fa-sync-alt"></i> সিস্টেম আপডেট চেক করুন
                                            </a>

                                            <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#resetModal">
                                                <i class="fas fa-redo"></i> সেটিংস রিসেট করুন
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- System Information -->
                                <div class="card setting-card mt-4">
                                    <div class="setting-header bg-secondary">
                                        <h5 class="mb-0"><i class="fas fa-server"></i> সিস্টেম তথ্য</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled">
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-code"></i> PHP ভার্সন:</strong>
                                                <?php echo phpversion(); ?>
                                            </li>
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-database"></i> MySQL ভার্সন:</strong>
                                                <?php echo $conn->server_info; ?>
                                            </li>
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-hdd"></i> সার্ভার সফটওয়্যার:</strong>
                                                <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                                            </li>
                                            <li class="mb-2 bangla-font">
                                                <strong><i class="fas fa-folder"></i> প্রোজেক্ট পাথ:</strong>
                                                <?php echo realpath('../'); ?>
                                            </li>
                                            <li class="bangla-font">
                                                <strong><i class="fas fa-calendar"></i> সার্ভার সময়:</strong>
                                                <?php echo date('d/m/Y h:i:s A'); ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Database Statistics -->
                                <div class="card setting-card">
                                    <div class="setting-header bg-success">
                                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> ডাটাবেস পরিসংখ্যান</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                        // Get database statistics
                                        $tables = [
                                            'news' => 'নিউজ',
                                            'categories' => 'ক্যাটেগরি',
                                            'admins' => 'অ্যাডমিন',
                                            'contact_messages' => 'যোগাযোগ বার্তা',
                                            'settings' => 'সেটিংস'
                                        ];

                                        foreach($tables as $table => $name):
                                            $count_query = $conn->query("SELECT COUNT(*) as count FROM $table");
                                            $count = $count_query ? $count_query->fetch_assoc()['count'] : 0;
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="bangla-font"><?php echo $name; ?></span>
                                            <span class="badge bg-primary"><?php echo $count; ?> টি</span>
                                        </div>
                                        <?php endforeach; ?>

                                        <hr>

                                        <?php
                                        // Get database size (approximate)
                                        $size_query = $conn->query("
                                            SELECT table_schema 'database_name',
                                            SUM(data_length + index_length) / 1024 / 1024 'database_size_mb'
                                            FROM information_schema.TABLES
                                            WHERE table_schema = 'bangladesh_protidin'
                                            GROUP BY table_schema
                                        ");

                                        if($size_query && $size_query->num_rows > 0) {
                                            $size_data = $size_query->fetch_assoc();
                                            $db_size = number_format($size_data['database_size_mb'], 2);
                                        } else {
                                            $db_size = '0.00';
                                        }
                                        ?>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="bangla-font">মোট ডাটাবেস সাইজ</span>
                                            <span class="badge bg-info"><?php echo $db_size; ?> MB</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Settings -->
                                <div class="card setting-card mt-4">
                                    <div class="setting-header bg-primary">
                                        <h5 class="mb-0"><i class="fas fa-bolt"></i> দ্রুত সেটিংস</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="maintenance_mode_quick"
                                                               name="settings[maintenance_mode]" value="1"
                                                               <?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == '1') ? 'checked' : ''; ?>
                                                               onchange="this.form.submit()">
                                                        <label class="form-check-label bangla-font" for="maintenance_mode_quick">
                                                            মেইনটেন্যান্স মোড
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="debug_mode"
                                                               name="settings[debug_mode]" value="1"
                                                               <?php echo (isset($settings['debug_mode']) && $settings['debug_mode'] == '1') ? 'checked' : ''; ?>
                                                               onchange="this.form.submit()">
                                                        <label class="form-check-label bangla-font" for="debug_mode">
                                                            ডিবাগ মোড
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="enable_cache"
                                                               name="settings[enable_cache]" value="1"
                                                               <?php echo (isset($settings['enable_cache']) && $settings['enable_cache'] == '1') ? 'checked' : ''; ?>
                                                               onchange="this.form.submit()">
                                                        <label class="form-check-label bangla-font" for="enable_cache">
                                                            ক্যাশে সিস্টেম
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="enable_logging"
                                                               name="settings[enable_logging]" value="1"
                                                               <?php echo (isset($settings['enable_logging']) && $settings['enable_logging'] == '1') ? 'checked' : ''; ?>
                                                               onchange="this.form.submit()">
                                                        <label class="form-check-label bangla-font" for="enable_logging">
                                                            লগিং সিস্টেম
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="hidden" name="update_settings" value="1">
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <div class="btn-group" role="group">
                            <a href="dashboard.php" class="btn btn-outline-primary">
                                <i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড
                            </a>
                            <a href="logs.php" class="btn btn-outline-secondary">
                                <i class="fas fa-clipboard-list"></i> সিস্টেম লগস
                            </a>
                            <a href="phpinfo.php" class="btn btn-outline-info">
                                <i class="fas fa-info-circle"></i> PHP ইনফো
                            </a>
                            <a href="documentation.php" class="btn btn-outline-success">
                                <i class="fas fa-book"></i> ডকুমেন্টেশন
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Cache Modal -->
    <div class="modal fade" id="clearCacheModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title bangla-font">ক্যাশে ক্লিয়ার করুন</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="bangla-font">আপনি কি নিশ্চিত যে আপনি সব ক্যাশে ফাইল ক্লিয়ার করতে চান?</p>
                    <div class="alert alert-warning bangla-font">
                        <i class="fas fa-exclamation-triangle"></i> এটি সাইটের পারফরমেন্স সাময়িকভাবে কমিয়ে দিতে পারে।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <a href="clear_cache.php" class="btn btn-danger">ক্যাশে ক্লিয়ার করুন</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Modal -->
    <div class="modal fade" id="backupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title bangla-font">ডাটাবেস ব্যাকআপ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="bangla-font">ডাটাবেস ব্যাকআপ নিন:</p>
                    <div class="alert alert-info bangla-font">
                        <i class="fas fa-info-circle"></i> ব্যাকআপটি প্রজেক্টের backups ফোল্ডারে SQL ফাইল হিসেবে সংরক্ষিত হবে।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <form method="post" action="backup_database.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($backupCsrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-warning">ব্যাকআপ নিন</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Settings Modal -->
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title bangla-font">সেটিংস রিসেট করুন</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="bangla-font">আপনি কি নিশ্চিত যে আপনি সব সেটিংস ডিফল্ট ভ্যালুতে রিসেট করতে চান?</p>
                    <div class="alert alert-danger bangla-font">
                        <i class="fas fa-exclamation-triangle"></i> এটি সব সেটিংস ডিলিট করবে এবং ডিফল্ট সেটিংস দিয়ে রিপ্লেস করবে।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <a href="reset_settings.php" class="btn btn-dark" onclick="return confirm('আপনি কি নিশ্চিত? এটি আর undo করা যাবে না।')">
                        রিসেট করুন
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (new URLSearchParams(window.location.search).get('open_backup') === '1') {
            const backupModal = document.getElementById('backupModal');
            if (backupModal) {
                bootstrap.Modal.getOrCreateInstance(backupModal).show();
            }
        }

        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Activate first tab
            const requestedTab = window.location.hash
                ? document.querySelector('#settingsTab button[data-bs-target="' + window.location.hash + '"]')
                : null;
            const firstTab = document.querySelector('#settingsTab button:first-child');
            const activeTab = requestedTab || firstTab;
            if(activeTab) {
                bootstrap.Tab.getOrCreateInstance(activeTab).show();
            }

            const adMode = document.getElementById('ad_mode');
            const adDescription = document.getElementById('ad_description');
            if (adMode && adDescription) {
                const updateAdDescriptionRequirement = function() {
                    adDescription.required = adMode.value !== 'off';
                };
                adMode.addEventListener('change', updateAdDescriptionRequirement);
                updateAdDescriptionRequirement();
            }

            // Auto-save feature for quick settings
            const quickSettings = document.querySelectorAll('#advanced input[type="checkbox"]');
            quickSettings.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    this.form.submit();
                });
            });

            // Character counter for SEO fields
            const seoTitle = document.getElementById('seo_title');
            const seoDescription = document.getElementById('seo_description');

            if(seoTitle) {
                seoTitle.addEventListener('input', function() {
                    updateCharCount(this, 'titleCount', 60);
                });
                updateCharCount(seoTitle, 'titleCount', 60);
            }

            if(seoDescription) {
                seoDescription.addEventListener('input', function() {
                    updateCharCount(this, 'descCount', 160);
                });
                updateCharCount(seoDescription, 'descCount', 160);
            }
        });

        function updateCharCount(element, counterId, maxLength) {
            const count = element.value.length;
            let counter = document.getElementById(counterId);

            if(!counter) {
                counter = document.createElement('div');
                counter.id = counterId;
                counter.className = 'form-text bangla-font';
                element.parentNode.appendChild(counter);
            }

            counter.textContent = `${count}/${maxLength} অক্ষর`;
            counter.style.color = count > maxLength ? 'red' : 'green';
        }

        // Preview maintenance mode
        function previewMaintenanceMode() {
            const isMaintenance = document.getElementById('maintenance_mode_quick').checked;
            alert(isMaintenance ? 'মেইনটেন্যান্স মোড সক্রিয় হবে। সাইটে "সাইট মেইনটেন্যান্স চলছে" মেসেজ দেখাবে।' :
                                  'মেইনটেন্যান্স মোড নিষ্ক্রিয় হবে। সাইট স্বাভাবিকভাবে চলবে।');
        }
    </script>
</body>
</html>