<?php
require_once 'includes/i18n.php';
$page_title = translate('আমাদের সম্পর্কে - Your News Portal');
include 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4 text-primary"><?php echo translate('আমাদের সম্পর্কে'); ?></h1>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="text-success"><?php echo translate('স্বাগতম'); ?> <span class="text-danger"><?php echo SITE_NAME; ?></span><?php echo translate('এ'); ?></h3>

                    <p class="lead mt-3">
                        <?php echo translate('বাংলাদেশের নির্ভরযোগ্য ও জনপ্রিয় অনলাইন নিউজ পোর্টাল হিসেবে আমরা ২০২৪ সাল থেকে কাজ করছি।'); ?>
                    </p>

                    <h4 class="mt-4 text-info"><?php echo translate('আমাদের উদ্দেশ্য'); ?></h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><?php echo translate('✓ দ্রুত, নির্ভুল ও নিরপেক্ষ সংবাদ পরিবেশন'); ?></li>
                        <li class="list-group-item"><?php echo translate('✓ ডিজিটাল বাংলাদেশ গড়তে তথ্য প্রযুক্তির ব্যবহার'); ?></li>
                        <li class="list-group-item"><?php echo translate('✓ তরুণ সাংবাদিকদের জন্য প্ল্যাটফর্ম তৈরি'); ?></li>
                        <li class="list-group-item"><?php echo translate('✓ আন্তর্জাতিক সংস্থার সাথে সহযোগিতা'); ?></li>
                    </ul>

                    <h4 class="mt-4 text-info"><?php echo translate('আমাদের টিম'); ?></h4>
                    <p><?php echo translate('অভিজ্ঞ সাংবাদিক, প্রযুক্তিবিদ ও সম্পাদকদের সমন্বয়ে গঠিত আমাদের টিম ২৪/৭ কাজ করে যাচ্ছে আপনাকে হালনাগাদ তথ্য প্রদানের জন্য।'); ?></p>

                    <h4 class="mt-4 text-info"><?php echo translate('যোগাযোগ'); ?></h4>
                    <p>
                        <strong><?php echo translate('ঠিকানা:'); ?></strong> <?php echo translate('ঢাকা, বাংলাদেশ'); ?><br>
                        <strong><?php echo translate('ইমেইল:'); ?></strong> info@<?php echo strtolower(SITE_NAME); ?>.com<br>
                        <strong><?php echo translate('ফোন:'); ?></strong> +৮৮০ ১৬XX-XXXXXX
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>