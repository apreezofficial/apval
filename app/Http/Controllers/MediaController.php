<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Media\UploadMediaRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController
{
    /**
     * Upload an image and return its public URL.
     *
     * Security hardening:
     * - Validates both file extension AND MIME type
     * - Uses guessExtension() instead of user-controlled getClientOriginalExtension()
     * - Enforces max file size (2MB)
     * - Limits image dimensions to prevent DoS
     */
    public function upload(UploadMediaRequest $request): JsonResponse
    {
        $request->validated();
        $file = $request->file('image');

        // Use guessExtension() based on MIME type rather than user-controlled extension
        $extension = $file->guessExtension() ?? 'jpg';
        $filename = Str::uuid() . '.' . $extension;

        // Store the file in the public disk under 'uploads/images'
        $path = $file->storeAs('uploads/images', $filename, 'public');

        // Generate the full URL
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded successfully',
            'url' => $url,
            'path' => $path,
            'data' => [
                'url' => $url,
                'path' => $path,
            ],
        ], 201);
    }
}
