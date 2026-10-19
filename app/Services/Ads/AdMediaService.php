<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Enums\Ads\MediaType;
use App\Exceptions\Ads\InvalidMediaException;
use App\Models\Ads\Advertisement;
use App\Models\Ads\AdvertisementMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class AdMediaService
{
    public function __construct(
        protected AdSettingService $settings,
        protected ImageManager $imageManager,
    ) {
    }

    public function store(Advertisement $advertisement, UploadedFile $file, int $order = 0): AdvertisementMedia
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $imageMimes = $this->settings->get('allowed_image_mimes', []);
        $videoMimes = $this->settings->get('allowed_video_mimes', []);

        $isImage = in_array($extension, $imageMimes, true);
        $isVideo = in_array($extension, $videoMimes, true);

        if (! $isImage && ! $isVideo) {
            throw new InvalidMediaException("Unsupported media type: .{$extension}");
        }

        $maxSizeMb = $isImage
            ? (float) $this->settings->get('max_image_size_mb')
            : (float) $this->settings->get('max_video_size_mb');

        if ($file->getSize() > $maxSizeMb * 1024 * 1024) {
            throw new InvalidMediaException("File exceeds the {$maxSizeMb}MB limit.");
        }

        return $isImage
            ? $this->storeImage($advertisement, $file, $order)
            : $this->storeVideo($advertisement, $file, $order);
    }

    protected function storeImage(Advertisement $advertisement, UploadedFile $file, int $order): AdvertisementMedia
    {
        $directory = "ads/{$advertisement->id}";
        $disk = config('ads.media_disk', 'public');

        $image = $this->imageManager->read($file->getRealPath());

        $width = $image->width();
        $height = $image->height();

        $encoded = $image->scaleDown(width: 1600)->toJpeg(quality: 80);

        $filename = uniqid('img_', true) . '.jpg';
        $path = "{$directory}/{$filename}";

        Storage::disk($disk)->put($path, (string) $encoded);

        return AdvertisementMedia::create([
            'advertisement_id' => $advertisement->id,
            'type' => MediaType::Image,
            'path' => $path,
            'thumbnail_path' => null,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'duration_seconds' => null,
            'order' => $order,
        ]);
    }

    protected function storeVideo(Advertisement $advertisement, UploadedFile $file, int $order): AdvertisementMedia
    {
        $directory = "ads/{$advertisement->id}";
        $disk = config('ads.media_disk', 'public');
        $extension = strtolower((string) $file->getClientOriginalExtension());

        $filename = uniqid('vid_', true) . '.' . $extension;
        $path = $file->storeAs($directory, $filename, $disk);

        // TODO: Wire up FFmpeg (composer require pbmedia/laravel-ffmpeg) to extract
        // duration_seconds (validate against video_min_seconds/video_max_seconds)
        // and generate a thumbnail_path.

        return AdvertisementMedia::create([
            'advertisement_id' => $advertisement->id,
            'type' => MediaType::Video,
            'path' => $path,
            'thumbnail_path' => null,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'order' => $order,
        ]);
    }

    public function delete(AdvertisementMedia $media): void
    {
        $disk = config('ads.media_disk', 'public');

        Storage::disk($disk)->delete(array_filter([$media->path, $media->thumbnail_path]));

        $media->delete();
    }
}