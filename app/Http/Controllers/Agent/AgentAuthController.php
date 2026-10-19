<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\AgentRegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Agent\AgentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AgentAuthController extends Controller
{
    public function register(AgentRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        [$user, $profile] = DB::transaction(function () use ($data): array {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'agent',
                'status' => 'active',
            ]);

            $profile = AgentProfile::create([
                'user_id' => $user->id,
                'agent_code' => $this->generateAgentCode(),
                'territory' => $data['territory'] ?? null,
                'status' => 'active',
                'onboarded_at' => now(),
            ]);

            return [$user, $profile];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Agent registration successful',
            'data' => $this->authenticationPayload($user, $profile, $data['device_name'] ?? 'agent-mobile'),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['login'])
            ->orWhere('phone', $data['login'])
            ->first();

        if (!$user || $user->role !== 'agent' || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are invalid.'],
            ]);
        }

        $profile = $user->agentProfile;
        if ($user->status !== 'active' || !$profile || $profile->status !== 'active') {
            throw ValidationException::withMessages([
                'login' => ['This agent account is not active.'],
            ]);
        }

        $profile->update(['last_login_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Agent login successful',
            'data' => $this->authenticationPayload($user, $profile, $data['device_name'] ?? 'agent-mobile'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Agent logged out successfully',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('agentProfile.manager');

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'agent_profile' => $user->agentProfile,
            ],
        ]);
    }

    private function authenticationPayload(User $user, AgentProfile $profile, string $deviceName): array
    {
        return [
            'token' => $user->createToken($deviceName)->plainTextToken,
            'user' => $user->load('agentProfile'),
            'agent_profile' => $profile,
        ];
    }

    private function generateAgentCode(): string
    {
        do {
            $code = 'AG-' . strtoupper(Str::random(6));
        } while (AgentProfile::where('agent_code', $code)->exists());

        return $code;
    }
}
