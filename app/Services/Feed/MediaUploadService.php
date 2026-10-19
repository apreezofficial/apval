<?php

declare(strict_types=1);

namespace App\Services\Feed;

use App\Models\FeedPostMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaUploadService
{
    // Size limits (in bytes)
    const MAX_IMAGE_SIZE = 10 * 1024 * 1024;      // 10MB
    const MAX_VIDEO_SIZE = 100 * 1024 * 1024;     // 100MB
    const MAX_GIF_SIZE = 15 * 1024 * 1024;        // 15MB

    // Allowed MIME types
    const IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    const VIDEO_MIME_TYPES = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/webm'];

    /**
     * Process and upload media files for a post
     */
    public function processMediaForPost(string $postId, array $files): array
    {
        $uploadedMedia = [];
        $sortOrder = 0;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $mediaData = $this->processSingleFile($file, $postId, $sortOrder);
            if ($mediaData) {
                $uploadedMedia[] = $mediaData;
                $sortOrder++;
            }
        }

        return $uploadedMedia;
    }

    /**
     * Process a single media file
     */
    private function processSingleFile(UploadedFile $file, string $postId, int $sortOrder): ?FeedPostMedia
    {
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // Validate file type
        $mediaType = $this->determineMediaType($mimeType);
        if (!$mediaType) {
            throw new \Exception('Unsupported file type: ' . $mimeType);
        }

        // Validate file size
        $this->validateFileSize($file, $mediaType);

        // Determine storage folder
        $folder = $this->getStorageFolder($mediaType);

        // Process based on media type
        if ($mediaType === 'image' || $mediaType === 'gif') {
            return $this->processImage($file, $postId, $mediaType, $folder, $sortOrder);
        }

        if ($mediaType === 'video') {
            return $this->processVideo($file, $postId, $folder, $sortOrder);
        }

        return null;
    }

    /**
     * Process image file (with compression and thumbnail)
     */
    private function processImage(
        UploadedFile $file,
        string $postId,
        string $mediaType,
        string $folder,
        int $sortOrder
    ): FeedPostMedia {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $folder . '/' . $filename;

        // Compress image using Intervention Image
        $compressedPath = $this->compressImage($file, $folder . '/compressed/' . $filename);

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($file, $folder . '/thumbnails/' . $filename);

        // Get image dimensions
        $dimensions = $this->getImageDimensions($file);

        // Store original file
        $file->storeAs($folder . '/original', $filename, 'public');

        // Create media record
        return FeedPostMedia::create([
            'id' => (string) Str::uuid(),
            'post_id' => $postId,
            'media_type' => $mediaType,
            'media_url' => $compressedPath,
            'thumbnail_url' => $thumbnailPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'sort_order' => $sortOrder,
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
                'original_size' => $file->getSize(),
            ],
        ]);
    }

    /**
     * Process video file
     */
    private function processVideo(
        UploadedFile $file,
        string $postId,
        string $folder,
        int $sortOrder
    ): FeedPostMedia {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($folder, $filename, 'public');

        // TODO: Generate video thumbnail using FFmpeg
        // For now, use a placeholder
        $thumbnailPath = null;

        return FeedPostMedia::create([
            'id' => (string) Str::uuid(),
            'post_id' => $postId,
            'media_type' => 'video',
            'media_url' => $path,
            'thumbnail_url' => $thumbnailPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => $sortOrder,
            'metadata' => [
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);
    }

    /**
     * Compress image using Intervention Image
     */
    private function compressImage(UploadedFile $file, string $path): string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            // Resize if larger than 1920px
            $image->scaleDown(width: 1920);

            // Convert to webp for better compression (except for gifs)
            $extension = $file->getClientOriginalExtension();
            if (strtolower($extension) !== 'gif') {
                $path = preg_replace('/\.[^.]+$/', '.webp', $path);
                $image->toWebp(85);
            } else {
                $image->toPng(85);
            }

            $image->save(storage_path('app/public/' . $path));

            return $path;
        } catch (\Exception $e) {
            // Fallback: store original file
            return $file->store($path, 'public');
        }
    }

    /**
     * Generate thumbnail for image
     */
    private function generateThumbnail(UploadedFile $file, string $path): string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            $image->cover(400, 400);

            $extension = $file->getClientOriginalExtension();
            if (strtolower($extension) !== 'gif') {
                $path = preg_replace('/\.[^.]+$/', '.webp', $path);
                $image->toWebp(75);
            } else {
                $image->toPng(75);
            }

            $image->save(storage_path('app/public/' . $path));

            return $path;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get image dimensions
     */
    private function getImageDimensions(UploadedFile $file): array
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Determine media type from MIME type
     */
    private function determineMediaType(string $mimeType): ?string
    {
        if (in_array($mimeType, self::IMAGE_MIME_TYPES)) {
            return $mimeType === 'image/gif' ? 'gif' : 'image';
        }

        if (in_array($mimeType, self::VIDEO_MIME_TYPES)) {
            return 'video';
        }

        return null;
    }

    /**
     * Validate file size
     */
    private function validateFileSize(UploadedFile $file, string $mediaType): void
    {
        $size = $file->getSize();
        $maxSize = match ($mediaType) {
            'image' => self::MAX_IMAGE_SIZE,
            'gif' => self::MAX_GIF_SIZE,
            'video' => self::MAX_VIDEO_SIZE,
            default => self::MAX_IMAGE_SIZE,
        };

        if ($size > $maxSize) {
            $maxMB = $maxSize / (1024 * 1024);
            throw new \Exception("File size exceeds maximum limit of {$maxMB}MB");
        }
    }

    /**
     * Get storage folder for media type
     */
    private function getStorageFolder(string $mediaType): string
    {
        return match ($mediaType) {
            'image', 'gif' => 'feed/images',
            'video' => 'feed/videos',
            default => 'feed/media',
        };
    }

    /**
     * Delete media files
     */
    public function deleteMedia(FeedPostMedia $media): void
    {
        if ($media->media_url) {
            Storage::disk('public')->delete($media->media_url);
        }

        if ($media->thumbnail_url) {
            Storage::disk('public')->delete($media->thumbnail_url);
        }
    }
}