<?php
//session_start();
require_once '../includes/admin_auth.php';

// Redirect if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

// Handle login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$username = $conn->real_escape_string($_POST['username']);
    $username=trim($_POST['username']);
    $password = $_POST['password'];

    //$query = "SELECT * FROM admins WHERE username = '$username'";
    //$result = $conn->query($query);

    // Prepared statement
    $stmt = $conn->prepare(
        'SELECT a.*, r.role_key
         FROM admins a
         JOIN admin_roles r ON r.id = a.role_id
         WHERE a.username = ? AND a.is_active = 1
         LIMIT 1'
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        // In production, use password_verify()
        // For demo purposes, we'll use a simple check
        if(password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_role'] = $admin['role_key'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = "ইউজারনেম বা পাসওয়ার্ড ভুল";
        }
    } else {
        $error_message = "ইউজারনেম বা পাসওয়ার্ড ভুল";
    }
}
?>


<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#101a2d">
    <title>অ্যাডমিন লগইন - বিজ্ঞান জগৎ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/images/logo1.jpg" type="image/x-icon">
    <style>
        :root {
            color-scheme: light;
            --ink: #172338;
            --muted: #748097;
            --accent: #c6283d;
            --accent-dark: #a91f32;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 32px 18px;
            color: var(--ink);
            background:
                radial-gradient(ellipse at 12% 12%, rgba(52, 94, 148, .28), transparent 34%),
                radial-gradient(ellipse at 88% 90%, rgba(198, 40, 61, .18), transparent 32%),
                #101a2d;
            font-family: "Noto Sans Bengali", "Segoe UI", sans-serif;
        }

        .login-shell {
            width: min(100%, 930px);
            min-height: 550px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .32);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 42px;
            color: #fff;
            background:
                linear-gradient(155deg, rgba(17, 31, 53, .88), rgba(24, 42, 69, .97)),
                #172943;
        }

        .brand-panel::before,
        .brand-panel::after {
            position: absolute;
            width: 280px;
            height: 280px;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .brand-panel::before { top: 21%; right: -155px; }
        .brand-panel::after { right: -95px; bottom: -155px; width: 210px; height: 210px; }

        .brand-content,
        .brand-footer { position: relative; z-index: 1; }

        .brand-mark {
            width: 54px;
            height: 54px;
            display: inline-grid;
            place-items: center;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 16px;
            color: #fff;
            background: rgba(255, 255, 255, .1);
            font-size: 23px;
        }

        .brand-kicker {
            margin-bottom: 12px;
            color: #f1a5af;
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .brand-panel h1 {
            max-width: 370px;
            margin-bottom: 16px;
            font-size: clamp(2rem, 3.4vw, 2.8rem);
            font-weight: 750;
            line-height: 1.35;
        }

        .brand-description {
            max-width: 360px;
            margin: 0;
            color: #c0cada;
            font-size: .98rem;
            line-height: 1.9;
        }

        .brand-footer {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #aebbd0;
            font-size: .83rem;
        }

        .brand-footer i { color: #77d5ac; }

        .form-panel {
            display: flex;
            align-items: center;
            padding: 54px clamp(28px, 5vw, 62px);
        }

        .login-form-wrap { width: 100%; max-width: 360px; margin: 0 auto; }

        .mobile-brand { display: none; }

        .form-eyebrow {
            margin-bottom: 8px;
            color: var(--accent);
            font-size: .84rem;
            font-weight: 700;
        }

        .form-title {
            margin-bottom: 8px;
            font-size: 1.8rem;
            font-weight: 750;
            letter-spacing: -.025em;
        }

        .form-subtitle {
            margin-bottom: 30px;
            color: var(--muted);
            font-size: .94rem;
            line-height: 1.7;
        }

        .form-label { margin-bottom: 8px; font-size: .9rem; font-weight: 650; }

        .login-input {
            min-height: 50px;
            border-color: #dce2eb;
            border-radius: 10px;
            color: var(--ink);
            background: #fbfcfe;
        }

        .login-input:focus {
            border-color: #bd6470;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(198, 40, 61, .1);
        }

        .input-group .login-input { border-right: 0; }
        .input-icon {
            min-width: 48px;
            justify-content: center;
            border-color: #dce2eb;
            border-radius: 10px 0 0 10px;
            color: #8390a5;
            background: #fbfcfe;
        }
        .input-group > .login-input { border-radius: 0 10px 10px 0; }
        .password-toggle {
            border: 1px solid #dce2eb;
            border-left: 0;
            border-radius: 0 10px 10px 0;
            color: #8390a5;
            background: #fbfcfe;
        }
        .password-toggle:hover { color: var(--ink); background: #f2f5f9; }

        .login-submit {
            min-height: 51px;
            margin-top: 10px;
            border: 0;
            border-radius: 10px;
            color: #fff;
            background: linear-gradient(110deg, var(--accent), #dc4052);
            box-shadow: 0 8px 18px rgba(198, 40, 61, .2);
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .login-submit:hover {
            color: #fff;
            background: linear-gradient(110deg, var(--accent-dark), #c93145);
            box-shadow: 0 10px 22px rgba(198, 40, 61, .27);
            transform: translateY(-1px);
        }

        .login-error {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 22px;
            padding: 12px 14px;
            border: 1px solid #f1c7cc;
            border-radius: 10px;
            color: #8d2431;
            background: #fff4f5;
            font-size: .9rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 25px;
            color: #67748a;
            font-size: .9rem;
            text-decoration: none;
        }
        .back-link:hover { color: var(--accent); }

        @media (max-width: 700px) {
            body { padding: 16px; }
            .login-shell {
                min-height: auto;
                max-width: 440px;
                grid-template-columns: 1fr;
                border-radius: 18px;
            }
            .brand-panel { display: none; }
            .form-panel { padding: 34px 26px 30px; }
            .mobile-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 30px;
            }
            .mobile-brand .brand-mark {
                flex: 0 0 48px;
                width: 48px;
                height: 48px;
                margin: 0;
                border-color: #f1d5d9;
                color: var(--accent);
                background: #fff4f5;
                font-size: 20px;
            }
            .mobile-brand-name { margin: 0; font-size: 1rem; font-weight: 750; }
            .mobile-brand-caption { margin: 3px 0 0; color: var(--muted); font-size: .78rem; }
            .form-title { font-size: 1.65rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="brand-panel" aria-label="বিজ্ঞান জগৎ">
            <div class="brand-content">
                <div class="brand-mark" aria-hidden="true"><i class="fas fa-newspaper"></i></div>
                <p class="brand-kicker">বিজ্ঞান জগৎ</p>
                <h1>সংবাদের পেছনের কাজ, এক জায়গায়।</h1>
                <p class="brand-description">আপনার প্রশাসনিক অ্যাকাউন্টে প্রবেশ করে সংবাদ ও পোর্টালের কার্যক্রম পরিচালনা করুন।</p>
            </div>
            <div class="brand-footer">
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                <span>নিরাপদ প্রশাসনিক প্রবেশ</span>
            </div>
        </section>

        <section class="form-panel">
            <div class="login-form-wrap">
                <div class="mobile-brand">
                    <div class="brand-mark" aria-hidden="true"><i class="fas fa-newspaper"></i></div>
                    <div>
                        <p class="mobile-brand-name">বিজ্ঞান জগৎ</p>
                        <p class="mobile-brand-caption">অ্যাডমিন প্যানেল</p>
                    </div>
                </div>

                <p class="form-eyebrow">অ্যাডমিন প্যানেল</p>
                <h2 class="form-title">স্বাগতম</h2>
                <p class="form-subtitle">আপনার অ্যাকাউন্টে প্রবেশ করতে লগইন তথ্য দিন।</p>

                <?php if ($error_message !== ''): ?>
                    <div class="login-error" role="alert">
                        <i class="fas fa-circle-exclamation mt-1" aria-hidden="true"></i>
                        <span><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="on">
                    <div class="mb-4">
                        <label for="username" class="form-label">ইউজারনেম</label>
                        <div class="input-group">
                            <span class="input-group-text input-icon"><i class="fas fa-user" aria-hidden="true"></i></span>
                            <input
                                type="text"
                                class="form-control login-input"
                                id="username"
                                name="username"
                                placeholder="আপনার ইউজারনেম লিখুন"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">পাসওয়ার্ড</label>
                        <div class="input-group">
                            <span class="input-group-text input-icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                            <input
                                type="password"
                                class="form-control login-input"
                                id="password"
                                name="password"
                                placeholder="আপনার পাসওয়ার্ড লিখুন"
                                autocomplete="current-password"
                                required
                            >
                            <button class="btn password-toggle" type="button" id="togglePassword" aria-label="পাসওয়ার্ড দেখান" aria-pressed="false">
                                <i class="fas fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn login-submit w-100">
                        <i class="fas fa-arrow-right-to-bracket me-2" aria-hidden="true"></i>নিরাপদে লগইন করুন
                    </button>
                </form>

                <div class="text-center">
                    <a href="../index.php" class="back-link">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i>
                        <span>মূল ওয়েবসাইটে ফিরে যান</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(showPassword));
            this.setAttribute('aria-label', showPassword ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখান');
            this.innerHTML = '<i class="fas fa-eye' + (showPassword ? '-slash' : '') + '" aria-hidden="true"></i>';
        });
    </script>
</body>
</html>