<?php
declare(strict_types=1);
namespace App\Services\AI;
use App\Models\Farm;

class FarmProfileTool {
    public function getFarmProfile(string $userId, ?string $farmId = null): array {
        if ($farmId) {
            $farm = Farm::where('id', $farmId)->where('user_id', $userId)->first();
            if (!$farm) {
                return ['found' => false, 'message' => 'Farm not found or access denied.'];
            }
        } else {
            $farm = Farm::where('user_id', $userId)->latest()->first();
        }

        if (!$farm) {
            return [
                'found' => false,
                'message' => 'You do not have a farm profile yet. Please create one first.',
                'action_required' => 'create_farm'
            ];
        }

        return [
            'found' => true,
            'farm' => [
                'id' => $farm->id,
                'name' => $farm->name,
                'location' => $farm->location,
                'state' => $farm->state,
                'size_hectares' => (float) $farm->size_hectares,
                'crop' => $farm->crop,
                'soil_type' => $farm->soil_type,
                'soil_ph' => $farm->soil_ph ? (float) $farm->soil_ph : null,
                'planting_date' => $farm->planting_date?->toDateString(),
                'expected_harvest' => $farm->expected_harvest?->toDateString(),
                'farming_method' => $farm->farming_method,
                'water_source' => $farm->water_source,
            ]
        ];
    }

    public function createFarm(string $userId, array $data): array {
        $farm = Farm::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $userId,
            'name' => $data['name'] ?? 'My Farm',
            'location' => $data['location'],
            'state' => $data['state'] ?? null,
            'size_hectares' => $data['size_hectares'],
            'crop' => $data['crop'] ?? null,
            'soil_type' => $data['soil_type'] ?? null,
            'planting_date' => $data['planting_date'] ?? null,
            'farming_method' => $data['farming_method'] ?? 'traditional',
        ]);

        return ['success' => true, 'farm_id' => $farm->id, 'message' => 'Farm profile created successfully.'];
    }
}