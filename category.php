<?php
require_once 'includes/i18n.php';
require_once 'includes/database.php';
require_once 'includes/advertisement.php';

// Get category slug from URL
$category_slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : '';

if(empty($category_slug)) {
    header('Location: ' . SITE_URL);
    exit;
}

// Get category details
$category_query = "SELECT * FROM categories WHERE slug = '$category_slug'";
$category_result = $conn->query($category_query);

if($category_result->num_rows == 0) {
    header('Location: ' . SITE_URL);
    exit;
}

$category = $category_result->fetch_assoc();
$category_id = $category['id'];
$category_name = localized_category_name($category['name'], $category['name_en'] ?? null);

$page_title = $category_name . ' - ' . translate('বিজ্ঞান জগৎ');
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Pagination
$limit = 12; // News per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total news count for this category
$count_query = "SELECT COUNT(*) as total FROM news WHERE category_id = $category_id AND status = 'published'";
$count_result = $conn->query($count_query);
$total_news = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_news / $limit);

// Get news for this category with pagination
$news_query = "SELECT * FROM news WHERE category_id = $category_id AND status = 'published' ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$news_result = $conn->query($news_query);

// Get related categories (exclude current category)
$related_categories_query = "SELECT * FROM categories WHERE id != $category_id ORDER BY RAND() LIMIT 6";
$related_categories_result = $conn->query($related_categories_query);
?>

<div class="container my-5">
    <!-- Category Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bangla-font">
                            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none"><i class="fas fa-home"></i> <?php echo translate('হোম'); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?></li>
                        </ol>
                    </nav>

                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="bangla-font text-danger border-start border-5 border-danger ps-3">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?>
                        </h1>
                        <div class="text-muted bangla-font">
                            <span class="badge bg-body text-dark"><?php echo translate('মোট নিউজ: '); ?><?php echo $total_news; ?></span>
                        </div>
                    </div>

                    <p class="bangla-font mt-3">
                        <?php echo sprintf(translate('%s বিভাগের সর্বশেষ ও গুরুত্বপূর্ণ সব খবর পড়ুন। বিজ্ঞান জগতের পাঠকদের জন্য নির্বাচিত ও যাচাইকৃত সংবাদ।'), $category_name); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main News Content -->
        <div class="col-md-8">
            <?php if($news_result && $news_result->num_rows > 0): ?>
                <div class="row">
                    <?php                     while($news = $news_result->fetch_assoc()):
                        $newsTitle = localized_news_value($news, 'title');
                        $newsExcerpt = localized_news_value($news, 'excerpt'); ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card news-card h-100">
                            <div class="position-relative">
                                <img src="<?php echo SITE_URL . 'assets/images/news/' . $news['featured_image']; ?>"
                                     class="card-img-top news-featured-image" alt="<?php echo htmlspecialchars($newsTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                     style="height: 200px;">
                                <div class="position-absolute top-0 start-0">
                                    <span class="badge bg-danger m-2"><?php echo htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <?php if($news['is_featured'] == 1): ?>
                                <div class="position-absolute top-0 end-0">
                                    <span class="badge bg-warning m-2"><?php echo translate('শীর্ষ'); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title news-title bangla-font" style="font-size: 1.1rem;">
                                    <a href="news.php?slug=<?php echo rawurlencode($news['slug']); ?>" class="text-decoration-none text-dark stretched-link">
                                        <?php echo htmlspecialchars(truncate_text($newsTitle, 80), ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted bangla-font" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars(substr(strip_tags($newsExcerpt), 0, 100), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php echo strlen(strip_tags($newsExcerpt)) > 100 ? '...' : ''; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted bangla-font">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars(translate($news['author']), ENT_QUOTES, 'UTF-8'); ?>
                                    </small>
                                    <small class="text-muted bangla-font">
                                        <i class="fas fa-clock"></i> <?php echo date('d M, Y', strtotime($news['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <a href="news.php?slug=<?php echo rawurlencode($news['slug']); ?>" class="btn btn-sm btn-outline-danger w-100 bangla-font position-relative news-read-more">
                                    <?php echo translate('বিস্তারিত পড়ুন'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center bangla-font">
                        <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="category.php?slug=<?php echo $category_slug; ?>&page=<?php echo $page - 1; ?>">
                                <i class="fas fa-angle-left"></i> <?php echo translate('পূর্ববর্তী'); ?>
                            </a>
                        </li>

                        <?php
                        // Show limited page numbers
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);

                        for($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="category.php?slug=<?php echo $category_slug; ?>&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="category.php?slug=<?php echo $category_slug; ?>&page=<?php echo $page + 1; ?>">
                                <?php echo translate('পরবর্তী'); ?> <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>

                    <div class="text-center mt-2">
                        <small class="text-muted bangla-font">
                            পৃষ্ঠা <?php echo $page; ?> এর <?php echo $total_pages; ?> (মোট <?php echo $total_news; ?> টি নিউজ)
                        </small>
                    </div>
                </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                    <h3 class="bangla-font text-muted"><?php echo translate('এই বিভাগে এখনও কোন নিউজ নেই'); ?></h3>
                    <p class="bangla-font"><?php echo translate('অন্যান্য বিভাগ থেকে নিউজ পড়ুন'); ?></p>
                    <a href="index.php" class="btn btn-dark bangla-font">
                        <i class="fas fa-home"></i> <?php echo translate('হোমপেজে ফিরে যান'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Related Categories -->
            <?php if($related_categories_result && $related_categories_result->num_rows > 0): ?>
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-th-large"></i> <?php echo translate('সম্পর্কিত বিভাগ'); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while($related_category = $related_categories_result->fetch_assoc()):
                            // Get count for this category
                            $cat_count_query = $conn->query("SELECT COUNT(*) as count FROM news WHERE category_id = " . $related_category['id'] . " AND status = 'published'");
                            $cat_count = $cat_count_query->fetch_assoc()['count'];
                        ?>
                        <div class="col-6 mb-3">
                            <a href="category.php?slug=<?php echo $related_category['slug']; ?>" class="text-decoration-none">
                                <div class="card text-center h-100 category-card">
                                    <div class="card-body">
                                        <h6 class="card-title bangla-font mb-2"><?php echo htmlspecialchars(localized_category_name($related_category['name'], $related_category['name_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?></h6>
                                        <span class="badge bg-danger"><?php echo $cat_count; ?> নিউজ</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Most Viewed News in This Category -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-fire"></i> <?php echo translate('এই বিভাগের জনপ্রিয়'); ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $popular_query = "SELECT * FROM news WHERE category_id = $category_id AND status = 'published' ORDER BY views DESC LIMIT 5";
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
                                <div class="flex-grow-1 ms-2">
                                    <a href="news.php?slug=<?php echo rawurlencode($popular['slug']); ?>" class="text-decoration-none stretched-link">
                                        <h6 class="bangla-font mb-1" style="font-size: 0.95rem;">
                                            <?php echo htmlspecialchars(truncate_text(localized_news_value($popular, 'title'), 70), ENT_QUOTES, 'UTF-8'); ?>
                                        </h6>
                                    </a>
                                    <small class="text-muted bangla-font">
                                        <i class="fas fa-eye"></i> <?php echo number_format($popular['views']); ?> <?php echo translate('বার দেখা হয়েছে'); ?>
                                    </small>
                                </div>
                            </div>
                        </li>
                        <?php $counter++; ?>
                        <?php endwhile; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <p class="bangla-font text-muted mb-0"><?php echo translate('কোন জনপ্রিয় নিউজ পাওয়া যায়নি'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest News in This Category -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-clock"></i> <?php echo translate('এই বিভাগের সর্বশেষ'); ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $latest_query = "SELECT * FROM news WHERE category_id = $category_id AND status = 'published' ORDER BY created_at DESC LIMIT 5";
                    $latest_result = $conn->query($latest_query);

                    if($latest_result && $latest_result->num_rows > 0):
                    ?>
                    <ul class="list-group list-group-flush">
                        <?php while($latest = $latest_result->fetch_assoc()): ?>
                        <li class="list-group-item position-relative">
                            <a href="news.php?slug=<?php echo rawurlencode($latest['slug']); ?>" class="text-decoration-none stretched-link">
                                <h6 class="bangla-font mb-1" style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars(truncate_text(localized_news_value($latest, 'title'), 70), ENT_QUOTES, 'UTF-8'); ?>
                                </h6>
                                <small class="text-muted bangla-font">
                                    <i class="fas fa-calendar"></i> <?php echo date('d M, h:i A', strtotime($latest['created_at'])); ?>
                                </small>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <p class="bangla-font text-muted mb-0"><?php echo translate('কোন সর্বশেষ নিউজ পাওয়া যায়নি'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Advertisement -->
            <?php render_advertisement(); ?>

            <!-- Category Statistics -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-chart-bar"></i> <?php echo translate('বিভাগ পরিসংখ্যান'); ?></h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get various statistics for this category
                    $stats_query = "
                        SELECT
                            COUNT(*) as total_news,
                            SUM(views) as total_views,
                            AVG(views) as avg_views,
                            MAX(views) as max_views,
                            MIN(created_at) as first_news_date,
                            MAX(created_at) as last_news_date
                        FROM news
                        WHERE category_id = $category_id AND status = 'published'
                    ";
                    $stats_result = $conn->query($stats_query);
                    $stats = $stats_result->fetch_assoc();
                    ?>
                    <ul class="list-unstyled bangla-font">
                        <li class="mb-2">
                            <i class="fas fa-newspaper text-primary"></i>
                            <strong><?php echo translate('মোট নিউজ:'); ?></strong> <?php echo $stats['total_news']; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-eye text-success"></i>
                            <strong><?php echo translate('মোট ভিউ:'); ?></strong> <?php echo number_format($stats['total_views']); ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-info"></i>
                            <strong><?php echo translate('গড় ভিউ:'); ?></strong> <?php echo round($stats['avg_views']); ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-star text-warning"></i>
                            <strong><?php echo translate('সর্বোচ্চ ভিউ:'); ?></strong> <?php echo number_format($stats['max_views']); ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-plus text-danger"></i>
                            <strong><?php echo translate('প্রথম নিউজ:'); ?></strong>
                            <?php echo $stats['first_news_date'] ? date('d/m/Y', strtotime($stats['first_news_date'])) : 'N/A'; ?>
                        </li>
                        <li>
                            <i class="fas fa-calendar-check text-success"></i>
                            <strong><?php echo translate('সর্বশেষ নিউজ:'); ?></strong>
                            <?php echo $stats['last_news_date'] ? date('d/m/Y', strtotime($stats['last_news_date'])) : 'N/A'; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Description -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0 bangla-font"><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($category_name, ENT_QUOTES, 'UTF-8'); ?> <?php echo translate('বিভাগ সম্পর্কে'); ?></h5>
                </div>
                <div class="card-body">
                    <p class="bangla-font">
                        <?php echo sprintf(translate('বিজ্ঞান জগৎ %s বিভাগে আপনি পাচ্ছেন সবচেয়ে নির্ভরযোগ্য ও সময়োপযোগী সংবাদ। আমাদের অভিজ্ঞ সাংবাদিক ও সংবাদদাতাদের মাধ্যমে সংগ্রহ করা তথ্য নিয়ে আমরা প্রকাশ করি সত্য ও নির্ভুল সংবাদ।'), $category_name); ?>
                    </p>
                    <p class="bangla-font">
                        <?php echo translate('এই বিভাগের প্রতিটি খবর প্রকাশের আগে যাচাই-বাছাই করা হয় এবং পাঠকদের জন্য সহজবোদ্ধ ভাষায় উপস্থাপন করা হয়। সর্বশেষ তথ্য ও আপডেটের জন্য আমাদের সাথে থাকুন।'); ?>
                    </p>

                    <div class="alert alert-info bangla-font">
                        <i class="fas fa-bell"></i>
                        <strong><?php echo translate('বিশেষ বিজ্ঞপ্তি:'); ?></strong>
                        <?php echo translate('এই বিভাগের কোন খবর সম্পর্কে আপনার কোন মন্তব্য বা পরামর্শ থাকলে নিচের কমেন্ট বক্সে লিখুন অথবা'); ?>
                        <a href="contact.php" class="alert-link"><?php echo translate('যোগাযোগ'); ?></a> <?php echo translate('পৃষ্ঠা থেকে আমাদের জানান।'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
    .category-card {
        transition: all 0.3s;
        border: 1px solid #eee;
    }

    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #d32f2f;
    }

    .news-card {
        border: 1px solid #eee;
        transition: all 0.3s;
    }

    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .news-card .card-img-top {
        transition: transform 0.5s;
    }

    .news-card:hover .card-img-top {
        transform: scale(1.05);
    }

    .breadcrumb {
        background-color: transparent;
        padding: 0;
    }

    .breadcrumb-item a {
        color: #d32f2f;
    }

    .breadcrumb-item.active {
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .category-card .card-body {
            padding: 10px;
        }

        .news-card {
            margin-bottom: 20px;
        }
    }
</style>

<script>
    // Add smooth scrolling for anchor links
    document.addEventListener('DOMContentLoaded', function() {
        // Add active class to current category in navigation
        const currentCategory = '<?php echo $category_slug; ?>';
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        navLinks.forEach(link => {
            if(link.getAttribute('href') && link.getAttribute('href').includes(currentCategory)) {
                link.classList.add('active');
            }
        });

        // Lazy load images
        const images = document.querySelectorAll('img[data-src]');
        if('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if(entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        }

        // Add click tracking for analytics
        const newsLinks = document.querySelectorAll('.news-card a');
        newsLinks.forEach(link => {
            link.addEventListener('click', function() {
                const newsId = this.getAttribute('href').split('slug=')[1];
                // You can send this data to your analytics
                console.log('News clicked:', newsId);
            });
        });
    });

    // Share category function
    function shareCategory() {
        const categoryName = <?php echo json_encode($category_name, JSON_UNESCAPED_UNICODE); ?>;
        const categoryUrl = window.location.href;

        if(navigator.share) {
            navigator.share({
                title: categoryName + ' - ' + <?php echo json_encode(translate('বাংলাদেশ প্রতিদিন')); ?>,
                text: <?php echo json_encode(translate('পড়ুন %s বিভাগের সর্বশেষ খবর')); ?>.replace('%s', categoryName),
                url: categoryUrl
            });
        } else {
            // Fallback for desktop
            const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(categoryUrl)}`;
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }
</script>