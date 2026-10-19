<?php
declare(strict_types=1);
namespace App\Http\Controllers\AI;

use App\Models\Farm;
use App\Services\AI\FarmProfileTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgrinovaFarmController {
    public function __construct(private FarmProfileTool $farmTool) {}

    public function index(): JsonResponse {
        $user = Auth::user();
        $farms = Farm::where('user_id', $user->id)->get();
        return response()->json(['success' => true, 'data' => $farms]);
    }

    public function store(Request $request): JsonResponse {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'size_hectares' => 'required|numeric|min:0.01',
            'crop' => 'nullable|string',
            'soil_type' => 'nullable|string',
            'planting_date' => 'nullable|date',
            'farming_method' => 'nullable|in:traditional,mechanized,organic',
        ]);

        $user = Auth::user();
        $result = $this->farmTool->createFarm($user->id, $request->all());
        return response()->json($result, 201);
    }

    public function show(string $id): JsonResponse {
        $user = Auth::user();
        $farm = Farm::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        return response()->json(['success' => true, 'data' => $farm]);
    }
}