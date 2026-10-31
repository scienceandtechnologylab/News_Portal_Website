<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

if(isset($_GET['id'])) {
    $message_id = intval($_GET['id']);
    $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $message_id");

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
exit;
?>