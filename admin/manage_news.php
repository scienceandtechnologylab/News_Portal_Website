<?php
require_once '../includes/admin_auth.php';
require_admin_permission('news_view');

// Handle delete request
if(isset($_GET['delete_id'])) {
    require_admin_permission('news_delete');
    $delete_id = intval($_GET['delete_id']);

    // Get image name to delete from server
    $image_query = $conn->query("SELECT featured_image FROM news WHERE id = $delete_id");
    if($image_query->num_rows > 0) {
        $image_data = $image_query->fetch_assoc();
        $image_path = '../assets/images/news/' . $image_data['featured_image'];

        // Delete image if not default
        if($image_data['featured_image'] != 'default.jpg' && file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete from database
    $delete_query = "DELETE FROM news WHERE id = $delete_id";
    if($conn->query($delete_query)) {
        $message = 'নিউজ সফলভাবে মুছে ফেলা হয়েছে';
        $message_type = 'success';
    } else {
        $message = 'নিউজ মুছতে সমস্যা হয়েছে: ' . $conn->error;
        $message_type = 'danger';
    }
}

// Handle bulk actions
if(isset($_POST['bulk_action']) && isset($_POST['selected_news'])) {
    $selected_news = $_POST['selected_news'];
    $bulk_action = $_POST['bulk_action'];

    if($bulk_action == 'delete') {
        require_admin_permission('news_delete');
        $deleted_count = 0;
        foreach($selected_news as $news_id) {
            $news_id = intval($news_id);
            $conn->query("DELETE FROM news WHERE id = $news_id");
            $deleted_count++;
        }
        $message = $deleted_count . ' টি নিউজ মুছে ফেলা হয়েছে';
        $message_type = 'success';
    } elseif($bulk_action == 'publish') {
        require_admin_permission('news_edit');
        $published_count = 0;
        foreach($selected_news as $news_id) {
            $news_id = intval($news_id);
            $conn->query("UPDATE news SET status = 'published' WHERE id = $news_id");
            $published_count++;
        }
        $message = $published_count . ' টি নিউজ প্রকাশিত হয়েছে';
        $message_type = 'success';
    } elseif($bulk_action == 'draft') {
        require_admin_permission('news_edit');
        $drafted_count = 0;
        foreach($selected_news as $news_id) {
            $news_id = intval($news_id);
            $conn->query("UPDATE news SET status = 'draft' WHERE id = $news_id");
            $drafted_count++;
        }
        $message = $drafted_count . ' টি নিউজ খসড়া হয়েছে';
        $message_type = 'success';
    }
}

// Pagination
$limit = 20; // Number of news per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total news count
$count_query = $conn->query("SELECT COUNT(*) as total FROM news");
$total_news = $count_query->fetch_assoc()['total'];
$total_pages = ceil($total_news / $limit);

// Get news with pagination
$news_query = "SELECT n.*, c.name as category_name, c.name_en as category_name_en
               FROM news n
               LEFT JOIN categories c ON n.category_id = c.id
               ORDER BY n.created_at DESC
               LIMIT $limit OFFSET $offset";
$news_result = $conn->query($news_query);
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিউজ ব্যবস্থাপনা - বিজ্ঞান জগৎ</title>
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
        .news-image {
            width: 80px;
            height: 60px;
            object-fit: contain;
            border-radius: 4px;
        }
        .table th {
            white-space: nowrap;
        }
        .table td {
            vertical-align: middle;
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
                        <a href="manage_news.php" class="active">
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
                    <h1 class="h2 bangla-font"><i class="fas fa-newspaper"></i> নিউজ ব্যবস্থাপনা</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_news.php" class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> নতুন নিউজ
                        </a>
                    </div>
                </div>

                <?php if(isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Bulk Actions Form -->
                <form method="POST" action="" id="bulkForm">
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label bangla-font" for="selectAll">
                                            সব নির্বাচন করুন
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <select class="form-select" name="bulk_action" id="bulk_action">
                                            <option value="">বাল্ক অ্যাকশন</option>
                                            <option value="publish">প্রকাশ করুন</option>
                                            <option value="draft">খসড়া করুন</option>
                                            <option value="delete">মুছে ফেলুন</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary" onclick="return confirmBulkAction()">
                                            প্রয়োগ করুন
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="50" class="text-center">#</th>
                                            <th width="50" class="text-center">ছবি</th>
                                            <th>শিরোনাম</th>
                                            <th>ক্যাটেগরি</th>
                                            <th>লেখক</th>
                                            <th>তারিখ</th>
                                            <th>স্ট্যাটাস</th>
                                            <th>ভিউ</th>
                                            <th width="150" class="text-center">অ্যাকশন</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($news_result && $news_result->num_rows > 0): ?>
                                            <?php $counter = 1; ?>
                                            <?php while($news = $news_result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input news-checkbox" type="checkbox"
                                                           name="selected_news[]" value="<?php echo $news['id']; ?>">
                                                </td>
                                                <td class="text-center">
                                                    <img src="<?php echo '../assets/images/news/' . $news['featured_image']; ?>"
                                                         alt="ছবি" class="news-image">
                                                </td>
                                                <td>
                                                    <a href="../news.php?slug=<?php echo $news['slug']; ?>"
                                                       target="_blank" class="text-decoration-none">
                                                        <?php echo function_exists('mb_substr') ? mb_substr($news['title'], 0, 60) : substr($news['title'], 0, 60); ?>
                                                        <?php echo (function_exists('mb_strlen') ? mb_strlen($news['title']) : strlen($news['title'])) > 60 ? '...' : ''; ?>
                                                    </a>
                                                    <?php if($news['is_featured'] == 1): ?>
                                                    <span class="badge bg-warning ms-1">শীর্ষ</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars(localized_category_name($news['category_name'], $news['category_name_en']), ENT_QUOTES, 'UTF-8'); ?></span>
                                                </td>
                                                <td><?php echo $news['author']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($news['created_at'])); ?></td>
                                                <td>
                                                    <?php if($news['status'] == 'published'): ?>
                                                    <span class="badge bg-success">প্রকাশিত</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-warning">খসড়া</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $news['views']; ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="edit_news.php?id=<?php echo $news['id']; ?>"
                                                       class="btn btn-sm btn-primary" title="সম্পাদনা">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="manage_news.php?delete_id=<?php echo $news['id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('আপনি কি এই নিউজটি মুছে ফেলতে চান?')"
                                                       title="মুছে ফেলুন">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <a href="../news.php?slug=<?php echo $news['slug']; ?>"
                                                       target="_blank" class="btn btn-sm btn-info" title="দেখুন">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php $counter++; ?>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="fas fa-newspaper fa-3x mb-3"></i>
                                                        <h5 class="bangla-font">কোন নিউজ পাওয়া যায়নি</h5>
                                                        <p class="bangla-font">প্রথম নিউজ যোগ করুন</p>
                                                        <a href="add_news.php" class="btn btn-success">
                                                            <i class="fas fa-plus"></i> নিউজ যোগ করুন
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>

                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">মোট নিউজ</h5>
                                <h2><?php echo $total_news; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php
                        $published_count = $conn->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'")->fetch_assoc()['count'];
                        ?>
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">প্রকাশিত</h5>
                                <h2><?php echo $published_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php
                        $draft_count = $conn->query("SELECT COUNT(*) as count FROM news WHERE status = 'draft'")->fetch_assoc()['count'];
                        ?>
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">খসড়া</h5>
                                <h2><?php echo $draft_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <?php
                        $featured_count = $conn->query("SELECT COUNT(*) as count FROM news WHERE is_featured = 1")->fetch_assoc()['count'];
                        ?>
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h5 class="card-title">শীর্ষ নিউজ</h5>
                                <h2><?php echo $featured_count; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.news-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Confirm bulk action
        function confirmBulkAction() {
            const selectedAction = document.getElementById('bulk_action').value;
            const selectedCount = document.querySelectorAll('.news-checkbox:checked').length;

            if(selectedAction === '') {
                alert('দয়া করে একটি অ্যাকশন নির্বাচন করুন');
                return false;
            }

            if(selectedCount === 0) {
                alert('দয়া করে কমপক্ষে একটি নিউজ নির্বাচন করুন');
                return false;
            }

            let message = '';
            if(selectedAction === 'delete') {
                message = `আপনি কি ${selectedCount} টি নিউজ মুছে ফেলতে চান?`;
            } else if(selectedAction === 'publish') {
                message = `আপনি কি ${selectedCount} টি নিউজ প্রকাশ করতে চান?`;
            } else if(selectedAction === 'draft') {
                message = `আপনি কি ${selectedCount} টি নিউজ খসড়া করতে চান?`;
            }

            return confirm(message);
        }

        // Search functionality (if you add search later)
        function searchNews() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const title = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                if(title.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Quick status change
        function changeStatus(newsId, status) {
            if(confirm('আপনি কি স্ট্যাটাস পরিবর্তন করতে চান?')) {
                fetch(`update_status.php?id=${newsId}&status=${status}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('স্ট্যাটাস পরিবর্তন ব্যর্থ হয়েছে');
                        }
                    });
            }
        }
    </script>
</body>
</html>