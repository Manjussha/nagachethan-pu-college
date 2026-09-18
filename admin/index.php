<?php
/**
 * Nagachethana PU College - Website Image Manager
 */

define('NPC_ADMIN', true);
require __DIR__ . '/inc/bootstrap.php';

$view = $_GET['view'] ?? 'gallery';
if (!in_array($view, ['gallery', 'site', 'history'], true)) {
    $view = 'gallery';
}

// ---------------------------------------------------------------------
// POST actions
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        csrf_check();
        $wait = login_locked_for();
        if ($wait > 0) {
            flash('error', 'Too many wrong attempts. Try again in ' . ceil($wait / 60) . ' minutes.');
        } elseif (attempt_login((string)($_POST['user'] ?? ''), (string)($_POST['pass'] ?? ''))) {
            record_login_result(true);
            redirect('gallery');
        } else {
            record_login_result(false);
            usleep(random_int(300000, 700000));
            flash('error', 'Wrong username or password.');
        }
        header('Location: ./');
        exit;
    }

    if (!is_logged_in()) {
        header('Location: ./');
        exit;
    }
    csrf_check();

    try {
        switch ($action) {
            case 'logout':
                $_SESSION = [];
                session_destroy();
                header('Location: ./');
                exit;

            case 'gallery_add':
                $files = array_filter(uploaded_files('photos'), fn($f) => $f['error'] !== UPLOAD_ERR_NO_FILE);
                if (!$files) {
                    throw new RuntimeException('Please choose at least one photo.');
                }
                if (count($files) > 20) {
                    throw new RuntimeException('Please upload at most 20 photos at a time.');
                }
                $category = array_key_exists($_POST['category'] ?? '', GALLERY_CATEGORIES) ? $_POST['category'] : 'campus';
                $caption = trim((string)($_POST['caption'] ?? ''));
                $caption = mb_substr($caption !== '' ? $caption : GALLERY_CATEGORIES[$category], 0, 100);
                $position = ($_POST['position'] ?? 'top') === 'bottom' ? 'bottom' : 'top';

                $new = [];
                foreach ($files as $f) {
                    $img = process_upload($f, 'gallery', safe_slug($caption) . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)), 1600);
                    $new[] = [
                        'id'       => bin2hex(random_bytes(6)),
                        'src'      => $img['jpg'],
                        'caption'  => $caption,
                        'alt'      => $caption . ' - Nagachethana PU College',
                        'category' => $category,
                        'tall'     => $img['height'] > $img['width'] * 1.15,
                        'width'    => 400,
                        'height'   => (int)round(400 * $img['height'] / $img['width']),
                    ];
                }
                gallery_update('Added ' . count($new) . ' gallery photo(s)', function ($items) use ($new, $position) {
                    return $position === 'top' ? array_merge($new, $items) : array_merge($items, $new);
                });
                flash('success', count($new) . ' photo(s) added to the gallery.');
                redirect('gallery');

            case 'gallery_edit':
                $id = (string)($_POST['id'] ?? '');
                $caption = mb_substr(trim((string)($_POST['caption'] ?? '')), 0, 100);
                if ($caption === '') {
                    throw new RuntimeException('Caption cannot be empty.');
                }
                $category = array_key_exists($_POST['category'] ?? '', GALLERY_CATEGORIES) ? $_POST['category'] : 'campus';
                $tall = !empty($_POST['tall']);
                gallery_update('Edited gallery photo "' . $caption . '"', function ($items) use ($id, $caption, $category, $tall) {
                    $i = gallery_find($items, $id);
                    $items[$i]['caption'] = $caption;
                    $items[$i]['alt'] = $caption . ' - Nagachethana PU College';
                    $items[$i]['category'] = $category;
                    $items[$i]['tall'] = $tall;
                    return $items;
                });
                flash('success', 'Photo details saved.');
                redirect('gallery');

            case 'gallery_replace':
                $id = (string)($_POST['id'] ?? '');
                $file = uploaded_files('photo')[0] ?? ['error' => UPLOAD_ERR_NO_FILE];
                $img = process_upload($file, 'gallery', 'photo-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)), 1600);
                gallery_update('Replaced a gallery photo', function ($items) use ($id, $img) {
                    $i = gallery_find($items, $id);
                    $items[$i]['src'] = $img['jpg'];
                    $items[$i]['width'] = 400;
                    $items[$i]['height'] = (int)round(400 * $img['height'] / $img['width']);
                    return $items;
                });
                flash('success', 'Photo replaced.');
                redirect('gallery');

            case 'gallery_delete':
                $id = (string)($_POST['id'] ?? '');
                gallery_update('Removed a gallery photo', function ($items) use ($id) {
                    array_splice($items, gallery_find($items, $id), 1);
                    return $items;
                });
                flash('success', 'Photo removed from the gallery. You can bring it back from History.');
                redirect('gallery');

            case 'gallery_move':
                $id = (string)($_POST['id'] ?? '');
                $dir = (string)($_POST['dir'] ?? '');
                gallery_update('Reordered gallery', function ($items) use ($id, $dir) {
                    $i = gallery_find($items, $id);
                    $to = match ($dir) {
                        'up' => max(0, $i - 1),
                        'down' => min(count($items) - 1, $i + 1),
                        'top' => 0,
                        default => $i,
                    };
                    $item = array_splice($items, $i, 1);
                    array_splice($items, $to, 0, $item);
                    return $items;
                });
                header('Location: ./?view=gallery#p-' . rawurlencode($id));
                exit;

            case 'slot_replace':
                $key = (string)($_POST['slot'] ?? '');
                $file = uploaded_files('photo')[0] ?? ['error' => UPLOAD_ERR_NO_FILE];
                slot_replace($key, $file);
                flash('success', SITE_SLOTS[$key]['label'] . ' updated on the website.');
                redirect('site');

            case 'restore':
                with_lock(fn() => restore_backup((string)($_POST['id'] ?? '')));
                flash('success', 'Website restored to that earlier version.');
                redirect('history');
        }
    } catch (Throwable $e) {
        $msg = $e instanceof RuntimeException ? $e->getMessage() : 'Something went wrong. Please try again.';
        if (!$e instanceof RuntimeException) {
            error_log('[npc-admin] ' . $e);
        }
        flash('error', $msg);
        redirect($view);
    }
    redirect($view);
}

// ---------------------------------------------------------------------
// Views
// ---------------------------------------------------------------------
function asset_url(string $rel): string
{
    return '../' . implode('/', array_map('rawurlencode', explode('/', $rel)));
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$setupMissing = !is_file(AUTH_FILE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Image Manager - Nagachethana PU College</title>
    <link rel="icon" href="../favicon-32x32.png">
    <link rel="stylesheet" href="admin.css?v=2">
</head>
<body>
<?php if (!is_logged_in()): ?>
    <main class="login">
        <form class="login__card" method="post" action="./" autocomplete="on">
            <img src="../logo-small.jpg" alt="" class="login__logo" width="64" height="64">
            <h1>Image Manager</h1>
            <p class="muted">Nagachethana PU College website</p>
            <?php if ($flash): ?><div class="alert alert--<?= h($flash[0]) ?>"><?= h($flash[1]) ?></div><?php endif; ?>
            <?php if ($setupMissing): ?>
                <div class="alert alert--error">Login is not set up yet. Ask the web developer to create the admin account.</div>
            <?php endif; ?>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="login">
            <label>Username <input name="user" required autocomplete="username" autofocus></label>
            <label>Password <input name="pass" type="password" required autocomplete="current-password"></label>
            <button class="btn btn--primary btn--block">Log in</button>
        </form>
    </main>
<?php else: ?>
    <header class="topbar">
        <div class="topbar__brand">
            <img src="../logo-small.jpg" alt="" width="36" height="36">
            <span>Image Manager</span>
        </div>
        <nav class="tabs">
            <a href="?view=gallery" class="<?= $view === 'gallery' ? 'active' : '' ?>">Gallery</a>
            <a href="?view=site" class="<?= $view === 'site' ? 'active' : '' ?>">Website photos</a>
            <a href="?view=history" class="<?= $view === 'history' ? 'active' : '' ?>">History / Undo</a>
        </nav>
        <form method="post" action="./" class="topbar__logout">
            <?= csrf_field() ?><input type="hidden" name="action" value="logout">
            <button class="btn btn--ghost">Log out</button>
        </form>
    </header>

    <main class="wrap">
        <?php if ($flash): ?><div class="alert alert--<?= h($flash[0]) ?>"><?= h($flash[1]) ?></div><?php endif; ?>

        <?php if ($view === 'gallery'): $items = gallery_load(); ?>
            <section class="card">
                <h2>Add photos to the gallery</h2>
                <form method="post" action="./?view=gallery" enctype="multipart/form-data" class="upload js-busy">
                    <?= csrf_field() ?><input type="hidden" name="action" value="gallery_add">
                    <label class="drop">
                        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" multiple required class="js-preview">
                        <span class="drop__text"><strong>Tap to choose photos</strong><br>JPG, PNG or WebP, up to 15 MB each, max 20 at once</span>
                        <span class="drop__previews"></span>
                    </label>
                    <div class="row">
                        <label>Category
                            <select name="category">
                                <?php foreach (GALLERY_CATEGORIES as $k => $label): ?>
                                    <option value="<?= h($k) ?>"><?= h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="grow">Caption <span class="muted">(what is in the photo)</span>
                            <input name="caption" maxlength="100" placeholder="e.g. Annual Day 2026">
                        </label>
                        <label>Show
                            <select name="position">
                                <option value="top">First in gallery</option>
                                <option value="bottom">Last in gallery</option>
                            </select>
                        </label>
                    </div>
                    <button class="btn btn--primary">Upload &amp; publish</button>
                </form>
            </section>

            <div class="section-head">
                <h2>Gallery photos (<?= count($items) ?>)</h2>
                <a class="btn btn--ghost" href="../gallery" target="_blank" rel="noopener">View gallery page &#8599;</a>
            </div>

            <div class="grid">
                <?php foreach ($items as $i => $it): ?>
                    <article class="photo" id="p-<?= h($it['id']) ?>">
                        <a href="<?= h(asset_url($it['src'])) ?>" target="_blank" rel="noopener" class="photo__img">
                            <img src="<?= h(asset_url($it['src'])) ?>" alt="" loading="lazy">
                            <span class="badge"><?= $i + 1 ?></span>
                        </a>
                        <form method="post" action="./?view=gallery" class="photo__form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="gallery_edit">
                            <input type="hidden" name="id" value="<?= h($it['id']) ?>">
                            <input name="caption" value="<?= h($it['caption']) ?>" maxlength="100" required aria-label="Caption">
                            <div class="row row--tight">
                                <select name="category" aria-label="Category">
                                    <?php foreach (GALLERY_CATEGORIES as $k => $label): ?>
                                        <option value="<?= h($k) ?>" <?= $it['category'] === $k ? 'selected' : '' ?>><?= h($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label class="check"><input type="checkbox" name="tall" value="1" <?= !empty($it['tall']) ? 'checked' : '' ?>> Tall</label>
                                <button class="btn btn--small">Save</button>
                            </div>
                        </form>
                        <div class="photo__actions">
                            <?php foreach (['top' => 'To top', 'up' => '&#8593;', 'down' => '&#8595;'] as $dir => $txt): ?>
                                <form method="post" action="./?view=gallery">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="gallery_move">
                                    <input type="hidden" name="id" value="<?= h($it['id']) ?>">
                                    <input type="hidden" name="dir" value="<?= $dir ?>">
                                    <button class="btn btn--small btn--ghost" title="Move <?= $dir ?>" <?= ($i === 0 && $dir !== 'down') || ($i === count($items) - 1 && $dir === 'down') ? 'disabled' : '' ?>><?= $txt ?></button>
                                </form>
                            <?php endforeach; ?>
                            <form method="post" action="./?view=gallery" enctype="multipart/form-data" class="js-busy">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="gallery_replace">
                                <input type="hidden" name="id" value="<?= h($it['id']) ?>">
                                <label class="btn btn--small btn--ghost">Change photo
                                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" class="js-autosubmit" hidden>
                                </label>
                            </form>
                            <form method="post" action="./?view=gallery" class="js-confirm" data-confirm="Remove this photo from the gallery?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="gallery_delete">
                                <input type="hidden" name="id" value="<?= h($it['id']) ?>">
                                <button class="btn btn--small btn--danger">Delete</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

        <?php elseif ($view === 'site'): ?>
            <p class="lead">These photos appear on fixed places of the website. Choose a new photo to replace one - it goes live immediately.</p>
            <div class="grid">
                <?php foreach (slots_state() as $key => $slot): ?>
                    <article class="photo">
                        <a href="<?= h(asset_url($slot['current_jpg'])) ?>" target="_blank" rel="noopener" class="photo__img">
                            <img src="<?= h(asset_url($slot['current_jpg'])) ?>" alt="" loading="lazy">
                        </a>
                        <div class="photo__body">
                            <h3><?= h($slot['label']) ?></h3>
                            <p class="muted small"><?= h($slot['where']) ?></p>
                            <?php if ($slot['updated']): ?>
                                <p class="muted small">Last changed <?= h(date('d M Y, g:i a', $slot['updated'])) ?></p>
                            <?php endif; ?>
                        </div>
                        <form method="post" action="./?view=site" enctype="multipart/form-data" class="photo__actions js-busy">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="slot_replace">
                            <input type="hidden" name="slot" value="<?= h($key) ?>">
                            <label class="btn btn--primary btn--small">Replace photo
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/heic,image/heif" class="js-autosubmit" hidden>
                            </label>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>

        <?php else: $backups = list_backups(); ?>
            <p class="lead">Every change saves a copy of the website first. If something looks wrong, restore the version from <strong>before</strong> that change.</p>
            <?php if (!$backups): ?>
                <p class="muted">No changes yet.</p>
            <?php else: ?>
                <div class="card">
                    <table class="history">
                        <thead><tr><th>When</th><th>Change</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($backups as $b): ?>
                            <tr>
                                <td><?= h(date('d M Y, g:i a', $b['time'])) ?></td>
                                <td><?= h($b['label']) ?></td>
                                <td>
                                    <form method="post" action="./?view=history" class="js-confirm" data-confirm="Undo this change and everything after it?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="id" value="<?= h($b['id']) ?>">
                                        <button class="btn btn--small btn--ghost">Undo this</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <div class="busy" hidden><div class="busy__box"><span class="spinner"></span>Uploading &amp; optimising photo&hellip;<br><small>Please keep this page open.</small></div></div>
<?php endif; ?>
<script src="admin.js?v=2"></script>
</body>
</html>
