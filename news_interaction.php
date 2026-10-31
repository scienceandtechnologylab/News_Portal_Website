<?php
require_once __DIR__ . '/includes/i18n.php';
site_language();
require_once __DIR__ . '/includes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit(translate('এই অনুরোধটি গ্রহণযোগ্য নয়।'));
}

$postedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['news_interaction_csrf'] ?? null;
if (!is_string($postedToken) || !is_string($sessionToken) || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(400);
    exit(translate('অনুরোধটি যাচাই করা যায়নি। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।'));
}

$newsIdInput = $_POST['news_id'] ?? '';
$newsId = is_string($newsIdInput) && ctype_digit($newsIdInput) ? (int)$newsIdInput : 0;
if ($newsId < 1) {
    http_response_code(400);
    exit(translate('নিউজটি সঠিকভাবে নির্বাচন করা হয়নি।'));
}

$newsStmt = $conn->prepare("SELECT slug FROM news WHERE id = ? AND status = 'published' LIMIT 1");
$newsStmt->bind_param('i', $newsId);
$newsStmt->execute();
$news = $newsStmt->get_result()->fetch_assoc();
if (!$news) {
    http_response_code(404);
    exit(translate('নিউজটি পাওয়া যায়নি।'));
}

$action = $_POST['interaction_action'] ?? '';
$flashType = 'success';
$parentId = null;

if ($action === 'like') {
    $visitorToken = $_COOKIE['news_visitor'] ?? '';
    if (!is_string($visitorToken) || !preg_match('/^[a-f0-9]{64}$/', $visitorToken)) {
        $visitorToken = bin2hex(random_bytes(32));
        setcookie('news_visitor', $visitorToken, [
            'expires' => time() + 31536000,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    $visitorHash = hash('sha256', $visitorToken);
    $likeStmt = $conn->prepare('INSERT INTO news_likes (news_id, visitor_hash) VALUES (?, ?)');
    $likeStmt->bind_param('is', $newsId, $visitorHash);
    try {
        $likeStmt->execute();
        $flashMessage = 'আপনার লাইকটি যোগ হয়েছে।';
    } catch (mysqli_sql_exception $exception) {
        if ((int)$exception->getCode() !== 1062) {
            throw $exception;
        }
        $flashType = 'info';
        $flashMessage = 'আপনি এই নিউজে আগেই লাইক দিয়েছেন।';
    }
} elseif ($action === 'comment' || $action === 'reply') {
    $nameInput = $_POST['comment_name'] ?? null;
    $commentInput = $_POST['comment_body'] ?? null;
    if (!is_string($nameInput) || !is_string($commentInput)) {
        http_response_code(400);
        exit(translate('নাম ও মন্তব্য লিখুন।'));
    }

    $commentName = trim($nameInput);
    $commentBody = trim($commentInput);
    $nameLength = preg_match_all('/./us', $commentName);
    $commentLength = preg_match_all('/./us', $commentBody);
    if ($action === 'reply') {
        $parentIdInput = $_POST['parent_comment_id'] ?? '';
        $parentId = is_string($parentIdInput) && ctype_digit($parentIdInput) ? (int)$parentIdInput : 0;
        if ($parentId < 1) {
            http_response_code(400);
            exit(translate('যে মন্তব্যের উত্তর দিতে চান সেটি সঠিকভাবে নির্বাচন করুন।'));
        }

        $parentStmt = $conn->prepare(
            'SELECT id FROM news_comments WHERE id = ? AND news_id = ? AND parent_id IS NULL LIMIT 1'
        );
        $parentStmt->bind_param('ii', $parentId, $newsId);
        $parentStmt->execute();
        if (!$parentStmt->get_result()->fetch_assoc()) {
            http_response_code(400);
            exit(translate('এই মন্তব্যটির উত্তর দেওয়া যাচ্ছে না।'));
        }
    }

    if ($commentName === '' || $nameLength === false || $nameLength > 80) {
        $_SESSION['news_interaction_flash'] = [
            'news_id' => $newsId,
            'type' => 'danger',
            'message' => 'নাম লিখুন (সর্বোচ্চ ৮০ অক্ষর)।',
            'form' => $action,
            'parent_id' => $parentId,
            'comment_name' => $commentName,
            'comment_body' => $commentBody,
        ];
        header('Location: news.php?slug=' . rawurlencode($news['slug']) . '#comments', true, 303);
        exit;
    }
    if ($commentBody === '' || $commentLength === false || $commentLength > 2000) {
        $_SESSION['news_interaction_flash'] = [
            'news_id' => $newsId,
            'type' => 'danger',
            'message' => 'মন্তব্য লিখুন (সর্বোচ্চ ২০০০ অক্ষর)।',
            'form' => $action,
            'parent_id' => $parentId,
            'comment_name' => $commentName,
            'comment_body' => $commentBody,
        ];
        header('Location: news.php?slug=' . rawurlencode($news['slug']) . '#comments', true, 303);
        exit;
    }

    if ($action === 'reply') {
        $commentStmt = $conn->prepare(
            'INSERT INTO news_comments (news_id, parent_id, commenter_name, comment_body)
             VALUES (?, ?, ?, ?)'
        );
        $commentStmt->bind_param('iiss', $newsId, $parentId, $commentName, $commentBody);
    } else {
        $commentStmt = $conn->prepare(
            'INSERT INTO news_comments (news_id, commenter_name, comment_body)
             VALUES (?, ?, ?)'
        );
        $commentStmt->bind_param('iss', $newsId, $commentName, $commentBody);
    }
    $commentStmt->execute();
    $flashMessage = $action === 'reply'
        ? 'আপনার উত্তরটি প্রকাশ করা হয়েছে।'
        : 'আপনার মন্তব্যটি প্রকাশ করা হয়েছে।';
} else {
    http_response_code(400);
    exit(translate('অনুরোধের ধরনটি সঠিক নয়।'));
}

$_SESSION['news_interaction_flash'] = [
    'news_id' => $newsId,
    'type' => $flashType,
    'message' => $flashMessage,
    'form' => $action,
    'parent_id' => $parentId,
];
header('Location: news.php?slug=' . rawurlencode($news['slug']) . ($action === 'like' ? '#news-reactions' : '#comments'), true, 303);
exit;
