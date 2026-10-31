<?php
require_once '../includes/admin_auth.php';
require_admin_permission('category_manage');

// Handle add category
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name'] ?? '');
    $nameEn = trim($_POST['name_en'] ?? '');
    $slug = strtolower(trim($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    if ($name === '' || $nameEn === '') {
        $message = 'ক্যাটেগরির বাংলা ও ইংরেজি নাম লিখুন।';
        $message_type = 'warning';
    } elseif ($slug === '') {
        $message = 'ইউআরএল স্লাগ দিন। উদাহরণ: poem';
        $message_type = 'warning';
    } else {
        $check_stmt = $conn->prepare('SELECT id FROM categories WHERE slug = ?');
        $check_stmt->bind_param('s', $slug);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows === 0) {
            $insert_stmt = $conn->prepare('INSERT INTO categories (name, name_en, slug) VALUES (?, ?, ?)');
            $insert_stmt->bind_param('sss', $name, $nameEn, $slug);
            if($insert_stmt->execute()) {
                $message = 'ক্যাটেগরি সফলভাবে যোগ করা হয়েছে';
                $message_type = 'success';
            } else {
                $message = 'ক্যাটেগরি যোগ করতে সমস্যা হয়েছে';
                $message_type = 'danger';
            }
        } else {
            $message = 'এই ইউআরএল স্লাগটি ইতিমধ্যে ব্যবহার করা হয়েছে। অন্য স্লাগ দিন।';
            $message_type = 'warning';
        }
    }
}

// Handle edit category
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $id = intval($_POST['category_id']);
    $name = trim($_POST['edit_name'] ?? '');
    $nameEn = trim($_POST['edit_name_en'] ?? '');
    $slug = strtolower(trim($_POST['edit_slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    if ($name === '') {
        $message = 'ক্যাটেগরির বাংলা নাম লিখুন।';
        $message_type = 'warning';
    } elseif ($slug === '') {
        $message = 'ইউআরএল স্লাগ দিন। উদাহরণ: poem';
        $message_type = 'warning';
    } else {
        $check_stmt = $conn->prepare('SELECT id FROM categories WHERE slug = ? AND id != ?');
        $check_stmt->bind_param('si', $slug, $id);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $message = 'এই ইউআরএল স্লাগটি ইতিমধ্যে ব্যবহার করা হয়েছে। অন্য স্লাগ দিন।';
            $message_type = 'warning';
        } else {
            $update_stmt = $conn->prepare("UPDATE categories SET name = ?, name_en = COALESCE(NULLIF(?, ''), name_en), slug = ? WHERE id = ?");
            $update_stmt->bind_param('sssi', $name, $nameEn, $slug, $id);
            if($update_stmt->execute()) {
                $message = 'ক্যাটেগরি সফলভাবে আপডেট করা হয়েছে';
                $message_type = 'success';
            } else {
                $message = 'ক্যাটেগরি আপডেট করতে সমস্যা হয়েছে';
                $message_type = 'danger';
            }
        }
    }
}

// Handle delete category
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Check if category has news
    $check_news = $conn->query("SELECT COUNT(*) as count FROM news WHERE category_id = $delete_id");
    $news_count = $check_news->fetch_assoc()['count'];

    if($news_count == 0) {
        $delete_query = "DELETE FROM categories WHERE id = $delete_id";
        if($conn->query($delete_query)) {
            $message = 'ক্যাটেগরি সফলভাবে মুছে ফেলা হয়েছে';
            $message_type = 'success';
        } else {
            $message = 'ক্যাটেগরি মুছতে সমস্যা হয়েছে';
            $message_type = 'danger';
        }
    } else {
        $message = 'এই ক্যাটেগরিতে ' . $news_count . ' টি নিউজ আছে। প্রথমে সেই নিউজগুলি মুছুন অথবা অন্য ক্যাটেগরিতে নিয়ে যান।';
        $message_type = 'warning';
    }
}

// Fetch all categories
$categories_query = "SELECT c.*, COUNT(n.id) as news_count
                    FROM categories c
                    LEFT JOIN news n ON c.id = n.category_id
                    GROUP BY c.id
                    ORDER BY c.name ASC";
$categories_result = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ক্যাটেগরি ব্যবস্থাপনা - বিজ্ঞান জগৎ</title>
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
        .category-badge {
            font-size: 0.8rem;
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
                        <a href="categories.php" class="active">
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
                    <h1 class="h2 bangla-font"><i class="fas fa-list"></i> ক্যাটেগরি ব্যবস্থাপনা</h1>
                </div>

                <?php if(isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Add Category Form -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0 bangla-font">নতুন ক্যাটেগরি যোগ করুন</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="name" class="form-label bangla-font">ক্যাটেগরি নাম *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="form-text bangla-font">উদাহরণ: রাজনীতি, খেলা, বিনোদন</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="name_en" class="form-label">Category name in English *</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="slug" class="form-label bangla-font">ইউআরএল স্লাগ *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">category.php?slug=</span>
                                            <input type="text" class="form-control" id="slug" name="slug"
                                                   placeholder="poem" pattern="[A-Za-z0-9]+(-[A-Za-z0-9]+)*"
                                                   maxlength="191" autocomplete="off" required>
                                        </div>
                                        <div class="form-text bangla-font">ইংরেজি অক্ষর, সংখ্যা ও হাইফেন ব্যবহার করুন। যেমন: কবিতার জন্য <code>poem</code></div>
                                    </div>
                                    <button type="submit" name="add_category" class="btn btn-success w-100 bangla-font">
                                        <i class="fas fa-plus"></i> ক্যাটেগরি যোগ করুন
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0 bangla-font">সকল ক্যাটেগরি</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>ক্যাটেগরি নাম</th>
                                                <th>ইউআরএল স্লাগ</th>
                                                <th class="text-center">নিউজ সংখ্যা</th>
                                                <th class="text-center">অ্যাকশন</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($categories_result && $categories_result->num_rows > 0): ?>
                                                <?php $counter = 1; ?>
                                                <?php while($category = $categories_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $counter; ?></td>
                                                    <td>
                                                        <span class="badge bg-primary category-badge">
                                                            <?php echo htmlspecialchars(localized_category_name($category['name'], $category['name_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <code>category.php?slug=<?php echo htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8'); ?></code>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-info"><?php echo $category['news_count']; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <details class="category-edit-details text-start">
                                                            <summary class="btn btn-sm btn-primary">
                                                                <i class="fas fa-edit"></i> সম্পাদনা
                                                            </summary>
                                                            <form method="POST" action="" class="category-edit-form bg-white border rounded shadow p-3 mt-2">
                                                                <input type="hidden" name="category_id" value="<?php echo (int) $category['id']; ?>">
                                                                <div class="mb-2">
                                                                    <label class="form-label bangla-font" for="edit_name_<?php echo (int) $category['id']; ?>">ক্যাটেগরির বাংলা নাম</label>
                                                                    <input type="text" class="form-control" id="edit_name_<?php echo (int) $category['id']; ?>" name="edit_name"
                                                                           value="<?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <label class="form-label" for="edit_name_en_<?php echo (int) $category['id']; ?>">Category name in English</label>
                                                                    <input type="text" class="form-control" id="edit_name_en_<?php echo (int) $category['id']; ?>" name="edit_name_en"
                                                                           value="<?php echo htmlspecialchars($category['name_en'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                                </div>
                                                                <div class="mb-2">
                                                                    <label class="form-label bangla-font" for="edit_slug_<?php echo (int) $category['id']; ?>">ইউআরএল স্লাগ</label>
                                                                    <input type="text" class="form-control" id="edit_slug_<?php echo (int) $category['id']; ?>" name="edit_slug"
                                                                           value="<?php echo htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8'); ?>"
                                                                           pattern="[A-Za-z0-9]+(-[A-Za-z0-9]+)*" maxlength="191" required>
                                                                </div>
                                                                <button type="submit" name="edit_category" value="1" class="btn btn-primary btn-sm">সংরক্ষণ করুন</button>
                                                            </form>
                                                        </details>
                                                        <a href="categories.php?delete_id=<?php echo $category['id']; ?>"
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('আপনি কি এই ক্যাটেগরি মুছে ফেলতে চান?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php $counter++; ?>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="fas fa-list fa-3x mb-3"></i>
                                                            <h5 class="bangla-font">কোন ক্যাটেগরি পাওয়া যায়নি</h5>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('input[name="slug"], input[name="edit_slug"]').forEach(function(field) {
            field.addEventListener('input', function() {
                this.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            });
        });
    </script>
</body>
</html>