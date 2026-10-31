<?php
// Include database connection
require_once 'database.php';

// Fetch categories for navigation
$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = $conn->query($categories_query);
?>

<!-- Main Navigation -->
<nav class="navbar navbar-dark navbar-expand-lg category-nav sticky-top"> <!--navbar-expand-lg-->
    <div class="container">
        <!-- Toggle button for mobile -->
       <!-- Toggle button -->
<button class="navbar-toggler text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
    <span class="navbar-toggler-icon"></span> <?php echo translate('মেনু'); ?>
</button>

<!-- Offcanvas Menu -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileMenuLabel"><?php echo translate('মেনু'); ?></h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo SITE_URL; ?>"><!--<i class="fas fa-home"></i>--> <?php echo translate('হোম'); ?></a>
            </li>

            <?php if($categories_result && $categories_result->num_rows > 0): ?>
                <?php while($category = $categories_result->fetch_assoc()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="category.php?slug=<?php echo $category['slug']; ?>">
                            <?php echo htmlspecialchars(localized_category_name($category['name'], $category['name_en'] ?? null), ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>contact.php"><!--<i class="fas fa-envelope"></i>--> <?php echo translate('যোগাযোগ'); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>subscribe.php"><!--<i class="fas fa-paper-plane"></i>--> <?php echo translate('নিউজলেটার সাবস্ক্রাইব'); ?></a>
            </li>
        </ul>
    </div>
</div>
    </div>
</nav>