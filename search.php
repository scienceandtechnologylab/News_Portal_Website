<?php
require_once 'includes/i18n.php';
$page_title = translate('খবর সার্চ');
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/database.php';

$search_query = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$results = [];
$total_results = 0;

if(!empty($search_query)) {
    $search_sql = "SELECT n.*, c.name as category_name, c.name_en as category_name_en
                  FROM news n
                  LEFT JOIN categories c ON n.category_id = c.id
                  WHERE (n.title LIKE '%$search_query%'
                  OR n.content LIKE '%$search_query%'
                  OR n.excerpt LIKE '%$search_query%')
                  AND n.status = 'published'
                  ORDER BY n.created_at DESC";

    $result = $conn->query($search_sql);

    if($result && $result->num_rows > 0) {
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $total_results = $result->num_rows;
    }
}
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Search Box -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="card-title bangla-font text-center mb-4"><?php echo translate('খবর সার্চ'); ?></h3>
                    <form action="search.php" method="GET">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="q"
                                   value="<?php echo htmlspecialchars($search_query); ?>"
                                   placeholder="<?php echo translate('আপনি কোন খবর খুঁজছেন?'); ?>" required>
                            <button class="btn btn-danger" type="submit">
                                <i class="fas fa-search"></i> <?php echo translate('সার্চ করুন'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search Results -->
            <?php if(!empty($search_query)): ?>
                <div class="search-results">
                    <h4 class="bangla-font mb-4">
                        <?php echo translate('সার্চ ফলাফল: "'); ?><?php echo htmlspecialchars($search_query); ?>"
                        <span class="badge bg-danger"><?php echo $total_results; ?><?php echo translate(' টি ফলাফল'); ?></span>
                    </h4>

                    <?php if($total_results > 0): ?>
                        <?php foreach($results as $news): ?>
                        <?php
                        $newsTitle = localized_news_value($news, 'title');
                        $newsContent = localized_news_value($news, 'content');
                        ?>
                        <div class="card news-card mb-3">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="<?php echo SITE_URL . 'assets/images/news/' . $news['featured_image']; ?>"
                                         class="img-fluid rounded-start news-featured-image" alt="<?php echo htmlspecialchars($newsTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                         style="height: 200px;">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars(localized_category_name($news['category_name'], $news['category_name_en']), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <h5 class="card-title bangla-font">
                                            <a href="news.php?slug=<?php echo rawurlencode($news['slug']); ?>" class="text-decoration-none text-dark stretched-link">
                                                <?php echo htmlspecialchars($newsTitle, ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text bangla-font">
                                            <?php echo htmlspecialchars(substr(strip_tags($newsContent), 0, 200), ENT_QUOTES, 'UTF-8'); ?>...
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted bangla-font">
                                                <i class="fas fa-calendar"></i> <?php echo date('d M, Y', strtotime($news['created_at'])); ?>
                                                | <i class="fas fa-eye"></i> <?php echo $news['views']; ?> <?php echo translate('বার দেখা হয়েছে'); ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h4 class="bangla-font"><?php echo translate('দুঃখিত, আপনার সার্চের সাথে মিলে এমন কোন খবর পাওয়া যায়নি'); ?></h4>
                            <p class="bangla-font"><?php echo translate('দয়া করে ভিন্ন কোনো শব্দ দিয়ে আবার চেষ্টা করুন'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>