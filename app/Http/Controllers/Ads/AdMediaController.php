<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ads;

use App\Http\Requests\Ads\UploadAdMediaRequest;
use App\Models\Ads\Advertisement;
use App\Services\Ads\AdMediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AdMediaController
{
    public function store(UploadAdMediaRequest $request, Advertisement $advertisement, AdMediaService $mediaService): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        $order = $advertisement->media()->count();

        $media = $mediaService->store($advertisement, $request->file('file'), $order);

        return response()->json([
            'status' => 'success',
            'message' => 'Media uploaded.',
            'data' => $media,
        ], 201);
    }

    public function destroy(Advertisement $advertisement, string $mediaId, AdMediaService $mediaService): JsonResponse
    {
        Gate::authorize('update', $advertisement);

        $media = $advertisement->media()->findOrFail($mediaId);
        $mediaService->delete($media);

        return response()->json([
            'status' => 'success',
            'message' => 'Media removed.',
        ]);
    }
}