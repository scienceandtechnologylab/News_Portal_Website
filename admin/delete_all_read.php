<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

// Delete all read messages
$delete_query = "DELETE FROM contact_messages WHERE is_read = 1";
if($conn->query($delete_query)) {
    $affected_rows = $conn->affected_rows;
    $_SESSION['message'] = "সব পড়া বার্তা মুছে ফেলা হয়েছে ($affected_rows টি বার্তা)";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'বার্তা মুছতে সমস্যা হয়েছে';
    $_SESSION['message_type'] = 'danger';
}

header('Location: messages.php');
exit;
?>