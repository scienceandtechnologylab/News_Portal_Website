<?php
require_once '../includes/admin_auth.php';
require_admin_permission('logs_view');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    $result = $conn->query(
        'SELECT id, log_type, action, created_at
         FROM system_logs
         ORDER BY id DESC
         LIMIT 10'
    );

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            'id' => (int)$row['id'],
            'type' => $row['log_type'],
            'action' => $row['action'] ?? '',
            'time' => date('H:i:s', strtotime($row['created_at'])),
        ];
    }

    echo json_encode(
        ['success' => true, 'logs' => $logs],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
} catch (mysqli_sql_exception $exception) {
    error_log('Live log query failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(
        ['success' => false, 'message' => 'লাইভ লগ লোড করা যায়নি।'],
        JSON_UNESCAPED_UNICODE
    );
}
