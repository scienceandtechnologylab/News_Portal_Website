<?php
// Footer should be included after all content
?>
<?php
    require_once __DIR__ . '/../config.php';
?>

    </div> <!-- End of container from contact.php or index.php -->

    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 class="border-bottom pb-2 mb-3 bangla-font"><?php echo translate('বিজ্ঞান জগৎ'); ?></h4>
                    <p class="bangla-font">
                        <?php echo translate('বাংলাদেশের শীর্ষ অনলাইন নিউজ পোর্টাল। সর্বশেষ খবর, ব্রেকিং নিউজ, বিশ্লেষণ, বিনোদন, খেলা, ব্যবসা, প্রযুক্তি এবং আরও অনেক কিছু।'); ?>
                    </p>
                    <div class="social-icons mt-3">
                        <a href="www.facebook.com" class="me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="www.x.com" class="me-3"><i class="fab fa-x fa-2x"></i></a>
                        <a href="www.youtube.com" class="me-3"><i class="fab fa-youtube fa-2x"></i></a>
                        <a href="www.instagram.com" class="me-3"><i class="fab fa-instagram fa-2x"></i></a>
                        <a href="www.linkden.com"><i class="fab fa-linkedin fa-2x"></i></a>
                    </div>
                </div>

                <div class="col-md-2 mb-4">
                    <h5 class="border-bottom pb-2 mb-3 bangla-font"><?php echo translate('লিংক'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>" class="bangla-font"><?php echo translate('হোমপেজ'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>about.php" class="bangla-font"><?php echo translate('আমাদের সম্পর্কে'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>contact.php" class="bangla-font"><?php echo translate('যোগাযোগ'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>privacy.php" class="bangla-font"><?php echo translate('গোপনীয়তা নীতি'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>terms.php" class="bangla-font"><?php echo translate('শর্তাবলী'); ?></a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h5 class="border-bottom pb-2 mb-3 bangla-font"><?php echo translate('ক্যাটেগরি'); ?></h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="category.php?slug=national" class="bangla-font"><?php echo translate('জাতীয়'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>category.php?slug=international" class="bangla-font"><?php echo translate('আন্তর্জাতিক'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>category.php?slug=politics" class="bangla-font"><?php echo translate('রাজনীতি'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>category.php?slug=sports" class="bangla-font"><?php echo translate('খেলা'); ?></a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>category.php?slug=entertainment" class="bangla-font"><?php echo translate('বিনোদন'); ?></a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-4">
                    <h5 class="border-bottom pb-2 mb-3 bangla-font"><?php echo translate('পরিচালনা পরিষদ'); ?></h5>
                    <p class="bangla-font"><?php echo translate('প্রতিষ্ঠাতা:'); ?> MD. Atiqur Rahman Limon</p>
                    <p class="bangla-font"><?php echo translate('ব্যবস্থাপনা পরিচালক:'); ?> MD. Atiqur Rahman Limon</p>
                    <p class="bangla-font"><?php echo translate('প্রধান সম্পাদক:'); ?> MD Atiqur Rahman Limon</p>
                </div>
            </div>

            <div class="row copyright">
                <div class="col-md-6">
                    <p class="mb-0 bangla-font"><?php echo translate('কপিরাইট ©'); ?> <?php echo date('Y'); ?> <?php echo translate('বিজ্ঞান জগৎ। সর্বস্বত্ব সংরক্ষিত।'); ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0 bangla-font"><?php echo translate('ডেভেলপড বাই:'); ?> <a href="https://www.facebook.com/share/1DXqbygrjz/" class="text-warning"><?php echo translate('টেক টিম'); ?></a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        window.siteTranslations = {
            searchMinimumLength: <?php echo json_encode(translate('দয়া করে কমপক্ষে ২টি অক্ষর লিখুন'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            subscribeLoading: <?php echo json_encode(translate('পাঠানো হচ্ছে...'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            subscribeFailed: <?php echo json_encode(translate('সাবস্ক্রিপশন সম্পন্ন করা যায়নি।'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            connectionFailed: <?php echo json_encode(translate('সার্ভারের সঙ্গে যোগাযোগ করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            partlyCloudy: <?php echo json_encode(translate('আংশিক মেঘলা'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            dhaka: <?php echo json_encode(translate('ঢাকা'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
        };
    </script>

    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>assets/js/main.js?v=3"></script>

    <script>
        // News search functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Search form submission
            const searchForm = document.querySelector('form[action="search.php"]');
            if(searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const searchInput = this.querySelector('input[name="q"]');
                    if(searchInput.value.trim() === '') {
                        e.preventDefault();
                        alert(<?php echo json_encode(translate('দয়া করে সার্চ কীওয়ার্ড লিখুন')); ?>);
                        searchInput.focus();
                    }
                });
            }

            // Mobile menu enhancement
            const navbarToggler = document.querySelector('.navbar-toggler');
            if(navbarToggler) {
                navbarToggler.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
            }
        });

        // Simple search AJAX (for future implementation)
        function performSearch(query) {
            // This would be connected to a search.php backend
            console.log('Searching for:', query);
        }
    </script>
</body>
</html>