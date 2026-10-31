<?php
require_once '../includes/admin_auth.php';
require_admin_permission('logs_view');

header('Content-Type: application/json; charset=utf-8');

// Get log distribution statistics
$stats_query = "SELECT log_type, COUNT(*) as count FROM system_logs GROUP BY log_type";
$stats_result = $conn->query($stats_query);

$labels = [];
$values = [];
$type_names = [
    'info' => 'তথ্য',
    'warning' => 'সতর্কতা',
    'error' => 'এরর',
    'security' => 'সিকিউরিটি',
    'login' => 'লগিন',
    'news' => 'নিউজ',
    'message' => 'বার্তা'
];

while($row = $stats_result->fetch_assoc()) {
    $labels[] = $type_names[$row['log_type']] ?? $row['log_type'];
    $values[] = $row['count'];
}

echo json_encode([
    'labels' => $labels,
    'values' => $values
]);
?>