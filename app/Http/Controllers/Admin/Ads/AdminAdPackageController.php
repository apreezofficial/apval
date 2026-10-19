<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Ads;

use App\Models\Ads\AdPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminAdPackageController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => AdPackage::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'placement_keys' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);

        $package = AdPackage::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Package created.',
            'data' => $package,
        ], 201);
    }

    public function update(Request $request, AdPackage $adPackage): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'duration_hours' => ['sometimes', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'placement_keys' => ['nullable', 'array'],
            'is_active' => ['boolean'],
        ]);

        $adPackage->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Package updated.',
            'data' => $adPackage,
        ]);
    }

    public function destroy(AdPackage $adPackage): JsonResponse
    {
        $adPackage->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Package deleted.',
        ]);
    }
}