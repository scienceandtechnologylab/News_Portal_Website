<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

$result = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
$count = $result->fetch_assoc()['count'];

echo json_encode(['count' => $count]);
exit;
?>