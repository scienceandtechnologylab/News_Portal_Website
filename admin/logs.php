<?php
require_once '../includes/admin_auth.php';
require_admin_permission('logs_view');

// Create logs table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_type ENUM('info', 'warning', 'error', 'security', 'login', 'news', 'message') DEFAULT 'info',
    module VARCHAR(100),
    action VARCHAR(255),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    admin_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_type (log_type),
    INDEX idx_created_at (created_at),
    INDEX idx_module (module),
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
)");

// Handle log actions
$message = '';
$message_type = '';

if(isset($_GET['action'])) {
    $action = $_GET['action'];

    if($action == 'clear_logs') {
        // Clear old logs (keep last 1000)
        $conn->query("DELETE FROM system_logs WHERE id NOT IN (SELECT id FROM (SELECT id FROM system_logs ORDER BY id DESC LIMIT 1000) as temp)");

        // Log this action
        log_action('system', 'লগস ক্লিয়ার করা হয়েছে', 'লগ ব্যবস্থাপনা', $_SESSION['admin_id']);

        $message = 'পুরানো লগস সফলভাবে ক্লিয়ার করা হয়েছে (শেষের ১০০০টি রেখে দেওয়া হয়েছে)';
        $message_type = 'success';

    } elseif($action == 'export_logs') {
        // Export logs to CSV
        export_logs_to_csv();
        exit();

    } elseif($action == 'delete_all_logs') {
        // Delete all logs (with confirmation)
        if(isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
            $conn->query("DELETE FROM system_logs");

            // Log this action
            log_action('system', 'সমস্ত লগস মুছে ফেলা হয়েছে', 'লগ ব্যবস্থাপনা', $_SESSION['admin_id']);

            $message = 'সমস্ত লগস সফলভাবে মুছে ফেলা হয়েছে';
            $message_type = 'success';
        }
    }
}

// Filter parameters
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filter_module = isset($_GET['module']) ? $_GET['module'] : 'all';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build query conditions
$conditions = [];
if($filter_type !== 'all') {
    $conditions[] = "log_type = '$filter_type'";
}

if($filter_module !== 'all') {
    $conditions[] = "module = '$filter_module'";
}

if(!empty($filter_date)) {
    $conditions[] = "DATE(created_at) = '$filter_date'";
}

if(!empty($search_term)) {
    $conditions[] = "(action LIKE '%$search_term%' OR description LIKE '%$search_term%' OR ip_address LIKE '%$search_term%')";
}

$where_clause = '';
if(!empty($conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $conditions);
}

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Get total logs count
$count_query = "SELECT COUNT(*) as total FROM system_logs $where_clause";
$count_result = $conn->query($count_query);
$total_logs = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_logs / $limit);
if($total_pages < 1) $total_pages = 1;
if($page > $total_pages) $page = $total_pages;

// Get logs with pagination
$logs_query = "SELECT l.*, a.username as admin_username, a.full_name as admin_name
               FROM system_logs l
               LEFT JOIN admins a ON l.admin_id = a.id
               $where_clause
               ORDER BY l.created_at DESC
               LIMIT $limit OFFSET $offset";
$logs_result = $conn->query($logs_query);

// Get unique modules for filter
$modules_query = $conn->query("SELECT DISTINCT module FROM system_logs WHERE module IS NOT NULL AND module != '' ORDER BY module");
$modules = [];
while($row = $modules_query->fetch_assoc()) {
    $modules[] = $row['module'];
}

// Statistics
$stats = [
    'total' => $total_logs,
    'today' => $conn->query("SELECT COUNT(*) as count FROM system_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'],
    'errors' => $conn->query("SELECT COUNT(*) as count FROM system_logs WHERE log_type = 'error'")->fetch_assoc()['count'],
    'security' => $conn->query("SELECT COUNT(*) as count FROM system_logs WHERE log_type = 'security'")->fetch_assoc()['count'],
    'logins' => $conn->query("SELECT COUNT(*) as count FROM system_logs WHERE log_type = 'login'")->fetch_assoc()['count'],
];

// Log viewing of logs
log_action('system', 'লগস পেজ ভিজিট করা হয়েছে', 'লগ ব্যবস্থাপনা', $_SESSION['admin_id']);
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সিস্টেম লগস - বিজ্ঞান জগৎ</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-layout.css?v=6">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
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
        .log-type-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 12px;
        }
        .log-info { background-color: #d1ecf1; color: #0c5460; }
        .log-warning { background-color: #fff3cd; color: #856404; }
        .log-error { background-color: #f8d7da; color: #721c24; }
        .log-security { background-color: #cce5ff; color: #004085; }
        .log-login { background-color: #d4edda; color: #155724; }
        .log-news { background-color: #e2e3e5; color: #383d41; }
        .log-message { background-color: #d1ecf1; color: #0c5460; }
        .log-row:hover {
            background-color: #f8f9fa;
        }
        .log-details {
            display: none;
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        .log-details.show {
            display: block;
        }
        .stats-card {
            border: none;
            border-radius: 10px;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-total { background: linear-gradient(45deg, #007bff, #6610f2); }
        .stats-today { background: linear-gradient(45deg, #28a745, #20c997); }
        .stats-error { background: linear-gradient(45deg, #dc3545, #fd7e14); }
        .stats-security { background: linear-gradient(45deg, #6f42c1, #e83e8c); }
        .datepicker {
            z-index: 10000 !important;
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
                        <h5><?php echo isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'অ্যাডমিন'; ?></h5>
                        <small>সুপার অ্যাডমিন</small>
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
                        <a href="settings.php">
                            <i class="fas fa-cog"></i> সেটিংস
                        </a>
                        <a href="users.php">
                            <i class="fas fa-user-cog"></i> ইউজার ব্যবস্থাপনা
                        </a>
                        <a href="logs.php" class="active">
                            <i class="fas fa-clipboard-list"></i> সিস্টেম লগস
                        </a>
                        <a href="phpinfo.php" target="_blank">
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
                    <h1 class="h2 bangla-font"><i class="fas fa-clipboard-list"></i> সিস্টেম লগস</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                                <i class="fas fa-broom"></i> লগস ক্লিয়ার
                            </button>
                            <a href="?action=export_logs" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download"></i> এক্সপোর্ট
                            </a>
                        </div>
                    </div>
                </div>

                <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-2 col-6">
                        <div class="stats-card stats-total">
                            <h5>মোট লগ</h5>
                            <h2><?php echo number_format($stats['total']); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="stats-card stats-today">
                            <h5>আজকের লগ</h5>
                            <h2><?php echo number_format($stats['today']); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="stats-card stats-error">
                            <h5>এরর লগ</h5>
                            <h2><?php echo number_format($stats['errors']); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="stats-card stats-security">
                            <h5>সিকিউরিটি</h5>
                            <h2><?php echo number_format($stats['security']); ?></h2>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5>লগিন লগ</h5>
                                <h2 class="text-dark"><?php echo number_format($stats['logins']); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5>লগ সাইজ</h5>
                                <h2 class="text-dark">
                                    <?php
                                    $size_query = $conn->query("
                                        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
                                        FROM information_schema.TABLES
                                        WHERE table_schema = DATABASE() AND table_name = 'system_logs'
                                    ");
                                    echo $size_query->fetch_assoc()['size_mb'] ?? '0.00';
                                    ?> MB
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 bangla-font"><i class="fas fa-filter"></i> ফিল্টার</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label bangla-font">লগ টাইপ</label>
                                <select class="form-select" name="type">
                                    <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>সব টাইপ</option>
                                    <option value="info" <?php echo $filter_type === 'info' ? 'selected' : ''; ?>>তথ্য</option>
                                    <option value="warning" <?php echo $filter_type === 'warning' ? 'selected' : ''; ?>>সতর্কতা</option>
                                    <option value="error" <?php echo $filter_type === 'error' ? 'selected' : ''; ?>>এরর</option>
                                    <option value="security" <?php echo $filter_type === 'security' ? 'selected' : ''; ?>>সিকিউরিটি</option>
                                    <option value="login" <?php echo $filter_type === 'login' ? 'selected' : ''; ?>>লগিন</option>
                                    <option value="news" <?php echo $filter_type === 'news' ? 'selected' : ''; ?>>নিউজ</option>
                                    <option value="message" <?php echo $filter_type === 'message' ? 'selected' : ''; ?>>বার্তা</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label bangla-font">মডিউল</label>
                                <select class="form-select" name="module">
                                    <option value="all" <?php echo $filter_module === 'all' ? 'selected' : ''; ?>>সব মডিউল</option>
                                    <?php foreach($modules as $module): ?>
                                    <option value="<?php echo htmlspecialchars($module); ?>" <?php echo $filter_module === $module ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($module); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label bangla-font">তারিখ</label>
                                <input type="text" class="form-control datepicker" name="date"
                                       value="<?php echo htmlspecialchars($filter_date); ?>"
                                       placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label bangla-font">সার্চ</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                           value="<?php echo htmlspecialchars($search_term); ?>"
                                           placeholder="লগ খুঁজুন...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if(!empty($filter_type) || !empty($filter_module) || !empty($filter_date) || !empty($search_term)): ?>
                                    <a href="logs.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 bangla-font"><i class="fas fa-list"></i> লগ তালিকা</h5>
                            <span class="badge bg-light text-dark">
                                <?php echo number_format($total_logs); ?> টি লগ
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if($logs_result && $logs_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="50">#</th>
                                            <th width="100">টাইপ</th>
                                            <th width="120">মডিউল</th>
                                            <th>অ্যাকশন</th>
                                            <th width="150">অ্যাডমিন</th>
                                            <th width="150">আইপি</th>
                                            <th width="180">সময়</th>
                                            <th width="80">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $counter = ($page - 1) * $limit + 1; ?>
                                        <?php while($log = $logs_result->fetch_assoc()): ?>
                                        <tr class="log-row">
                                            <td><?php echo $counter; ?></td>
                                            <td>
                                                <span class="log-type-badge log-<?php echo $log['log_type']; ?>">
                                                    <?php
                                                    $type_names = [
                                                        'info' => 'তথ্য',
                                                        'warning' => 'সতর্কতা',
                                                        'error' => 'এরর',
                                                        'security' => 'সিকিউরিটি',
                                                        'login' => 'লগিন',
                                                        'news' => 'নিউজ',
                                                        'message' => 'বার্তা'
                                                    ];
                                                    echo $type_names[$log['log_type']] ?? $log['log_type'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($log['module']): ?>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($log['module']); ?></span>
                                                <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold bangla-font" style="font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </div>
                                                <small class="text-muted bangla-font" style="font-size: 0.8rem;">
                                                    <?php echo substr(htmlspecialchars($log['description']), 0, 100); ?>
                                                    <?php echo strlen($log['description']) > 100 ? '...' : ''; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if($log['admin_name']): ?>
                                                <div class="bangla-font" style="font-size: 0.85rem;">
                                                    <?php echo htmlspecialchars($log['admin_name']); ?>
                                                </div>
                                                <small class="text-muted">@<?php echo htmlspecialchars($log['admin_username']); ?></small>
                                                <?php else: ?>
                                                <span class="text-muted">সিস্টেম</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($log['ip_address']); ?></code>
                                            </td>
                                            <td>
                                                <div class="bangla-font" style="font-size: 0.85rem;">
                                                    <?php echo date('d/m/Y', strtotime($log['created_at'])); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('h:i A', strtotime($log['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info" onclick="toggleLogDetails(<?php echo $log['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Log Details Row -->
                                        <tr id="logDetails<?php echo $log['id']; ?>" class="log-details">
                                            <td colspan="8">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="bangla-font mb-2">বিস্তারিত তথ্য:</h6>
                                                        <p class="bangla-font"><?php echo nl2br(htmlspecialchars($log['description'])); ?></p>

                                                        <h6 class="bangla-font mt-3 mb-2">ইউজার এজেন্ট:</h6>
                                                        <p class="bg-light p-2 rounded" style="font-size: 0.8rem;">
                                                            <?php echo htmlspecialchars($log['user_agent']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="bangla-font mb-2">টেকনিক্যাল তথ্য:</h6>
                                                        <ul class="list-unstyled">
                                                            <li class="mb-1">
                                                                <strong>লগ আইডি:</strong> <?php echo $log['id']; ?>
                                                            </li>
                                                            <li class="mb-1">
                                                                <strong>অ্যাডমিন আইডি:</strong> <?php echo $log['admin_id'] ?: 'N/A'; ?>
                                                            </li>
                                                            <li class="mb-1">
                                                                <strong>রেফারার:</strong>
                                                                <?php
                                                                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A';
                                                                echo htmlspecialchars($referer);
                                                                ?>
                                                            </li>
                                                            <li class="mb-1">
                                                                <strong>রিকোয়েস্ট মেথড:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?>
                                                            </li>
                                                            <li>
                                                                <strong>বর্তমান ইউআরএল:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php $counter++; ?>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="p-3 border-top">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&type=<?php echo $filter_type; ?>&module=<?php echo $filter_module; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search_term); ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>

                                    <?php
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);

                                    for($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&type=<?php echo $filter_type; ?>&module=<?php echo $filter_module; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search_term); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&type=<?php echo $filter_type; ?>&module=<?php echo $filter_module; ?>&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search_term); ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                                <div class="text-center mt-2">
                                    <small class="text-muted bangla-font">
                                        পৃষ্ঠা <?php echo $page; ?> এর <?php echo $total_pages; ?> (মোট <?php echo number_format($total_logs); ?> টি লগ)
                                    </small>
                                </div>
                            </nav>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                                <h5 class="bangla-font text-muted">কোন লগ পাওয়া যায়নি</h5>
                                <p class="bangla-font">সিস্টেম এখনও কোন লগ জেনারেট করেনি</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Log Analysis -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0 bangla-font"><i class="fas fa-chart-pie"></i> লগ ডিস্ট্রিবিউশন</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="logDistributionChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0 bangla-font"><i class="fas fa-chart-line"></i> রিয়েল-টাইম লগ</h5>
                            </div>
                            <div class="card-body">
                                <div class="real-time-logs">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="bangla-font">লাইভ লগ মনিটরিং</span>
                                        <button class="btn btn-sm btn-outline-success" onclick="startLiveLogs()">
                                            <i class="fas fa-play"></i> শুরু করুন
                                        </button>
                                    </div>
                                    <div class="live-log-container bg-dark text-light p-2 rounded" style="height: 150px; overflow-y: auto; font-family: monospace; font-size: 0.8rem;">
                                        <!-- Live logs will appear here -->
                                    </div>
                                    <small class="live-log-status text-muted d-block mt-2" role="status" aria-live="polite">
                                        লাইভ লগ দেখতে শুরু করুন।
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Clear Logs Modal -->
    <div class="modal fade" id="clearLogsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title bangla-font">লগস ক্লিয়ার করুন</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="bangla-font">আপনি কি পুরানো লগস ক্লিয়ার করতে চান?</p>
                    <div class="alert alert-warning bangla-font">
                        <i class="fas fa-exclamation-triangle"></i>
                        এটি শুধুমাত্র পুরানো লগস ক্লিয়ার করবে (শেষের ১০০০টি রেখে দেবে)।
                        সমস্ত লগস মুছে ফেলতে চাইলে নিচের অপশন ব্যবহার করুন।
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <a href="?action=clear_logs" class="btn btn-danger">ক্লিয়ার করুন</a>
                    <a href="?action=delete_all_logs&confirm=yes"
                       class="btn btn-dark"
                       onclick="return confirm('আপনি কি নিশ্চিত? সমস্ত লগস মুছে যাবে। এটি আর undo করা যাবে না।')">
                        সব মুছুন
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Initialize datepicker
        $(document).ready(function(){
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });

        // Toggle log details
        function toggleLogDetails(logId) {
            const detailsElement = document.getElementById('logDetails' + logId);
            detailsElement.classList.toggle('show');
        }

        // Chart for log distribution
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('logDistributionChart').getContext('2d');

            // Fetch log distribution data via AJAX
            fetch('get_log_stats.php')
                .then(response => response.json())
                .then(data => {
                    const chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.values,
                                backgroundColor: [
                                    '#007bff', // info
                                    '#ffc107', // warning
                                    '#dc3545', // error
                                    '#6f42c1', // security
                                    '#28a745', // login
                                    '#6c757d', // news
                                    '#17a2b8'  // message
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            family: "'Noto Sans Bengali', sans-serif"
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
        });

        // Real-time log monitoring
        let liveLogInterval;
        function startLiveLogs() {
            const container = document.querySelector('.live-log-container');
            if (!container) return;

            if(liveLogInterval) {
                clearInterval(liveLogInterval);
            }

            liveLogInterval = setInterval(fetchLiveLogs, 5000);
            fetchLiveLogs();

            const button = document.querySelector('.real-time-logs button');
            button.innerHTML = '<i class="fas fa-stop"></i> বন্ধ করুন';
            button.onclick = stopLiveLogs;
            button.className = 'btn btn-sm btn-outline-danger';
        }

        function stopLiveLogs() {
            if(liveLogInterval) {
                clearInterval(liveLogInterval);
                liveLogInterval = null;
            }
            const button = document.querySelector('.real-time-logs button');
            button.innerHTML = '<i class="fas fa-play"></i> শুরু করুন';
            button.onclick = startLiveLogs;
            button.className = 'btn btn-sm btn-outline-success';
        }

        async function fetchLiveLogs() {
            const container = document.querySelector('.live-log-container');
            const status = document.querySelector('.live-log-status');
            if (!container || !status) return;

            try {
                const response = await fetch('get_live_logs.php', {
                    cache: 'no-store',
                    headers: { Accept: 'application/json' }
                });
                const data = await response.json();
                if (!response.ok || !data.success || !Array.isArray(data.logs)) {
                    throw new Error(data.message || 'লাইভ লগ লোড করা যায়নি।');
                }

                container.replaceChildren();
                if (data.logs.length === 0) {
                    container.textContent = 'এখনও কোনো লগ নেই।';
                } else {
                    data.logs.slice().reverse().forEach(log => {
                        const logElement = document.createElement('div');
                        logElement.className = 'mb-1';

                        const time = document.createElement('span');
                        time.className = 'text-success';
                        time.textContent = `[${log.time}] `;

                        const type = document.createElement('span');
                        type.className = 'text-warning';
                        type.textContent = `${log.type} `;

                        const action = document.createElement('span');
                        action.className = 'text-info';
                        action.textContent = log.action;

                        logElement.append(time, type, action);
                        container.appendChild(logElement);
                    });
                    container.scrollTop = container.scrollHeight;
                }

                status.textContent = `সর্বশেষ আপডেট: ${new Date().toLocaleTimeString('bn-BD')}`;
            } catch (error) {
                console.error('Live log request failed:', error);
                status.textContent = error.message || 'লাইভ লগ লোড করা যায়নি।';
            }
        }

    </script>
</body>
</html>

<?php
// Function to export logs to CSV
function export_logs_to_csv() {
    global $conn, $where_clause;

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=system_logs_' . date('Y-m-d_H-i-s') . '.csv');

    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8
    fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Column headers
    $headers = ['ID', 'টাইপ', 'মডিউল', 'অ্যাকশন', 'বর্ণনা', 'আইপি', 'অ্যাডমিন', 'তারিখ'];
    fputcsv($output, $headers);

    // Get all logs for export
    $export_query = "SELECT l.*, a.username as admin_username
                     FROM system_logs l
                     LEFT JOIN admins a ON l.admin_id = a.id
                     $where_clause
                     ORDER BY l.created_at DESC";
    $export_result = $conn->query($export_query);

    while($log = $export_result->fetch_assoc()) {
        $data = [
            $log['id'],
            $log['log_type'],
            $log['module'],
            $log['action'],
            $log['description'],
            $log['ip_address'],
            $log['admin_username'] ?: 'সিস্টেম',
            $log['created_at']
        ];
        fputcsv($output, $data);
    }

    fclose($output);
    exit();
}

// Function to log actions
function log_action($module, $action, $description = '', $admin_id = null) {
    global $conn;

    $log_type = 'info';
    if(stripos($action, 'error') !== false || stripos($action, 'failed') !== false) {
        $log_type = 'error';
    } elseif(stripos($action, 'warning') !== false) {
        $log_type = 'warning';
    } elseif(stripos($action, 'login') !== false || stripos($action, 'logout') !== false) {
        $log_type = 'login';
    } elseif(stripos($module, 'security') !== false) {
        $log_type = 'security';
    }

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $query = "INSERT INTO system_logs (log_type, module, action, description, ip_address, user_agent, admin_id)
              VALUES ('$log_type', '$module', '$action', '$description', '$ip_address', '$user_agent', " . ($admin_id ?: 'NULL') . ")";

    $conn->query($query);
}
?>