<?php
require_once 'includes/i18n.php';
require_once 'includes/database.php';
$page_title = translate('যোগাযোগ');
$contactCsrfToken = $_SESSION['contact_csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['contact_csrf_token'] = $contactCsrfToken;

$name = '';
$email = '';
$phone = '';
$subject = '';
$message = '';
$error_message = '';
$message_sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['contact_flash']);
    $invalidFieldType = false;
    foreach (['name', 'email', 'phone', 'subject', 'message'] as $field) {
        $value = $_POST[$field] ?? '';
        if (!is_string($value)) {
            http_response_code(400);
            $error_message = translate('ফর্মের তথ্য সঠিক নয়। আবার চেষ্টা করুন।');
            $invalidFieldType = true;
            continue;
        }
        $$field = trim($value);
    }

    $postedToken = $_POST['csrf_token'] ?? null;
    if (!$invalidFieldType && (!is_string($postedToken) || !hash_equals($contactCsrfToken, $postedToken))) {
        http_response_code(400);
        $error_message = translate('অনুরোধটি যাচাই করা যায়নি। পেজ রিফ্রেশ করে আবার চেষ্টা করুন।');
    } elseif (!$invalidFieldType && !empty($_POST['website'])) {
        $_SESSION['contact_flash'] = translate('আপনার বার্তা সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।');
        header('Location: contact.php', true, 303);
        exit;
    } elseif (!$invalidFieldType) {
        $nameLength = preg_match_all('/./us', $name);
        $subjectLength = preg_match_all('/./us', $subject);
        $messageLength = preg_match_all('/./us', $message);
        $emailValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $phoneDigits = $phone === '' ? '' : preg_replace('/[^\p{N}]/u', '', $phone);
        $phoneLength = $phone === '' ? 0 : preg_match_all('/./us', $phone);

        if ($name === '' || $nameLength === false || $nameLength > 100) {
            $error_message = translate('নাম লিখুন (সর্বোচ্চ ১০০ অক্ষর)।');
        } elseif (preg_match('/[\x00-\x1F\x7F]/u', $name)) {
            $error_message = translate('নামে অগ্রহণযোগ্য control character ব্যবহার করা যাবে না।');
        } elseif ($email === '' || strlen($email) > 100 || !$emailValid) {
            $error_message = translate('সঠিক ইমেইল ঠিকানা দিন (সর্বোচ্চ ১০০ অক্ষর)।');
        } elseif ($phone !== '' && (
            !is_string($phoneDigits)
            || !preg_match('/^\+?[\p{N}().\-\s]+$/u', $phone)
            || preg_match_all('/\p{N}/u', $phone) < 7
            || preg_match_all('/\p{N}/u', $phone) > 15
            || $phoneLength === false
            || $phoneLength > 20
        )) {
            $error_message = translate('ফোন নম্বরটি সঠিকভাবে লিখুন (৭ থেকে ১৫টি সংখ্যা)।');
        } elseif ($subject === '' || $subjectLength === false || $subjectLength > 200) {
            $error_message = translate('বিষয় লিখুন (সর্বোচ্চ ২০০ অক্ষর)।');
        } elseif (preg_match('/[\x00-\x1F\x7F]/u', $subject)) {
            $error_message = translate('বিষয়ে অগ্রহণযোগ্য control character ব্যবহার করা যাবে না।');
        } elseif ($message === '' || $messageLength === false || $messageLength > 10000) {
            $error_message = translate('বার্তা লিখুন (সর্বোচ্চ ১০,০০০ অক্ষর)।');
        } elseif (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $message)) {
            $error_message = translate('বার্তায় অগ্রহণযোগ্য control character ব্যবহার করা যাবে না।');
        } else {
            try {
                $insertStmt = $conn->prepare(
                    'INSERT INTO contact_messages (name, email, phone, subject, message)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $insertStmt->bind_param('sssss', $name, $email, $phone, $subject, $message);
                $insertStmt->execute();

                $_SESSION['contact_flash'] = translate('আপনার বার্তা সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।');
                header('Location: contact.php', true, 303);
                exit;
            } catch (mysqli_sql_exception $exception) {
                error_log('Unable to save contact form submission: ' . $exception->getMessage());
                $error_message = translate('বার্তা পাঠাতে সমস্যা হয়েছে। পরে আবার চেষ্টা করুন।');
            }
        }
    }
}

if (isset($_SESSION['contact_flash'])) {
    $message_sent = true;
    unset($_SESSION['contact_flash']);
}

$escapeContactValue = static function (string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h3 class="mb-0 bangla-font"><?php echo translate('যোগাযোগ করুন'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($message_sent): ?>
                    <div class="alert alert-success bangla-font" role="status">
                        <i class="fas fa-check-circle"></i> <?php echo translate('আপনার বার্তা সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।'); ?>
                    </div>
                    <?php elseif ($error_message !== ''): ?>
                    <div class="alert alert-danger bangla-font" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $escapeContactValue($error_message); ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo $escapeContactValue($contactCsrfToken); ?>">
                        <div class="visually-hidden" aria-hidden="true">
                            <label for="website"><?php echo translate('ওয়েবসাইট'); ?></label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label bangla-font"><?php echo translate('আপনার নাম *'); ?></label>
                                <input type="text" class="form-control" id="name" name="name" maxlength="100" autocomplete="name" value="<?php echo $escapeContactValue($name); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label bangla-font"><?php echo translate('ইমেইল ঠিকানা *'); ?></label>
                                <input type="email" class="form-control" id="email" name="email" maxlength="100" autocomplete="email" value="<?php echo $escapeContactValue($email); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label bangla-font"><?php echo translate('ফোন নম্বর'); ?></label>
                            <input type="tel" class="form-control" id="phone" name="phone" maxlength="20" autocomplete="tel" value="<?php echo $escapeContactValue($phone); ?>" aria-describedby="phoneHelp">
                            <div id="phoneHelp" class="form-text bangla-font"><?php echo translate('ঐচ্ছিক; আন্তর্জাতিক নম্বরসহ ৭ থেকে ১৫টি সংখ্যা লিখুন।'); ?></div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label bangla-font"><?php echo translate('বিষয় *'); ?></label>
                            <input type="text" class="form-control" id="subject" name="subject" maxlength="200" value="<?php echo $escapeContactValue($subject); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label bangla-font"><?php echo translate('আপনার বার্তা *'); ?></label>
                            <textarea class="form-control" id="message" name="message" rows="6" maxlength="10000" required><?php echo $escapeContactValue($message); ?></textarea>
                            <div class="form-text bangla-font"><?php echo translate('সর্বোচ্চ ১০,০০০ অক্ষর।'); ?></div>
                        </div>

                        <button type="submit" class="btn btn-dark bangla-font">
                            <i class="fas fa-paper-plane"></i> <?php echo translate('বার্তা পাঠান'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0 bangla-font"><?php echo translate('যোগাযোগ তথ্য'); ?></h4>
                </div>
                <div class="card-body">
                    <h5 class="bangla-font text-danger"><?php echo translate('বিজ্ঞান জগৎ'); ?></h5>
                    <p class="bangla-font">
                        <i class="fas fa-map-marker-alt text-danger"></i>
                        <?php echo translate('বরিশাল পলিটেকনিক কলেজ রোড, দক্ষিণ আলেকান্দা, বরিশাল'); ?>
                    </p>
                    <p class="bangla-font">
                        <i class="fas fa-phone text-danger"></i>
                        +৮৮ ০১৩২১৩৫৮৯২১
                    </p>
                    <p class="bangla-font">
                        <i class="fas fa-envelope text-danger"></i>
                        smatiqurrahmanlimon@gmail.com
                    </p>
                    <p class="bangla-font">
                        <i class="fas fa-fax text-danger"></i>
                        +৮৮ ০১৮১৮৭৬৮৭৭৭
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0 bangla-font"><?php echo translate('প্রচার ও বিজ্ঞাপন'); ?></h4>
                </div>
                <div class="card-body">
                    <p class="bangla-font">
                        <?php echo translate('বিজ্ঞাপন ও প্রচারের জন্য যোগাযোগ করুন:'); ?>
                    </p>
                    <p class="bangla-font">
                        <i class="fas fa-user-tie text-success"></i>
                        <?php echo translate('মো: আতিকুর রহমান লিমন'); ?>
                    </p>
                    <p class="bangla-font">
                        <i class="fas fa-phone text-success"></i>
                        +৮৮ ০১৩২১৩৫৮৯২১
                    </p>
                    <p class="bangla-font">
                        <i class="fas fa-envelope text-success"></i>
                        scienceandtechnologylab24@gmail.com
                    </p>
                </div>
            </div>

            <!-- Map Placeholder -->
            <div class="mt-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="bangla-font mb-3"><?php echo translate('আমাদের অবস্থান'); ?></h5>
                        <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <iframe
                                src="https://www.google.com/maps?q=BUET,Dhaka&z=15&output=embed"
                                width="100%"
                                height="250"
                                style="border:0;"
                                loading="lazy">
                            </iframe>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    require_once('./includes/footer.php');
?>