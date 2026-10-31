<?php
require_once 'includes/i18n.php';
require_once 'includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page_title = translate('নিউজলেটার সাবস্ক্রিপশন');
    require 'includes/header.php';
    require 'includes/navbar.php';
    ?>
    <style>
        .subscribe-page {
            padding: 3rem 0 4rem;
        }

        .subscribe-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            background:
                radial-gradient(circle at 85% 15%, rgba(255,255,255,.18), transparent 28%),
                linear-gradient(130deg, #7f101b 0%, #c62828 55%, #ef5350 100%);
            color: #fff;
            box-shadow: 0 1.5rem 4rem rgba(120, 20, 30, .2);
        }

        .subscribe-hero::after {
            content: "";
            position: absolute;
            width: 20rem;
            height: 20rem;
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 50%;
            right: -7rem;
            bottom: -12rem;
            box-shadow: 0 0 0 2rem rgba(255,255,255,.04), 0 0 0 4rem rgba(255,255,255,.03);
            pointer-events: none;
        }

        .subscribe-hero-content {
            position: relative;
            z-index: 1;
            padding: clamp(2rem, 5vw, 4.5rem);
        }

        .subscribe-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .5rem .85rem;
            border: 1px solid rgba(255,255,255,.28);
            border-radius: 99rem;
            background: rgba(255,255,255,.1);
            font-size: .9rem;
        }

        .subscribe-title {
            max-width: 46rem;
            margin: 1.25rem 0 1rem;
            color: #fff !important;
            font-size: clamp(2.2rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.2;
        }

        .subscribe-lead {
            max-width: 38rem;
            color: rgba(255,255,255,.86);
            font-size: 1.1rem;
            line-height: 1.9;
        }

        .subscribe-content {
            position: relative;
            z-index: 2;
            margin-top: -2.25rem;
        }

        .subscribe-card {
            overflow: hidden;
            border: 1px solid var(--theme-border);
            border-radius: 1.25rem;
            background: var(--theme-surface);
            box-shadow: var(--theme-card-shadow);
        }

        .subscribe-card-body {
            padding: clamp(1.5rem, 4vw, 2.5rem);
        }

        .subscribe-card .form-label {
            color: var(--theme-text);
            font-weight: 600;
        }

        .subscribe-card .form-control {
            min-height: 3.25rem;
            border-radius: .75rem;
        }

        .subscribe-submit {
            min-height: 3.25rem;
            border-radius: .75rem;
            font-weight: 700;
            box-shadow: 0 .5rem 1rem rgba(198,40,40,.18);
        }

        .subscribe-benefit {
            height: 100%;
            padding: 1.25rem;
            border: 1px solid var(--theme-border);
            border-radius: 1rem;
            background: var(--theme-surface);
        }

        .subscribe-benefit-icon {
            display: inline-grid;
            width: 2.75rem;
            height: 2.75rem;
            place-items: center;
            margin-bottom: .85rem;
            border-radius: .85rem;
            background: rgba(198,40,40,.1);
            color: var(--theme-primary);
        }

        .subscribe-note {
            color: var(--theme-text-muted);
            font-size: .9rem;
            line-height: 1.7;
        }

        @media (max-width: 575.98px) {
            .subscribe-page { padding-top: 1.5rem; }
            .subscribe-content { margin-top: -1rem; }
        }
    </style>

    <main class="container subscribe-page">
        <section class="subscribe-hero">
            <div class="subscribe-hero-content">
                <span class="subscribe-eyebrow">
                    <i class="fas fa-envelope-open-text"></i>
                    <?php echo translate('বিজ্ঞান জগৎ নিউজলেটার'); ?>
                </span>
                <h1 class="subscribe-title"><?php echo translate('খবর থাকুক আপনার হাতের মুঠোয়'); ?></h1>
                <p class="subscribe-lead mb-0">
                    <?php echo translate('গুরুত্বপূর্ণ সংবাদ, বিশেষ প্রতিবেদন ও দিনের সেরা আপডেট সরাসরি ইমেইলে পেতে আমাদের নিউজলেটারে যুক্ত হোন।'); ?>
                </p>
            </div>
        </section>

        <div class="row g-4 subscribe-content">
            <div class="col-lg-7">
                <section class="subscribe-card h-100">
                    <div class="subscribe-card-body">
                        <span class="text-danger fw-semibold"><?php echo translate('নিউজলেটারে যোগ দিন'); ?></span>
                        <h2 class="h3 mt-2 mb-2"><?php echo translate('আপডেট মিস করবেন না'); ?></h2>
                        <p class="subscribe-note mb-4">
                            <?php echo translate('আপনার ইমেইল দিন। সাবস্ক্রিপশন নিশ্চিত করতে একটি confirmation email পাঠানো হবে।'); ?>
                        </p>

                        <form class="newsletter-subscribe-form" action="<?php echo htmlspecialchars(SITE_URL . 'subscribe.php', ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                            <div class="mb-3">
                                <label for="newsletterName" class="form-label"><?php echo translate('আপনার নাম'); ?> <span class="text-muted fw-normal"><?php echo translate('(ঐচ্ছিক)'); ?></span></label>
                                <input type="text" class="form-control" id="newsletterName" name="name" maxlength="100" autocomplete="name" placeholder="<?php echo translate('নাম লিখুন'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="newsletterEmail" class="form-label"><?php echo translate('ইমেইল ঠিকানা'); ?></label>
                                <input type="email" class="form-control" id="newsletterEmail" name="email" maxlength="100" autocomplete="email" placeholder="<?php echo translate('আপনার ইমেইল লিখুন'); ?>" required>
                            </div>
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" value="1" id="newsletterConsent" required>
                                <label class="form-check-label subscribe-note" for="newsletterConsent">
                                    <?php echo translate('আমি ইমেইলে নিউজ ও আপডেট পেতে সম্মত।'); ?>
                                </label>
                            </div>
                            <div class="subscribe-status d-none mb-3" role="status" aria-live="polite"></div>
                            <button type="submit" class="btn btn-danger w-100 subscribe-submit">
                                <i class="fas fa-paper-plane me-2"></i>
                                <span class="subscribe-submit-label"><?php echo translate('সাবস্ক্রাইব করুন'); ?></span>
                            </button>
                        </form>

                        <p class="subscribe-note mt-3 mb-0">
                            <i class="fas fa-lock me-1"></i>
                            <?php echo translate('আপনার ইমেইল নিরাপদ থাকবে। যেকোনো সময় আনসাবস্ক্রাইব করতে পারবেন।'); ?>
                        </p>
                    </div>
                </section>
            </div>

            <div class="col-lg-5">
                <div class="d-grid gap-3 h-100">
                    <article class="subscribe-benefit">
                        <span class="subscribe-benefit-icon"><i class="fas fa-bolt"></i></span>
                        <h3 class="h5"><?php echo translate('দ্রুত গুরুত্বপূর্ণ আপডেট'); ?></h3>
                        <p class="subscribe-note mb-0"><?php echo translate('দেশ-বিদেশের জরুরি খবর ও দিনের উল্লেখযোগ্য ঘটনা এক জায়গায় পান।'); ?></p>
                    </article>
                    <article class="subscribe-benefit">
                        <span class="subscribe-benefit-icon"><i class="fas fa-newspaper"></i></span>
                        <h3 class="h5"><?php echo translate('বাছাই করা সংবাদ'); ?></h3>
                        <p class="subscribe-note mb-0"><?php echo translate('বিশেষ প্রতিবেদন, বিশ্লেষণ এবং পাঠকের জন্য প্রয়োজনীয় খবর।'); ?></p>
                    </article>
                    <article class="subscribe-benefit">
                        <span class="subscribe-benefit-icon"><i class="fas fa-user-shield"></i></span>
                        <h3 class="h5"><?php echo translate('আপনার নিয়ন্ত্রণে'); ?></h3>
                        <p class="subscribe-note mb-0"><?php echo translate('আপনার সম্মতি ছাড়া অন্য কাজে ইমেইল ব্যবহার করা হবে না।'); ?></p>
                    </article>
                </div>
            </div>
        </div>
    </main>

    <?php require 'includes/footer.php'; ?>
    <?php
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$respond = static function (array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(['success' => false, 'message' => 'Invalid request method'], 405);
}

$email = trim($_POST['email'] ?? '');
$name = trim($_POST['name'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $respond(['success' => false, 'message' => translate('দয়া করে একটি সঠিক ইমেইল ঠিকানা দিন।')], 422);
}

$nameLength = preg_match_all('/./us', $name);
if (strlen($email) > 100 || $nameLength === false || $nameLength > 100) {
    $respond(['success' => false, 'message' => translate('ইমেইল বা নামের দৈর্ঘ্য বেশি।')], 422);
}

$siteUrl = rtrim(SITE_URL, '/') . '/';
$sendConfirmation = static function (
    string $email,
    string $name,
    string $confirmationToken,
    string $unsubscribeToken
) use ($siteUrl): bool {
    $subject = 'Newsletter subscription confirmation - ' . SITE_NAME;
    $confirmationLink = $siteUrl . 'confirm_subscription.php?token=' . rawurlencode($confirmationToken);
    $unsubscribeLink = $siteUrl . 'unsubscribe.php?token=' . rawurlencode($unsubscribeToken);
    $message = translate('প্রিয় ') . ($name !== '' ? $name : translate('গ্রাহক')) . ",\n\n"
        . translate('আপনার ইমেইল ঠিকানা ') . $email . translate(' আমাদের নিউজলেটারে যোগ করতে নিচের লিংক ক্লিক করুন:') . "\n"
        . $confirmationLink . "\n\n"
        . translate('আনসাবস্ক্রাইব লিংক: ') . $unsubscribeLink . "\n\n"
        . translate('ধন্যবাদ,') . "\n" . SITE_NAME . translate(' টিম');

    return @mail($email, $subject, $message, "Content-Type: text/plain; charset=UTF-8");
};

try {
    $checkStmt = $conn->prepare(
        'SELECT id, is_active, confirmed, confirmation_token FROM subscribers WHERE email = ?'
    );
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $subscriber = $result->fetch_assoc();

    if ($subscriber && $subscriber['is_active'] && $subscriber['confirmed']) {
        $respond([
            'success' => false,
            'message' => translate('এই ইমেইলটি ইতিমধ্যে আমাদের সাবস্ক্রাইবার লিস্টে আছে।'),
        ]);
    }

    $confirmationToken = bin2hex(random_bytes(16));
    $unsubscribeToken = bin2hex(random_bytes(16));

    if ($subscriber) {
        $updateStmt = $conn->prepare(
            'UPDATE subscribers
             SET name = ?, is_active = TRUE, confirmed = FALSE, confirmation_token = ?,
                 unsubscribe_token = ?, subscription_date = NOW()
             WHERE id = ?'
        );
        $updateStmt->bind_param(
            'sssi',
            $name,
            $confirmationToken,
            $unsubscribeToken,
            $subscriber['id']
        );
        $updateStmt->execute();
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $insertStmt = $conn->prepare(
            'INSERT INTO subscribers (email, name, confirmation_token, unsubscribe_token, ip_address)
             VALUES (?, ?, ?, ?, ?)'
        );
        $insertStmt->bind_param('sssss', $email, $name, $confirmationToken, $unsubscribeToken, $ip);
        $insertStmt->execute();
    }

    if (!$sendConfirmation($email, $name, $confirmationToken, $unsubscribeToken)) {
        error_log('Newsletter confirmation email could not be sent; check the PHP SMTP configuration.');
        $respond([
            'success' => false,
            'message' => translate('আপনার তথ্য সংরক্ষিত হয়েছে, কিন্তু কনফার্মেশন ইমেইল পাঠানো যায়নি। সার্ভারের ইমেইল সেটিংস পরীক্ষা করে আবার চেষ্টা করুন।'),
        ], 500);
    }

    $respond([
        'success' => true,
        'message' => translate('সাবস্ক্রিপশনের জন্য ধন্যবাদ! আপনার ইমেইলে পাঠানো কনফার্মেশন লিংকে ক্লিক করুন।'),
    ]);
} catch (mysqli_sql_exception $exception) {
    error_log('Newsletter subscription database error: ' . $exception->getMessage());
    $respond([
        'success' => false,
        'message' => translate('সাবস্ক্রিপশন সংরক্ষণ করা যায়নি। অনুগ্রহ করে পরে আবার চেষ্টা করুন।'),
    ], 500);
}
