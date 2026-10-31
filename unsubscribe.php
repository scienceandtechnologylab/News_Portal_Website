<?php
// unsubscribe.php
require_once 'includes/i18n.php';
require_once 'includes/database.php';

$title = translate('আনসাবস্ক্রাইব');
include 'includes/header.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "UPDATE subscribers
            SET is_active = FALSE,
                unsubscribe_token = NULL
            WHERE unsubscribe_token = ? AND is_active = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $message = translate('আপনি সফলভাবে আনসাবস্ক্রাইব করেছেন। আমরা আপনাকে আর ইমেইল পাঠাবো না।');
        $status = 'success';
    } else {
        $message = translate('আপনি ইতিমধ্যে আনসাবস্ক্রাইব করেছেন অথবা লিংকটি সঠিক নয়।');
        $status = 'warning';
    }
} elseif (isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // ইমেইল দিয়ে আনসাবস্ক্রাইব লিংক পাঠান
    $sql = "SELECT id FROM subscribers WHERE email = ? AND is_active = TRUE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $update_sql = "UPDATE subscribers SET unsubscribe_token = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $token, $email);
        $update_stmt->execute();

        // আনসাবস্ক্রাইব লিংক ইমেইল করুন
        $unsubscribe_link = SITE_URL . "/unsubscribe.php?token=" . $token;

        $message = translate('আপনার ইমেইলে আনসাবস্ক্রাইব লিংক পাঠানো হয়েছে। দয়া আপনার ইমেইল চেক করুন।');
        $status = 'info';
    } else {
        $message = translate('এই ইমেইলটি আমাদের সাবস্ক্রাইবার লিস্টে নেই।');
        $status = 'warning';
    }
}

if (!isset($message)) {
    $message = translate('আপনার ইমেইল দিন। আমরা আনসাবস্ক্রাইব লিংক পাঠাবো।');
    $status = 'info';
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4"><?php echo translate('নিউজলেটার আনসাবস্ক্রাইব'); ?></h3>

                    <div class="alert alert-<?php echo $status; ?>">
                        <?php echo $message; ?>
                    </div>

                    <?php if (!isset($_GET['token']) && !isset($_POST['email'])): ?>
                    <form method="POST" class="mt-3">
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo translate('আপনার ইমেইল ঠিকানা'); ?></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-ban"></i> <?php echo translate('আনসাবস্ক্রাইব লিংক পাঠান'); ?>
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>

                    <div class="mt-4 text-center">
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> <?php echo translate('হোম পেজে ফিরে যান'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>