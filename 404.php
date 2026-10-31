<?php
require_once 'includes/i18n.php';
$page_title = translate('পেজ পাওয়া যায়নি - 404 Error');
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page">
                <h1 class="display-1 text-danger">404</h1>
                <h2 class="bangla-font mb-4"><?php echo translate('দুঃখিত, পেজটি পাওয়া যায়নি'); ?></h2>

                <div class="mb-5">
                    <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
                    <p class="bangla-font fs-5">
                        <?php echo translate('আপনি যে পেজটি খুঁজছেন তা মুছে ফেলা হয়েছে, নাম পরিবর্তন করা হয়েছে বা সাময়িকভাবে অপ্রাপ্য হতে পারে।'); ?>
                    </p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="bangla-font mb-3"><?php echo translate('আপনি কি করতে পারেন:'); ?></h5>
                                <div class="d-grid gap-2">
                                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg bangla-font">
                                        <i class="fas fa-home"></i> <?php echo translate('হোমপেজে ফিরে যান'); ?>
                                    </a>
                                    <a href="javascript:history.back()" class="btn btn-outline-secondary bangla-font">
                                        <i class="fas fa-arrow-left"></i> <?php echo translate('পূর্ববর্তী পেজে ফিরে যান'); ?>
                                    </a>
                                    <a href="contact.php" class="btn btn-outline-info bangla-font">
                                        <i class="fas fa-envelope"></i> <?php echo translate('আমাদের সাথে যোগাযোগ করুন'); ?>
                                    </a>
                                </div>

                                <div class="mt-4">
                                    <h6 class="bangla-font mb-3"><?php echo translate('অথবা খুঁজুন:'); ?></h6>
                                    <form action="search.php" method="GET" class="d-flex">
                                        <input type="text" name="q" class="form-control" placeholder="<?php echo translate('আপনি কি খুঁজছেন?'); ?>" required>
                                        <button type="submit" class="btn btn-danger ms-2">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popular Categories -->
                <div class="mt-5">
                    <h4 class="bangla-font mb-4"><?php echo translate('জনপ্রিয় বিভাগসমূহ'); ?></h4>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <a href="category.php?slug=national" class="btn btn-outline-primary w-100 bangla-font"><?php echo translate('জাতীয়'); ?></a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="category.php?slug=politics" class="btn btn-outline-primary w-100 bangla-font"><?php echo translate('রাজনীতি'); ?></a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="category.php?slug=sports" class="btn btn-outline-primary w-100 bangla-font"><?php echo translate('খেলা'); ?></a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="category.php?slug=entertainment" class="btn btn-outline-primary w-100 bangla-font"><?php echo translate('বিনোদন'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
    .error-page {
        padding: 60px 0;
    }

    .display-1 {
        font-size: 8rem;
        font-weight: 700;
        line-height: 1;
    }

    @media (max-width: 768px) {
        .display-1 {
            font-size: 5rem;
        }
    }
</style>