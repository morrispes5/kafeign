<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resizes and re-encodes an admin-uploaded menu photo before storing it.
 *
 * Why this exists rather than just storing the uploaded file as-is: a
 * phone camera photo is routinely 3000x4000px and several megabytes —
 * far larger than anything the menu card displays it at, which just
 * means a slower page for every customer scanning the QR code on
 * cafe wifi. Resizing once at upload time (down to a sensible max
 * dimension, re-encoded as JPEG at a fixed quality) keeps every stored
 * photo a predictable, small size regardless of what the admin's phone
 * produced, and sidesteps PHP's own upload-size ceiling being reached
 * for anything genuinely oversized.
 */
class MenuItemImage
{
    private const MAX_DIMENSION = 1000;

    private const JPEG_QUALITY = 82;

    /**
     * Resizes $file down to fit within MAX_DIMENSION (never upscales a
     * smaller image), re-encodes it as JPEG, stores it on the public
     * disk, and returns the stored path — same contract as
     * UploadedFile::store(), so it's a drop-in replacement.
     */
    public static function store(UploadedFile $file, string $directory = 'menu-items'): string
    {
        $source = self::readSource($file);

        // Formats GD can't decode (shouldn't happen — the `image`
        // validation rule already restricts the mime type — but a
        // corrupt upload is a real possibility) fall back to storing the
        // original untouched rather than losing the upload entirely.
        if (! $source) {
            return $file->store($directory, 'public');
        }

        [$width, $height] = [imagesx($source), imagesy($source)];
        $ratio = min(1, self::MAX_DIMENSION / max($width, $height));
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        // Flatten onto white instead of preserving transparency — every
        // stored photo becomes a plain JPEG, so there is no alpha
        // channel to carry through, and this avoids png/webp
        // transparency turning black on re-encode.
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $path = $directory.'/'.Str::random(40).'.jpg';

        ob_start();
        imagejpeg($resized, null, self::JPEG_QUALITY);
        $contents = ob_get_clean();
        imagedestroy($resized);

        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    /**
     * @return \GdImage|null
     */
    private static function readSource(UploadedFile $file)
    {
        $path = $file->getRealPath();

        return match ($file->getMimeType()) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/bmp' => @imagecreatefrombmp($path),
            default => null,
        } ?: null;
    }
}
