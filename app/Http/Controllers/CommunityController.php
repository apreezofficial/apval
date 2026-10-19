<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Chat\CommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController
{
    public function __construct(private CommunityService $communityService) {}

    /**
     * GET /api/communities
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->input('category');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 20);

        $communities = $this->communityService->listCommunities($category, $search, $request->user(), $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $communities,
        ]);
    }

    /**
     * POST /api/communities
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url'],
            'rules' => ['nullable', 'string'],
        ]);

        $community = $this->communityService->createCommunity($request->user(), $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Community created successfully.',
            'data' => $community,
        ], 201);
    }

    /**
     * GET /api/communities/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $community = $this->communityService->getCommunity($id, $request->user());

        return response()->json([
            'status' => 'success',
            'data' => $community,
        ]);
    }

    /**
     * POST /api/communities/{id}/join
     */
    public function join(Request $request, string $id): JsonResponse
    {
        $membership = $this->communityService->joinCommunity($request->user(), $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully joined community.',
            'data' => $membership,
        ]);
    }

    /**
     * POST /api/communities/{id}/leave
     */
    public function leave(Request $request, string $id): JsonResponse
    {
        $this->communityService->leaveCommunity($request->user(), $id);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully left community.',
        ]);
    }

    /**
     * POST /api/communities/{id}/polls
     */
    public function createPoll(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'options' => ['required', 'array', 'min:2', 'max:10'],
            'options.*' => ['required', 'string', 'max:150'],
        ]);

        $poll = $this->communityService->createPoll(
            $request->user(),
            $id,
            $request->question,
            $request->options
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Poll created.',
            'data' => $poll,
        ], 201);
    }

    /**
     * POST /api/communities/polls/{pollId}/vote
     */
    public function votePoll(Request $request, string $pollId): JsonResponse
    {
        $request->validate([
            'option_id' => ['required', 'string'],
        ]);

        $result = $this->communityService->votePoll($request->user(), $pollId, $request->option_id);

        return response()->json($result);
    }

    /**
     * PUT /api/communities/{id}/members/{userId}/role
     */
    public function updateRole(Request $request, string $id, string $userId): JsonResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:admin,moderator,member'],
        ]);

        $member = $this->communityService->updateMemberRole($request->user(), $id, $userId, $request->role);

        return response()->json([
            'status' => 'success',
            'message' => 'Member role updated.',
            'data' => $member,
        ]);
    }

    /**
     * POST /api/communities/{id}/members/{userId}/mute
     */
    public function mute(Request $request, string $id, string $userId): JsonResponse
    {
        $request->validate([
            'minutes' => ['nullable', 'integer', 'min:1', 'max:43200'], // up to 30 days
        ]);

        $member = $this->communityService->muteMember(
            $request->user(),
            $id,
            $userId,
            $request->input('minutes') ? (int) $request->input('minutes') : null
        );

        return response()->json([
            'status' => 'success',
            'message' => $member->is_muted ? 'Member muted.' : 'Member unmuted.',
            'data' => $member,
        ]);
    }
}
