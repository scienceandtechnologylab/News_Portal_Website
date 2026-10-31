<?php
require_once '../includes/admin_auth.php';
require_once '../includes/admin_avatar.php';
require_admin_permission('users_manage');

$csrfToken = $_SESSION['admin_users_csrf'] ?? bin2hex(random_bytes(32));
$_SESSION['admin_users_csrf'] = $csrfToken;
$message = '';
$messageType = '';
$currentAdminId = (int)$_SESSION['admin_id'];

$permissionNames = [
    'dashboard_view' => 'ড্যাশবোর্ড দেখা',
    'news_view' => 'নিউজ তালিকা দেখা',
    'news_add' => 'নিউজ যোগ করা',
    'news_edit' => 'নিউজ সম্পাদনা ও প্রকাশের স্ট্যাটাস বদলানো',
    'news_delete' => 'নিউজ মুছে ফেলা',
    'category_manage' => 'ক্যাটেগরি পরিচালনা',
    'messages_manage' => 'যোগাযোগের বার্তা পরিচালনা',
    'subscribers_view' => 'সাবস্ক্রাইবার দেখা',
    'settings_manage' => 'সেটিংস, cache ও PHP তথ্য পরিচালনা',
    'logs_view' => 'সিস্টেম লগ দেখা',
    'backup_manage' => 'ডাটাবেস ব্যাকআপ',
    'users_manage' => 'ইউজার তৈরি ও পরিচালনা (শুধু সুপার অ্যাডমিন)',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedCsrfToken = $_POST['csrf_token'] ?? null;
    if (!is_string($postedCsrfToken) || !hash_equals($csrfToken, $postedCsrfToken)) {
        http_response_code(400);
        $message = 'অনুরোধটি যাচাই করা যায়নি। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।';
        $messageType = 'danger';
    } else {
        $action = $_POST['user_action'] ?? '';

        try {
            if ($action === 'update_own_profile_image') {
                $removeAvatar = isset($_POST['remove_own_profile_image']);
                $avatarFile = $_FILES['own_profile_image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
                if (!is_array($avatarFile)) {
                    throw new InvalidArgumentException('প্রোফাইল ছবির তথ্য সঠিক নয়।');
                }
                if ($removeAvatar && ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    throw new InvalidArgumentException('একই সঙ্গে ছবি বদলানো ও সরানো যাবে না।');
                }
                $currentAvatarStmt = $conn->prepare('SELECT profile_image FROM admins WHERE id = ?');
                $currentAvatarStmt->bind_param('i', $currentAdminId);
                $currentAvatarStmt->execute();
                $currentAvatar = $currentAvatarStmt->get_result()->fetch_assoc();
                if (!$currentAvatar) {
                    throw new RuntimeException('আপনার account তথ্য পাওয়া যায়নি।');
                }

                $hasNewAvatar = ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
                $newAvatarFilename = $removeAvatar || !$hasNewAvatar
                    ? null
                    : process_admin_avatar($avatarFile, __DIR__ . '/../assets/images/admins');
                $profileImage = $removeAvatar
                    ? null
                    : ($newAvatarFilename ?? $currentAvatar['profile_image']);

                if ($removeAvatar || $hasNewAvatar) {
                    try {
                        $avatarUpdateStmt = $conn->prepare('UPDATE admins SET profile_image = ? WHERE id = ?');
                        $avatarUpdateStmt->bind_param('si', $profileImage, $currentAdminId);
                        $avatarUpdateStmt->execute();
                    } catch (Throwable $exception) {
                        if ($newAvatarFilename !== null) {
                            unlink(__DIR__ . '/../assets/images/admins/' . $newAvatarFilename);
                        }
                        throw $exception;
                    }
                }

                if (($removeAvatar || $newAvatarFilename !== null) && !empty($currentAvatar['profile_image'])) {
                    $oldAvatarPath = __DIR__ . '/../assets/images/admins/' . basename($currentAvatar['profile_image']);
                    if (is_file($oldAvatarPath) && !unlink($oldAvatarPath)) {
                        error_log('Unable to remove previous profile image for admin id ' . $currentAdminId);
                    }
                }

                if ($removeAvatar) {
                    $message = 'আপনার প্রোফাইল ছবি সরানো হয়েছে।';
                    $messageType = 'success';
                } elseif ($hasNewAvatar) {
                    $message = 'আপনার প্রোফাইল ছবি আপডেট হয়েছে।';
                    $messageType = 'success';
                } else {
                    $message = 'বর্তমান প্রোফাইল ছবি অপরিবর্তিত রাখা হয়েছে।';
                    $messageType = 'info';
                }
            } elseif ($action === 'create') {
                $usernameInput = $_POST['username'] ?? null;
                $fullNameInput = $_POST['full_name'] ?? null;
                $emailInput = $_POST['email'] ?? null;
                $password = $_POST['password'] ?? null;
                $roleInput = $_POST['role_id'] ?? null;
                if (!is_string($usernameInput) || !is_string($fullNameInput)
                    || !is_string($emailInput) || !is_string($password)) {
                    throw new InvalidArgumentException('সব user তথ্য সঠিকভাবে পূরণ করুন।');
                }
                $username = trim($usernameInput);
                $fullName = trim($fullNameInput);
                $email = trim($emailInput);
                $roleId = is_string($roleInput) && ctype_digit($roleInput) ? (int)$roleInput : 0;
                $fullNameLength = preg_match_all('/./us', $fullName);

                if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
                    throw new InvalidArgumentException('ইউজারনেম ৩-৫০ অক্ষরের ইংরেজি অক্ষর, সংখ্যা, ডট, হাইফেন বা আন্ডারস্কোর হতে হবে।');
                }
                if ($fullName === '' || $fullNameLength === false || $fullNameLength > 100) {
                    throw new InvalidArgumentException('নাম দিন (সর্বোচ্চ ১০০ অক্ষর)।');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
                    throw new InvalidArgumentException('একটি সঠিক ইমেইল ঠিকানা দিন।');
                }
                if (strlen($password) < 10) {
                    throw new InvalidArgumentException('পাসওয়ার্ড কমপক্ষে ১০ অক্ষরের হতে হবে।');
                }

                $roleStmt = $conn->prepare('SELECT id FROM admin_roles WHERE id = ? AND is_super_admin = 0');
                $roleStmt->bind_param('i', $roleId);
                $roleStmt->execute();
                if (!$roleStmt->get_result()->fetch_assoc()) {
                    throw new InvalidArgumentException('একটি বৈধ user role নির্বাচন করুন।');
                }

                $avatarFile = $_FILES['profile_image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
                if (!is_array($avatarFile)) {
                    throw new InvalidArgumentException('প্রোফাইল ছবির তথ্য সঠিক নয়।');
                }
                $avatarFilename = process_admin_avatar(
                    $avatarFile,
                    __DIR__ . '/../assets/images/admins'
                );
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = $conn->prepare(
                        'INSERT INTO admins (username, password, email, full_name, role_id, is_active, profile_image)
                         VALUES (?, ?, ?, ?, ?, 1, ?)'
                    );
                    $stmt->bind_param('ssssis', $username, $passwordHash, $email, $fullName, $roleId, $avatarFilename);
                    $stmt->execute();
                } catch (Throwable $exception) {
                    if ($avatarFilename !== null) {
                        unlink(__DIR__ . '/../assets/images/admins/' . $avatarFilename);
                    }
                    throw $exception;
                }
                $message = 'নতুন user সফলভাবে তৈরি হয়েছে।';
                $messageType = 'success';
            } elseif ($action === 'update') {
                $userIdInput = $_POST['user_id'] ?? null;
                $fullNameInput = $_POST['full_name'] ?? null;
                $emailInput = $_POST['email'] ?? null;
                $roleInput = $_POST['role_id'] ?? null;
                $newPassword = $_POST['new_password'] ?? null;
                if (!is_string($userIdInput) || !is_string($fullNameInput)
                    || !is_string($emailInput) || !is_string($newPassword)) {
                    throw new InvalidArgumentException('সব user তথ্য সঠিকভাবে পূরণ করুন।');
                }
                $userId = ctype_digit($userIdInput) ? (int)$userIdInput : 0;
                $fullName = trim($fullNameInput);
                $email = trim($emailInput);
                $roleId = is_string($roleInput) && ctype_digit($roleInput) ? (int)$roleInput : 0;
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                $fullNameLength = preg_match_all('/./us', $fullName);

                if ($userId < 1 || $userId === $currentAdminId) {
                    throw new InvalidArgumentException('নিজের বা অবৈধ user record এখান থেকে পরিবর্তন করা যাবে না।');
                }
                if ($fullName === '' || $fullNameLength === false || $fullNameLength > 100) {
                    throw new InvalidArgumentException('নাম দিন (সর্বোচ্চ ১০০ অক্ষর)।');
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
                    throw new InvalidArgumentException('একটি সঠিক ইমেইল ঠিকানা দিন।');
                }
                if ($newPassword !== '' && strlen($newPassword) < 10) {
                    throw new InvalidArgumentException('নতুন পাসওয়ার্ড কমপক্ষে ১০ অক্ষরের হতে হবে।');
                }

                $roleStmt = $conn->prepare('SELECT id FROM admin_roles WHERE id = ? AND is_super_admin = 0');
                $roleStmt->bind_param('i', $roleId);
                $roleStmt->execute();
                if (!$roleStmt->get_result()->fetch_assoc()) {
                    throw new InvalidArgumentException('একটি বৈধ user role নির্বাচন করুন।');
                }

                $accountStmt = $conn->prepare(
                    'SELECT r.is_super_admin, a.profile_image FROM admins a
                     LEFT JOIN admin_roles r ON r.id = a.role_id WHERE a.id = ?'
                );
                $accountStmt->bind_param('i', $userId);
                $accountStmt->execute();
                $account = $accountStmt->get_result()->fetch_assoc();
                if (!$account || (int)$account['is_super_admin'] === 1) {
                    throw new InvalidArgumentException('সুপার অ্যাডমিন অ্যাকাউন্ট পরিবর্তন করা যাবে না।');
                }

                $removeAvatar = isset($_POST['remove_profile_image']);
                $avatarFile = $_FILES['edit_profile_image'] ?? ['error' => UPLOAD_ERR_NO_FILE];
                if (!is_array($avatarFile)) {
                    throw new InvalidArgumentException('প্রোফাইল ছবির তথ্য সঠিক নয়।');
                }
                if ($removeAvatar && ($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    throw new InvalidArgumentException('ছবি বদলাতে নতুন ছবি দিন অথবা বর্তমান ছবি সরানোর অপশনটি বেছে নিন।');
                }

                $newAvatarFilename = process_admin_avatar(
                    $avatarFile,
                    __DIR__ . '/../assets/images/admins'
                );
                $profileImage = $removeAvatar
                    ? null
                    : ($newAvatarFilename ?? $account['profile_image']);

                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare(
                        'UPDATE admins
                         SET full_name = ?, email = ?, role_id = ?, is_active = ?, profile_image = ?
                         WHERE id = ?'
                    );
                    $stmt->bind_param('ssiisi', $fullName, $email, $roleId, $isActive, $profileImage, $userId);
                    $stmt->execute();

                    if ($newPassword !== '') {
                        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $passwordStmt = $conn->prepare('UPDATE admins SET password = ? WHERE id = ?');
                        $passwordStmt->bind_param('si', $passwordHash, $userId);
                        $passwordStmt->execute();
                    }

                    $conn->commit();
                } catch (Throwable $exception) {
                    $conn->rollback();
                    if ($newAvatarFilename !== null) {
                        unlink(__DIR__ . '/../assets/images/admins/' . $newAvatarFilename);
                    }
                    throw $exception;
                }

                if (($removeAvatar || $newAvatarFilename !== null) && !empty($account['profile_image'])) {
                    $oldAvatarPath = __DIR__ . '/../assets/images/admins/' . basename($account['profile_image']);
                    if (is_file($oldAvatarPath) && !unlink($oldAvatarPath)) {
                        error_log('Unable to remove replaced profile image for admin id ' . $userId);
                    }
                }

                $message = 'User তথ্য আপডেট হয়েছে।';
                $messageType = 'success';
            } elseif ($action === 'delete') {
                $userIdInput = $_POST['user_id'] ?? null;
                $userId = is_string($userIdInput) && ctype_digit($userIdInput) ? (int)$userIdInput : 0;
                if ($userId < 1 || $userId === $currentAdminId) {
                    throw new InvalidArgumentException('নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না।');
                }

                $avatarStmt = $conn->prepare(
                    'SELECT a.profile_image FROM admins a
                     JOIN admin_roles r ON r.id = a.role_id
                     WHERE a.id = ? AND r.is_super_admin = 0'
                );
                $avatarStmt->bind_param('i', $userId);
                $avatarStmt->execute();
                $account = $avatarStmt->get_result()->fetch_assoc();
                if (!$account) {
                    throw new InvalidArgumentException('User পাওয়া যায়নি অথবা সুপার অ্যাডমিন অ্যাকাউন্ট মুছতে পারবেন না।');
                }

                $stmt = $conn->prepare(
                    'DELETE a FROM admins a
                     JOIN admin_roles r ON r.id = a.role_id
                     WHERE a.id = ? AND r.is_super_admin = 0'
                );
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                if ($stmt->affected_rows !== 1) {
                    throw new InvalidArgumentException('User পাওয়া যায়নি অথবা সুপার অ্যাডমিন অ্যাকাউন্ট মুছতে পারবেন না।');
                }
                if (!empty($account['profile_image'])) {
                    $avatarPath = __DIR__ . '/../assets/images/admins/' . basename($account['profile_image']);
                    if (is_file($avatarPath) && !unlink($avatarPath)) {
                        error_log('Unable to remove profile image for deleted admin id ' . $userId);
                    }
                }
                $message = 'User অ্যাকাউন্ট মুছে ফেলা হয়েছে।';
                $messageType = 'success';
            } else {
                throw new InvalidArgumentException('অবৈধ user management action।');
            }
        } catch (InvalidArgumentException $exception) {
            $message = $exception->getMessage();
            $messageType = 'warning';
        } catch (mysqli_sql_exception $exception) {
            if ((int)$exception->getCode() === 1062) {
                $message = 'এই username ইতিমধ্যে ব্যবহার করা হয়েছে।';
                $messageType = 'warning';
            } else {
                error_log('Admin user management failed: ' . $exception->getMessage());
                $message = 'User পরিবর্তন সংরক্ষণ করা যায়নি। পরে আবার চেষ্টা করুন।';
                $messageType = 'danger';
            }
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
            $messageType = 'warning';
        } catch (Throwable $exception) {
            error_log('Admin user management failed: ' . $exception->getMessage());
            $message = 'User পরিবর্তন সংরক্ষণ করা যায়নি। পরে আবার চেষ্টা করুন।';
            $messageType = 'danger';
        }
    }
}

$rolesResult = $conn->query(
    'SELECT id, role_key, role_name, permissions_json, is_super_admin
     FROM admin_roles ORDER BY id'
);
$roles = [];
$roleDescriptions = [];
while ($role = $rolesResult->fetch_assoc()) {
    $roleLabel = admin_role_label($role['role_key']);
    if ($role['role_name'] !== $roleLabel) {
        $repairStmt = $conn->prepare('UPDATE admin_roles SET role_name = ? WHERE role_key = ?');
        $repairStmt->bind_param('ss', $roleLabel, $role['role_key']);
        $repairStmt->execute();
    }
    $role['role_name'] = $roleLabel;
    if ((int)$role['is_super_admin'] === 1) {
        continue;
    }
    $role['permissions'] = json_decode($role['permissions_json'], true) ?: [];
    $roles[] = $role;
    $roleDescriptions[$role['role_key']] = implode(
        ' · ',
        array_map(
            static fn(string $permission): string => $permissionNames[$permission] ?? $permission,
            $role['permissions']
        )
    );
}

$usersResult = $conn->query(
    'SELECT a.id, a.username, a.full_name, a.email, a.profile_image, a.is_active, a.created_at,
            r.role_key, r.role_name, r.is_super_admin
     FROM admins a
     JOIN admin_roles r ON r.id = a.role_id
     ORDER BY r.is_super_admin DESC, a.id ASC'
);
$currentProfileStmt = $conn->prepare('SELECT profile_image FROM admins WHERE id = ?');
$currentProfileStmt->bind_param('i', $currentAdminId);
$currentProfileStmt->execute();
$currentProfile = $currentProfileStmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইউজার ব্যবস্থাপনা - বিজ্ঞান জগৎ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-layout.css?v=6">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <style>
        body { background: #f5f5f5; }
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar a { display: block; padding: .65rem .8rem; margin-bottom: .3rem; border-radius: .4rem; color: #fff; text-decoration: none; }
        .sidebar a:hover, .sidebar a.active { background: #495057; }
        .permission-list { max-width: 32rem; white-space: normal; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row admin-layout">
        <aside class="col-md-3 col-lg-2 sidebar admin-sidebar p-3 text-white">
            <h4 class="text-center border-bottom pb-3 mb-4">বিজ্ঞান জগৎ</h4>
            <div class="text-center mb-4">
                <?php require __DIR__ . '/../includes/admin_profile_avatar.php'; ?>
                <h5><?php echo htmlspecialchars($_SESSION['admin_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                <small>সুপার অ্যাডমিন</small>
            </div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড</a>
                <a href="add_news.php"><i class="fas fa-plus-circle"></i> নিউজ যোগ করুন</a>
                <a href="manage_news.php"><i class="fas fa-newspaper"></i> নিউজ ব্যবস্থাপনা</a>
                <a href="categories.php"><i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা</a>
                <a href="messages.php"><i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা</a>
                <a href="subscribers.php"><i class="fas fa-users"></i> সাবস্ক্রাইবার</a>
                <a href="settings.php"><i class="fas fa-cog"></i> সেটিংস</a>
                <a href="users.php" class="active"><i class="fas fa-user-cog"></i> ইউজার ব্যবস্থাপনা</a>
                <a href="logs.php"><i class="fas fa-clipboard-list"></i> সিস্টেম লগস</a>
                <a href="phpinfo.php"><i class="fas fa-info-circle"></i> PHP ইনফো</a>
                <a href="documentation.php"><i class="fas fa-book"></i> ডকুমেন্টেশন</a>
                <a href="logout.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> লগআউট</a>
            </nav>
        </aside>

        <main class="col-md-9 col-lg-10 px-md-4 py-4 admin-content">
            <h1 class="h3 mb-4">ইউজার ও Role ব্যবস্থাপনা</h1>
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <section class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h2 class="h5 mb-0"><i class="fas fa-user-circle"></i> আমার প্রোফাইল ছবি</h2>
                </div>
                <div class="card-body">
                    <form id="ownProfileImageForm" method="post" enctype="multipart/form-data" class="row align-items-center g-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="user_action" value="update_own_profile_image">
                        <div class="col-auto">
                            <?php if (!empty($currentProfile['profile_image'])): ?>
                                <img
                                    id="own_profile_image_preview"
                                    src="../assets/images/admins/<?php echo rawurlencode(basename($currentProfile['profile_image'])); ?>"
                                    alt="আপনার বর্তমান প্রোফাইল ছবি"
                                    class="rounded-circle"
                                    width="88"
                                    height="88"
                                    style="object-fit: cover;"
                                >
                                <span id="own_profile_image_placeholder" class="rounded-circle bg-secondary text-white d-none justify-content-center align-items-center" style="width: 88px; height: 88px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </span>
                            <?php else: ?>
                                <img id="own_profile_image_preview" alt="আপনার প্রোফাইল ছবির preview" class="rounded-circle d-none" width="88" height="88" style="object-fit: cover;">
                                <span id="own_profile_image_placeholder" class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center" style="width: 88px; height: 88px;">
                                    <i class="fas fa-user fa-2x"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md">
                            <label class="form-label" for="own_profile_image">আপনার ছবি নির্বাচন করুন</label>
                            <input class="form-control" type="file" id="own_profile_image" name="own_profile_image" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="form-text">JPG, PNG, GIF বা WebP; সর্বোচ্চ ২ MB। নতুন ছবি না দিলে বর্তমান ছবি অপরিবর্তিত থাকবে।</div>
                        </div>
                        <div class="col-12">
                            <?php if (!empty($currentProfile['profile_image'])): ?>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="remove_own_profile_image" name="remove_own_profile_image" value="1">
                                    <label class="form-check-label" for="remove_own_profile_image">আমার বর্তমান ছবি সরান</label>
                                </div>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> প্রোফাইল ছবি সংরক্ষণ
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-xl-4">
                    <section class="card shadow-sm">
                        <div class="card-header bg-primary text-white"><h2 class="h5 mb-0">নতুন user তৈরি করুন</h2></div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="user_action" value="create">
                                <div class="mb-3">
                                    <label class="form-label" for="username">Username</label>
                                    <input class="form-control" id="username" name="username" minlength="3" maxlength="50" pattern="[A-Za-z0-9._-]+" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="full_name">পূর্ণ নাম</label>
                                    <input class="form-control" id="full_name" name="full_name" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="email">ইমেইল</label>
                                    <input class="form-control" type="email" id="email" name="email" maxlength="100" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="profile_image">প্রোফাইল ছবি <span class="text-muted">(ঐচ্ছিক)</span></label>
                                    <input class="form-control" type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="form-text">JPG, PNG, GIF বা WebP; সর্বোচ্চ ২ MB।</div>
                                    <img id="profile_image_preview" class="mt-2 rounded-circle d-none" alt="নির্বাচিত প্রোফাইল ছবির preview" width="96" height="96" style="object-fit: cover;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="password">প্রাথমিক পাসওয়ার্ড</label>
                                    <input class="form-control" type="password" id="password" name="password" minlength="10" autocomplete="new-password" required>
                                    <div class="form-text">কমপক্ষে ১০ অক্ষর।</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="role_id">Role / অনুমতি</label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <option value="">Role নির্বাচন করুন</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo (int)$role['id']; ?>"><?php echo htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button class="btn btn-success w-100" type="submit"><i class="fas fa-user-plus"></i> User তৈরি করুন</button>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-xl-8">
                    <section class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white"><h2 class="h5 mb-0">Role templates ও তাদের অনুমতি</h2></div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 align-middle">
                                <thead><tr><th>Role</th><th>ক্ষমতা</th></tr></thead>
                                <tbody>
                                <?php foreach ($roles as $role): ?>
                                    <tr>
                                        <th><?php echo htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8'); ?></th>
                                        <td class="permission-list"><?php echo htmlspecialchars($roleDescriptions[$role['role_key']], ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer small text-muted">সুপার অ্যাডমিন user/role ব্যবস্থাপনা ও সব site permission-সহ সংরক্ষিত; অন্য user-কে এই role দেওয়া যাবে না।</div>
                    </section>

                    <section class="card shadow-sm">
                        <div class="card-header bg-primary text-white"><h2 class="h5 mb-0">সকল user</h2></div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>নাম / Username</th><th>Role</th><th>অবস্থা</th><th>পরিচালনা</th></tr></thead>
                                <tbody>
                                <?php while ($user = $usersResult->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="../assets/images/admins/<?php echo rawurlencode(basename($user['profile_image'])); ?>" alt="" class="rounded-circle me-2" width="44" height="44" style="object-fit: cover;">
                                            <?php else: ?>
                                                <span class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center me-2" style="width: 44px; height: 44px;">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                            <small><?php echo htmlspecialchars($user['username'] . ' · ' . $user['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars(admin_role_label($user['role_key']), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($user['is_super_admin']): ?>
                                                <span class="badge bg-danger">সুপার অ্যাডমিন</span>
                                            <?php elseif ($user['is_active']): ?>
                                                <span class="badge bg-success">সক্রিয়</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">নিষ্ক্রিয়</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$user['is_super_admin']): ?>
                                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#editUserModal"
                                                        data-id="<?php echo (int)$user['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        data-image="<?php echo htmlspecialchars(
                                                            !empty($user['profile_image'])
                                                                ? '../assets/images/admins/' . rawurlencode(basename($user['profile_image']))
                                                                : '',
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ); ?>"
                                                        data-role="<?php echo (int)array_reduce($roles, static fn($found, $role) => $role['role_key'] === $user['role_key'] ? $role['id'] : $found, 0); ?>"
                                                        data-active="<?php echo (int)$user['is_active']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="post" class="d-inline" onsubmit="return confirm('এই user account স্থায়ীভাবে মুছে ফেলবেন?')">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="user_action" value="delete">
                                                    <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-trash"></i></button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">সুরক্ষিত account</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserTitle" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" enctype="multipart/form-data">
            <div class="modal-header bg-primary text-white">
                <h2 class="modal-title h5" id="editUserTitle">User সম্পাদনা</h2>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="user_action" value="update">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="mb-3">
                    <label class="form-label" for="edit_profile_image">প্রোফাইল ছবি</label>
                    <div class="mb-2">
                        <img id="edit_profile_image_preview" class="rounded-circle d-none" alt="বর্তমান প্রোফাইল ছবি" width="96" height="96" style="object-fit: cover;">
                        <span id="edit_profile_image_placeholder" class="rounded-circle bg-secondary text-white d-inline-flex justify-content-center align-items-center" style="width: 96px; height: 96px;">
                            <i class="fas fa-user fa-2x"></i>
                        </span>
                    </div>
                    <input class="form-control" type="file" id="edit_profile_image" name="edit_profile_image" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="form-text">JPG, PNG, GIF বা WebP; সর্বোচ্চ ২ MB। নতুন ছবি বাছলে বর্তমান ছবিটি বদলে যাবে।</div>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remove_profile_image" name="remove_profile_image" value="1">
                    <label class="form-check-label" for="remove_profile_image">বর্তমান প্রোফাইল ছবি সরান</label>
                </div>
                <div class="mb-3"><label class="form-label" for="edit_full_name">পূর্ণ নাম</label><input class="form-control" id="edit_full_name" name="full_name" maxlength="100" required></div>
                <div class="mb-3"><label class="form-label" for="edit_email">ইমেইল</label><input class="form-control" type="email" id="edit_email" name="email" maxlength="100" required></div>
                <div class="mb-3">
                    <label class="form-label" for="edit_role_id">Role / অনুমতি</label>
                    <select class="form-select" id="edit_role_id" name="role_id" required>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo (int)$role['id']; ?>"><?php echo htmlspecialchars($role['role_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label" for="new_password">নতুন পাসওয়ার্ড <small>(ঐচ্ছিক)</small></label><input class="form-control" type="password" id="new_password" name="new_password" minlength="10" autocomplete="new-password"></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1"><label class="form-check-label" for="edit_is_active">এই user সক্রিয়</label></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="btn btn-primary">পরিবর্তন সংরক্ষণ</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('profile_image').addEventListener('change', function () {
    const preview = document.getElementById('profile_image_preview');
    const file = this.files && this.files[0];
    if (!file) {
        preview.removeAttribute('src');
        preview.classList.add('d-none');
        return;
    }
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('d-none');
});

const editImageInput = document.getElementById('edit_profile_image');
const editImagePreview = document.getElementById('edit_profile_image_preview');
const editImagePlaceholder = document.getElementById('edit_profile_image_placeholder');
const removeImageCheckbox = document.getElementById('remove_profile_image');
const ownImageInput = document.getElementById('own_profile_image');
const ownImagePreview = document.getElementById('own_profile_image_preview');
const ownImagePlaceholder = document.getElementById('own_profile_image_placeholder');
const removeOwnImageCheckbox = document.getElementById('remove_own_profile_image');

ownImageInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) {
        return;
    }

    if (removeOwnImageCheckbox) {
        removeOwnImageCheckbox.checked = false;
    }
    ownImagePreview.src = URL.createObjectURL(file);
    ownImagePreview.classList.remove('d-none');
    ownImagePlaceholder.classList.add('d-none');
});

if (removeOwnImageCheckbox) {
    removeOwnImageCheckbox.addEventListener('change', function () {
        if (this.checked) {
            ownImageInput.value = '';
            ownImagePreview.removeAttribute('src');
            ownImagePreview.classList.add('d-none');
            ownImagePlaceholder.classList.remove('d-none');
        }
    });
}

editImageInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) {
        return;
    }

    removeImageCheckbox.checked = false;
    editImagePreview.src = URL.createObjectURL(file);
    editImagePreview.classList.remove('d-none');
    editImagePlaceholder.classList.add('d-none');
});

removeImageCheckbox.addEventListener('change', function () {
    if (this.checked) {
        editImageInput.value = '';
        editImagePreview.removeAttribute('src');
        editImagePreview.classList.add('d-none');
        editImagePlaceholder.classList.remove('d-none');
    }
});

document.getElementById('editUserModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('edit_user_id').value = button.dataset.id;
    document.getElementById('edit_full_name').value = button.dataset.name;
    document.getElementById('edit_email').value = button.dataset.email;
    document.getElementById('edit_role_id').value = button.dataset.role;
    document.getElementById('edit_is_active').checked = button.dataset.active === '1';
    editImageInput.value = '';
    removeImageCheckbox.checked = false;

    if (button.dataset.image) {
        editImagePreview.src = button.dataset.image;
        editImagePreview.classList.remove('d-none');
        editImagePlaceholder.classList.add('d-none');
    } else {
        editImagePreview.removeAttribute('src');
        editImagePreview.classList.add('d-none');
        editImagePlaceholder.classList.remove('d-none');
    }
});
</script>
</body>
</html>
