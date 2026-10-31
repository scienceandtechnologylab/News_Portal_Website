<?php
require_once '../includes/admin_auth.php';
require_admin_permission('dashboard_view');

// Log this action
$conn->query("INSERT INTO system_logs (log_type, module, action, description, ip_address, user_agent, admin_id)
              VALUES ('info', 'system', 'ডকুমেন্টেশন পেজ ভিজিট', 'অ্যাডমিন ডকুমেন্টেশন পেজ ভিজিট করা হয়েছে',
              '" . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0') . "',
              '" . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "',
              " . $_SESSION['admin_id'] . ")");
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ডকুমেন্টেশন - বিজ্ঞান জগৎ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-layout.css?v=6">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github.min.css">
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
        .doc-sidebar {
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .doc-content {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .doc-section {
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .doc-title {
            color: #2c3e50;
            border-left: 4px solid #3498db;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .doc-card {
            border: 1px solid #e3e3e3;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .doc-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .doc-card.warning {
            border-left: 4px solid #f39c12;
        }
        .doc-card.danger {
            border-left: 4px solid #e74c3c;
        }
        .doc-card.success {
            border-left: 4px solid #2ecc71;
        }
        .doc-card.info {
            border-left: 4px solid #3498db;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        .nav-doc {
            position: sticky;
            top: 0;
            background: white;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            z-index: 100;
        }
        .nav-doc a {
            color: #3498db;
            text-decoration: none;
            margin: 0 10px;
        }
        .nav-doc a:hover {
            text-decoration: underline;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #3498db;
            color: white;
            text-align: center;
            line-height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
        pre {
            border-radius: 5px;
            padding: 15px;
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
                        <a href="phpinfo.php">
                            <i class="fas fa-info-circle"></i> PHP ইনফো
                        </a>
                        <a href="documentation.php" class="active">
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
                    <h1 class="h2 bangla-font"><i class="fas fa-book"></i> অ্যাডমিন ডকুমেন্টেশন</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button onclick="window.print()" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-print"></i> প্রিন্ট
                            </button>
                            <button onclick="downloadPDF()" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-download"></i> PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Documentation Navigation -->
                <div class="nav-doc mb-4">
                    <div class="d-flex flex-wrap">
                        <a href="#getting-started">শুরু করুন</a>
                        <a href="#news-management">নিউজ ম্যানেজমেন্ট</a>
                        <a href="#category-management">ক্যাটেগরি ম্যানেজমেন্ট</a>
                        <a href="#message-management">বার্তা ম্যানেজমেন্ট</a>
                        <a href="#settings">সেটিংস</a>
                        <a href="#troubleshooting">সমস্যা সমাধান</a>
                        <a href="#api">API ডকুমেন্টেশন</a>
                        <a href="#faq">সাধারণ প্রশ্ন</a>
                    </div>
                </div>

                <div class="row">
                    <!-- Documentation Sidebar -->
                    <div class="col-md-3">
                        <div class="doc-sidebar">
                            <h5 class="bangla-font mb-3">বিষয়সূচি</h5>
                            <div class="list-group list-group-flush">
                                <a href="#getting-started" class="list-group-item list-group-item-action">শুরু করার নির্দেশিকা</a>
                                <a href="#system-requirements" class="list-group-item list-group-item-action">সিস্টেম প্রয়োজনীয়তা</a>
                                <a href="#installation" class="list-group-item list-group-item-action">ইনস্টলেশন</a>
                                <a href="#admin-panel" class="list-group-item list-group-item-action">অ্যাডমিন প্যানেল</a>
                                <a href="#news-management" class="list-group-item list-group-item-action">নিউজ ম্যানেজমেন্ট</a>
                                <a href="#category-management" class="list-group-item list-group-item-action">ক্যাটেগরি ম্যানেজমেন্ট</a>
                                <a href="#message-management" class="list-group-item list-group-item-action">বার্তা ম্যানেজমেন্ট</a>
                                <a href="#user-management" class="list-group-item list-group-item-action">ইউজার ম্যানেজমেন্ট</a>
                                <a href="#settings" class="list-group-item list-group-item-action">সেটিংস কনফিগারেশন</a>
                                <a href="#backup-restore" class="list-group-item list-group-item-action">ব্যাকআপ ও রিস্টোর</a>
                                <a href="#security" class="list-group-item list-group-item-action">সিকিউরিটি গাইড</a>
                                <a href="#seo" class="list-group-item list-group-item-action">এসইও অপ্টিমাইজেশন</a>
                                <a href="#performance" class="list-group-item list-group-item-action">পারফরমেন্স টিউনিং</a>
                                <a href="#troubleshooting" class="list-group-item list-group-item-action">সমস্যা সমাধান</a>
                                <a href="#faq" class="list-group-item list-group-item-action">সাধারণ প্রশ্ন (FAQ)</a>
                                <a href="#api" class="list-group-item list-group-item-action">API ডকুমেন্টেশন</a>
                                <a href="#updates" class="list-group-item list-group-item-action">আপডেট গাইড</a>
                            </div>

                            <div class="mt-4">
                                <h6 class="bangla-font mb-3">দ্রুত লিংক</h6>
                                <div class="d-grid gap-2">
                                    <a href="dashboard.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-tachometer-alt"></i> ড্যাশবোর্ড
                                    </a>
                                    <a href="settings.php" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-cog"></i> সেটিংস
                                    </a>
                                    <a href="logs.php" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-clipboard-list"></i> সিস্টেম লগস
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documentation Content -->
                    <div class="col-md-9">
                        <div class="doc-content">

                            <!-- Getting Started -->
                            <div class="doc-section" id="getting-started">
                                <h2 class="doc-title bangla-font">শুরু করার নির্দেশিকা</h2>

                                <div class="doc-card info">
                                    <h4 class="bangla-font"><i class="fas fa-rocket"></i> দ্রুত শুরু গাইড</h4>
                                    <p class="bangla-font">
                                        বিজ্ঞান জগৎ নিউজ পোর্টাল সিস্টেমে আপনাকে স্বাগতম। এই গাইড আপনাকে সিস্টেম সেটআপ এবং ব্যবহারের প্রাথমিক ধারণা দেবে।
                                    </p>

                                    <div class="mt-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="step-number">1</span>
                                            <span class="bangla-font">ডাটাবেস কনফিগারেশন সম্পন্ন করুন</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="step-number">2</span>
                                            <span class="bangla-font">অ্যাডমিন অ্যাকাউন্ট তৈরি করুন</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="step-number">3</span>
                                            <span class="bangla-font">প্রাথমিক ক্যাটেগরি যোগ করুন</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="step-number">4</span>
                                            <span class="bangla-font">প্রথম নিউজ প্রকাশ করুন</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="step-number">5</span>
                                            <span class="bangla-font">সাইট সেটিংস কনফিগার করুন</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- System Requirements -->
                            <div class="doc-section" id="system-requirements">
                                <h3 class="doc-title bangla-font">সিস্টেম প্রয়োজনীয়তা</h3>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-server"></i> সার্ভার রিকোয়ারমেন্ট</h5>
                                            <ul class="bangla-font">
                                                <li>PHP 7.4 বা উচ্চতর</li>
                                                <li>MySQL 5.6 বা MariaDB 10.0+</li>
                                                <li>Apache 2.4+ বা Nginx</li>
                                                <li>mod_rewrite (URL রিরাইটিং)</li>
                                                <li>GD Library (ইমেজ প্রসেসিং)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-hdd"></i> সফটওয়্যার রিকোয়ারমেন্ট</h5>
                                            <ul class="bangla-font">
                                                <li>cURL এক্সটেনশন</li>
                                                <li>JSON সাপোর্ট</li>
                                                <li>MBString এক্সটেনশন</li>
                                                <li>OpenSSL (SSL সাপোর্ট)</li>
                                                <li>PDO বা MySQLi</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="doc-card warning">
                                    <h5 class="bangla-font"><i class="fas fa-exclamation-triangle"></i> গুরুত্বপূর্ণ নির্দেশিকা</h5>
                                    <p class="bangla-font">
                                        <strong>সিকিউরিটি টিপস:</strong>
                                    </p>
                                    <ul class="bangla-font">
                                        <li>সময়মতো PHP এবং MySQL আপডেট রাখুন</li>
                                        <li>.htaccess ফাইল সঠিকভাবে কনফিগার করুন</li>
                                        <li>নিয়মিত ডাটাবেস ব্যাকআপ নিন</li>
                                        <li>এডমিন প্যানেলের জন্য শক্তিশালী পাসওয়ার্ড ব্যবহার করুন</li>
                                        <li>SSL সার্টিফিকেট ব্যবহার করুন (যদি সম্ভব হয়)</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- News Management -->
                            <div class="doc-section" id="news-management">
                                <h3 class="doc-title bangla-font">নিউজ ম্যানেজমেন্ট</h3>

                                <div class="doc-card success">
                                    <h5 class="bangla-font"><i class="fas fa-newspaper"></i> নিউজ যোগ করা</h5>
                                    <p class="bangla-font">নতুন নিউজ যোগ করার ধাপসমূহ:</p>
                                    <ol class="bangla-font">
                                        <li>অ্যাডমিন প্যানেলে "নিউজ যোগ করুন" মেনুতে ক্লিক করুন</li>
                                        <li>নিউজ শিরোনাম লিখুন (ইউনিক এবং আকর্ষণীয়)</li>
                                        <li>নিউজ কন্টেন্ট লিখুন (সম্পূর্ণ HTML এডিটর সাপোর্ট)</li>
                                        <li>ফিচার্ড ইমেজ আপলোড করুন (সুপারিশকৃত: 800×500px)</li>
                                        <li>ক্যাটেগরি নির্বাচন করুন</li>
                                        <li>লেখকের নাম দিন</li>
                                        <li>স্ট্যাটাস সেট করুন (প্রকাশিত/খসড়া)</li>
                                        <li>শীর্ষ নিউজ হিসেবে মার্ক করুন (যদি প্রয়োজন হয়)</li>
                                        <li>"সংরক্ষণ করুন" বাটনে ক্লিক করুন</li>
                                    </ol>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-edit"></i> নিউজ সম্পাদনা</h5>
                                            <p class="bangla-font">
                                                বিদ্যমান নিউজ সম্পাদনা করতে "নিউজ ব্যবস্থাপনা" পেজে যান এবং সংশ্লিষ্ট নিউজের পাশের "সম্পাদনা" বাটনে ক্লিক করুন।
                                            </p>
                                            <p class="bangla-font text-muted">
                                                <small><i class="fas fa-lightbulb"></i> টিপ: নিউজ আপডেট করলে স্বয়ংক্রিয়ভাবে আপডেট টাইমস্ট্যাম্প পরিবর্তন হবে।</small>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-trash"></i> নিউজ মুছে ফেলা</h5>
                                            <p class="bangla-font">
                                                নিউজ মুছে ফেলতে "নিউজ ব্যবস্থাপনা" পেজে যান এবং সংশ্লিষ্ট নিউজের পাশের "মুছে ফেলুন" বাটনে ক্লিক করুন। কনফার্মেশন ডায়ালগে "OK" ক্লিক করুন।
                                            </p>
                                            <p class="bangla-font text-danger">
                                                <small><i class="fas fa-exclamation-circle"></i> সতর্কতা: মুছে ফেলা নিউজ পুনরুদ্ধার করা যাবে না।</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-code"></i> নিউজ কন্টেন্ট ফরম্যাটিং</h5>
                                    <p class="bangla-font">নিউজ এডিটরে উপলব্ধ ফরম্যাটিং টুলস:</p>
                                    <div class="code-block">
// HTML ট্যাগ সাপোর্ট
&lt;h2&gt;হেডিং&lt;/h2&gt;
&lt;p&gt;প্যারাগ্রাফ&lt;/p&gt;
&lt;ul&gt;
    &lt;li&gt;লিস্ট আইটেম ১&lt;/li&gt;
    &lt;li&gt;লিস্ট আইটেম ২&lt;/li&gt;
&lt;/ul&gt;
&lt;strong&gt;বোল্ড টেক্সট&lt;/strong&gt;
&lt;em&gt;ইটালিক টেক্সট&lt;/em&gt;
&lt;a href="#"&gt;লিংক&lt;/a&gt;
&lt;img src="image.jpg" alt="ছবি"&gt;
                                    </div>
                                </div>
                            </div>

                            <!-- Category Management -->
                            <div class="doc-section" id="category-management">
                                <h3 class="doc-title bangla-font">ক্যাটেগরি ম্যানেজমেন্ট</h3>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-folder-plus"></i> নতুন ক্যাটেগরি যোগ করা</h5>
                                    <p class="bangla-font">ক্যাটেগরি যোগ করার ধাপ:</p>
                                    <ol class="bangla-font">
                                        <li>অ্যাডমিন প্যানেলে "ক্যাটেগরি ব্যবস্থাপনা" মেনুতে ক্লিক করুন</li>
                                        <li>বাম পাশের ফর্মে ক্যাটেগরি নাম লিখুন</li>
                                        <li>"ক্যাটেগরি যোগ করুন" বাটনে ক্লিক করুন</li>
                                        <li>ক্যাটেগরি স্বয়ংক্রিয়ভাবে তৈরি হবে</li>
                                    </ol>
                                    <p class="bangla-font text-muted">
                                        <small><i class="fas fa-info-circle"></i> তথ্য: ক্যাটেগরি স্লাগ স্বয়ংক্রিয়ভাবে তৈরি হয়।</small>
                                    </p>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-sitemap"></i> ক্যাটেগরি স্ট্রাকচার</h5>
                                            <p class="bangla-font">ডিফল্ট ক্যাটেগরি সমূহ:</p>
                                            <ul class="bangla-font">
                                                <li>জাতীয়</li>
                                                <li>আন্তর্জাতিক</li>
                                                <li>রাজনীতি</li>
                                                <li>বাণিজ্য</li>
                                                <li>খেলা</li>
                                                <li>বিনোদন</li>
                                                <li>স্বাস্থ্য</li>
                                                <li>প্রযুক্তি</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="doc-card danger">
                                            <h5 class="bangla-font"><i class="fas fa-trash-alt"></i> ক্যাটেগরি মুছে ফেলা</h5>
                                            <p class="bangla-font">
                                                ক্যাটেগরি মুছে ফেলার আগে নিশ্চিত করুন সেই ক্যাটেগরিতে কোন নিউজ নেই।
                                                যদি নিউজ থাকে, প্রথমে সেই নিউজগুলো অন্য ক্যাটেগরিতে নিয়ে যান অথবা মুছে ফেলুন।
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Management -->
                            <div class="doc-section" id="message-management">
                                <h3 class="doc-title bangla-font">বার্তা ব্যবস্থাপনা</h3>

                                <div class="doc-card info">
                                    <h5 class="bangla-font"><i class="fas fa-envelope"></i> ভিজিটর বার্তা ম্যানেজমেন্ট</h5>
                                    <p class="bangla-font">বার্তা ব্যবস্থাপনার বৈশিষ্ট্য:</p>
                                    <ul class="bangla-font">
                                        <li>সমস্ত বার্তা একটি জায়গায় দেখানো</li>
                                        <li>অপড়া বার্তা স্বয়ংক্রিয়ভাবে হাইলাইট করা</li>
                                        <li>বার্তার বিস্তারিত দেখার অপশন</li>
                                        <li>জবাব দেওয়ার সরাসরি ব্যবস্থা</li>
                                        <li>বার্তা ফিল্টারিং এবং সার্চ</li>
                                        <li>বাল্ক অপারেশন সাপোর্ট</li>
                                        <li>বার্তা CSV হিসেবে এক্সপোর্ট</li>
                                    </ul>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-reply"></i> বার্তার জবাব দেওয়া</h5>
                                            <p class="bangla-font">জবাব দেওয়ার ধাপ:</p>
                                            <ol class="bangla-font">
                                                <li>বার্তা তালিকা থেকে জবাব দিতে চাওয়া বার্তা নির্বাচন করুন</li>
                                                <li>"জবাব দিন" বাটনে ক্লিক করুন</li>
                                                <li>জবাব লিখুন এবং "জবাব পাঠান" ক্লিক করুন</li>
                                                <li>জবাব স্বয়ংক্রিয়ভাবে পাঠানো হবে</li>
                                            </ol>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <h5 class="bangla-font"><i class="fas fa-filter"></i> বার্তা ফিল্টারিং</h5>
                                            <p class="bangla-font">ফিল্টার অপশন সমূহ:</p>
                                            <ul class="bangla-font">
                                                <li>শুধু অপড়া বার্তা দেখুন</li>
                                                <li>শুধু পড়া বার্তা দেখুন</li>
                                                <li>তারিখ অনুসারে সর্ট করুন</li>
                                                <li>নাম বা ইমেইল দ্বারা সার্চ করুন</li>
                                                <li>বিষয় অনুসারে সার্চ করুন</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings -->
                            <div class="doc-section" id="settings">
                                <h3 class="doc-title bangla-font">সেটিংস কনফিগারেশন</h3>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-cogs"></i> সাইট সেটিংস</h5>
                                    <p class="bangla-font">প্রধান সেটিংস সমূহ:</p>
                                    <table class="table table-sm bangla-font">
                                        <thead>
                                            <tr>
                                                <th>সেটিং</th>
                                                <th>বর্ণনা</th>
                                                <th>ডিফল্ট ভ্যালু</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>সাইট নাম</td>
                                                <td>ওয়েবসাইটের নাম</td>
                                                <td>বাংলাদেশ প্রতিদিন</td>
                                            </tr>
                                            <tr>
                                                <td>সাইট ইউআরএল</td>
                                                <td>ওয়েবসাইটের সম্পূর্ণ ইউআরএল</td>
                                                <td>http://localhost/</td>
                                            </tr>
                                            <tr>
                                                <td>নিউজ পার পেজ</td>
                                                <td>প্রতি পেজে কতটি নিউজ দেখাবে</td>
                                                <td>20</td>
                                            </tr>
                                            <tr>
                                                <td>ব্রেকিং নিউজ</td>
                                                <td>ব্রেকিং নিউজ সক্ষম/অক্ষম</td>
                                                <td>সক্ষম</td>
                                            </tr>
                                            <tr>
                                                <td>মেইনটেন্যান্স মোড</td>
                                                <td>সাইট সাময়িক বন্ধ রাখা</td>
                                                <td>অক্ষম</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-ad"></i> বিজ্ঞাপন যোগ করুন</h5>
                                    <p class="bangla-font">
                                        সেটিংসের <strong>বিজ্ঞাপন</strong> ট্যাবে গিয়ে বন্ধ, নিজের ব্যানার অথবা Google AdSense নির্বাচন করে সংরক্ষণ করুন।
                                    </p>
                                    <ul class="bangla-font">
                                        <li><strong>নিজস্ব ব্যানার:</strong> তিনটি করে সর্বোচ্চ ২ MB JPG, PNG, GIF অথবা WebP ছবি দিন। ছবিগুলো প্রতি ২ সেকেন্ডে অ্যানিমেশনে বদলাবে; নিচের নির্দেশক দিয়ে সরাসরি ছবিও বেছে নেওয়া যাবে। ছবি, ক্লিকের URL এবং বিকল্প লেখা দিন।</li>
                                        <li>চাইলে বিজ্ঞাপনের নিচে পণ্য/সেবার সর্বোচ্চ ৫০০ অক্ষরের বিবরণও দেখাতে পারেন।</li>
                                        <li><strong>Google AdSense:</strong> AdSense-এ অনুমোদিত সাইট ও 300×250 বিজ্ঞাপন ইউনিটের Publisher ID এবং Slot ID দিন। বিজ্ঞাপনের JavaScript কোড পেস্ট করার দরকার নেই।</li>
                                        <li>localhost-এ AdSense বিজ্ঞাপন নাও দেখা যেতে পারে; অনুমোদিত প্রকাশিত ডোমেইনে পরীক্ষা করুন।</li>
                                        <li>এই বিজ্ঞাপনটি হোমপেজের শীর্ষ খবরের পাশে, বিভাগ পেজে এবং খবরের বিস্তারিত পেজে দেখা যায়।</li>
                                    </ul>
                                </div>

                                <div class="doc-card warning">
                                    <h5 class="bangla-font"><i class="fas fa-shield-alt"></i> সিকিউরিটি সেটিংস</h5>
                                    <p class="bangla-font">গুরুত্বপূর্ণ সিকিউরিটি সেটিংস:</p>
                                    <ul class="bangla-font">
                                        <li><strong>অ্যাডমিন লগইন অ্যাটেম্পট:</strong> ভুল পাসওয়ার্ড দেওয়ার পর টাইম আউট</li>
                                        <li><strong>ইপি ব্লকিং:</strong> সন্দেহজনক ইপি অ্যাড্রেস ব্লক করা</li>
                                        <li><strong>সেশন টাইমআউট:</strong> অ্যাকটিভিটি না থাকলে স্বয়ংক্রিয় লগআউট</li>
                                        <li><strong>পাসওয়ার্ড পলিসি:</strong> শক্তিশালী পাসওয়ার্ড বাধ্যতামূলক</li>
                                        <li><strong>SSL ফোর্স:</strong> HTTPS ব্যবহার বাধ্যতামূলক (যদি উপলব্ধ হয়)</li>
                                    </ul>
                                </div>

                                <div class="doc-card success">
                                    <h5 class="bangla-font"><i class="fas fa-search"></i> এসইও সেটিংস</h5>
                                    <p class="bangla-font">এসইও অপ্টিমাইজেশনের জন্য গুরুত্বপূর্ণ সেটিংস:</p>
                                    <div class="code-block">
&lt;meta name="description" content="বাংলাদেশের শীর্ষ অনলাইন নিউজ পোর্টাল"&gt;
&lt;meta name="keywords" content="বাংলাদেশ, খবর, নিউজ, রাজনীতি, বিনোদন"&gt;
&lt;meta property="og:title" content="বাংলাদেশ প্রতিদিন"&gt;
&lt;meta property="og:description" content="সর্বশেষ খবর"&gt;
&lt;meta property="og:image" content="logo.png"&gt;
                                    </div>
                                </div>
                            </div>

                            <!-- Troubleshooting -->
                            <div class="doc-section" id="troubleshooting">
                                <h3 class="doc-title bangla-font">সমস্যা সমাধান</h3>

                                <div class="doc-card danger">
                                    <h5 class="bangla-font"><i class="fas fa-exclamation-triangle"></i> সাধারণ সমস্যা ও সমাধান</h5>

                                    <div class="accordion" id="troubleshootingAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed bangla-font" type="button" data-bs-toggle="collapse" data-bs-target="#problem1">
                                                    সমস্যা: "Database connection failed"
                                                </button>
                                            </h2>
                                            <div id="problem1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                                <div class="accordion-body bangla-font">
                                                    <p><strong>সমাধান:</strong></p>
                                                    <ol>
                                                        <li>config.php ফাইলে ডাটাবেস ক্রেডেনশিয়াল চেক করুন</li>
                                                        <li>MySQL সার্ভার চলছে কিনা চেক করুন</li>
                                                        <li>ডাটাবেস নাম সঠিক কিনা নিশ্চিত করুন</li>
                                                        <li>ইউজারনেম/পাসওয়ার্ড সঠিক কিনা চেক করুন</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed bangla-font" type="button" data-bs-toggle="collapse" data-bs-target="#problem2">
                                                    সমস্যা: "404 Not Found" এরর
                                                </button>
                                            </h2>
                                            <div id="problem2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                                <div class="accordion-body bangla-font">
                                                    <p><strong>সমাধান:</strong></p>
                                                    <ol>
                                                        <li>.htaccess ফাইল আছে কিনা চেক করুন</li>
                                                        <li>Apache-তে mod_rewrite চালু আছে কিনা চেক করুন</li>
                                                        <li>AllowOverride All সেট করা আছে কিনা নিশ্চিত করুন</li>
                                                        <li>ফাইল পারমিশন চেক করুন</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed bangla-font" type="button" data-bs-toggle="collapse" data-bs-target="#problem3">
                                                    সমস্যা: "Image upload failed"
                                                </button>
                                            </h2>
                                            <div id="problem3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                                <div class="accordion-body bangla-font">
                                                    <p><strong>সমাধান:</strong></p>
                                                    <ol>
                                                        <li>uploads ফোল্ডারে write পারমিশন দিন (chmod 755)</li>
                                                        <li>php.ini-তে upload_max_filesize এবং post_max_size বাড়ান</li>
                                                        <li>GD Library ইনস্টল করা আছে কিনা চেক করুন</li>
                                                        <li>ফাইল টাইপ সাপোর্টেড কিনা নিশ্চিত করুন</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-bug"></i> ডিবাগিং টিপস</h5>
                                    <p class="bangla-font">সিস্টেম ডিবাগ করার উপায়:</p>
                                    <div class="code-block">
// ডিবাগ মোড চালু করুন
config.php ফাইলে:
error_reporting(E_ALL);
ini_set('display_errors', 1);

// লগ ফাইল চেক করুন
admin/logs.php

// PHP এরর লগ চেক করুন
error_log ফাইল

// MySQL কুয়েরি লগ
SHOW PROCESSLIST;
SET GLOBAL general_log = 'ON';
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ -->
                            <div class="doc-section" id="faq">
                                <h3 class="doc-title bangla-font">সাধারণ প্রশ্ন (FAQ)</h3>

                                <div class="accordion" id="faqAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button bangla-font" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                                কিভাবে নতুন অ্যাডমিন অ্যাকাউন্ট তৈরি করব?
                                            </button>
                                        </h2>
                                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body bangla-font">
                                                <p>বর্তমানে শুধুমাত্র ডাটাবেসে সরাসরি INSERT কুয়েরি দিয়ে নতুন অ্যাডমিন অ্যাকাউন্ট তৈরি করা যায়।</p>
                                                <div class="code-block">
INSERT INTO admins (username, password, email, full_name)
VALUES ('newadmin', MD5('password123'), 'admin@email.com', 'নতুন অ্যাডমিন');
                                                </div>
                                                <p class="text-muted"><small>পরবর্তী আপডেটে অ্যাডমিন অ্যাকাউন্ট তৈরি করার অপশন যুক্ত হবে।</small></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bangla-font" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                                কিভাবে সাইট ব্যাকআপ নেব?
                                            </button>
                                        </h2>
                                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body bangla-font">
                                                <p>অ্যাডমিন প্যানেলের "সেটিংস" পেজে "উন্নত সেটিংস" ট্যাবে "ডাটাবেস ব্যাকআপ নিন" বাটনে ক্লিক করুন।</p>
                                                <p>অথবা phpMyAdmin ব্যবহার করে ডাটাবেস এক্সপোর্ট করুন।</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed bangla-font" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                                কিভাবে সাইটের থিম পরিবর্তন করব?
                                            </button>
                                        </h2>
                                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body bangla-font">
                                                <p>বর্তমানে থিম পরিবর্তনের অপশন নেই। থিম পরিবর্তন করতে নিচের ফাইলগুলি এডিট করুন:</p>
                                                <ul>
                                                    <li>assets/css/style.css</li>
                                                    <li>assets/css/responsive.css</li>
                                                    <li>includes/header.php</li>
                                                    <li>includes/footer.php</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- API Documentation -->
                            <div class="doc-section" id="api">
                                <h3 class="doc-title bangla-font">API ডকুমেন্টেশন</h3>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-code"></i> REST API এন্ডপয়েন্ট</h5>
                                    <p class="bangla-font">সিস্টেমটি নিম্নলিখিত API এন্ডপয়েন্ট সাপোর্ট করে:</p>

                                    <div class="mb-3">
                                        <h6 class="bangla-font">নিউজ API</h6>
                                        <div class="code-block">
// সর্বশেষ নিউজ
GET /api/news/latest

// ক্যাটেগরি অনুসারে নিউজ
GET /api/news/category/{category_id}

// নির্দিষ্ট নিউজ
GET /api/news/{id}

// নিউজ সার্চ
GET /api/news/search?q={query}

// API কী প্রয়োজন: api_key=YOUR_API_KEY
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="bangla-font">API ব্যবহার উদাহরণ</h6>
                                        <div class="code-block">
// JavaScript ফেচ API
fetch('/api/news/latest?api_key=your_api_key')
    .then(response => response.json())
    .then(data => {
        console.log(data);
    });

// PHP cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://yoursite.com/api/news/latest');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['API-Key: your_api_key']);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
                                        </div>
                                    </div>

                                    <div class="alert alert-info bangla-font">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>নোট:</strong> API ফিচার উন্নয়নাধীন। সম্পূর্ণ API ডকুমেন্টেশন পরবর্তী আপডেটে যুক্ত হবে।
                                    </div>
                                </div>
                            </div>

                            <!-- Update Guide -->
                            <div class="doc-section" id="updates">
                                <h3 class="doc-title bangla-font">আপডেট গাইড</h3>

                                <div class="doc-card warning">
                                    <h5 class="bangla-font"><i class="fas fa-sync-alt"></i> সিস্টেম আপডেট করা</h5>
                                    <p class="bangla-font">সিস্টেম আপডেট করার ধাপ:</p>
                                    <ol class="bangla-font">
                                        <li><strong>ডাটাবেস ব্যাকআপ নিন:</strong> phpMyAdmin বা অ্যাডমিন প্যানেল থেকে</li>
                                        <li><strong>ফাইল ব্যাকআপ:</strong> সমস্ত ফাইল ZIP করে সংরক্ষণ করুন</li>
                                        <li><strong>নতুন ভার্সন ডাউনলোড:</strong> অফিসিয়াল ওয়েবসাইট থেকে</li>
                                        <li><strong>ফাইল রিপ্লেস:</strong> নতুন ফাইলগুলি আপলোড করুন (config.php বাদে)</li>
                                        <li><strong>ডাটাবেস আপডেট:</strong> update.sql ফাইল ইমপোর্ট করুন</li>
                                        <li><strong>পরীক্ষা:</strong> সমস্ত ফিচার ঠিকমত কাজ করছে কিনা চেক করুন</li>
                                    </ol>

                                    <p class="bangla-font text-danger mt-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>সতর্কতা:</strong> আপডেট করার আগে অবশ্যই সম্পূর্ণ ব্যাকআপ নিন।
                                    </p>
                                </div>

                                <div class="doc-card">
                                    <h5 class="bangla-font"><i class="fas fa-history"></i> ভার্সন হিস্ট্রি</h5>
                                    <table class="table table-sm bangla-font">
                                        <thead>
                                            <tr>
                                                <th>ভার্সন</th>
                                                <th>তারিখ</th>
                                                <th>পরিবর্তনসমূহ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>v1.0.0</td>
                                                <td>২০২৩-০১-১৫</td>
                                                <td>প্রাথমিক রিলিজ</td>
                                            </tr>
                                            <tr>
                                                <td>v1.1.0</td>
                                                <td>২০২৩-০২-১০</td>
                                                <td>অ্যাডমিন প্যানেল যুক্ত</td>
                                            </tr>
                                            <tr>
                                                <td>v1.2.0</td>
                                                <td>২০২৩-০৩-০৫</td>
                                                <td>এসইও অপ্টিমাইজেশন</td>
                                            </tr>
                                            <tr>
                                                <td>v1.3.0</td>
                                                <td>২০২৩-০৪-২০</td>
                                                <td>API সাপোর্ট যুক্ত</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Support -->
                            <div class="doc-section">
                                <div class="doc-card info">
                                    <h4 class="bangla-font"><i class="fas fa-life-ring"></i> সাপোর্ট ও সাহায্য</h4>
                                    <p class="bangla-font">
                                        আপনার যদি কোন প্রশ্ন বা সমস্যা থাকে, তাহলে আমাদের সাথে যোগাযোগ করুন:
                                    </p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="bangla-font">
                                                <li><strong>ইমেইল:</strong> support@bangladeshprotidin.com</li>
                                                <li><strong>ফোন:</strong> +৮৮০ ২ ৯৫৫৫৫৫৫</li>
                                                <li><strong>ওয়েবসাইট:</strong> www.bangladeshprotidin.com</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="bangla-font">
                                                <li><strong>অফিসিয়াল ডকুমেন্টেশন:</strong> docs.bangladeshprotidin.com</li>
                                                <li><strong>গিটহাব রিপোজিটরি:</strong> github.com/bdprotidin</li>
                                                <li><strong>কমিউনিটি ফোরাম:</strong> forum.bangladeshprotidin.com</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="bangla-font mt-3">
                                        <i class="fas fa-lightbulb"></i>
                                        <strong>টিপ:</strong> সমস্যা সমাধানের জন্য প্রথমে এই ডকুমেন্টেশন এবং সিস্টেম লগস চেক করুন।
                                    </p>
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
    <!-- Highlight.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>

    <script>
        // Initialize highlight.js
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.code-block').forEach(block => {
                hljs.highlightElement(block);
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Download as PDF (placeholder)
        function downloadPDF() {
            alert('PDF ডাউনলোড ফিচার উন্নয়নাধীন। শীঘ্রই চালু হবে।');
            // In future, implement PDF generation here
        }

        // Search in documentation
        function searchDocumentation() {
            const searchTerm = document.getElementById('searchDoc').value.toLowerCase();
            const sections = document.querySelectorAll('.doc-section');
            let found = false;

            sections.forEach(section => {
                const text = section.textContent.toLowerCase();
                if(text.includes(searchTerm)) {
                    section.style.backgroundColor = '#fff3cd';
                    found = true;
                } else {
                    section.style.backgroundColor = '';
                }
            });

            if(!found && searchTerm) {
                alert('কোন ফলাফল পাওয়া যায়নি: ' + searchTerm);
            }
        }

        // Add search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const topBar = document.querySelector('.d-flex.justify-content-between');
            const searchBox = document.createElement('div');
            searchBox.className = 'input-group input-group-sm';
            searchBox.style.width = '300px';
            searchBox.innerHTML = `
                <input type="text" id="searchDoc" class="form-control" placeholder="ডকুমেন্টেশনে সার্চ করুন...">
                <button class="btn btn-outline-secondary" onclick="searchDocumentation()">
                    <i class="fas fa-search"></i>
                </button>
            `;
            topBar.appendChild(searchBox);
        });

        // Print documentation
        function printDocumentation() {
            const printContent = document.querySelector('.doc-content').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>বাংলাদেশ প্রতিদিন - অ্যাডমিন ডকুমেন্টেশন</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .doc-title { color: #2c3e50; border-left: 4px solid #3498db; padding-left: 15px; }
                        .code-block { background: #f5f5f5; border: 1px solid #ddd; padding: 10px; margin: 10px 0; font-family: monospace; }
                        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f2f2f2; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>বাংলাদেশ প্রতিদিন - অ্যাডমিন ডকুমেন্টেশন</h1>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                        <hr>
                    </div>
                    ${printContent}
                    <div class="footer">
                        <hr>
                        <p>© <?php echo date('Y'); ?> বাংলাদেশ প্রতিদিন - সর্বস্বত্ব সংরক্ষিত</p>
                        <p>এই ডকুমেন্টেশন শুধুমাত্র অভ্যন্তরীণ ব্যবহারের জন্য</p>
                    </div>
                </body>
                </html>
            `;

            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
</body>
</html>