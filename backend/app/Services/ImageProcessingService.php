<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

/**
 * Resizes and compresses images at upload time (avatar, couple photo, gallery
 * photo, QRIS) — the "Resize Image, Compress Image" requirement that had no
 * implementation anywhere before this phase; uploads were previously stored
 * byte-for-byte as the browser sent them.
 */
class ImageProcessingService
{
    private readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(Driver::class);
    }

    /**
     * For photos where file size matters more than pixel-perfect fidelity
     * (avatars, couple photos, gallery photos) — re-encoded as JPEG.
     */
    public function storePhoto(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1600,
        int $quality = 82,
        string $disk = 'public',
    ): string {
        $image = $this->manager->decodePath($file->getRealPath())->scaleDown(width: $maxWidth);
        $encoded = $image->encode(new JpegEncoder(quality: $quality));

        return $this->save($encoded, $directory, 'jpg', $disk);
    }

    /**
     * For QRIS images, where JPEG's lossy compression artifacts risk making the
     * code unscannable — capped in size but kept lossless.
     */
    public function storeLosslessImage(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1000,
        string $disk = 'public',
    ): string {
        $image = $this->manager->decodePath($file->getRealPath())->scaleDown(width: $maxWidth);
        $encoded = $image->encode(new PngEncoder);

        return $this->save($encoded, $directory, 'png', $disk);
    }

    private function save(mixed $encoded, string $directory, string $extension, string $disk): string
    {
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$extension;

        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }
}
