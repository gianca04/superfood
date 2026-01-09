<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageConversionService
{
    /**
     * Convert an image to WebP format and fix EXIF orientation
     *
     * @param string $path Path relative to the storage disk
     * @param string $disk Storage disk name (default: 'public')
     * @param int $quality WebP quality (0-100, default: 85)
     * @return string|null New path if converted, original path if already WebP, null on error
     */
    public static function convertToWebP(string $path, string $disk = 'public', int $quality = 85): ?string
    {
        try {
            // Verify file exists
            if (!Storage::disk($disk)->exists($path)) {
                Log::warning("ImageConversionService: File not found: {$path}");
                return null;
            }

            $fullPath = Storage::disk($disk)->path($path);
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

            // If already WebP, no conversion needed
            if ($extension === 'webp') {
                Log::info("ImageConversionService: File already WebP: {$path}");
                return $path;
            }

            // Only process image files
            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                Log::warning("ImageConversionService: Unsupported format: {$extension} for file: {$path}");
                return $path; // Return original path for unsupported formats
            }

            // Create image manager instance
            $manager = new ImageManager(new Driver());

            // Read the image
            $image = $manager->read($fullPath);

            // Fix EXIF orientation for JPEG images
            if ($extension === 'jpg' || $extension === 'jpeg') {
                if (function_exists('exif_read_data')) {
                    $exif = @exif_read_data($fullPath);
                    if ($exif && isset($exif['Orientation'])) {
                        $orientation = $exif['Orientation'];

                        // Rotate based on EXIF orientation
                        switch ($orientation) {
                            case 3:
                                $image->rotate(180);
                                break;
                            case 6:
                                $image->rotate(-90);
                                break;
                            case 8:
                                $image->rotate(90);
                                break;
                        }
                    }
                }
            }

            // Generate new WebP path
            $pathInfo = pathinfo($path);
            $newPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
            $newFullPath = Storage::disk($disk)->path($newPath);

            // Encode to WebP
            $encoded = $image->toWebp($quality);

            // Save the WebP image
            $encoded->save($newFullPath);

            // Delete original file after successful conversion
            Storage::disk($disk)->delete($path);

            Log::info("ImageConversionService: Successfully converted {$path} to {$newPath}");

            return $newPath;
        } catch (\Exception $e) {
            Log::error("ImageConversionService: Error converting {$path}: " . $e->getMessage());
            return $path; // Return original path on error
        }
    }

    /**
     * Batch convert multiple images to WebP
     *
     * @param array $paths Array of paths relative to the storage disk
     * @param string $disk Storage disk name (default: 'public')
     * @param int $quality WebP quality (0-100, default: 85)
     * @return array Array of converted paths
     */
    public static function batchConvertToWebP(array $paths, string $disk = 'public', int $quality = 85): array
    {
        $convertedPaths = [];

        foreach ($paths as $path) {
            if ($path) {
                $converted = self::convertToWebP($path, $disk, $quality);
                $convertedPaths[] = $converted;
            }
        }

        return $convertedPaths;
    }
}
