<?php
require_once 'includes/i18n.php';
site_language();
require_once 'includes/database.php';
require_once 'includes/advertisement.php';

// Get news slug from URL
$news_slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';

if(empty($news_slug)) {
    header('Location: index.php');
    exit;
}

// Get news details
$news_query = "SELECT n.*, c.name as category_name, c.name_en as category_name_en, c.slug as category_slug
              FROM news n
              LEFT JOIN categories c ON n.category_id = c.id
              WHERE n.slug = '$news_slug' AND n.status = 'published'";
$news_result = $conn->query($news_query);

if($news_result->num_rows == 0) {
    header('Location: index.php');
    exit;
}

$news = $news_result->fetch_assoc();
$newsTitle = localized_news_value($news, 'title');
$newsContent = localized_news_value($news, 'content');
$newsExcerpt = localized_news_value($news, 'excerpt');
$page_title = $newsTitle . ' - ' . translate('বিজ্ঞান জগৎ');

// Increment view count
$news_id = $news['id'];
$conn->query("UPDATE news SET views = views + 1 WHERE id = $news_id");

$newsInteractionCsrf = $_SESSION['news_interaction_csrf'] ?? bin2hex(random_bytes(32));
$_SESSION['news_interaction_csrf'] = $newsInteractionCsrf;

$likeCountStmt = $conn->prepare('SELECT COUNT(*) AS total FROM news_likes WHERE news_id = ?');
$likeCountStmt->bind_param('i', $news_id);
$likeCountStmt->execute();
$likeCount = (int)$likeCountStmt->get_result()->fetch_assoc()['total'];

$commentCountStmt = $conn->prepare('SELECT COUNT(*) AS total FROM news_comments WHERE news_id = ?');
$commentCountStmt->bind_param('i', $news_id);
$commentCountStmt->execute();
$commentCount = (int)$commentCountStmt->get_result()->fetch_assoc()['total'];

$commentsStmt = $conn->prepare(
    'SELECT comment.id, comment.parent_id, comment.commenter_name, comment.comment_body, comment.created_at
     FROM (
         SELECT id, created_at
         FROM news_comments
         WHERE news_id = ? AND parent_id IS NULL
         ORDER BY created_at DESC, id DESC
         LIMIT 100
     ) AS top_comments
     INNER JOIN news_comments AS comment
         ON comment.id = top_comments.id OR comment.parent_id = top_comments.id
     ORDER BY top_comments.created_at DESC, top_comments.id DESC,
              comment.parent_id IS NOT NULL, comment.created_at ASC, comment.id ASC'
);
$commentsStmt->bind_param('i', $news_id);
$commentsStmt->execute();
$commentsResult = $commentsStmt->get_result();
$commentsByParent = [];
while ($comment = $commentsResult->fetch_assoc()) {
    if ($comment['parent_id'] === null) {
        $comment['replies'] = [];
        $commentsByParent[(int)$comment['id']] = $comment;
    } elseif (isset($commentsByParent[(int)$comment['parent_id']])) {
        $commentsByParent[(int)$comment['parent_id']]['replies'][] = $comment;
    }
}

$visitorToken = $_COOKIE['news_visitor'] ?? '';
$likedByVisitor = false;
if (is_string($visitorToken) && preg_match('/^[a-f0-9]{64}$/', $visitorToken)) {
    $visitorHash = hash('sha256', $visitorToken);
    $likedStmt = $conn->prepare('SELECT 1 FROM news_likes WHERE news_id = ? AND visitor_hash = ? LIMIT 1');
    $likedStmt->bind_param('is', $news_id, $visitorHash);
    $likedStmt->execute();
    $likedByVisitor = (bool)$likedStmt->get_result()->fetch_row();
}

$interactionFlash = $_SESSION['news_interaction_flash'] ?? null;
if (is_array($interactionFlash) && (int)($interactionFlash['news_id'] ?? 0) === (int)$news_id) {
    unset($_SESSION['news_interaction_flash']);
} else {
    $interactionFlash = null;
}

// Get related news (same category, exclude current news)
$related_query = "SELECT * FROM news WHERE category_id = " . $news['category_id'] . " AND id != $news_id AND status = 'published' ORDER BY created_at DESC LIMIT 4";
$related_result = $conn->query($related_query);

// Get previous and next news
$prev_query = "SELECT * FROM news WHERE id < $news_id AND status = 'published' ORDER BY id DESC LIMIT 1";
$next_query = "SELECT * FROM news WHERE id > $news_id AND status = 'published' ORDER BY id ASC LIMIT 1";

$prev_result = $conn->query($prev_query);
$next_result = $conn->query($next_query);

$prev_news = $prev_result->num_rows > 0 ? $prev_result->fetch_assoc() : null;
$next_news = $next_result->num_rows > 0 ? $next_result->fetch_assoc() : null;

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    <!-- News Content -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb bangla-font">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none"><i class="fas fa-home"></i> <?php echo translate('হোম'); ?></a></li>
                    <li class="breadcrumb-item"><a href="category.php?slug=<?php echo $news['category_slug']; ?>" class="text-decoration-none"><?php echo htmlspecialchars(localized_category_name($news['category_name'], $news['category_name_en']), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo translate('নিউজ বিস্তারিত'); ?></li>
                </ol>
            </nav>

            <!-- News Title and Meta -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-danger fs-6 mb-2"><?php echo htmlspecialchars(localized_category_name($news['category_name'], $news['category_name_en']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if($news['is_featured'] == 1): ?>
                            <span class="badge bg-warning fs-6 mb-2"><?php echo translate('শীর্ষ নিউজ'); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted bangla-font">
                            <i class="fas fa-eye"></i> <?php echo number_format($news['views'] + 1); ?> <?php echo translate('বার দেখা হয়েছে'); ?>
                        </div>
                    </div>

                    <h1 class="bangla-font news-title mb-4"><?php echo htmlspecialchars($newsTitle, ENT_QUOTES, 'UTF-8'); ?></h1>

                    <div class="d-flex justify-content-between border-top border-bottom py-3 mb-4">
                        <div>
                            <i class="fas fa-user text-danger"></i>
                            <span class="bangla-font ms-2"><?php echo htmlspecialchars(translate($news['author']), ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div>
                            <i class="fas fa-calendar text-danger"></i>
                            <span class="bangla-font ms-2"><?php echo date('d F, Y h:i A', strtotime($news['created_at'])); ?></span>
                        </div>
                        <div>
                            <i class="fas fa-clock text-danger"></i>
                            <?php $readingMinutes = (int)ceil(str_word_count(strip_tags($newsContent)) / 200); ?>
                            <span class="bangla-font ms-2"><?php echo translate('পড়তে সময়: '); ?><?php echo $readingMinutes; ?> <?php echo site_language() === 'en' ? ($readingMinutes === 1 ? 'minute' : 'minutes') : translate('মিনিট'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="card mb-4">
                <div class="card-body p-0">
                    <img src="<?php echo SITE_URL . 'assets/images/news/' . $news['featured_image']; ?>"
                         alt="<?php echo htmlspecialchars($newsTitle, ENT_QUOTES, 'UTF-8'); ?>"
                         class="img-fluid rounded-top news-article-featured-image">
                    <div class="p-3 text-center bg-light">
                        <small class="text-muted bangla-font">
                            <?php echo translate('ছবি: বিজ্ঞান জগৎ'); ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- News Content -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="news-content bangla-font" style="font-size: 1.1rem; line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars_decode($newsContent)); ?>
                    </div>

                    <!-- Tags -->
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="bangla-font mb-3"><i class="fas fa-tags"></i> <?php echo translate('ট্যাগ:'); ?></h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-secondary p-2 bangla-font"><?php echo htmlspecialchars(localized_category_name($news['category_name'], $news['category_name_en']), ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="badge bg-secondary p-2 bangla-font"><?php echo translate('বাংলাদেশ'); ?></span>
                            <span class="badge bg-secondary p-2 bangla-font"><?php echo translate('নিউজ'); ?></span>
                            <span class="badge bg-secondary p-2 bangla-font"><?php echo translate('খবর'); ?></span>
                            <span class="badge bg-secondary p-2 bangla-font"><?php echo translate('লেটেস্ট'); ?></span>
                        </div>
                    </div>

                    <!-- Share Buttons -->
                    <div class="mt-5 pt-4 border-top">
                        <h6 class="bangla-font mb-3"><i class="fas fa-share-alt"></i> <?php echo translate('শেয়ার করুন:'); ?></h6>
                        <div class="d-flex gap-2">
                            <button onclick="shareOnFacebook()" class="btn btn-primary btn-sm">
                                <i class="fab fa-facebook"></i> Facebook
                            </button>
                            <button onclick="shareOnTwitter()" class="btn btn-info btn-sm text-white">
                                <i class="fab fa-twitter"></i> Twitter
                            </button>
                            <button onclick="shareOnWhatsApp()" class="btn btn-success btn-sm">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                            <button onclick="copyLink()" class="btn btn-secondary btn-sm">
                                <i class="fas fa-link"></i> <?php echo translate('লিংক কপি'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation (Previous/Next) -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if($prev_news): ?>
                            <a href="news.php?slug=<?php echo $prev_news['slug']; ?>" class="text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-chevron-left fa-2x text-danger"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <small class="text-muted bangla-font"><?php echo translate('পূর্ববর্তী নিউজ'); ?></small>
                                        <h6 class="bangla-font mb-0"><?php echo htmlspecialchars(truncate_text(localized_news_value($prev_news, 'title'), 60), ENT_QUOTES, 'UTF-8'); ?></h6>
                                    </div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if($next_news): ?>
                            <a href="news.php?slug=<?php echo $next_news['slug']; ?>" class="text-decoration-none">
                                <div class="d-flex align-items-center p-3 border rounded text-end">
                                    <div class="flex-grow-1 me-3">
                                        <small class="text-muted bangla-font"><?php echo translate('পরবর্তী নিউজ'); ?></small>
                                        <h6 class="bangla-font mb-0"><?php echo htmlspecialchars(truncate_text(localized_news_value($next_news, 'title'), 60), ENT_QUOTES, 'UTF-8'); ?></h6>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-chevron-right fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <section id="news-reactions" class="card mb-4">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="bangla-font mb-1"><?php echo translate('আপনার মতামত জানান'); ?></h5>
                        <p class="text-muted bangla-font mb-0"><?php echo translate('নিউজটি ভালো লাগলে লাইক দিন, অথবা আপনার মতামত লিখুন।'); ?></p>
                    </div>
                    <form method="post" action="news_interaction.php" class="m-0">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($newsInteractionCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="news_id" value="<?php echo (int)$news_id; ?>">
                        <input type="hidden" name="interaction_action" value="like">
                        <button type="submit" class="btn <?php echo $likedByVisitor ? 'btn-danger' : 'btn-outline-danger'; ?>" <?php echo $likedByVisitor ? 'disabled aria-pressed="true"' : 'aria-pressed="false"'; ?>>
                            <i class="fas fa-heart me-1" aria-hidden="true"></i>
                            <?php echo $likedByVisitor ? translate('লাইক দিয়েছেন') : translate('লাইক দিন'); ?>
                            <span class="badge text-bg-light ms-1"><?php echo number_format($likeCount); ?></span>
                        </button>
                    </form>
                    <?php if ($interactionFlash && ($interactionFlash['form'] ?? '') === 'like'): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($interactionFlash['type'], ENT_QUOTES, 'UTF-8'); ?> bangla-font w-100 mb-0" role="status">
                            <?php echo htmlspecialchars(translate($interactionFlash['message']), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section id="comments" class="card mb-4">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0 bangla-font">
                        <i class="fas fa-comments text-danger" aria-hidden="true"></i>
                        <?php echo translate('মন্তব্য'); ?> <span class="badge bg-secondary"><?php echo number_format($commentCount); ?></span>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($interactionFlash && ($interactionFlash['form'] ?? '') === 'comment'): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($interactionFlash['type'], ENT_QUOTES, 'UTF-8'); ?> bangla-font" role="status">
                            <?php echo htmlspecialchars(translate($interactionFlash['message']), ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php $commentFormOpen = $interactionFlash && ($interactionFlash['form'] ?? '') === 'comment'; ?>
                    <div class="comment-composer mb-4">
                        <button
                            type="button"
                            class="comment-composer-trigger"
                            data-bs-toggle="collapse"
                            data-bs-target="#newCommentForm"
                            aria-expanded="<?php echo $commentFormOpen ? 'true' : 'false'; ?>"
                            aria-controls="newCommentForm"
                        >
                            <span class="comment-composer-avatar" aria-hidden="true"><i class="fas fa-user"></i></span>
                            <span class="comment-composer-prompt bangla-font"><?php echo translate('মন্তব্য লিখুন...'); ?></span>
                            <i class="fas fa-pen comment-composer-icon" aria-hidden="true"></i>
                        </button>

                        <div id="newCommentForm" class="collapse mt-3 <?php echo $commentFormOpen ? 'show' : ''; ?>">
                            <form method="post" action="news_interaction.php" class="comment-composer-form">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($newsInteractionCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="news_id" value="<?php echo (int)$news_id; ?>">
                                <input type="hidden" name="interaction_action" value="comment">
                                <div class="mb-3">
                                    <label for="comment_name" class="form-label bangla-font"><?php echo translate('আপনার নাম'); ?></label>
                                    <input type="text" class="form-control" id="comment_name" name="comment_name" maxlength="80" autocomplete="name" value="<?php echo htmlspecialchars(($interactionFlash['form'] ?? '') === 'comment' ? ($interactionFlash['comment_name'] ?? '') : '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="comment_body" class="form-label bangla-font"><?php echo translate('আপনার মন্তব্য'); ?></label>
                                    <textarea class="form-control" id="comment_body" name="comment_body" rows="4" maxlength="2000" required><?php echo htmlspecialchars(($interactionFlash['form'] ?? '') === 'comment' ? ($interactionFlash['comment_body'] ?? '') : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <div class="form-text bangla-font"><?php echo translate('সর্বোচ্চ ২০০০ অক্ষর। মন্তব্য জমা দিলে তা সঙ্গে সঙ্গে প্রকাশিত হবে।'); ?></div>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#newCommentForm" aria-controls="newCommentForm">
                                        <?php echo translate('বাতিল'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-paper-plane me-1" aria-hidden="true"></i> <?php echo translate('মন্তব্য প্রকাশ করুন'); ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="list-group list-group-flush">
                        <?php if ($commentsByParent): ?>
                            <?php foreach ($commentsByParent as $comment): ?>
                                <?php
                                $commentId = (int)$comment['id'];
                                $replyFormId = 'reply-form-' . $commentId;
                                $replyNotice = $interactionFlash
                                    && ($interactionFlash['form'] ?? '') === 'reply'
                                    && (int)($interactionFlash['parent_id'] ?? 0) === $commentId;
                                $replyError = $replyNotice && ($interactionFlash['type'] ?? '') === 'danger';
                                ?>
                                <article class="list-group-item px-0 py-3" id="comment-<?php echo $commentId; ?>">
                                    <div class="d-flex gap-3">
                                        <div class="comment-avatar rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" aria-hidden="true">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <h3 class="h6 bangla-font fw-bold mb-1"><?php echo htmlspecialchars($comment['commenter_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                                <time class="small text-muted text-nowrap" datetime="<?php echo htmlspecialchars(date(DATE_ATOM, strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($comment['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                                </time>
                                            </div>
                                            <p class="bangla-font mb-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($comment['comment_body'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <button
                                                class="btn btn-link btn-sm p-0 text-decoration-none"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#<?php echo $replyFormId; ?>"
                                                aria-expanded="<?php echo $replyNotice ? 'true' : 'false'; ?>"
                                                aria-controls="<?php echo $replyFormId; ?>"
                                            >
                                                <i class="fas fa-reply me-1" aria-hidden="true"></i><?php echo translate('উত্তর দিন'); ?>
                                            </button>

                                            <div id="<?php echo $replyFormId; ?>" class="collapse mt-3 <?php echo $replyNotice ? 'show' : ''; ?>">
                                                <?php if ($replyNotice): ?>
                                                    <div class="alert alert-<?php echo htmlspecialchars($interactionFlash['type'], ENT_QUOTES, 'UTF-8'); ?> py-2" role="<?php echo $replyError ? 'alert' : 'status'; ?>">
                                                        <?php echo htmlspecialchars(translate($interactionFlash['message']), ENT_QUOTES, 'UTF-8'); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <form method="post" action="news_interaction.php">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($newsInteractionCsrf, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="news_id" value="<?php echo (int)$news_id; ?>">
                                                    <input type="hidden" name="interaction_action" value="reply">
                                                    <input type="hidden" name="parent_comment_id" value="<?php echo $commentId; ?>">
                                                    <div class="mb-2">
                                                        <label for="reply-name-<?php echo $commentId; ?>" class="visually-hidden"><?php echo translate('আপনার নাম'); ?></label>
                                                        <input type="text" class="form-control form-control-sm" id="reply-name-<?php echo $commentId; ?>" name="comment_name" maxlength="80" autocomplete="name" placeholder="<?php echo translate('আপনার নাম'); ?>" value="<?php echo $replyError ? htmlspecialchars($interactionFlash['comment_name'] ?? '', ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label for="reply-body-<?php echo $commentId; ?>" class="visually-hidden"><?php echo translate('আপনার উত্তর'); ?></label>
                                                        <textarea class="form-control form-control-sm" id="reply-body-<?php echo $commentId; ?>" name="comment_body" rows="2" maxlength="2000" placeholder="<?php echo translate('একটি উত্তর লিখুন...'); ?>" required><?php echo $replyError ? htmlspecialchars($interactionFlash['comment_body'] ?? '', ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-paper-plane me-1" aria-hidden="true"></i><?php echo translate('উত্তর প্রকাশ করুন'); ?>
                                                    </button>
                                                </form>
                                            </div>

                                            <?php if ($comment['replies']): ?>
                                                <div class="comment-replies mt-3 ps-3">
                                                    <?php foreach ($comment['replies'] as $reply): ?>
                                                        <div class="d-flex gap-2 mb-3">
                                                            <div class="comment-avatar comment-avatar-sm rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center flex-shrink-0" aria-hidden="true">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                                    <h4 class="small bangla-font fw-bold mb-1"><?php echo htmlspecialchars($reply['commenter_name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                                                    <time class="small text-muted text-nowrap" datetime="<?php echo htmlspecialchars(date(DATE_ATOM, strtotime($reply['created_at'])), ENT_QUOTES, 'UTF-8'); ?>">
                                                                        <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($reply['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                                                    </time>
                                                                </div>
                                                                <p class="bangla-font small mb-0" style="white-space: pre-wrap;"><?php echo htmlspecialchars($reply['comment_body'], ENT_QUOTES, 'UTF-8'); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted bangla-font mb-0"><?php echo translate('এখনো কোনো মন্তব্য নেই। প্রথম মন্তব্যটি আপনিই লিখুন।'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Related News -->
            <?php if($related_result && $related_result->num_rows > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-link"></i> <?php echo translate('সম্পর্কিত নিউজ'); ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php while($related = $related_result->fetch_assoc()): ?>
                        <?php $relatedTitle = localized_news_value($related, 'title'); ?>
                        <a href="news.php?slug=<?php echo $related['slug']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <img src="<?php echo SITE_URL . 'assets/images/news/' . $related['featured_image']; ?>"
                                         alt="<?php echo htmlspecialchars($relatedTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                         class="rounded news-featured-image"
                                         style="width: 80px; height: 60px;">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="bangla-font mb-1" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars(truncate_text($relatedTitle, 70), ENT_QUOTES, 'UTF-8'); ?>
                                    </h6>
                                    <small class="text-muted bangla-font">
                                        <i class="fas fa-clock"></i> <?php echo date('d M', strtotime($related['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Most Popular News -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-fire"></i> <?php echo translate('জনপ্রিয় নিউজ'); ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $popular_query = "SELECT * FROM news WHERE status = 'published' ORDER BY views DESC LIMIT 5";
                    $popular_result = $conn->query($popular_query);

                    if($popular_result && $popular_result->num_rows > 0):
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php $counter = 1; ?>
                        <?php while($popular = $popular_result->fetch_assoc()): ?>
                        <li class="list-group-item position-relative">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-dark me-2"><?php echo $counter; ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="news.php?slug=<?php echo rawurlencode($popular['slug']); ?>" class="text-decoration-none stretched-link">
                                        <h6 class="bangla-font mb-1" style="font-size: 0.9rem;">
                                            <?php echo htmlspecialchars(truncate_text(localized_news_value($popular, 'title'), 60), ENT_QUOTES, 'UTF-8'); ?>
                                        </h6>
                                    </a>
                                    <small class="text-muted bangla-font">
                                        <i class="fas fa-eye"></i> <?php echo number_format($popular['views']); ?> <?php echo translate('ভিউ'); ?>
                                    </small>
                                </div>
                            </div>
                        </li>
                        <?php $counter++; ?>
                        <?php endwhile; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest News -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-clock"></i> <?php echo translate('সর্বশেষ নিউজ'); ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $latest_query = "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 5";
                    $latest_result = $conn->query($latest_query);

                    if($latest_result && $latest_result->num_rows > 0):
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php while($latest = $latest_result->fetch_assoc()): ?>
                        <li class="list-group-item position-relative">
                            <a href="news.php?slug=<?php echo rawurlencode($latest['slug']); ?>" class="text-decoration-none stretched-link">
                                <h6 class="bangla-font mb-1" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars(truncate_text(localized_news_value($latest, 'title'), 70), ENT_QUOTES, 'UTF-8'); ?>
                                </h6>
                                <small class="text-muted bangla-font">
                                    <i class="fas fa-calendar"></i> <?php echo date('d M, h:i A', strtotime($latest['created_at'])); ?>
                                </small>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Advertisement -->
            <?php render_advertisement(); ?>

            <!-- Newsletter Subscription -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-newspaper"></i> <?php echo translate('নিউজলেটার সাবস্ক্রাইব'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="bangla-font" style="font-size: 0.9rem;">
                        <?php echo translate('সর্বশেষ খবর সরাসরি আপনার ইমেইলে পেতে সাবস্ক্রাইব করুন।'); ?>
                    </p>
                    <form>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="<?php echo translate('আপনার ইমেইল ঠিকানা'); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 bangla-font">
                            <?php echo translate('সাবস্ক্রাইব করুন'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
    .news-title {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.4;
        color: #333;
    }

    .news-content {
        text-align: justify;
    }

    .news-content img {
        max-width: 100%;
        height: auto;
        margin: 20px 0;
        border-radius: 5px;
    }

    .news-content p {
        margin-bottom: 1.5rem;
    }

    .news-content h2, .news-content h3, .news-content h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #d32f2f;
    }

    .news-content ul, .news-content ol {
        padding-left: 20px;
        margin-bottom: 1.5rem;
    }

    .news-content li {
        margin-bottom: 0.5rem;
    }

    .comment-avatar {
        width: 38px;
        height: 38px;
    }

    .comment-avatar-sm {
        width: 30px;
        height: 30px;
        font-size: .8rem;
    }

    .comment-replies {
        border-left: 2px solid #e9ecef;
    }

    .comment-composer-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border: 1px solid var(--bs-border-color);
        border-radius: 999px;
        color: var(--bs-secondary-color);
        background: var(--bs-tertiary-bg);
        text-align: left;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .comment-composer-trigger:hover,
    .comment-composer-trigger:focus-visible {
        border-color: var(--bs-danger);
        background: var(--bs-body-bg);
        box-shadow: 0 0 0 3px rgba(220, 53, 69, .1);
        outline: 0;
    }

    .comment-composer-avatar {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 50%;
        color: var(--bs-danger);
        background: var(--bs-danger-bg-subtle);
    }

    .comment-composer-prompt {
        flex: 1;
    }

    .comment-composer-icon {
        color: var(--bs-secondary-color);
        font-size: .9rem;
    }

    .comment-composer-form {
        padding: 16px;
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        background: var(--bs-tertiary-bg);
    }

    @media (max-width: 768px) {
        .news-title {
            font-size: 1.5rem;
        }

        .news-content {
            font-size: 1rem;
        }
    }
</style>

<script>
    const newCommentForm = document.getElementById('newCommentForm');
    if (newCommentForm) {
        newCommentForm.addEventListener('shown.bs.collapse', function () {
            const commentNameInput = document.getElementById('comment_name');
            if (commentNameInput) {
                commentNameInput.focus();
            }
        });
    }

    // Share functions
    function shareOnFacebook() {
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${title}`, '_blank');
    }

    function shareOnTwitter() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(document.title);
        window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
    }

    function shareOnWhatsApp() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(document.title);
        window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    }

    function copyLink() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert(<?php echo json_encode(translate('লিংক কপি করা হয়েছে!')); ?>);
        });
    }

    // Print news
    function printNews() {
        window.print();
    }

    // Text size adjustment
    function changeTextSize(size) {
        const content = document.querySelector('.news-content');
        content.style.fontSize = size + 'rem';
        localStorage.setItem('textSize', size);
    }

    // Load saved text size
    document.addEventListener('DOMContentLoaded', function() {
        const savedSize = localStorage.getItem('textSize');
        if(savedSize) {
            changeTextSize(savedSize);
        }

        // Add print button to header
        const header = document.querySelector('.news-title').parentElement;
        const printBtn = document.createElement('button');
        printBtn.className = 'btn btn-outline-secondary btn-sm';
        printBtn.innerHTML = '<i class="fas fa-print"></i> ' + <?php echo json_encode(translate('প্রিন্ট করুন')); ?>;
        printBtn.onclick = printNews;
        header.appendChild(printBtn);

        // Add text size controls
        const sizeControls = document.createElement('div');
        sizeControls.className = 'btn-group btn-group-sm mt-2';
        sizeControls.innerHTML = '<button class="btn btn-outline-secondary" onclick="changeTextSize(1)">' + <?php echo json_encode(translate('ছোট')); ?> + '</button>'
            + '<button class="btn btn-outline-secondary" onclick="changeTextSize(1.1)">' + <?php echo json_encode(translate('মাধ্যম')); ?> + '</button>'
            + '<button class="btn btn-outline-secondary" onclick="changeTextSize(1.2)">' + <?php echo json_encode(translate('বড়')); ?> + '</button>';
        header.appendChild(sizeControls);
    });
</script>