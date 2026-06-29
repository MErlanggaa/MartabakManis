<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaCompressionService
{
    /**
     * Compress an uploaded image using GD library and save it.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $filename
     * @param int $quality
     * @param int $maxDim
     * @return string Path to the stored file relative to the 'public' disk
     */
    public static function compressAndStoreImage(UploadedFile $file, string $directory, ?string $filename = null, int $quality = 75, int $maxDim = 1200): string
    {
        $filename = $filename ?? uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $sourcePath = $file->getRealPath();
        
        // Create directory in public disk if not exists
        Storage::disk('public')->makeDirectory($directory);
        $destinationPath = Storage::disk('public')->path($directory . '/' . $filename);

        $info = getimagesize($sourcePath);
        if ($info === false) {
            // Fallback to default Laravel store if getimagesize fails
            return $file->storeAs($directory, $filename, 'public');
        }

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];

        // Determine matching image resource creator
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($sourcePath);
                break;
            default:
                $image = null;
        }

        // Fallback to storing normally if creation failed
        if (!$image) {
            return $file->storeAs($directory, $filename, 'public');
        }

        // Resize image if it exceeds the maximum dimension
        if ($width > $maxDim || $height > $maxDim) {
            if ($width > $height) {
                $newWidth = $maxDim;
                $newHeight = (int)($height * ($maxDim / $width));
            } else {
                $newHeight = $maxDim;
                $newWidth = (int)($width * ($maxDim / $height));
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG/WebP
            if ($mime === 'image/png' || $mime === 'image/webp') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // Save and compress based on mime type
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($image, $destinationPath, $quality);
                break;
            case 'image/png':
                // PNG quality is 0-9 (0 is no compression, 9 is maximum compression)
                imagealphablending($image, false);
                imagesavealpha($image, true);
                $pngQuality = (int)max(0, min(9, 9 - round($quality / 10)));
                imagepng($image, $destinationPath, $pngQuality);
                break;
            case 'image/webp':
                imagewebp($image, $destinationPath, $quality);
                break;
            case 'image/gif':
                imagegif($image, $destinationPath);
                break;
        }

        imagedestroy($image);

        return $directory . '/' . $filename;
    }

    /**
     * Compress an uploaded video using FFmpeg if available, or fall back to storing it.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $filename
     * @return string Path to the stored file relative to the 'public' disk
     */
    public static function compressAndStoreVideo(UploadedFile $file, string $directory, ?string $filename = null): string
    {
        $filename = $filename ?? uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $sourcePath = $file->getRealPath();
        
        Storage::disk('public')->makeDirectory($directory);
        $destinationPath = Storage::disk('public')->path($directory . '/' . $filename);

        // Check if FFmpeg is installed
        $ffmpegAvailable = false;
        $output = [];
        $returnVar = -1;
        
        // Execute command check
        @exec('ffmpeg -version', $output, $returnVar);
        if ($returnVar === 0) {
            $ffmpegAvailable = true;
        }

        if ($ffmpegAvailable) {
            try {
                // Compress video: scale height to 720p maximum, set crf to 28 (good compression/quality ratio), fastpreset
                $cmd = sprintf(
                    'ffmpeg -y -i %s -vf "scale=-2:\'min(720,ih)\'" -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k %s 2>&1',
                    escapeshellarg($sourcePath),
                    escapeshellarg($destinationPath)
                );
                
                $compressOutput = [];
                $compressResult = -1;
                @exec($cmd, $compressOutput, $compressResult);

                if ($compressResult === 0) {
                    return $directory . '/' . $filename;
                }
                
                Log::warning('FFmpeg video compression failed, storing original video instead. Output: ' . implode("\n", $compressOutput));
            } catch (\Exception $e) {
                Log::error('Exception during video compression: ' . $e->getMessage());
            }
        }

        // Fallback to storing the original file if FFmpeg is not available or compression failed
        return $file->storeAs($directory, $filename, 'public');
    }
}
