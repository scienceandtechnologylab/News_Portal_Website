<?php
require_once '../includes/admin_auth.php';
require_admin_permission('settings_manage');

// Log this action
$conn->query("INSERT INTO system_logs (log_type, module, action, description, ip_address, user_agent, admin_id)
              VALUES ('info', 'system', 'PHPInfo পেজ ভিজিট', 'PHP ইনফরমেশন পেজ ভিজিট করা হয়েছে',
              '" . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') . "',
              '" . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "',
              " . $_SESSION['admin_id'] . ")");
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP ইনফরমেশন - বিজ্ঞান জগৎ</title>
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
        .phpinfo-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .phpinfo-header {
            background: linear-gradient(45deg, #4e54c8, #8f94fb);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .phpinfo-content {
            background: white;
            padding: 20px;
            max-height: 80vh;
            overflow-y: auto;
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
        .quick-info {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-card {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 15px;
        }
        .info-card.warning {
            border-left-color: #ffc107;
        }
        .info-card.danger {
            border-left-color: #dc3545;
        }
        .info-card.success {
            border-left-color: #28a745;
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
                        <a href="phpinfo.php" class="active">
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
                    <h1 class="h2 bangla-font"><i class="fas fa-info-circle"></i> PHP ইনফরমেশন</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> ড্যাশবোর্ড
                            </a>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-print"></i> প্রিন্ট
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick PHP Information -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="quick-info">
                            <h4 class="bangla-font mb-4 text-primary">
                                <i class="fas fa-server"></i> দ্রুত PHP তথ্য
                            </h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-card">
                                        <h6 class="bangla-font">PHP ভার্সন</h6>
                                        <h4 class="text-primary"><?php echo phpversion(); ?></h4>
                                        <small class="text-muted">
                                            সর্বশেষ: <?php echo version_compare(phpversion(), '8.0.0', '>=') ?
                                            '<span class="text-success">আপ টু ডেট</span>' :
                                            '<span class="text-danger">আপডেট প্রয়োজন</span>'; ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card warning">
                                        <h6 class="bangla-font">মেমোরি লিমিট</h6>
                                        <h4 class="text-warning"><?php echo ini_get('memory_limit'); ?></h4>
                                        <small class="text-muted">
                                            <?php
                                            $memory = ini_get('memory_limit');
                                            $memory_num = (int)$memory;
                                            if($memory_num >= 256) {
                                                echo '<span class="text-success">পর্যাপ্ত</span>';
                                            } elseif($memory_num >= 128) {
                                                echo '<span class="text-warning">পর্যাপ্ত</span>';
                                            } else {
                                                echo '<span class="text-danger">বাড়ানো প্রয়োজন</span>';
                                            }
                                            ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-card success">
                                        <h6 class="bangla-font">PHP এক্সটেনশন</h6>
                                        <h4 class="text-success"><?php echo count(get_loaded_extensions()); ?></h4>
                                        <small class="text-muted">
                                            <?php
                                            $required = ['mysqli', 'pdo', 'gd', 'mbstring', 'curl'];
                                            $missing = array_diff($required, get_loaded_extensions());
                                            echo empty($missing) ?
                                                '<span class="text-success">সব প্রয়োজনীয় আছে</span>' :
                                                '<span class="text-danger">' . count($missing) . ' টি নেই</span>';
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <div class="info-card">
                                        <h6 class="bangla-font">ম্যাক্স এক্সিকিউশন টাইম</h6>
                                        <p class="mb-0"><?php echo ini_get('max_execution_time'); ?> সেকেন্ড</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-card">
                                        <h6 class="bangla-font">পোস্ট ম্যাক্স সাইজ</h6>
                                        <p class="mb-0"><?php echo ini_get('post_max_size'); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-card">
                                        <h6 class="bangla-font">আপলোড ম্যাক্স সাইজ</h6>
                                        <p class="mb-0"><?php echo ini_get('upload_max_filesize'); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-card">
                                        <h6 class="bangla-font">এরর রিপোর্টিং</h6>
                                        <p class="mb-0">
                                            <?php
                                            $error_reporting = ini_get('error_reporting');
                                            echo $error_reporting == 0 ? 'Off' : 'On';
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Server Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0 bangla-font"><i class="fas fa-server"></i> সার্ভার তথ্য</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th class="bangla-font">সার্ভার সফটওয়্যার</th>
                                        <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">সার্ভার নাম</th>
                                        <td><?php echo $_SERVER['SERVER_NAME']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">সার্ভার পোর্ট</th>
                                        <td><?php echo $_SERVER['SERVER_PORT']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">সার্ভার প্রোটোকল</th>
                                        <td><?php echo $_SERVER['SERVER_PROTOCOL']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">ডকুমেন্ট রুট</th>
                                        <td><?php echo $_SERVER['DOCUMENT_ROOT']; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0 bangla-font"><i class="fas fa-database"></i> ডাটাবেস তথ্য</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th class="bangla-font">MySQL ভার্সন</th>
                                        <td><?php echo $conn->server_info; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">হস্টনেম</th>
                                        <td><?php echo $conn->host_info; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">প্রোটোকল</th>
                                        <td><?php echo $conn->protocol_version; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">ক্যারেক্টার সেট</th>
                                        <td><?php echo $conn->character_set_name(); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bangla-font">স্ট্যাটাস</th>
                                        <td><span class="badge bg-success">কানেক্টেড</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Full PHP Info -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="phpinfo-container">
                            <div class="phpinfo-header">
                                <h3 class="bangla-font mb-0"><i class="fab fa-php"></i> সম্পূর্ণ PHP ইনফরমেশন</h3>
                                <p class="mb-0">সর্বশেষ আপডেট: <?php echo date('d/m/Y h:i:s A'); ?></p>
                            </div>
                            <div class="phpinfo-content">
                                <?php
                                // Capture phpinfo output
                                ob_start();
                                phpinfo();
                                $phpinfo = ob_get_contents();
                                ob_end_clean();

                                // Clean up the output
                                $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
                                $phpinfo = str_replace('border="0"', 'border="1" class="table table-bordered table-sm"', $phpinfo);
                                $phpinfo = str_replace('cellpadding="3"', 'class="p-2"', $phpinfo);
                                $phpinfo = str_replace('<table', '<table class="table table-bordered table-sm table-hover"', $phpinfo);
                                $phpinfo = str_replace('<td class="e"', '<td class="p-2 bg-light"', $phpinfo);
                                $phpinfo = str_replace('<td class="v"', '<td class="p-2"', $phpinfo);
                                $phpinfo = str_replace('<td class="h"', '<td class="p-2 bg-info text-white"', $phpinfo);

                                echo $phpinfo;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Important Extensions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0 bangla-font"><i class="fas fa-plug"></i> গুরুত্বপূর্ণ PHP এক্সটেনশন</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php
                                    $important_extensions = [
                                        'mysqli' => ['title' => 'MySQLi', 'desc' => 'ডাটাবেস কানেকশনের জন্য', 'required' => true],
                                        'pdo' => ['title' => 'PDO', 'desc' => 'ডাটাবেস অ্যাবস্ট্রাকশন লেয়ার', 'required' => true],
                                        'gd' => ['title' => 'GD Library', 'desc' => 'ইমেজ প্রসেসিং', 'required' => true],
                                        'mbstring' => ['title' => 'mbstring', 'desc' => 'মাল্টিবাইট স্ট্রিং সাপোর্ট', 'required' => true],
                                        'curl' => ['title' => 'cURL', 'desc' => 'HTTP রিকোয়েস্ট', 'required' => true],
                                        'openssl' => ['title' => 'OpenSSL', 'desc' => 'এনক্রিপশন সাপোর্ট', 'required' => true],
                                        'json' => ['title' => 'JSON', 'desc' => 'JSON প্রসেসিং', 'required' => true],
                                        'zip' => ['title' => 'ZIP', 'desc' => 'ZIP আর্কাইভ', 'required' => false],
                                        'xml' => ['title' => 'XML', 'desc' => 'XML প্রসেসিং', 'required' => false],
                                        'session' => ['title' => 'Session', 'desc' => 'সেশন ম্যানেজমেন্ট', 'required' => true],
                                        'filter' => ['title' => 'Filter', 'desc' => 'ডাটা ফিল্টারিং', 'required' => true],
                                        'hash' => ['title' => 'Hash', 'desc' => 'হ্যাশিং ফাংশন', 'required' => true],
                                    ];

                                    foreach($important_extensions as $ext => $info):
                                        $is_loaded = extension_loaded($ext);
                                    ?>
                                    <div class="col-md-3 col-6 mb-3">
                                        <div class="card h-100 <?php echo $is_loaded ? 'border-success' : ($info['required'] ? 'border-danger' : 'border-warning'); ?>">
                                            <div class="card-body text-center p-3">
                                                <div class="mb-2">
                                                    <?php if($is_loaded): ?>
                                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                                    <?php elseif($info['required']): ?>
                                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                                    <?php else: ?>
                                                    <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <h6 class="card-title bangla-font mb-1"><?php echo $info['title']; ?></h6>
                                                <p class="card-text text-muted" style="font-size: 0.8rem;"><?php echo $info['desc']; ?></p>
                                                <span class="badge bg-<?php echo $is_loaded ? 'success' : ($info['required'] ? 'danger' : 'warning'); ?>">
                                                    <?php echo $is_loaded ? 'লোডেড' : ($info['required'] ? 'প্রয়োজনীয়' : 'অপশনাল'); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
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
        // Print functionality
        function printPHPInfo() {
            const printContent = document.querySelector('.phpinfo-container').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>PHP Information - বাংলাদেশ প্রতিদিন</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; }
                        th { background-color: #f2f2f2; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>PHP Information - বাংলাদেশ প্রতিদিন</h2>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                    </div>
                    ${printContent}
                    <div class="footer">
                        <p>© <?php echo date('Y'); ?> বাংলাদেশ প্রতিদিন</p>
                    </div>
                </body>
                </html>
            `;

            window.print();
            document.body.innerHTML = originalContent;
        }

        // Search in PHP info
        function searchInPHPInfo() {
            const searchTerm = document.getElementById('searchPhpInfo').value.toLowerCase();
            const tables = document.querySelectorAll('.phpinfo-content table');

            tables.forEach(table => {
                const rows = table.querySelectorAll('tr');
                let found = false;

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if(text.includes(searchTerm)) {
                        row.style.backgroundColor = '#fff3cd';
                        found = true;
                    } else {
                        row.style.backgroundColor = '';
                    }
                });

                // Show/hide table based on search
                table.style.display = found || searchTerm === '' ? '' : 'none';
            });
        }

        // Add search box dynamically
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.phpinfo-header');
            const searchBox = document.createElement('div');
            searchBox.className = 'input-group mt-3';
            searchBox.innerHTML = `
                <input type="text" id="searchPhpInfo" class="form-control" placeholder="PHPInfo তে সার্চ করুন...">
                <button class="btn btn-outline-light" onclick="searchInPHPInfo()">
                    <i class="fas fa-search"></i>
                </button>
            `;
            header.appendChild(searchBox);

            // Add export button
            const exportBtn = document.createElement('button');
            exportBtn.className = 'btn btn-outline-light btn-sm mt-2';
            exportBtn.innerHTML = '<i class="fas fa-download"></i> TXT হিসেবে ডাউনলোড';
            exportBtn.onclick = exportPHPInfo;
            header.appendChild(exportBtn);
        });

        // Export PHP info as text file
        function exportPHPInfo() {
            const phpinfoContent = document.querySelector('.phpinfo-content').textContent;
            const blob = new Blob([phpinfoContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'phpinfo_' + new Date().toISOString().slice(0, 10) + '.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Auto-refresh every 5 minutes
        setTimeout(() => {
            if(confirm('আপনি ৫ মিনিট ধরে এই পেজে আছেন। পেজ রিফ্রেশ করতে চান?')) {
                location.reload();
            }
        }, 300000);
    </script>
</body>
</html>