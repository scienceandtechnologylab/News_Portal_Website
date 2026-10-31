<?php
require_once './config.php';
$page_title = 'বিজ্ঞান জগৎ - সবচেয়ে দ্রুত খবর';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
require_once 'includes/database.php';
require_once 'includes/advertisement.php';
?>

<div class="container mt-4">
    <!-- Featured News Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card featured-news">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0 bangla-font"><?php echo translate('শীর্ষ খবর'); ?></h4>
                </div>
                <div class="card-body">

                    <?php
                    // Fetch featured news
                    $featured_query = "SELECT * FROM news WHERE is_featured = 1 AND status = 'published' ORDER BY created_at DESC LIMIT 1";
                    $featured_result = $conn->query($featured_query);

                    if($featured_result && $featured_result->num_rows > 0):
                        $featured_news = $featured_result->fetch_assoc();
                        $featuredTitle = localized_news_value($featured_news, 'title');
                        $featuredExcerpt = localized_news_excerpt($featured_news);
                    ?>


                    <img src="<?php echo SITE_URL . 'assets/images/news/' . $featured_news['featured_image']; ?>"
                         alt="<?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid w-100 rounded mb-3 news-featured-image"
                         style="height: 400px;">
                    <h2 class="news-title bangla-font">
                        <a href="news.php?slug=<?php echo rawurlencode($featured_news['slug']); ?>" class="text-decoration-none text-dark stretched-link">
                            <?php echo htmlspecialchars($featuredTitle, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h2>
                    <p class="text-muted bangla-font">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars(translate($featured_news['author']), ENT_QUOTES, 'UTF-8'); ?> |
                        <i class="fas fa-calendar"></i> <?php echo date('d M, Y', strtotime($featured_news['created_at'])); ?>
                    </p>
                    <p class="bangla-font"><?php echo htmlspecialchars(truncate_text($featuredExcerpt, 300), ENT_QUOTES, 'UTF-8'); ?></p>
                    <a href="news.php?slug=<?php echo rawurlencode($featured_news['slug']); ?>" class="btn btn-danger bangla-font position-relative"><?php echo translate('সম্পূর্ণ পড়ুন'); ?></a>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <h4 class="bangla-font"><?php echo translate('কোন শীর্ষ খবর পাওয়া যায়নি'); ?></h4>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Latest News Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0 bangla-font"><?php echo translate('সর্বশেষ খবর'); ?></h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php
                        $latest_query = "SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT 4";
                        $latest_result = $conn->query($latest_query);

                        if($latest_result && $latest_result->num_rows > 0):
                            while($latest_news = $latest_result->fetch_assoc()):
                                $latestTitle = localized_news_value($latest_news, 'title');
                        ?>
                        <li class="list-group-item position-relative">
                            <a href="news.php?slug=<?php echo rawurlencode($latest_news['slug']); ?>" class="text-decoration-none stretched-link">
                                <h6 class="bangla-font news-title mb-1"><?php echo htmlspecialchars($latestTitle, ENT_QUOTES, 'UTF-8'); ?></h6>
                                <small class="text-muted bangla-font">
                                    <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($latest_news['created_at'])); ?>
                                </small>
                            </a>
                        </li>
                        <?php endwhile; else: ?>
                        <li class="list-group-item text-center bangla-font"><?php echo translate('কোন খবর পাওয়া যায়নি'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Advertisement -->
            <?php render_advertisement(); ?>
        </div>
    </div>

    <!-- Category-wise News -->
    <?php
    // Fetch all categories
    $cat_query = "SELECT * FROM categories ORDER BY id ASC";
    $cat_result = $conn->query($cat_query);

    if($cat_result && $cat_result->num_rows > 0):
        while($category = $cat_result->fetch_assoc()):
    ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="bangla-font border-start border-4 border-danger ps-2"><?php echo htmlspecialchars(localized_category_name($category['name'], $category['name_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></h3>
                <a href="category.php?slug=<?php echo $category['slug']; ?>" class="btn btn-sm btn-outline-danger bangla-font"><?php echo translate('সব দেখুন'); ?></a>
            </div>

            <div class="row">
                <?php
                // Fetch news for this category
                $news_query = "SELECT * FROM news WHERE category_id = " . $category['id'] . " AND status = 'published' ORDER BY created_at DESC LIMIT 4";
                $news_result = $conn->query($news_query);

                if($news_result && $news_result->num_rows > 0):
                    while($news = $news_result->fetch_assoc()):
                    $newsTitle = localized_news_value($news, 'title');
                ?>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card news-card h-100">
                        <img src="<?php echo SITE_URL . 'assets/images/news/' . $news['featured_image']; ?>"
                             class="card-img-top news-featured-image" alt="<?php echo htmlspecialchars($newsTitle, ENT_QUOTES, 'UTF-8'); ?>"
                             style="height: 180px;">
                        <div class="card-body">
                            <h5 class="card-title news-title bangla-font" style="font-size: 1rem;">
                                <a href="news.php?slug=<?php echo rawurlencode($news['slug']); ?>" class="text-decoration-none text-dark stretched-link">
                                    <?php echo htmlspecialchars(truncate_text($newsTitle, 80), ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted bangla-font" style="font-size: 0.85rem;">
                                <small>
                                    <i class="fas fa-clock"></i> <?php echo date('d M, Y', strtotime($news['created_at'])); ?>
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div class="col-12">
                    <p class="text-center bangla-font"><?php echo translate('এই বিভাগে কোন খবর নেই'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; endif; ?>
</div>

<?php
require_once('includes/footer.php');
?>