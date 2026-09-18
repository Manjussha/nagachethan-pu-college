<?php
/**
 * JSON storage, atomic writes, locking and undo backups.
 */

if (!defined('NPC_ADMIN')) {
    http_response_code(403);
    exit;
}

function read_json(string $file, $default)
{
    if (!is_file($file)) {
        return $default;
    }
    $data = json_decode((string)file_get_contents($file), true);
    return $data === null ? $default : $data;
}

function write_json(string $file, $data): void
{
    atomic_write($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function atomic_write(string $file, string $contents): void
{
    $tmp = $file . '.tmp-' . bin2hex(random_bytes(4));
    if (file_put_contents($tmp, $contents) === false) {
        throw new RuntimeException('Could not write ' . basename($file));
    }
    @chmod($tmp, 0644);
    if (!rename($tmp, $file)) {
        @unlink($tmp);
        throw new RuntimeException('Could not replace ' . basename($file));
    }
}

/** Run $fn while holding an exclusive lock so two saves never overlap. */
function with_lock(callable $fn)
{
    $fp = fopen(ADMIN_DATA . '/.lock', 'c');
    flock($fp, LOCK_EX);
    try {
        return $fn();
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

/** Top-level pages, styles and scripts the admin may rewrite. */
function managed_files(): array
{
    $files = array_merge(glob(SITE_ROOT . '/*.html'), glob(SITE_ROOT . '/*.css'), glob(SITE_ROOT . '/*.js'));
    return array_map('basename', $files);
}

/**
 * Snapshot every managed page + admin state (gzipped) before a change,
 * so History can put the whole site back exactly as it was.
 */
function make_backup(string $label): string
{
    $id = date('Ymd-His') . '-' . bin2hex(random_bytes(2));
    $dir = BACKUP_DIR . '/' . $id;
    mkdir($dir . '/files', 0755, true);
    mkdir($dir . '/state', 0755, true);

    $saved = [];
    foreach (managed_files() as $rel) {
        file_put_contents($dir . '/files/' . $rel . '.gz', gzencode((string)file_get_contents(SITE_ROOT . '/' . $rel), 6));
        $saved[] = $rel;
    }
    foreach (glob(ADMIN_DATA . '/*.json') as $stateFile) {
        $name = basename($stateFile);
        if ($name !== 'auth.json' && $name !== 'login-attempts.json') {
            copy($stateFile, $dir . '/state/' . $name);
        }
    }
    write_json($dir . '/meta.json', [
        'label' => $label,
        'time'  => time(),
        'seq'   => microtime(true), // orders changes made within the same second
        'user'  => $_SESSION['uid'] ?? 'system',
        'files' => $saved,
    ]);
    prune_backups();
    return $id;
}

function list_backups(): array
{
    $out = [];
    foreach (glob(BACKUP_DIR . '/*/meta.json') as $meta) {
        $m = read_json($meta, null);
        if ($m) {
            $m['id'] = basename(dirname($meta));
            $out[] = $m;
        }
    }
    usort($out, fn($a, $b) => ($b['seq'] ?? $b['time']) <=> ($a['seq'] ?? $a['time']));
    return $out;
}

function prune_backups(): void
{
    $all = list_backups();
    foreach (array_slice($all, MAX_BACKUPS) as $old) {
        rrmdir(BACKUP_DIR . '/' . $old['id']);
    }
}

function restore_backup(string $id): void
{
    if (!preg_match('/^[0-9]{8}-[0-9]{6}-[0-9a-f]{4}$/', $id)) {
        throw new RuntimeException('Invalid backup.');
    }
    $dir = BACKUP_DIR . '/' . $id;
    $meta = read_json($dir . '/meta.json', null);
    if (!$meta) {
        throw new RuntimeException('Backup not found.');
    }
    // Backup current state first so a restore can itself be undone.
    make_backup('Undo of: ' . $meta['label'] . ' (' . date('d M, g:i a', $meta['time']) . ')');

    foreach ($meta['files'] as $rel) {
        $src = $dir . '/files/' . basename($rel) . '.gz';
        if (!is_file($src)) {
            continue;
        }
        $old = gzdecode((string)file_get_contents($src));
        $target = SITE_ROOT . '/' . basename($rel);
        if ($old !== false && (!is_file($target) || file_get_contents($target) !== $old)) {
            atomic_write($target, $old);
        }
    }
    // State files that did not exist at backup time are removed, so the
    // admin falls back to reading the restored HTML.
    $backedUp = array_map('basename', glob($dir . '/state/*.json'));
    foreach (glob(ADMIN_DATA . '/*.json') as $current) {
        $name = basename($current);
        if (!in_array($name, ['auth.json', 'login-attempts.json'], true) && !in_array($name, $backedUp, true)) {
            unlink($current);
        }
    }
    foreach ($backedUp as $name) {
        copy($dir . '/state/' . $name, ADMIN_DATA . '/' . $name);
    }
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}
