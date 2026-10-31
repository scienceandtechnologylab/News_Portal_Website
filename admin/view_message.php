<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

// Get message ID
if(!isset($_GET['id'])) {
    header('Location: messages.php');
    exit();
}

$message_id = intval($_GET['id']);

// Get message details
$query = "SELECT * FROM contact_messages WHERE id = $message_id";
$result = $conn->query($query);

if($result->num_rows === 0) {
    header('Location: messages.php');
    exit();
}

$message = $result->fetch_assoc();

// Mark as read
$conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $message_id");
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বার্তা দেখুন - বিজ্ঞান জগৎ</title>
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
        .message-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
                        </a>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i> সেটিংস
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
                    <h1 class="h2 bangla-font"><i class="fas fa-envelope-open"></i> বার্তা দেখুন</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="messages.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> বার্তা তালিকায় ফিরে যান
                        </a>
                    </div>
                </div>

                <div class="message-box">
                    <!-- Message Header -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="bangla-font text-primary"><?php echo htmlspecialchars($message['subject']); ?></h3>
                            <span class="badge bg-success">পড়া হয়েছে</span>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-user"></i> প্রেরক:</strong>
                                <?php echo htmlspecialchars($message['name']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-envelope"></i> ইমেইল:</strong>
                                <?php echo htmlspecialchars($message['email']); ?></p>
                            </div>
                            <?php if(!empty($message['phone'])): ?>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-phone"></i> ফোন:</strong>
                                <?php echo htmlspecialchars($message['phone']); ?></p>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-calendar"></i> তারিখ:</strong>
                                <?php echo date('d/m/Y h:i A', strtotime($message['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="mb-5">
                        <h5 class="bangla-font mb-3 border-bottom pb-2">বার্তা:</h5>
                        <div class="bg-light p-4 rounded">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <div>
                            <!-- <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>"
                               class="btn btn-primary">
                                <i class="fas fa-reply"></i> জবাব দিন
                            </a> -->
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo urlencode($message['email']); ?>&su=<?php echo urlencode('Re: ' . $message['subject']); ?>"
                                target="_blank"
                                class="btn btn-primary">
                                <i class="fas fa-reply"></i> জবাব দিন
                            </a>
                        </div>
                        <div>
                            <a href="messages.php?mark_unread=<?php echo $message['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-envelope"></i> অপড়া হিসেবে মার্ক করুন
                            </a>
                            <a href="messages.php?delete_id=<?php echo $message['id']; ?>"
                               class="btn btn-danger"
                               onclick="return confirm('আপনি কি এই বার্তা মুছে ফেলতে চান?')">
                                <i class="fas fa-trash"></i> মুছে ফেলুন
                            </a>
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