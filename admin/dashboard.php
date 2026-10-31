<?php
require_once '../includes/admin_auth.php';
require_admin_permission('dashboard_view');

$canViewNews = admin_has_permission('news_view');
$canAddNews = admin_has_permission('news_add');
$canManageCategories = admin_has_permission('category_manage');
$canManageMessages = admin_has_permission('messages_manage');
$canViewSubscribers = admin_has_permission('subscribers_view');

$total_news = $canViewNews ? $conn->query("SELECT COUNT(*) as count FROM news")->fetch_assoc()['count'] : 0;
$published_news = $canViewNews ? $conn->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'")->fetch_assoc()['count'] : 0;
$total_categories = $canManageCategories ? $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'] : 0;
$total_messages = $canManageMessages ? $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")->fetch_assoc()['count'] : 0;
$total_subscribers = $canViewSubscribers ? $conn->query("SELECT COUNT(*) as count FROM subscribers")->fetch_assoc()['count'] : 0;
$recent_news = $canViewNews ? $conn->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 5") : null;
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন ড্যাশবোর্ড - বিজ্ঞান জগৎ</title>
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
        .stat-card {
            border-radius: 10px;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
        }
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .bg-news { background: linear-gradient(45deg, #FF416C, #FF4B2B); }
        .bg-category { background: linear-gradient(45deg, #36D1DC, #5B86E5); }
        .bg-published { background: linear-gradient(45deg, #00b09b, #96c93d); }
        .bg-messages { background: linear-gradient(45deg, #8E2DE2, #4A00E0); }
        .bg-subscribers { background: linear-gradient(45deg, #11998e, #38ef7d); }
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
                        <h5><?php echo htmlspecialchars($_SESSION['admin_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <small><?php echo htmlspecialchars($_SESSION['admin_role_name'] ?? 'অ্যাডমিন', ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>

                    <nav class="nav flex-column">
                        <a href="dashboard.php" class="active">
                            <i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড
                        </a>
                        <?php if ($canAddNews): ?>
                        <a href="add_news.php">
                            <i class="fas fa-plus-circle"></i> নিউজ যোগ করুন
                        </a>
                        <?php endif; ?>
                        <?php if ($canViewNews): ?>
                        <a href="manage_news.php">
                            <i class="fas fa-newspaper"></i> নিউজ ব্যবস্থাপনা
                        </a>
                        <?php endif; ?>
                        <?php if ($canManageCategories): ?>
                        <a href="categories.php">
                            <i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা
                        </a>
                        <?php endif; ?>
                        <?php if ($canManageMessages): ?>
                        <a href="messages.php">
                            <i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা
                            <?php if($total_messages > 0): ?>
                            <span class="badge bg-danger float-end"><?php echo $total_messages; ?></span>
                            <?php endif; ?>
                        </a>
                        <?php endif; ?>
                        <?php if ($canViewSubscribers): ?>
                        <a href="subscribers.php">
                            <i class="fas fa-users"></i> সাবস্ক্রাইবার
                        </a>
                        <?php endif; ?>
                        <?php if (admin_has_permission('settings_manage')): ?>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i> সেটিংস
                        </a>
                        <?php endif; ?>
                        <?php if (admin_has_permission('users_manage')): ?>
                        <a href="users.php">
                            <i class="fas fa-user-cog"></i> ইউজার ব্যবস্থাপনা
                        </a>
                        <?php endif; ?>
                        <?php if (admin_has_permission('logs_view')): ?>
                        <a href="logs.php">
                            <i class="fas fa-clipboard-list"></i> সিস্টেম লগ
                        </a>
                        <?php endif; ?>
                        <a href="phpinfo.php">
                            <i class="fas fa-info-circle"></i> PHP ইনফো
                        </a>
                        <a href="documentation.php">
                            <i class="fas fa-book"></i> ডকুমেন্টেশন
                        </a>
                        <?php if (admin_has_permission('backup_manage')): ?>
                        <a href="settings.php?open_backup=1">
                            <i class="fas fa-database"></i> ডাটাবেস ব্যাকআপ
                        </a>
                        <?php endif; ?>
                
                        <a href="logout.php" class="text-warning">
                            <i class="fas fa-sign-out-alt"></i> লগআউট
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 admin-content">
                <!-- Top Bar -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 bangla-font">ড্যাশবোর্ড</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-external-link-alt"></i> ওয়েবসাইট দেখুন
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <?php if ($canViewNews): ?>
                    <div class="col-md-4 col-xl">
                        <div class="stat-card bg-news">
                            <i class="fas fa-newspaper"></i>
                            <h3><?php echo $total_news; ?></h3>
                            <p class="mb-0 bangla-font">মোট নিউজ</p>
                        </div>
                    </div>
                    <div class="col-md-4 col-xl">
                        <div class="stat-card bg-published">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $published_news; ?></h3>
                            <p class="mb-0 bangla-font">প্রকাশিত নিউজ</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($canManageCategories): ?>
                    <div class="col-md-4 col-xl">
                        <div class="stat-card bg-category">
                            <i class="fas fa-list-alt"></i>
                            <h3><?php echo $total_categories; ?></h3>
                            <p class="mb-0 bangla-font">ক্যাটেগরি</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($canManageMessages): ?>
                    <div class="col-md-4 col-xl">
                        <div class="stat-card bg-messages">
                            <i class="fas fa-envelope"></i>
                            <h3><?php echo $total_messages; ?></h3>
                            <p class="mb-0 bangla-font">নতুন বার্তা</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($canViewSubscribers): ?>
                    <div class="col-md-4 col-xl">
                        <a href="subscribers.php" class="text-decoration-none">
                            <div class="stat-card bg-subscribers">
                                <i class="fas fa-users"></i>
                                <h3><?php echo $total_subscribers; ?></h3>
                                <p class="mb-0 bangla-font">সাবস্ক্রাইবার</p>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Recent News -->
                <?php if ($canViewNews): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0 bangla-font">সর্বশেষ নিউজ</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="bangla-font">শিরোনাম</th>
                                                <th class="bangla-font">তারিখ</th>
                                                <th class="bangla-font">স্ট্যাটাস</th>
                                                <th class="bangla-font">অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($recent_news && $recent_news->num_rows > 0): ?>
                                                <?php while($news = $recent_news->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="bangla-font"><?php echo htmlspecialchars(function_exists('mb_substr') ? mb_substr($news['title'], 0, 50) : substr($news['title'], 0, 50), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>...</td>
                                                    <td class="bangla-font"><?php echo date('d/m/Y', strtotime($news['created_at'])); ?></td>
                                                    <td>
                                                        <?php if($news['status'] == 'published'): ?>
                                                            <span class="badge bg-success bangla-font">প্রকাশিত</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning bangla-font">খসড়া</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (admin_has_permission('news_edit')): ?>
                                                        <a href="edit_news.php?id=<?php echo $news['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center bangla-font">কোন নিউজ পাওয়া যায়নি</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="manage_news.php" class="btn btn-primary bangla-font">সব নিউজ দেখুন</a>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0 bangla-font">দ্রুত অ্যাকশন</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <?php if ($canAddNews): ?>
                                    <a href="add_news.php" class="btn btn-danger bangla-font">
                                        <i class="fas fa-plus"></i> নতুন নিউজ যোগ করুন
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($canManageCategories): ?>
                                    <a href="categories.php" class="btn btn-info bangla-font">
                                        <i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($canManageMessages): ?>
                                    <a href="messages.php" class="btn btn-warning bangla-font">
                                        <i class="fas fa-envelope"></i> বার্তা দেখুন
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (admin_has_permission('settings_manage')): ?>
                        <!-- System Info -->
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0 bangla-font">সিস্টেম তথ্য</h5>
                            </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2 bangla-font">
                                        <i class="fas fa-server"></i> সার্ভার: <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                                    </li>
                                    <li class="mb-2 bangla-font">
                                        <i class="fas fa-code"></i> PHP: <?php echo phpversion(); ?>
                                    </li>
                                    <li class="mb-2 bangla-font">
                                        <i class="fas fa-database"></i> MySQL: <?php echo $conn->server_info; ?>
                                    </li>
                                    <li class="bangla-font">
                                        <i class="fas fa-calendar-alt"></i> তারিখ: <?php echo date('d/m/Y H:i:s'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dashboard scripts
        document.addEventListener('DOMContentLoaded', function() {
            // Auto refresh every 60 seconds
            setTimeout(function() {
                window.location.reload();
            }, 60000);
        });
    </script>
</body>
</html>