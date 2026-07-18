<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StoreEvidenceImageService
{
    public function store(UploadedFile $file, string $folder): string
    {
        $folder = trim($folder, '/');

        if (function_exists('imagejpeg')) {
            $compressed = $this->compressToJpeg($file);

            if ($compressed !== null) {
                $path = $folder.'/'.Str::uuid().'.jpg';
                $stored = Storage::disk('evidence')->put($path, $compressed);

                if ($stored && Storage::disk('evidence')->exists($path)) {
                    return $path;
                }

                throw new RuntimeException("File evidence gagal ditulis ke storage: {$path}");
            }
        }

        $path = $file->store($folder, 'evidence');

        if (! is_string($path) || $path === '' || ! Storage::disk('evidence')->exists($path)) {
            throw new RuntimeException('File evidence gagal disimpan ke storage.');
        }

        return $path;
    }

    private function compressToJpeg(UploadedFile $file): ?string
    {
        $mime = $file->getClientMimeType();
        $sourcePath = $file->getPathname();

        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $image) {
            return null;
        }

        ob_start();
        $compressed = imagejpeg($image, null, 75);
        $contents = ob_get_clean();
        imagedestroy($image);

        return $compressed && is_string($contents) ? $contents : null;
    }
}
