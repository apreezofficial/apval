<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chat;

use App\Http\Requests\Chat\CreateCommunityRequest;
use App\Services\Chat\CommunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController
{
    public function __construct(
        private CommunityService $communityService
    ) {}

    /**
     * Discover communities
     * GET /api/v1/chat/communities/discover
     */
    public function discover(Request $request): JsonResponse
    {
        $category = $request->get('category');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $communities = $this->communityService->getDiscoverableCommunities(
            $category,
            $search,
            (int) $perPage
        );

        return response()->json([
            'success' => true,
            'data' => $communities->map(fn($community) => $this->formatCommunity($community)),
            'meta' => [
                'current_page' => $communities->currentPage(),
                'last_page' => $communities->lastPage(),
                'per_page' => $communities->perPage(),
                'total' => $communities->total(),
            ],
        ]);
    }

    /**
     * Get user's communities
     * GET /api/v1/chat/communities/my
     */
    public function myCommunities(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);

        $communities = $this->communityService->getUserCommunities($user->id, (int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $communities->map(fn($community) => $this->formatCommunity($community)),
            'meta' => [
                'current_page' => $communities->currentPage(),
                'last_page' => $communities->lastPage(),
                'per_page' => $communities->perPage(),
                'total' => $communities->total(),
            ],
        ]);
    }

    /**
     * Get community details
     * GET /api/v1/chat/communities/{id}
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $community = $this->communityService->getCommunity($id, $user->id);

            return response()->json([
                'success' => true,
                'data' => $this->formatCommunity($community, true),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Create a community
     * POST /api/v1/chat/communities
     */
    public function store(CreateCommunityRequest $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $community = $this->communityService->createCommunity(
                $user->id,
                $request->name,
                $request->description,
                $request->privacy ?? 'public',
                $request->category,
                $request->tags ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Community created',
                'data' => $this->formatCommunity($community),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Join a community
     * POST /api/v1/chat/communities/{id}/join
     */
    public function join(string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->communityService->joinCommunity($id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'You have joined the community',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Leave a community
     * POST /api/v1/chat/communities/{id}/leave
     */
    public function leave(string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->communityService->leaveCommunity($id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'You have left the community',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update community (admin only)
     * PUT /api/v1/chat/communities/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'privacy' => 'nullable|in:public,private,invite_only',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'approval_required' => 'nullable|boolean',
            'members_can_post' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        try {
            $community = $this->communityService->updateCommunity(
                $id,
                $user->id,
                $request->only([
                    'name', 'description', 'privacy', 'category',
                    'tags', 'approval_required', 'members_can_post'
                ])
            );

            return response()->json([
                'success' => true,
                'message' => 'Community updated',
                'data' => $this->formatCommunity($community),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get community members
     * GET /api/v1/chat/communities/{id}/members
     */
    public function members(Request $request, string $id): JsonResponse
    {
        $perPage = $request->get('per_page', 50);

        $members = $this->communityService->getCommunityMembers($id, (int) $perPage);

        return response()->json([
            'success' => true,
            'data' => $members->map(fn($member) => [
                'user_id' => $member->user_id,
                'name' => $member->user->first_name . ' ' . $member->user->last_name,
                'profile_picture' => $member->user->profile_picture,
                'role' => $member->role,
                'joined_at' => $member->joined_at?->toISOString(),
                'is_muted' => $member->is_muted,
            ]),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /**
     * Ban member (admin only)
     * POST /api/v1/chat/communities/{id}/members/{userId}/ban
     */
    public function banMember(Request $request, string $id, string $userId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user = Auth::user();

        try {
            $this->communityService->banMember($id, $userId, $user->id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Member banned',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unban member (admin only)
     * POST /api/v1/chat/communities/{id}/members/{userId}/unban
     */
    public function unbanMember(string $id, string $userId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->communityService->unbanMember($id, $userId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Member unbanned',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mute member (admin/moderator only)
     * POST /api/v1/chat/communities/{id}/members/{userId}/mute
     */
    public function muteMember(Request $request, string $id, string $userId): JsonResponse
    {
        $request->validate([
            'minutes' => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();

        try {
            $this->communityService->muteMember(
                $id,
                $userId,
                $user->id,
                $request->minutes
            );

            return response()->json([
                'success' => true,
                'message' => 'Member muted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Unmute member (admin/moderator only)
     * POST /api/v1/chat/communities/{id}/members/{userId}/unmute
     */
    public function unmuteMember(string $id, string $userId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->communityService->unmuteMember($id, $userId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Member unmuted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Promote member (owner only)
     * POST /api/v1/chat/communities/{id}/members/{userId}/promote
     */
    public function promoteMember(Request $request, string $id, string $userId): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:moderator,admin',
        ]);

        $user = Auth::user();

        try {
            $this->communityService->promoteMember(
                $id,
                $userId,
                $user->id,
                $request->role
            );

            return response()->json([
                'success' => true,
                'message' => 'Member promoted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Demote member (owner only)
     * POST /api/v1/chat/communities/{id}/members/{userId}/demote
     */
    public function demoteMember(string $id, string $userId): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->communityService->demoteMember($id, $userId, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Member demoted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete community (owner only)
     * DELETE /api/v1/chat/communities/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            $this->communityService->deleteCommunity($id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Community deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Format community for API response
     */
    private function formatCommunity($community, bool $includeDetails = false): array
    {
        $data = [
            'id' => $community->id,
            'name' => $community->name,
            'slug' => $community->slug,
            'description' => $community->description,
            'avatar_url' => $community->avatar_url,
            'banner_url' => $community->banner_url,
            'category' => $community->category,
            'tags' => $community->tags,
            'privacy' => $community->privacy,
            'members_count' => $community->members_count,
            'posts_count' => $community->posts_count,
            'owner' => [
                'user_id' => $community->owner->id,
                'name' => $community->owner->first_name . ' ' . $community->owner->last_name,
                'profile_picture' => $community->owner->profile_picture,
            ],
            'is_locked' => $community->is_locked,
            'created_at' => $community->created_at->toISOString(),
        ];

        if ($includeDetails) {
            $user = Auth::user();
            $data['is_member'] = $community->hasMember($user->id);
            $data['is_admin'] = $community->isAdmin($user->id);
            $data['is_banned'] = $community->isBanned($user->id);
            $data['approval_required'] = $community->approval_required;
            $data['members_can_post'] = $community->members_can_post;
            $data['rules'] = $community->rules;
        }

        return $data;
    }
}