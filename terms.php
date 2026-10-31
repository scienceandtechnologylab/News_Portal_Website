<?php
require_once 'includes/i18n.php';
$page_title = translate('শর্তাবলী - Your News Portal');
include 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4 text-primary"><?php echo translate('সেবার শর্তাবলী'); ?></h1>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <p class="text-muted">এই শর্তাবলী <?php echo date('d F, Y'); ?> থেকে কার্যকর</p>

                    <h4 class="mt-3 text-info"><?php echo translate('১. ওয়েবসাইট ব্যবহার'); ?></h4>
                    <p><?php echo translate('এই ওয়েবসাইট ব্যবহারের মাধ্যমে আপনি আমাদের শর্তাবলী মেনে নিচ্ছেন। আপনার বয়স ১৩ বছরের বেশি হতে হবে।'); ?></p>

                    <h4 class="mt-4 text-info"><?php echo translate('২. মন্তব্য নীতি'); ?></h4>
                    <p><?php echo translate('মন্তব্য করার সময় আপনাকে নিম্নোক্ত নিয়ম মেনে চলতে হবে:'); ?></p>
                    <ul>
                        <li><?php echo translate('অশ্লীল বা আপত্তিকর ভাষা ব্যবহার করা যাবে না'); ?></li>
                        <li><?php echo translate('ব্যক্তিগত আক্রমণ করা যাবে না'); ?></li>
                        <li><?php echo translate('ঘৃণামূলক বা বিদ্বেষপূর্ণ কথা বলা যাবে না'); ?></li>
                        <li><?php echo translate('স্প্যাম বা বিজ্ঞাপন দেয়া যাবে না'); ?></li>
                        <li><?php echo translate('মিথ্যা তথ্য দেয়া যাবে না'); ?></li>
                    </ul>
                    <p><?php echo translate('নিয়ম ভঙ্গ করলে আপনার মন্তব্য মুছে ফেলা হবে এবং অ্যাকাউন্ট ব্লক করা হতে পারে।'); ?></p>

                    <h4 class="mt-4 text-info"><?php echo translate('৩. কপিরাইট'); ?></h4>
                    <p><?php echo translate('এই ওয়েবসাইটের সকল কন্টেন্ট (নিউজ, ছবি, ভিডিও) আমাদের কপিরাইট দ্বারা সুরক্ষিত।'); ?></p>
                    <ul>
                        <li><?php echo translate('নিউজ শেয়ার করার সময় সোর্স উল্লেখ করুন'); ?></li>
                        <li><?php echo translate('বাণিজ্যিক উদ্দেশ্যে কন্টেন্ট ব্যবহার করা যাবে না'); ?></li>
                        <li><?php echo translate('মডিফাইকেশন বা বিকৃতিকরণ করা যাবে না'); ?></li>
                    </ul>

                    <h4 class="mt-4 text-info"><?php echo translate('৪. দায়িত্বরহিত ঘোষণা'); ?></h4>
                    <p><?php echo translate('আমরা প্রকাশিত প্রতিটি সংবাদের সত্যতা যাচাই করলেও কোন তথ্য ভুল হলে আমরা দায়ী নই। পাঠকদের নিজ দায়িত্বে তথ্য ব্যবহার করতে হবে।'); ?></p>

                    <h4 class="mt-4 text-info"><?php echo translate('৫. পরিবর্তনের অধিকার'); ?></h4>
                    <p><?php echo translate('আমরা যে কোন সময় এই শর্তাবলী পরিবর্তন করতে পারব। পরিবর্তনগুলো ওয়েবসাইটে পোস্ট করার পর থেকে কার্যকর হবে।'); ?></p>

                    <h4 class="mt-4 text-info"><?php echo translate('৬. আইনি অধিকার'); ?></h4>
                    <p><?php echo translate('বাংলাদেশের আইন এই শর্তাবলীর জন্য প্রযোজ্য হবে। কোন বিরোধ দেখা দিলে ঢাকার আদালত এখতিয়ার প্রাপ্ত হবে।'); ?></p>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong><?php echo translate('দ্রষ্টব্য:'); ?></strong> <?php echo translate('এই শর্তাবলী পড়ে না বুঝে "আমি সম্মত" ক্লিক করলে আপনি নিজেই দায়ী থাকবেন।'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>