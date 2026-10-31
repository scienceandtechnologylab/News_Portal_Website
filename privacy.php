<?php
require_once 'includes/i18n.php';
$page_title = translate('গোপনীয়তা নীতি - Your News Portal');
include 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4 text-primary"><?php echo translate('গোপনীয়তা নীতি'); ?></h1>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <p class="text-muted">সর্বশেষ আপডেট: <?php echo date('d F, Y'); ?></p>

                    <h4 class="mt-3 text-info"><?php echo translate('১. তথ্য সংগ্রহ'); ?></h4>
                    <p><?php echo translate('আমরা নিম্নলিখিত তথ্যগুলো সংগ্রহ করি:'); ?></p>
                    <ul>
                        <li><?php echo translate('নাম ও ইমেইল ঠিকানা (মন্তব্য বা নিউজলেটারে সাবস্ক্রাইব করার সময়)'); ?></li>
                        <li><?php echo translate('অটোমেটিকভাবে সংগ্রহিত তথ্য (আইপি এড্রেস, ব্রাউজার টাইপ, ভিজিটের সময়)'); ?></li>
                        <li><?php echo translate('কুকিজের মাধ্যমে তথ্য'); ?></li>
                    </ul>

                    <h4 class="mt-4 text-info"><?php echo translate('২. তথ্য ব্যবহার'); ?></h4>
                    <p><?php echo translate('সংগৃহীত তথ্য আমরা শুধুমাত্র নিম্নোক্ত কাজে ব্যবহার করি:'); ?></p>
                    <ul>
                        <li><?php echo translate('ওয়েবসাইটের কার্যকারিতা উন্নত করা'); ?></li>
                        <li><?php echo translate('ব্যক্তিগতকৃত ব্যবহারকারীর অভিজ্ঞতা প্রদান'); ?></li>
                        <li><?php echo translate('নিউজলেটার পাঠানো'); ?></li>
                        <li><?php echo translate('স্প্যাম ও অপব্যবহার রোধ করা'); ?></li>
                    </ul>

                    <h4 class="mt-4 text-info"><?php echo translate('৩. তথ্য সুরক্ষা'); ?></h4>
                    <p><?php echo translate('আমরা SSL এনক্রিপশন, ফায়ারওয়াল ও নিয়মিত সিকিউরিটি আপডেটের মাধ্যমে আপনার তথ্য সুরক্ষিত রাখি।'); ?></p>

                    <h4 class="mt-4 text-info"><?php echo translate('৪. তৃতীয় পক্ষ'); ?></h4>
                    <p><?php echo translate('আপনার ব্যক্তিগত তথ্য আমরা কোন তৃতীয় পক্ষের সাথে শেয়ার করি না, তবে:'); ?></p>
                    <ul>
                        <li><?php echo translate('গুগল অ্যানালিটিক্স ট্র্যাফিক বিশ্লেষণের জন্য ব্যবহার করতে পারে'); ?></li>
                        <li><?php echo translate('বিজ্ঞাপন নেটওয়ার্ক (Google Adsense) কুকিজ ব্যবহার করতে পারে'); ?></li>
                    </ul>

                    <h4 class="mt-4 text-info"><?php echo translate('৫. আপনার অধিকার'); ?></h4>
                    <p><?php echo translate('আপনি যে কোন সময়:'); ?></p>
                    <ul>
                        <li><?php echo translate('আপনার ব্যক্তিগত তথ্য দেখতে পারেন'); ?></li>
                        <li><?php echo translate('তথ্য সংশোধন করতে পারেন'); ?></li>
                        <li><?php echo translate('তথ্য মুছে ফেলার জন্য অনুরোধ করতে পারেন'); ?></li>
                        <li><?php echo translate('কুকিজ গ্রহণ থেকে বিরত থাকতে পারেন'); ?></li>
                    </ul>

                    <div class="alert alert-info mt-4">
                        <strong><?php echo translate('প্রশ্ন থাকলে:'); ?></strong> privacy@<?php echo strtolower(SITE_NAME); ?>.com ইমেইল করুন।
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>