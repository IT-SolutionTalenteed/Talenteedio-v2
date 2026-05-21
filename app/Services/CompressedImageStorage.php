<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Throwable;

final class CompressedImageStorage
{
    public function __construct(
        private readonly ImageManager $images,
    ) {}

    /**
     * Store an uploaded file: raster images (except GIF) are scaled and saved as WebP or JPEG.
     * Non-images and GIF are stored unchanged.
     */
    public function store(UploadedFile $file, string $directory, string $preset = 'content'): string
    {
        $mime = strtolower((string) $file->getMimeType());

        if (! str_starts_with($mime, 'image/')) {
            return $file->store($directory, 'public');
        }

        if ($mime === 'image/gif') {
            return $file->store($directory, 'public');
        }

        /** @var array{max_width: int, max_height: int, quality: int} $presetConfig */
        $presetConfig = config("images.presets.$preset", config('images.presets.content'));
        $maxWidth = (int) $presetConfig['max_width'];
        $maxHeight = (int) $presetConfig['max_height'];
        $quality = (int) $presetConfig['quality'];

        $path = $file->getRealPath();
        if ($path === false || ! is_readable($path)) {
            return $file->store($directory, 'public');
        }

        try {
            $image = $this->images->read($path);
            $image->scaleDown(width: $maxWidth, height: $maxHeight);

            $disk = Storage::disk('public');
            $directory = trim($directory, '/');
            $basename = Str::uuid()->toString();

            $encoded = $this->encodeRaster($image, $quality);
            $extension = $encoded->mediaType() === 'image/webp' ? 'webp' : 'jpg';
            $relativePath = $directory.'/'.$basename.'.'.$extension;

            $disk->put($relativePath, (string) $encoded);

            return $relativePath;
        } catch (Throwable $e) {
            Log::warning('CompressedImageStorage: fallback to raw store', [
                'message' => $e->getMessage(),
                'preset' => $preset,
            ]);

            return $file->store($directory, 'public');
        }
    }

    private function encodeRaster(ImageInterface $image, int $quality): EncodedImageInterface
    {
        try {
            return $image->toWebp($quality);
        } catch (Throwable) {
            return $image->toJpeg($quality);
        }
    }
}
