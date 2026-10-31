<?php
require_once '../includes/admin_auth.php';
require_admin_permission('subscribers_view');

$limit = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$count_result = $conn->query('SELECT COUNT(*) AS total FROM subscribers');
$total_rows = (int)$count_result->fetch_assoc()['total'];
$total_pages = (int)ceil($total_rows / $limit);
$page = $total_pages > 0 ? min($page, $total_pages) : 1;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare(
    'SELECT id, name, email, subscription_date, is_active, confirmed
     FROM subscribers
     ORDER BY subscription_date DESC
     LIMIT ? OFFSET ?'
);
$stmt->bind_param('ii', $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সাবস্ক্রাইবার - বিজ্ঞান জগৎ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-layout.css?v=6">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <style>
        body { background-color: #f5f5f5; }
        .sidebar { background-color: #343a40; min-height: 100vh; color: white; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px 15px; border-radius: 5px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row admin-layout">
            <aside class="col-md-3 col-lg-2 sidebar admin-sidebar p-3">
                <h4 class="text-center mb-4 border-bottom pb-3">
                    <i class="fas fa-newspaper"></i> বিজ্ঞান জগৎ
                </h4>
                <div class="text-center mb-4">
                    <?php require __DIR__ . '/../includes/admin_profile_avatar.php'; ?>
                    <h5><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'অ্যাডমিন', ENT_QUOTES, 'UTF-8'); ?></h5>
                    <small><?php echo htmlspecialchars($_SESSION['admin_role_name'] ?? 'অ্যাডমিন', ENT_QUOTES, 'UTF-8'); ?></small>
                </div>
                <nav class="nav flex-column">
                    <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড</a>
                    <a href="add_news.php"><i class="fas fa-plus-circle"></i> নিউজ যোগ করুন</a>
                    <a href="manage_news.php"><i class="fas fa-newspaper"></i> নিউজ ব্যবস্থাপনা</a>
                    <a href="categories.php"><i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা</a>
                    <a href="messages.php"><i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা</a>
                    <a href="subscribers.php" class="active"><i class="fas fa-users"></i> সাবস্ক্রাইবার</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> সেটিংস</a>
                    <a href="users.php"><i class="fas fa-user-cog"></i> ইউজার ব্যবস্থাপনা</a>
                    <a href="logs.php"><i class="fas fa-clipboard-list"></i> সিস্টেম লগস</a>
                    <a href="phpinfo.php"><i class="fas fa-info-circle"></i> PHP ইনফো</a>
                    <a href="documentation.php"><i class="fas fa-book"></i> ডকুমেন্টেশন</a>
                    <a href="logout.php" class="text-warning"><i class="fas fa-sign-out-alt"></i> লগআউট</a>
                </nav>
            </aside>

            <main class="col-md-9 col-lg-10 px-md-4 py-4 admin-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">সাবস্ক্রাইবার তালিকা</h1>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">ড্যাশবোর্ডে ফিরুন</a>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">মোট সাবস্ক্রাইবার: <?php echo $total_rows; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>নাম</th>
                                        <th>ইমেইল</th>
                                        <th>তারিখ</th>
                                        <th>স্ট্যাটাস</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($total_rows === 0): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">এখনও কেউ সাবস্ক্রাইব করেননি।</td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo (int)$row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['name'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['subscription_date'])); ?></td>
                                        <td>
                                            <?php if ($row['confirmed']): ?>
                                                <span class="badge bg-success">কনফার্মড</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">পেন্ডিং</span>
                                            <?php endif; ?>
                                            <?php if ($row['is_active']): ?>
                                                <span class="badge bg-primary">এক্টিভ</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">ইনএক্টিভ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="সাবস্ক্রাইবার পৃষ্ঠা">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>