<?php
require_once '../includes/admin_auth.php';
require_admin_permission('settings_manage');

// Define cache directories
$cache_dirs = [
    '../cache/',
    '../temp/'
];

$cleared = 0;
$errors = [];

foreach($cache_dirs as $dir) {
    if(file_exists($dir)) {
        $files = glob($dir . '*');
        foreach($files as $file) {
            if(is_file($file)) {
                if(unlink($file)) {
                    $cleared++;
                } else {
                    $errors[] = $file;
                }
            }
        }
    }
}

// Clear session cache
session_unset();

// Set message
if(count($errors) > 0) {
    $_SESSION['cache_message'] = $cleared . ' টি ফাইল ক্লিয়ার হয়েছে, ' . count($errors) . ' টিতে সমস্যা';
    $_SESSION['cache_message_type'] = 'warning';
} else {
    $_SESSION['cache_message'] = $cleared . ' টি ক্যাশে ফাইল সফলভাবে ক্লিয়ার হয়েছে';
    $_SESSION['cache_message_type'] = 'success';
}

header('Location: settings.php');
exit;
?>