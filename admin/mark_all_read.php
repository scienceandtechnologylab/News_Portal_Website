<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

// Mark all messages as read
$update_query = "UPDATE contact_messages SET is_read = 1 WHERE is_read = 0";
if($conn->query($update_query)) {
    $affected_rows = $conn->affected_rows;
    $_SESSION['message'] = "সব বার্তা পড়া হিসেবে মার্ক করা হয়েছে ($affected_rows টি বার্তা)";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'বার্তা মার্ক করতে সমস্যা হয়েছে';
    $_SESSION['message_type'] = 'danger';
}

header('Location: messages.php');
exit;
?>