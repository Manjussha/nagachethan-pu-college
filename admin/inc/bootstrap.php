<?php
/**
 * Nagachethana PU College - image admin: config, session, auth, CSRF.
 */

if (!defined('NPC_ADMIN')) {
    http_response_code(403);
    exit;
}

define('SITE_ROOT', realpath(__DIR__ . '/../..'));
define('PRIVATE_DIR', SITE_ROOT . '/.private');
define('ADMIN_DATA', PRIVATE_DIR . '/admin');
define('BACKUP_DIR', ADMIN_DATA . '/backups');
define('UPLOAD_DIR', SITE_ROOT . '/uploads');
define('AUTH_FILE', ADMIN_DATA . '/auth.json');
define('ATTEMPTS_FILE', ADMIN_DATA . '/login-attempts.json');

const MAX_UPLOAD_BYTES = 15 * 1024 * 1024;   // 15 MB per photo
const MAX_LOGIN_FAILS = 5;
const LOCKOUT_SECONDS = 900;                  // 15 minutes
const SESSION_IDLE_SECONDS = 1800;            // 30 minutes
const MAX_BACKUPS = 30;

@ini_set('memory_limit', '512M');
@set_time_limit(120);

foreach ([ADMIN_DATA, BACKUP_DIR, UPLOAD_DIR . '/gallery', UPLOAD_DIR . '/site'] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
// Admin data (password hash, backups) must never be downloadable.
if (!is_file(PRIVATE_DIR . '/.htaccess')) {
    file_put_contents(PRIVATE_DIR . '/.htaccess', "Deny from all\n");
}

require __DIR__ . '/storage.php';
require __DIR__ . '/images.php';
require __DIR__ . '/site.php';

// ---------- Security headers ----------
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

// ---------- Session ----------
session_name('NPCADMIN');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/admin',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if (!empty($_SESSION['uid']) && (time() - ($_SESSION['last'] ?? 0)) > SESSION_IDLE_SECONDS) {
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['flash'] = ['info', 'Logged out after 30 minutes of inactivity.'];
}
if (!empty($_SESSION['uid'])) {
    $_SESSION['last'] = time();
}

function h(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Session expired. Go back, reload the page and try again.');
    }
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = [$type, $msg];
}

function redirect(string $view = 'gallery'): never
{
    header('Location: ./?view=' . rawurlencode($view));
    exit;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['uid']);
}

// ---------- Login with lockout ----------
function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function login_locked_for(): int
{
    $all = read_json(ATTEMPTS_FILE, []);
    $rec = $all[client_ip()] ?? null;
    if (!$rec || $rec['fails'] < MAX_LOGIN_FAILS) {
        return 0;
    }
    $left = ($rec['time'] + LOCKOUT_SECONDS) - time();
    return max(0, $left);
}

function record_login_result(bool $ok): void
{
    with_lock(function () use ($ok) {
        $all = read_json(ATTEMPTS_FILE, []);
        $now = time();
        // drop stale entries
        foreach ($all as $ip => $rec) {
            if ($now - $rec['time'] > LOCKOUT_SECONDS) {
                unset($all[$ip]);
            }
        }
        $ip = client_ip();
        if ($ok) {
            unset($all[$ip]);
        } else {
            $all[$ip] = ['fails' => ($all[$ip]['fails'] ?? 0) + 1, 'time' => $now];
        }
        write_json(ATTEMPTS_FILE, $all);
    });
}

function attempt_login(string $user, string $pass): bool
{
    $auth = read_json(AUTH_FILE, null);
    if (!$auth || empty($auth['hash'])) {
        return false;
    }
    $userOk = hash_equals(strtolower($auth['user']), strtolower(trim($user)));
    $passOk = password_verify($pass, $auth['hash']);
    if ($userOk && $passOk) {
        session_regenerate_id(true);
        $_SESSION['uid'] = $auth['user'];
        $_SESSION['last'] = time();
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return true;
    }
    return false;
}
