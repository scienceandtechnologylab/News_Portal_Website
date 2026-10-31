<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

// Handle message deletion
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM contact_messages WHERE id = $delete_id";

    if($conn->query($delete_query)) {
        $_SESSION['message'] = 'বার্তা সফলভাবে মুছে ফেলা হয়েছে';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'বার্তা মুছতে সমস্যা হয়েছে: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
    header('Location: messages.php');
    exit();
}

// Handle multiple delete
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action']) && isset($_POST['selected_messages'])) {
    $selected_messages = $_POST['selected_messages'];
    $bulk_action = $_POST['bulk_action'];

    if($bulk_action == 'delete') {
        $deleted_count = 0;
        foreach($selected_messages as $msg_id) {
            $msg_id = intval($msg_id);
            $conn->query("DELETE FROM contact_messages WHERE id = $msg_id");
            $deleted_count++;
        }
        $_SESSION['message'] = $deleted_count . ' টি বার্তা মুছে ফেলা হয়েছে';
        $_SESSION['message_type'] = 'success';
    } elseif($bulk_action == 'mark_read') {
        $marked_count = 0;
        foreach($selected_messages as $msg_id) {
            $msg_id = intval($msg_id);
            $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $msg_id");
            $marked_count++;
        }
        $_SESSION['message'] = $marked_count . ' টি বার্তা পড়া হয়েছে হিসেবে মার্ক করা হয়েছে';
        $_SESSION['message_type'] = 'success';
    } elseif($bulk_action == 'mark_unread') {
        $marked_count = 0;
        foreach($selected_messages as $msg_id) {
            $msg_id = intval($msg_id);
            $conn->query("UPDATE contact_messages SET is_read = 0 WHERE id = $msg_id");
            $marked_count++;
        }
        $_SESSION['message'] = $marked_count . ' টি বার্তা অপড়া হিসেবে মার্ক করা হয়েছে';
        $_SESSION['message_type'] = 'success';
    }
    header('Location: messages.php');
    exit();
}

// Handle mark as read
if(isset($_GET['mark_read'])) {
    $msg_id = intval($_GET['mark_read']);
    $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $msg_id");
    header('Location: messages.php');
    exit();
}

// Handle mark as unread
if(isset($_GET['mark_unread'])) {
    $msg_id = intval($_GET['mark_unread']);
    $conn->query("UPDATE contact_messages SET is_read = 0 WHERE id = $msg_id");
    header('Location: messages.php');
    exit();
}

// Filter parameters
$filter_read = isset($_GET['filter_read']) ? $_GET['filter_read'] : 'all';
$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build query conditions
$conditions = [];
if($filter_read !== 'all') {
    $conditions[] = "is_read = " . ($filter_read === 'read' ? 1 : 0);
}

if(!empty($search_term)) {
    $conditions[] = "(name LIKE '%$search_term%' OR email LIKE '%$search_term%' OR subject LIKE '%$search_term%' OR message LIKE '%$search_term%')";
}

$where_clause = '';
if(!empty($conditions)) {
    $where_clause = "WHERE " . implode(' AND ', $conditions);
}

// Sort order
$order_by = '';
switch($sort_by) {
    case 'oldest':
        $order_by = "created_at ASC";
        break;
    case 'name':
        $order_by = "name ASC";
        break;
    case 'unread_first':
        $order_by = "is_read ASC, created_at DESC";
        break;
    default:
        $order_by = "created_at DESC";
        break;
}

// Pagination
$limit = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Get total messages count
$count_query = "SELECT COUNT(*) as total FROM contact_messages $where_clause";
$count_result = $conn->query($count_query);
if($count_result) {
    $total_messages = $count_result->fetch_assoc()['total'];
} else {
    $total_messages = 0;
}
$total_pages = ceil($total_messages / $limit);
if($total_pages < 1) $total_pages = 1;
if($page > $total_pages) $page = $total_pages;

// Get messages with pagination
$messages_query = "SELECT * FROM contact_messages $where_clause ORDER BY $order_by LIMIT $limit OFFSET $offset";
$messages_result = $conn->query($messages_query);

// Get unread count for badge
$unread_count_query = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
if($unread_count_query) {
    $unread_count = $unread_count_query->fetch_assoc()['count'];
} else {
    $unread_count = 0;
}

// Display session messages if any
if(isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
} else {
    $message = '';
    $message_type = '';
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বার্তা ব্যবস্থাপনা - বিজ্ঞান জগৎ</title>
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
        .message-row {
            transition: all 0.3s;
            cursor: pointer;
        }
        .message-row:hover {
            background-color: #f8f9fa;
        }
        .unread-message {
            background-color: #e3f2fd !important;
            font-weight: 500;
        }
        .message-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 300px;
        }
        .badge-new {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
                        <a href="messages.php" class="active">
                            <i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা
                            <?php if($unread_count > 0): ?>
                            <span class="badge bg-danger float-end"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
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
                    <h1 class="h2 bangla-font"><i class="fas fa-envelope"></i> বার্তা ব্যবস্থাপনা</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?filter_read=unread" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-envelope"></i> অপড়া বার্তা
                                <?php if($unread_count > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="messages.php" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-sync-alt"></i> রিফ্রেশ
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

                <!-- Filters and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label bangla-font">ফিল্টার করুন</label>
                                <select class="form-select" name="filter_read" onchange="this.form.submit()">
                                    <option value="all" <?php echo $filter_read === 'all' ? 'selected' : ''; ?>>সব বার্তা</option>
                                    <option value="unread" <?php echo $filter_read === 'unread' ? 'selected' : ''; ?>>শুধু অপড়া</option>
                                    <option value="read" <?php echo $filter_read === 'read' ? 'selected' : ''; ?>>শুধু পড়া</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label bangla-font">সর্ট করুন</label>
                                <select class="form-select" name="sort" onchange="this.form.submit()">
                                    <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>নতুন থেকে পুরানো</option>
                                    <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>পুরানো থেকে নতুন</option>
                                    <option value="unread_first" <?php echo $sort_by === 'unread_first' ? 'selected' : ''; ?>>অপড়া বার্তা প্রথমে</option>
                                    <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>নাম অনুসারে</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label bangla-font">বার্তা খুঁজুন</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                           value="<?php echo htmlspecialchars($search_term); ?>"
                                           placeholder="নাম, ইমেইল বা বিষয় লিখুন">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if(!empty($search_term)): ?>
                                    <a href="messages.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 bangla-font">বার্তা তালিকা</h5>
                    </div>
                    <div class="card-body">
                        <?php if($messages_result && $messages_result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th>নাম</th>
                                            <th>ইমেইল</th>
                                            <th>বিষয়</th>
                                            <th>তারিখ</th>
                                            <th>স্ট্যাটাস</th>
                                            <th width="150">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $counter = 1; ?>
                                        <?php while($msg = $messages_result->fetch_assoc()): ?>
                                        <tr class="<?php echo $msg['is_read'] == 0 ? 'unread-message' : ''; ?>">
                                            <td><?php echo $counter; ?></td>
                                            <td>
                                                <strong class="bangla-font"><?php echo htmlspecialchars($msg['name']); ?></strong>
                                                <?php if(!empty($msg['phone'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($msg['phone']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                            <td class="message-preview" title="<?php echo htmlspecialchars($msg['subject']); ?>">
                                                <?php echo htmlspecialchars($msg['subject']); ?>
                                            </td>
                                            <td><?php echo date('d/m/Y h:i A', strtotime($msg['created_at'])); ?></td>
                                            <td>
                                                <?php if($msg['is_read'] == 0): ?>
                                                <span class="badge bg-danger">অপড়া</span>
                                                <?php else: ?>
                                                <span class="badge bg-success">পড়া</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <?php if($msg['is_read'] == 0): ?>
                                                    <a href="?mark_read=<?php echo $msg['id']; ?>"
                                                       class="btn btn-success" title="পড়া হিসেবে মার্ক করুন">
                                                        <i class="fas fa-envelope-open"></i>
                                                    </a>
                                                    <?php else: ?>
                                                    <a href="?mark_unread=<?php echo $msg['id']; ?>"
                                                       class="btn btn-warning" title="অপড়া হিসেবে মার্ক করুন">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    <a href="view_message.php?id=<?php echo $msg['id']; ?>"
                                                       class="btn btn-info" title="দেখুন">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="?delete_id=<?php echo $msg['id']; ?>"
                                                       class="btn btn-danger"
                                                       onclick="return confirm('আপনি কি এই বার্তা মুছে ফেলতে চান?')"
                                                       title="মুছে ফেলুন">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter_read=<?php echo $filter_read; ?>&sort=<?php echo $sort_by; ?>&search=<?php echo urlencode($search_term); ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>

                                    <?php
                                    // Show limited page numbers
                                    $start_page = max(1, $page - 2);
                                    $end_page = min($total_pages, $page + 2);

                                    for($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&filter_read=<?php echo $filter_read; ?>&sort=<?php echo $sort_by; ?>&search=<?php echo urlencode($search_term); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter_read=<?php echo $filter_read; ?>&sort=<?php echo $sort_by; ?>&search=<?php echo urlencode($search_term); ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                                <div class="text-center mt-2">
                                    <small class="text-muted bangla-font">
                                        পৃষ্ঠা <?php echo $page; ?> এর <?php echo $total_pages; ?> (মোট <?php echo $total_messages; ?> টি বার্তা)
                                    </small>
                                </div>
                            </nav>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-envelope-open fa-4x text-muted mb-3"></i>
                                <h5 class="bangla-font text-muted">কোন বার্তা পাওয়া যায়নি</h5>
                                <p class="bangla-font">কোন ভিজিটর এখনও বার্তা পাঠায়নি</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">মোট বার্তা</h5>
                                <h2><?php echo $total_messages; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">অপড়া বার্তা</h5>
                                <h2><?php echo $unread_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php
                        $read_count = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 1")->fetch_assoc()['count'];
                        ?>
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">পড়া বার্তা</h5>
                                <h2><?php echo $read_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php
                        $today_count = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
                        ?>
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">আজকের বার্তা</h5>
                                <h2><?php echo $today_count; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>