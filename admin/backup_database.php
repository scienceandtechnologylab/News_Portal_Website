<?php
require_once '../includes/admin_auth.php';
require_admin_permission('backup_manage');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('ব্যাকআপ তৈরির জন্য POST অনুরোধ প্রয়োজন।');
}

$postedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['backup_csrf_token'] ?? '';
if (!is_string($postedToken) || !is_string($sessionToken)
    || $sessionToken === '' || !hash_equals($sessionToken, $postedToken)) {
    http_response_code(400);
    $_SESSION['backup_message'] = 'ব্যাকআপ অনুরোধটি যাচাই করা যায়নি। আবার চেষ্টা করুন।';
    $_SESSION['backup_message_type'] = 'danger';
    header('Location: settings.php');
    exit;
}

$backupDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'backups';
$backupHandle = null;
$temporaryFile = null;
$transactionStarted = false;

try {
    if (!is_dir($backupDirectory)
        && !mkdir($backupDirectory, 0750, true)
        && !is_dir($backupDirectory)) {
        throw new RuntimeException('Backup directory could not be created.');
    }
    if (!is_writable($backupDirectory)) {
        throw new RuntimeException('Backup directory is not writable.');
    }

    $fileName = 'backup_' . date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4)) . '.sql';
    $backupFile = $backupDirectory . DIRECTORY_SEPARATOR . $fileName;
    $temporaryFile = $backupFile . '.tmp';
    $backupHandle = fopen($temporaryFile, 'xb');
    if ($backupHandle === false) {
        throw new RuntimeException('Temporary backup file could not be opened.');
    }

    $write = static function (string $contents) use ($backupHandle): void {
        $length = strlen($contents);
        $offset = 0;
        while ($offset < $length) {
            $written = fwrite($backupHandle, substr($contents, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Backup file could not be written completely.');
            }
            $offset += $written;
        }
    };

    $write("-- Database backup: " . DB_NAME . "\n");
    $write("-- Created: " . date(DATE_ATOM) . "\n\n");
    $write("SET NAMES utf8mb4;\n");
    $write("SET FOREIGN_KEY_CHECKS=0;\n");
    $write('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', DB_NAME) . '`;' . "\n");
    $write('USE `' . str_replace('`', '``', DB_NAME) . '`;' . "\n\n");

    $conn->begin_transaction(MYSQLI_TRANS_START_READ_ONLY | MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT);
    $transactionStarted = true;

    $tableResult = $conn->query('SHOW FULL TABLES');
    $tables = [];
    while ($tableRow = $tableResult->fetch_array(MYSQLI_NUM)) {
        if (($tableRow[1] ?? '') === 'BASE TABLE') {
            $tables[] = $tableRow[0];
        }
    }

    foreach ($tables as $tableName) {
        $quotedTable = '`' . str_replace('`', '``', $tableName) . '`';
        $createResult = $conn->query('SHOW CREATE TABLE ' . $quotedTable);
        $createRow = $createResult->fetch_array(MYSQLI_NUM);
        if (!isset($createRow[1])) {
            throw new RuntimeException('Could not read table definition for ' . $tableName);
        }

        $write('DROP TABLE IF EXISTS ' . $quotedTable . ";\n");
        $write($createRow[1] . ";\n\n");

        $columnsResult = $conn->query('SHOW COLUMNS FROM ' . $quotedTable);
        $columns = [];
        while ($column = $columnsResult->fetch_assoc()) {
            if (stripos($column['Extra'], 'GENERATED') === false) {
                $columns[] = $column['Field'];
            }
        }
        if ($columns === []) {
            continue;
        }

        $quotedColumns = array_map(
            static fn(string $columnName): string => '`' . str_replace('`', '``', $columnName) . '`',
            $columns
        );
        $insertPrefix = 'INSERT INTO ' . $quotedTable . ' (' . implode(', ', $quotedColumns) . ') VALUES ';
        $dataResult = $conn->query('SELECT * FROM ' . $quotedTable);
        while ($row = $dataResult->fetch_assoc()) {
            $values = [];
            foreach ($columns as $columnName) {
                $value = $row[$columnName];
                $values[] = $value === null
                    ? 'NULL'
                    : "'" . $conn->real_escape_string((string)$value) . "'";
            }
            $write($insertPrefix . '(' . implode(', ', $values) . ");\n");
        }
        $write("\n");
    }

    $write("SET FOREIGN_KEY_CHECKS=1;\n");
    $conn->commit();
    $transactionStarted = false;

    if (!fclose($backupHandle)) {
        $backupHandle = null;
        throw new RuntimeException('Backup file could not be closed.');
    }
    $backupHandle = null;

    if (!rename($temporaryFile, $backupFile)) {
        throw new RuntimeException('Completed backup file could not be moved into place.');
    }
    $temporaryFile = null;

    $_SESSION['backup_message'] = 'ডাটাবেস ব্যাকআপ তৈরি হয়েছে: ' . $fileName;
    $_SESSION['backup_message_type'] = 'success';
} catch (Throwable $exception) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    if (is_resource($backupHandle)) {
        fclose($backupHandle);
    }
    if ($temporaryFile !== null && is_file($temporaryFile)) {
        unlink($temporaryFile);
    }

    error_log('Database backup failed: ' . $exception->getMessage());
    $_SESSION['backup_message'] = 'ডাটাবেস ব্যাকআপ তৈরি করা যায়নি। backups ফোল্ডারের write permission এবং PHP error log পরীক্ষা করুন।';
    $_SESSION['backup_message_type'] = 'danger';
}

header('Location: settings.php');
exit;
