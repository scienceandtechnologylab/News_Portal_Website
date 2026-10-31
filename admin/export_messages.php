<?php
require_once '../includes/admin_auth.php';
require_admin_permission('messages_manage');

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=messages_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8
fputs($output, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

// Column headers
$headers = ['ID', 'নাম', 'ইমেইল', 'ফোন', 'বিষয়', 'বার্তা', 'পড়া হয়েছে', 'তারিখ'];
fputcsv($output, $headers);

// Get all messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($query);

while($row = $result->fetch_assoc()) {
    $data = [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $row['subject'],
        $row['message'],
        $row['is_read'] ? 'হ্যাঁ' : 'না',
        $row['created_at']
    ];
    fputcsv($output, $data);
}

fclose($output);
exit;
?>