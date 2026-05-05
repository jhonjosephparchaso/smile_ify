<?php
// ===== Load .env file =====
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// ===== Encryption key setup =====
define('APP_ENC_KEY', getenv('APP_ENC_KEY'));
$ENCRYPTION_KEY = hex2bin(APP_ENC_KEY);

// ===== AES-256-GCM ENCRYPTION / DECRYPTION =====
if (!function_exists('encryptField')) {
    function encryptField($plaintext)
    {
        global $ENCRYPTION_KEY;

        if ($plaintext === null || $plaintext === '') {
            return [null, null, null];
        }

        $iv = random_bytes(12); // 96-bit IV for GCM
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return [
            base64_encode($ciphertext),
            base64_encode($iv),
            base64_encode($tag)
        ];
    }
}

if (!function_exists('decryptField')) {
    function decryptField($data, $iv, $tag)
    {
        global $ENCRYPTION_KEY;

        if (empty($data) || empty($iv) || empty($tag)) {
            return null;
        }

        return openssl_decrypt(
            base64_decode($data),
            'aes-256-gcm',
            $ENCRYPTION_KEY,
            OPENSSL_RAW_DATA,
            base64_decode($iv),
            base64_decode($tag)
        );
    }
}

// ===== SMTP CONFIG =====
define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_PORT', getenv('SMTP_PORT'));
define('SMTP_SECURE', getenv('SMTP_SECURE'));
define('SMTP_AUTH', filter_var(getenv('SMTP_AUTH'), FILTER_VALIDATE_BOOLEAN));
define('SMTP_USER', getenv('SMTP_USER'));
define('SMTP_PASS', getenv('SMTP_PASS'));

// ===== Default timezone =====
date_default_timezone_set('Asia/Manila');

// ===== Session setup =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Token setup =====
if (!isset($_SESSION['tab_token'])) {
    $_SESSION['tab_token'] = bin2hex(random_bytes(16));
}

// ===== Define constants (CLI-safe) =====
if (php_sapi_name() === 'cli') {
    // CLI mode — no $_SERVER vars
    define('BASE_URL', '');
    define('BASE_PATH', __DIR__ . '/..');
} else {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/Smile-ify') !== false)
        ? '/Smile-ify'
        : '';

    define('BASE_URL', "$protocol://$host$basePath");
    define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . $basePath);
}

// Detect AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Session inactivity timeout (only for non-AJAX and logged-in users)
if (php_sapi_name() !== 'cli' && !$isAjax) {
    $timeout_duration = getenv('SESSION_TIMEOUT') ?: 1800;

    if (isset($_SESSION['user_id'])) {

        if (isset($_SESSION['LAST_ACTIVITY']) &&
            (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {

            session_unset();
            session_destroy();
            header("Location: " . BASE_URL . "/index.php?timeout=1");
            exit();
        }

        $_SESSION['LAST_ACTIVITY'] = time();
    }
}
?>