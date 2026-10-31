<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/i18n.php';
site_language();

function admin_localize_output(string $output, int $phase = PHP_OUTPUT_HANDLER_FINAL): string
{
    static $pendingOutput = '';
    if (($phase & PHP_OUTPUT_HANDLER_FINAL) === 0) {
        $pendingOutput .= $output;
        return '';
    }

    $output = $pendingOutput . $output;
    $pendingOutput = '';

    $containsDocument = stripos($output, '<html') !== false;
    if ($containsDocument) {
        $language = htmlspecialchars(site_language(), ENT_QUOTES, 'UTF-8');
        $output = preg_replace_callback(
            '/(<html\b[^>]*\blang=["\'])[^"\']*(["\'])/i',
            static function (array $matches) use ($language): string {
                return $matches[1] . $language . $matches[2];
            },
            $output
        ) ?? $output;
    }

    $output = preg_replace_callback(
        '/>([^<>]+)</',
        static function (array $matches): string {
            $text = $matches[1];
            $trimmedText = preg_replace('/[ \t\r\n]+/', ' ', trim($text)) ?? trim($text);
            if ($trimmedText === '') {
                return $matches[0];
            }

            $leadingWhitespace = substr($text, 0, strlen($text) - strlen(ltrim($text)));
            $trailingWhitespace = substr($text, strlen(rtrim($text)));
            $translatedText = translate($trimmedText);
            return '>' . $leadingWhitespace . $translatedText . $trailingWhitespace . '<';
        },
        $output
    ) ?? $output;

    $output = preg_replace_callback(
        '/\b(placeholder|aria-label|alt|title)=(")(.*?)\\2/is',
        static function (array $matches): string {
            $decodedValue = html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return $matches[1] . '="' . htmlspecialchars(
                translate($decodedValue),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ) . '"';
        },
        $output
    ) ?? $output;

    if ($containsDocument && stripos($output, 'site-language-switcher') === false) {
        $switcher = '<div class="admin-language-switcher" style="position:fixed;top:10px;right:16px;z-index:1100;padding:6px 12px;background:#fff;border:1px solid #dee2e6;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.12)">'
            . language_switcher()
            . '</div>';
        $output = preg_replace('/(<body\b[^>]*>)/i', '$1' . $switcher, $output, 1) ?? $output;
    }

    return $output;
}

ob_start('admin_localize_output', 0);

function admin_role_label(string $roleKey): string
{
    $roleLabels = [
        'super_admin' => 'সুপার অ্যাডমিন',
        'administrator' => 'অ্যাডমিনিস্ট্রেটর',
        'news_manager' => 'নিউজ ম্যানেজার',
        'news_editor' => 'নিউজ এডিটর',
        'moderator' => 'মডারেটর',
        'log_viewer' => 'লগ পর্যবেক্ষক',
    ];

    return translate($roleLabels[$roleKey] ?? $roleKey);
}

function require_admin_permission(string $permission = 'dashboard_view'): void
{
    $expectsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
        || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

    if (!isset($_SESSION['admin_logged_in'], $_SESSION['admin_id']) || $_SESSION['admin_logged_in'] !== true) {
        if ($expectsJson) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(
                ['success' => false, 'message' => translate('লগইন সেশন শেষ হয়েছে। আবার লগইন করুন।')],
                JSON_UNESCAPED_UNICODE
            );
            exit;
        }
        header('Location: ' . SITE_URL . 'admin/login.php');
        exit;
    }

    $adminId = (int)$_SESSION['admin_id'];
    $stmt = $GLOBALS['conn']->prepare(
        'SELECT a.id, a.username, a.full_name, a.profile_image, a.is_active,
                r.role_key, r.role_name, r.permissions_json, r.is_super_admin
         FROM admins a
         LEFT JOIN admin_roles r ON r.id = a.role_id
         WHERE a.id = ?
         LIMIT 1'
    );
    if (!$stmt) {
        error_log('Unable to prepare administrator authorization query: ' . $GLOBALS['conn']->error);
        http_response_code(500);
        exit(translate('প্রবেশাধিকার যাচাই করা যায়নি।'));
    }

    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if (!$admin || !(int)$admin['is_active'] || empty($admin['role_key'])) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . SITE_URL . 'admin/login.php');
        exit;
    }

    $permissions = json_decode($admin['permissions_json'], true);
    $permissions = is_array($permissions) ? $permissions : [];
    $isSuperAdmin = (bool)$admin['is_super_admin'];

    $_SESSION['admin_name'] = $admin['full_name'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_profile_image'] = $admin['profile_image'];
    $_SESSION['admin_role'] = $admin['role_key'];
    $_SESSION['admin_role_name'] = admin_role_label($admin['role_key']);
    $_SESSION['admin_permissions'] = $permissions;
    $_SESSION['admin_is_super_admin'] = $isSuperAdmin;

    if ($isSuperAdmin || in_array($permission, $permissions, true)) {
        return;
    }

    http_response_code(403);
    if ($expectsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['success' => false, 'message' => translate('এই কাজটি করার অনুমতি আপনার নেই।')],
            JSON_UNESCAPED_UNICODE
        );
    } else {
        echo '<!doctype html><html lang="bn"><meta charset="utf-8"><title>অনুমতি নেই</title>'
            . '<body style="font-family:sans-serif;padding:2rem"><h1>অনুমতি নেই</h1>'
            . '<p>এই পৃষ্ঠা দেখার বা কাজটি করার অনুমতি আপনার নেই।</p>'
            . '<a href="' . htmlspecialchars(SITE_URL . 'admin/dashboard.php', ENT_QUOTES, 'UTF-8') . '">ড্যাশবোর্ডে ফিরুন</a>'
            . '</body></html>';
    }
    exit;
}

function admin_has_permission(string $permission): bool
{
    return !empty($_SESSION['admin_is_super_admin'])
        || in_array($permission, $_SESSION['admin_permissions'] ?? [], true);
}
