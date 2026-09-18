<?php
/**
 * Upload validation and image processing.
 * Every upload is decoded and re-encoded, so only clean JPG + WebP files
 * ever reach the public uploads folder.
 */

if (!defined('NPC_ADMIN')) {
    http_response_code(403);
    exit;
}

const MAX_SOURCE_PIXELS = 80000000; // ~80 MP, guards against decompression bombs

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The photo is too large.',
        UPLOAD_ERR_PARTIAL => 'Upload was interrupted. Please try again.',
        UPLOAD_ERR_NO_FILE => 'Please choose a photo first.',
        default => 'Upload failed (code ' . $code . ').',
    };
}

/**
 * Normalise $_FILES[field] (single or multiple) into a list of files.
 */
function uploaded_files(string $field): array
{
    if (empty($_FILES[$field])) {
        return [];
    }
    $f = $_FILES[$field];
    if (!is_array($f['name'])) {
        return [$f];
    }
    $out = [];
    foreach ($f['name'] as $i => $name) {
        $out[] = [
            'name'     => $name,
            'tmp_name' => $f['tmp_name'][$i],
            'error'    => $f['error'][$i],
            'size'     => $f['size'][$i],
        ];
    }
    return $out;
}

function has_imagick(): bool
{
    return extension_loaded('imagick') && class_exists('Imagick');
}

function imagick_supports(string $format): bool
{
    return has_imagick() && count(Imagick::queryFormats(strtoupper($format))) > 0;
}

/**
 * Validate one uploaded file and write resized JPG + WebP versions.
 *
 * @param array  $file     one entry from uploaded_files()
 * @param string $subdir   'gallery' or 'site'
 * @param string $baseName safe file name without extension
 * @param int    $maxWidth longest allowed width in pixels
 * @return array{jpg:string, webp:?string, width:int, height:int} paths relative to SITE_ROOT
 */
function process_upload(array $file, string $subdir, string $baseName, int $maxWidth): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(upload_error_message($file['error']));
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload.');
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Photo is larger than 15 MB. Please use a smaller photo.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (imagick_supports('HEIC')) {
        array_push($allowed, 'image/heic', 'image/heif');
    }
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Only JPG, PNG or WebP photos are allowed (got ' . h($mime) . ').');
    }

    $relJpg  = 'uploads/' . $subdir . '/' . $baseName . '.jpg';
    $relWebp = 'uploads/' . $subdir . '/' . $baseName . '.webp';
    $absJpg  = SITE_ROOT . '/' . $relJpg;
    $absWebp = SITE_ROOT . '/' . $relWebp;

    $result = has_imagick()
        ? process_with_imagick($file['tmp_name'], $absJpg, $absWebp, $maxWidth)
        : process_with_gd($file['tmp_name'], $absJpg, $absWebp, $maxWidth);

    @chmod($absJpg, 0644);
    if ($result['webp']) {
        @chmod($absWebp, 0644);
    }

    return [
        'jpg'    => $relJpg,
        'webp'   => $result['webp'] ? $relWebp : null,
        'width'  => $result['width'],
        'height' => $result['height'],
    ];
}

function process_with_imagick(string $src, string $jpg, string $webp, int $maxWidth): array
{
    $ping = new Imagick();
    $ping->pingImage($src);
    if ($ping->getImageWidth() * $ping->getImageHeight() > MAX_SOURCE_PIXELS) {
        throw new RuntimeException('Photo resolution is too high.');
    }
    $ping->clear();

    $im = new Imagick();
    $im->readImage($src);
    $im->setIteratorIndex(0);
    $im = $im->getImage();

    imagick_auto_orient($im);

    if ($im->getImageAlphaChannel()) {
        $im->setImageBackgroundColor('white');
        $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    }
    $im->transformImageColorspace(Imagick::COLORSPACE_SRGB);

    if ($im->getImageWidth() > $maxWidth) {
        $im->resizeImage($maxWidth, 0, Imagick::FILTER_LANCZOS, 1);
    }
    $im->stripImage();

    $out = clone $im;
    $out->setImageFormat('jpeg');
    $out->setImageCompressionQuality(82);
    $out->setInterlaceScheme(Imagick::INTERLACE_PLANE);
    $out->writeImage($jpg);

    $webpOk = false;
    if (imagick_supports('WEBP')) {
        $w = clone $im;
        $w->setImageFormat('webp');
        $w->setImageCompressionQuality(80);
        $webpOk = $w->writeImage($webp);
    } elseif (function_exists('imagewebp')) {
        $gd = imagecreatefromjpeg($jpg);
        $webpOk = $gd && imagewebp($gd, $webp, 80);
    }

    return ['width' => $im->getImageWidth(), 'height' => $im->getImageHeight(), 'webp' => $webpOk];
}

function imagick_auto_orient(Imagick $im): void
{
    switch ($im->getImageOrientation()) {
        case Imagick::ORIENTATION_BOTTOMRIGHT: $im->rotateImage('#000', 180); break;
        case Imagick::ORIENTATION_RIGHTTOP:    $im->rotateImage('#000', 90);  break;
        case Imagick::ORIENTATION_LEFTBOTTOM:  $im->rotateImage('#000', -90); break;
        case Imagick::ORIENTATION_TOPRIGHT:    $im->flopImage(); break;
        case Imagick::ORIENTATION_BOTTOMLEFT:  $im->flipImage(); break;
        case Imagick::ORIENTATION_LEFTTOP:     $im->transposeImage(); break;
        case Imagick::ORIENTATION_RIGHTBOTTOM: $im->transverseImage(); break;
    }
    $im->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
}

function process_with_gd(string $src, string $jpg, string $webp, int $maxWidth): array
{
    $info = @getimagesize($src);
    if (!$info) {
        throw new RuntimeException('This file is not a valid photo.');
    }
    if ($info[0] * $info[1] > MAX_SOURCE_PIXELS) {
        throw new RuntimeException('Photo resolution is too high.');
    }
    $img = @imagecreatefromstring((string)file_get_contents($src));
    if (!$img) {
        throw new RuntimeException('This photo could not be read.');
    }

    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($src);
        $rot = match ((int)($exif['Orientation'] ?? 1)) { 3 => 180, 6 => -90, 8 => 90, default => 0 };
        if ($rot) {
            $img = imagerotate($img, $rot, 0);
        }
    }

    $w = imagesx($img);
    $h = imagesy($img);
    [$nw, $nh] = [$w, $h];
    if ($w > $maxWidth) {
        [$nw, $nh] = [$maxWidth, (int)round($h * $maxWidth / $w)];
    }

    // Resize and flatten any transparency onto white in one step.
    $flat = imagecreatetruecolor($nw, $nh);
    imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
    imagecopyresampled($flat, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    [$w, $h] = [$nw, $nh];

    imageinterlace($flat, true);
    if (!imagejpeg($flat, $jpg, 82)) {
        throw new RuntimeException('Could not save the photo.');
    }
    $webpOk = function_exists('imagewebp') && imagewebp($flat, $webp, 80);

    return ['width' => $w, 'height' => $h, 'webp' => $webpOk];
}

function safe_slug(string $s, int $max = 40): string
{
    $s = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $s));
    $s = trim($s, '-');
    return substr($s !== '' ? $s : 'photo', 0, $max);
}
