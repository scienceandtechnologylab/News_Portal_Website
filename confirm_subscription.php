<?php
// confirm_subscription.php
require_once 'includes/i18n.php';
require_once 'includes/database.php';

$title = translate('সাবস্ক্রিপশন কনফার্মেশন');
include 'includes/header.php';

$message = '';
$status = 'danger';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT id, email, name FROM subscribers
            WHERE confirmation_token = ? AND confirmed = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $subscriber = $result->fetch_assoc();

        // কনফার্ম করুন
        $update_sql = "UPDATE subscribers
                      SET confirmed = TRUE,
                          confirmation_token = NULL,
                          subscription_date = NOW()
                      WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $subscriber['id']);

        if ($update_stmt->execute()) {
            $message = translate('🎉 ধন্যবাদ ') . ($subscriber['name'] ?: '') . translate('! আপনার সাবস্ক্রিপশন সফলভাবে কনফার্ম হয়েছে।');
            $status = 'success';

            // Welcome email পাঠানো যেতে পারে
        } else {
            $message = translate('কনফার্মেশন প্রসেসে সমস্যা হয়েছে। দয়া পরে আবার চেষ্টা করুন।');
        }
    } else {
        $message = translate('অবৈধ বা মেয়াদোত্তীর্ণ টোকেন। দয়া করে নতুন করে সাবস্ক্রাইব করুন।');
    }
} else {
    $message = translate('কোন টোকেন পাওয়া যায়নি।');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <?php if ($status == 'success'): ?>
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle fa-4x text-danger"></i>
                        <?php endif; ?>
                    </div>

                    <h3 class="mb-3"><?php echo translate('সাবস্ক্রিপশন স্ট্যাটাস'); ?></h3>

                    <div class="alert alert-<?php echo $status; ?>">
                        <?php echo $message; ?>
                    </div>

                    <div class="mt-4">
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> <?php echo translate('হোম পেজে যান'); ?>
                        </a>
                        <?php if ($status == 'success'): ?>
                            <a href="category.php" class="btn btn-success ms-2">
                                <i class="fas fa-newspaper"></i> <?php echo translate('সর্বশেষ সংবাদ দেখুন'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>