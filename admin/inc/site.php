<?php
/**
 * Gallery + fixed site images: state, HTML rendering and publishing.
 */

if (!defined('NPC_ADMIN')) {
    http_response_code(403);
    exit;
}

define('GALLERY_FILE', ADMIN_DATA . '/gallery.json');
define('SLOTS_FILE', ADMIN_DATA . '/site-images.json');
const GALLERY_PAGE = 'gallery.html';
const GALLERY_START = '<!-- GALLERY:START (managed by /admin - edits here are overwritten on publish) -->';
const GALLERY_END = '<!-- GALLERY:END -->';

const GALLERY_CATEGORIES = [
    'campus'   => 'Campus',
    'events'   => 'Events',
    'sports'   => 'Sports',
    'cultural' => 'Cultural',
];

/**
 * Fixed images used on specific pages. 'pages' => '*' means every page.
 * 'jpg' / 'webp' are the file names currently in the HTML when nothing has
 * been replaced yet.
 */
const SITE_SLOTS = [
    'campus' => [
        'label' => 'Main campus photo',
        'where' => 'Banner on blog & area pages, blog cards, link previews',
        'jpg' => 'college-campus.jpg', 'webp' => 'college-campus.webp',
        'pages' => '*', 'max' => 1600,
    ],
    'og-banner' => [
        'label' => 'Link preview banner',
        'where' => 'Image shown when the website link is shared on WhatsApp / Facebook (best size 1200 x 630)',
        'jpg' => 'og-banner.jpg', 'webp' => null,
        'pages' => '*', 'max' => 1200,
    ],
    'physics-lab' => [
        'label' => 'Physics Lab', 'where' => 'Academics page - Facilities',
        'jpg' => 'facility-physics-lab.jpg', 'webp' => null,
        'pages' => ['academics.html'], 'max' => 1200,
    ],
    'chemistry-lab' => [
        'label' => 'Chemistry Lab', 'where' => 'Academics page - Facilities',
        'jpg' => 'facility-chemistry-lab.jpg', 'webp' => null,
        'pages' => ['academics.html'], 'max' => 1200,
    ],
    'biology-lab' => [
        'label' => 'Biology Lab', 'where' => 'Academics page - Facilities',
        'jpg' => 'facility-biology-lab.jpg', 'webp' => null,
        'pages' => ['academics.html'], 'max' => 1200,
    ],
    'computer-lab' => [
        'label' => 'Computer Lab', 'where' => 'Academics page - Facilities',
        'jpg' => 'cs lab.jpg', 'webp' => null,
        'pages' => ['academics.html'], 'max' => 1200,
    ],
    'library' => [
        'label' => 'Library', 'where' => 'Academics page - Facilities',
        'jpg' => 'facility-library.jpg', 'webp' => null,
        'pages' => ['academics.html'], 'max' => 1200,
    ],
    'smart-classroom' => [
        'label' => 'Smart Classroom', 'where' => 'Academics page - Facilities',
        'jpg' => 'class room.jpg', 'webp' => null,
        'pages' => ['academics.html'], 'max' => 1200,
    ],
    'results-1' => [
        'label' => 'Exam results - page 1', 'where' => 'Achievements page - Latest Exam Results',
        'jpg' => 'pg-1 EXAM -1 RESULT 2025.JPG', 'webp' => null,
        'pages' => ['achievements.html'], 'max' => 1600,
    ],
    'results-2' => [
        'label' => 'Exam results - page 2', 'where' => 'Achievements page - Latest Exam Results',
        'jpg' => 'pg-2 EXAM-1 RESULT 2025.JPG', 'webp' => null,
        'pages' => ['achievements.html'], 'max' => 1600,
    ],
];

// =====================================================================
// Gallery
// =====================================================================

function gallery_load(): array
{
    $items = read_json(GALLERY_FILE, null);
    return is_array($items) ? $items : gallery_parse_html();
}

/** Read the current gallery items straight from gallery.html (first run / after restore). */
function gallery_parse_html(): array
{
    $block = gallery_block((string)file_get_contents(SITE_ROOT . '/' . GALLERY_PAGE));
    if ($block === null) {
        return [];
    }
    preg_match_all(
        '~<div class="gallery-item(?<tall> gallery-item--tall)?[^"]*" data-category="(?<cat>[^"]*)" data-image="(?<img>[^"]*)">.*?'
        . '<img[^>]*?alt="(?<alt>[^"]*)"[^>]*?width="(?<w>\d+)" height="(?<h>\d+)"[^>]*>.*?'
        . 'gallery-item__caption">(?<cap>.*?)</p>~s',
        $block,
        $m,
        PREG_SET_ORDER
    );
    $items = [];
    foreach ($m as $n => $row) {
        $items[] = [
            // Stable id so the forms still match on the next request.
            'id'       => substr(md5($n . '|' . $row['img']), 0, 12),
            'src'      => html_entity_decode($row['img'], ENT_QUOTES, 'UTF-8'),
            'caption'  => html_entity_decode($row['cap'], ENT_QUOTES, 'UTF-8'),
            'alt'      => html_entity_decode($row['alt'], ENT_QUOTES, 'UTF-8'),
            'category' => isset(GALLERY_CATEGORIES[$row['cat']]) ? $row['cat'] : 'campus',
            'tall'     => $row['tall'] !== '',
            'width'    => (int)$row['w'],
            'height'   => (int)$row['h'],
        ];
    }
    return $items;
}

function gallery_block(string $html): ?string
{
    $s = strpos($html, GALLERY_START);
    $e = strpos($html, GALLERY_END);
    if ($s === false || $e === false || $e < $s) {
        return null;
    }
    return substr($html, $s + strlen(GALLERY_START), $e - $s - strlen(GALLERY_START));
}

function gallery_render(array $items): string
{
    $out = "\n";
    foreach ($items as $it) {
        $cls = 'gallery-item' . (!empty($it['tall']) ? ' gallery-item--tall' : '') . ' show';
        $label = GALLERY_CATEGORIES[$it['category']] ?? 'Campus';
        $out .= '                <div class="' . $cls . '" data-category="' . h($it['category']) . '" data-image="' . h($it['src']) . "\">\n"
            . '                    <img class="gallery-item__image" src="' . h($it['src']) . '" alt="' . h($it['alt']) . '" loading="lazy" width="' . (int)$it['width'] . '" height="' . (int)$it['height'] . "\">\n"
            . "                    <div class=\"gallery-item__zoom\">\n"
            . "                        <i class=\"fas fa-search-plus\"></i>\n"
            . "                    </div>\n"
            . "                    <div class=\"gallery-item__overlay\">\n"
            . '                        <p class="gallery-item__caption">' . h($it['caption']) . "</p>\n"
            . '                        <p class="gallery-item__category">' . h($label) . "</p>\n"
            . "                    </div>\n"
            . "                </div>\n\n";
    }
    return rtrim($out, "\n") . "\n                ";
}

/**
 * Apply $change to the gallery items, then save JSON and rewrite gallery.html.
 * Everything is backed up first so it can be undone from History.
 */
function gallery_update(string $label, callable $change): void
{
    with_lock(function () use ($label, $change) {
        $items = gallery_load();
        $items = $change($items);

        $path = SITE_ROOT . '/' . GALLERY_PAGE;
        $html = (string)file_get_contents($path);
        if (gallery_block($html) === null) {
            throw new RuntimeException('Gallery markers are missing in gallery.html. Contact the web developer.');
        }

        make_backup($label);

        $s = strpos($html, GALLERY_START) + strlen(GALLERY_START);
        $e = strpos($html, GALLERY_END);
        $html = substr($html, 0, $s) . gallery_render(array_values($items)) . substr($html, $e);

        write_json(GALLERY_FILE, array_values($items));
        atomic_write($path, $html);
    });
}

function gallery_find(array $items, string $id): int
{
    foreach ($items as $i => $it) {
        if ($it['id'] === $id) {
            return $i;
        }
    }
    throw new RuntimeException('Photo not found. Reload the page and try again.');
}

// =====================================================================
// Fixed site images ("slots")
// =====================================================================

/** Current file names for every slot (defaults merged with saved replacements). */
function slots_state(): array
{
    $saved = read_json(SLOTS_FILE, []);
    $out = [];
    foreach (SITE_SLOTS as $key => $def) {
        $out[$key] = $def + [
            'current_jpg'  => $saved[$key]['jpg'] ?? $def['jpg'],
            'current_webp' => array_key_exists($key, $saved) ? ($saved[$key]['webp'] ?? null) : $def['webp'],
            'updated'      => $saved[$key]['time'] ?? null,
        ];
    }
    return $out;
}

function slot_pages(array $slot): array
{
    if ($slot['pages'] === '*') {
        $files = array_merge(glob(SITE_ROOT . '/*.html'), glob(SITE_ROOT . '/*.css'), glob(SITE_ROOT . '/*.js'));
        return array_map('basename', $files);
    }
    return $slot['pages'];
}

/** Every way a file name may be written inside HTML/CSS. */
function name_variants(string $name): array
{
    return array_unique([
        $name,
        str_replace(' ', '%20', $name),
        implode('/', array_map('rawurlencode', explode('/', $name))),
    ]);
}

/** Replace references to $old with $new; only whole file names after a quote, bracket, slash or '='. */
function replace_file_refs(string $text, string $old, string $new): string
{
    foreach (name_variants($old) as $variant) {
        $text = preg_replace(
            '~(?<=["\'(/=])' . preg_quote($variant, '~') . '(?![A-Za-z0-9_.-])~',
            $new,
            $text
        );
    }
    return $text;
}

function slot_replace(string $key, array $file): void
{
    if (!isset(SITE_SLOTS[$key])) {
        throw new RuntimeException('Unknown image.');
    }
    with_lock(function () use ($key, $file) {
        $slot = slots_state()[$key];
        $img = process_upload($file, 'site', $key . '-' . date('Ymd-His'), $slot['max']);

        $pages = slot_pages($slot);
        make_backup('Replaced ' . $slot['label']);

        $pairs = [[$slot['current_jpg'], $img['jpg']]];
        if ($slot['current_webp'] && $img['webp']) {
            $pairs[] = [$slot['current_webp'], $img['webp']];
        } elseif ($slot['current_webp']) {
            // No WebP produced: point old WebP references to the new JPG.
            $pairs[] = [$slot['current_webp'], $img['jpg']];
        }

        $changed = 0;
        foreach ($pages as $page) {
            $path = SITE_ROOT . '/' . $page;
            $orig = (string)file_get_contents($path);
            $text = $orig;
            foreach ($pairs as [$old, $new]) {
                $text = replace_file_refs($text, $old, $new);
            }
            if ($text !== $orig) {
                atomic_write($path, $text);
                $changed++;
            }
        }
        if ($changed === 0) {
            throw new RuntimeException('Could not find this image on the website pages. Nothing was changed.');
        }

        $saved = read_json(SLOTS_FILE, []);
        $saved[$key] = [
            'jpg'  => $img['jpg'],
            'webp' => $slot['current_webp'] ? $img['webp'] : null,
            'time' => time(),
        ];
        write_json(SLOTS_FILE, $saved);
    });
}
