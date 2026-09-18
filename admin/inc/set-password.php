<?php
/**
 * Create / reset the admin login. Command line only (blocked from the web).
 *
 *   php admin/inc/set-password.php <username>            -> generates a strong password
 *   php admin/inc/set-password.php <username> <password> -> uses the given password
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}

$user = $argv[1] ?? '';
if (!preg_match('/^[A-Za-z0-9._-]{3,40}$/', $user)) {
    fwrite(STDERR, "Usage: php set-password.php <username> [password]\n");
    exit(1);
}

$pass = $argv[2] ?? '';
if ($pass === '') {
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    for ($i = 0; $i < 14; $i++) {
        $pass .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
} elseif (strlen($pass) < 10) {
    fwrite(STDERR, "Password must be at least 10 characters.\n");
    exit(1);
}

$dir = realpath(__DIR__ . '/../..') . '/.private/admin';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($dir . '/auth.json', json_encode([
    'user'    => $user,
    'hash'    => password_hash($pass, PASSWORD_DEFAULT),
    'created' => date('c'),
], JSON_PRETTY_PRINT));
chmod($dir . '/auth.json', 0600);
@unlink($dir . '/login-attempts.json');

echo "Admin login saved.\nUsername: {$user}\nPassword: {$pass}\n";
