<?php
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/i18n.php';
    site_language();

    // Site default from admin settings (light | dark | system)
    $site_default_theme = 'light';
    if (file_exists(__DIR__ . '/database.php')) {
        require_once __DIR__ . '/database.php';
        if (isset($conn) && $conn) {
            // Ensure settings row exists
            $conn->query("CREATE TABLE IF NOT EXISTS settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            $theme_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'site_theme' LIMIT 1");
            if ($theme_result && $theme_row = $theme_result->fetch_assoc()) {
                $site_default_theme = $theme_row['setting_value'];
            } else {
                $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('site_theme', 'light')");
                $site_default_theme = 'light';
            }
        }
    }
    if (!in_array($site_default_theme, ['light', 'dark', 'system'], true)) {
        $site_default_theme = 'light';
    }

    // Resolve active theme: user cookie first, then admin default
    $active_theme = 'light';
    if (!empty($_COOKIE['np_theme']) && in_array($_COOKIE['np_theme'], ['light', 'dark'], true)) {
        $active_theme = $_COOKIE['np_theme'];
    } elseif ($site_default_theme === 'system') {
        $active_theme = 'light'; // JS may refine; cookie/system handled client-side once
    } elseif ($site_default_theme === 'dark') {
        $active_theme = 'dark';
    } else {
        $active_theme = 'light';
    }
?>

<!DOCTYPE html>
<html lang="<?php echo site_language(); ?>"
      data-theme="<?php echo htmlspecialchars($active_theme); ?>"
      data-bs-theme="<?php echo htmlspecialchars($active_theme); ?>"
      data-default-theme="<?php echo htmlspecialchars($site_default_theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(isset($page_title) ? translate($page_title) . ' | ' : '', ENT_QUOTES, 'UTF-8'); ?><?php echo htmlspecialchars(translate('বিজ্ঞান জগৎ'), ENT_QUOTES, 'UTF-8'); ?></title>

    <script>
        (function () {
            var COOKIE_NAME = 'np_theme';

            function getCookie(name) {
                var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
                return match ? decodeURIComponent(match[1]) : null;
            }

            function setCookie(name, value, days) {
                var maxAge = days * 24 * 60 * 60;
                document.cookie = name + '=' + encodeURIComponent(value) +
                    '; path=/; max-age=' + maxAge + '; SameSite=Lax';
            }

            function resolveTheme() {
                var cookieTheme = getCookie(COOKIE_NAME);
                if (cookieTheme === 'dark' || cookieTheme === 'light') {
                    return cookieTheme;
                }
                try {
                    var saved = localStorage.getItem('theme');
                    if (saved === 'dark' || saved === 'light') {
                        return saved;
                    }
                } catch (e) {}

                var siteDefault = document.documentElement.getAttribute('data-default-theme') || 'light';
                if (siteDefault === 'system') {
                    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                return siteDefault === 'dark' ? 'dark' : 'light';
            }

            function applyTheme(theme, persist) {
                if (theme !== 'dark' && theme !== 'light') {
                    theme = 'light';
                }
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.setAttribute('data-bs-theme', theme);
                if (document.body) {
                    document.body.classList.toggle('dark-mode', theme === 'dark');
                }
                document.querySelectorAll('.theme-toggle i').forEach(function (icon) {
                    icon.classList.remove('fa-moon', 'fa-sun');
                    icon.classList.add(theme === 'dark' ? 'fa-sun' : 'fa-moon');
                });
                if (persist) {
                    setCookie(COOKIE_NAME, theme, 365);
                    try { localStorage.setItem('theme', theme); } catch (e) {}
                }
            }

            // Apply once on load (do not overwrite storage unless missing)
            var theme = resolveTheme();
            applyTheme(theme, false);

            // If admin default is system and no cookie yet, keep visual in sync
            if (!getCookie(COOKIE_NAME)) {
                try {
                    if (!localStorage.getItem('theme')) {
                        applyTheme(theme, false);
                    }
                } catch (e) {}
            }

            window.applySiteTheme = applyTheme;
            window.setSiteTheme = function (theme) {
                applyTheme(theme, true);
            };
            window.toggleSiteTheme = function () {
                var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                applyTheme(current === 'dark' ? 'light' : 'dark', true);
            };
            window.syncThemeIcons = function () {
                applyTheme(document.documentElement.getAttribute('data-theme') || 'light', false);
            };
        })();
    </script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css?v=4">

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>assets/images/logo1.jpg" type="image/x-icon">

    <style>
        .top-header {
            background-color: var(--theme-top-header-bg, #f5f5f5);
            padding: 8px 0;
            border-bottom: 1px solid var(--theme-border, #ddd);
            color: var(--theme-text, #212529);
        }

        @media (max-width: 768px) {
            .top-header {
                display: none;
            }
        }

        .search-box-container {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.4s ease;
        }

        .search-box-container.active {
            max-height: 100px;
            opacity: 1;
            transform: translateY(0);
            margin-top: 10px;
        }

        body {
            font-family: 'Noto Sans Bengali', sans-serif;
            direction: ltr;
            background-color: var(--theme-bg, #f8f9fa);
            color: var(--theme-text, #212529);
        }
        .bangla-font {
            font-family: 'Noto Sans Bengali', sans-serif;
        }
        .news-title {
            font-weight: 700;
            line-height: 1.4;
            color: var(--theme-text, #212529);
        }
        .breaking-news {
            background-color: #c62828;
            color: white;
            padding: 8px 0;
        }
        .main-logo {
            max-height: 100px;
        }
        .category-nav {
            background-color: var(--theme-nav-bg, #ffffff);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
            z-index: 1050;
        }
        .category-nav a,
        .category-nav .nav-link {
            color: var(--theme-nav-text, #333333) !important;
            font-weight: 500;
            padding: 0.5rem 0.8rem !important;
            border-radius: 6px;
            transition: color 0.3s ease, background-color 0.3s ease, transform 0.2s ease;
        }
        .category-nav a:hover,
        .category-nav .nav-link:hover,
        .category-nav .nav-link:focus {
            color: var(--theme-nav-hover-text, #ffffff) !important;
            background-color: var(--theme-nav-hover-bg, #b71c1c) !important;
            transform: translateY(-2px);
        }
        .category-nav .nav-link.active {
            color: var(--theme-primary, #c62828) !important;
            font-weight: 600;
        }
        .news-card {
            transition: transform 0.3s;
            border: 1px solid var(--theme-border, #eee);
            margin-bottom: 20px;
            background-color: var(--theme-surface, #fff);
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--theme-card-shadow, 0 5px 15px rgba(0,0,0,0.1));
        }
        .featured-news {
            border-left: 4px solid #d32f2f;
        }
        .footer {
            background-color: #222;
            color: #ddd;
            padding: 40px 0 20px;
        }
        .footer a {
            color: #ddd;
            text-decoration: none;
        }
        .footer a:hover {
            color: #fff;
        }
        .copyright {
            background-color: #111;
            color: #aaa;
            padding: 15px 0;
            margin-top: 20px;
        }
        .search-box {
            max-width: 400px;
        }
        @media (max-width: 768px) {
            .main-logo {
                max-height: 70px;
            }
        }

        .top-line {
            height: 3px;
            background-color: var(--theme-line, #111);
            width: 100%;
        }

        .sticky-top.scrolled {
            box-shadow: 0 6px 12px rgba(0,0,0,0.12);
        }

        .offcanvas-body .nav-link {
            color: var(--theme-text, #333) !important;
            padding: 0.5rem 1rem;
            border-radius: 6px;
        }

        .offcanvas-body .nav-link:hover {
            color: #fff !important;
            background-color: var(--theme-nav-hover-bg, #b71c1c) !important;
        }

        header.py-2 {
            background-color: var(--theme-surface, #fff);
            border-bottom-color: var(--theme-border, #dee2e6) !important;
        }
    </style>

    <!-- Theme CSS must load after inline styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/theme.css">
</head>

<body>

<!-- Top Header -->
<div class="top-header d-none d-md-block container">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <?php
                    date_default_timezone_set('Asia/Dhaka');

                    $days = [
                        'Sunday'=>'রবিবার','Monday'=>'সোমবার','Tuesday'=>'মঙ্গলবার',
                        'Wednesday'=>'বুধবার','Thursday'=>'বৃহস্পতিবার',
                        'Friday'=>'শুক্রবার','Saturday'=>'শনিবার'
                    ];

                    $months = [
                        'January'=>'জানুয়ারি','February'=>'ফেব্রুয়ারি','March'=>'মার্চ',
                        'April'=>'এপ্রিল','May'=>'মে','June'=>'জুন','July'=>'জুলাই',
                        'August'=>'আগস্ট','September'=>'সেপ্টেম্বর',
                        'October'=>'অক্টোবর','November'=>'নভেম্বর','December'=>'ডিসেম্বর'
                    ];

                    if (site_language() === 'en') {
                        echo date('l, d F Y');
                    } else {
                        echo $days[date('l')] . ', ' . date('d') . ' ' . $months[date('F')] . ' ' . date('Y');
                    }
                ?>
            </div>
            <div class="col-md-6 text-end">
                <a href="#" class="text-dark me-3"><i class="fas fa-rss"></i> RSS</a>
                <a href="https://www.facebook.com/share/1DXqbygrjz/" class="text-dark me-3"><i class="fab fa-facebook"></i></a>
                <a href="www.x.com" class="text-dark me-3"><i class="fab fa-x"></i></a>
                <a href="www.youtube.com" class="text-dark me-3"><i class="fab fa-youtube"></i></a>
                <a href="www.instagram.com" class="text-dark"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Main Header -->
<header class="py-2 border-bottom site-main-header">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center">

            <!-- Logo -->
            <a href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>assets/images/logo.jpg" class="main-logo">
            </a>

            <!-- Right Side -->
            <div class="d-flex align-items-center">

                <!-- Language Toggle -->
                <?php echo language_toggle(); ?>

                <!-- Theme Toggle -->
                <button type="button" class="theme-toggle me-2" title="<?php echo htmlspecialchars(translate('থিম পরিবর্তন'), ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars(translate('থিম পরিবর্তন'), ENT_QUOTES, 'UTF-8'); ?>"
                        onclick="toggleSiteTheme()">
                    <i class="fas fa-moon"></i>
                </button>

                <!-- Search Icon -->
                <button class="btn btn-outline-secondary btn-sm me-2" id="searchToggle" aria-label="<?php echo htmlspecialchars(translate('খবর খুঁজুন'), ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="fas fa-search"></i>
                </button>

                <!-- Login -->
                <a href="<?php echo ADMIN_URL; ?>login.php" class="btn btn-sm btn-outline-danger header-login">
                    <?php echo translate('লগইন'); ?>
                </a>

                <!-- Subscribe -->
                <button class="btn btn-sm btn-danger ms-2 header-subscribe"
                        type="button"
                        aria-label="<?php echo htmlspecialchars(translate('সাবস্ক্রাইব করুন'), ENT_QUOTES, 'UTF-8'); ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#subscribeModal">
                    <i class="fas fa-envelope me-1"></i>
                    <span class="header-subscribe-label"><?php echo translate('সাবস্ক্রাইব'); ?></span>
                </button>

            </div>
        </div>

        <!-- Search Box (Hidden by default) -->
        <div id="searchBox" class="search-box-container">
        <form action="search.php" method="GET" class="d-flex align-items-center">

        <input type="text" name="q" class="form-control me-2"
               placeholder="<?php echo htmlspecialchars(translate('খবর খুঁজুন...'), ENT_QUOTES, 'UTF-8'); ?>">

        <button type="submit" class="btn btn-danger me-2">
            <i class="fas fa-search"></i>
        </button>

        <!-- Cross Button -->
        <button type="button" class="btn btn-outline-secondary" id="closeSearch">
            <i class="fas fa-times"></i>
        </button>
    </form>
</div>
</div>
</header>

<!-- Subscribe Modal -->
<div class="modal fade" id="subscribeModal" tabindex="-1" aria-labelledby="subscribeModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="subscribeModalTitle"><?php echo translate('সাবস্ক্রাইব করুন'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo htmlspecialchars(translate('বন্ধ করুন'), ENT_QUOTES, 'UTF-8'); ?>"></button>
            </div>

            <div class="modal-body">
                <form class="newsletter-subscribe-form" action="<?php echo SITE_URL; ?>subscribe.php" method="POST">
                    <div class="mb-3">
                        <label for="subscribeName" class="form-label"><?php echo translate('নাম (ঐচ্ছিক)'); ?></label>
                        <input type="text" class="form-control" id="subscribeName" name="name" maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label for="subscribeEmail" class="form-label"><?php echo translate('আপনার ইমেইল'); ?></label>
                        <input type="email" class="form-control" id="subscribeEmail" name="email"
                               placeholder="name@example.com" maxlength="100" required>
                    </div>
                    <div class="subscribe-status d-none" role="status" aria-live="polite"></div>
                    <button type="submit" class="btn btn-danger w-100 subscribe-submit">
                        <span class="subscribe-submit-label"><?php echo translate('সাবস্ক্রাইব'); ?></span>
                    </button>
                    <a class="d-block text-center mt-3 small" href="<?php echo SITE_URL; ?>subscribe.php">
                        <?php echo translate('নিউজলেটার সম্পর্কে আরও জানুন'); ?>
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="top-line container"></div>

<script>
    (function () {
        // Only sync icons — do NOT rewrite cookie/localStorage on load
        if (typeof window.syncThemeIcons === "function") {
            window.syncThemeIcons();
        }

        var openBtn = document.getElementById("searchToggle");
        var closeBtn = document.getElementById("closeSearch");
        var searchBox = document.getElementById("searchBox");
        if (openBtn && searchBox) {
            openBtn.addEventListener("click", function () {
                searchBox.classList.add("active");
            });
        }
        if (closeBtn && searchBox) {
            closeBtn.addEventListener("click", function () {
                searchBox.classList.remove("active");
            });
        }
    })();
</script>
