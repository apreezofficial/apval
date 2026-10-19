<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Exceptions\Domain\InvalidRequestException;
use App\Models\ChatCommunity;
use App\Models\ChatCommunityMember;
use App\Models\ChatConversation;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommunityService
{
    // =========================================================================
    // FLAME COMMUNITY & POLL METHODS
    // =========================================================================

    /**
     * List / browse communities with optional category and search filters.
     */
    public function listCommunities(?string $category = null, ?string $search = null, ?User $currentUser = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Community::with(['owner:id,first_name,last_name'])->latest();

        if ($category && $category !== 'All') {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        if ($currentUser) {
            $userCommunityIds = CommunityMember::where('user_id', $currentUser->id)
                ->pluck('community_id')
                ->flip()
                ->all();

            $paginator->getCollection()->transform(function (Community $community) use ($userCommunityIds) {
                $community->is_joined = isset($userCommunityIds[$community->id]);
                return $community;
            });
        }

        return $paginator;
    }

    /**
     * Create a new community (Flame version).
     */
    public function createFlameCommunity(User $creator, array $data): Community
    {
        return DB::transaction(function () use ($creator, $data) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Community::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $community = Community::create([
                'id' => (string) Str::uuid(),
                'name' => $data['name'],
                'slug' => $slug,
                'category' => $data['category'] ?? 'General',
                'description' => $data['description'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'rules' => $data['rules'] ?? null,
                'owner_id' => $creator->id,
                'members_count' => 1,
                'is_locked' => false,
            ]);

            CommunityMember::create([
                'id' => (string) Str::uuid(),
                'community_id' => $community->id,
                'user_id' => $creator->id,
                'role' => 'owner',
            ]);

            $conversation = Conversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'community',
                'category' => 'community',
                'community_id' => $community->id,
                'name' => $community->name,
                'created_by' => $creator->id,
            ]);

            ConversationParticipant::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'user_id' => $creator->id,
                'role' => 'admin',
            ]);

            return $community->load(['conversation', 'owner']);
        });
    }

    /**
     * Get community details (Flame version).
     */
    public function getFlameCommunity(string $id, ?User $currentUser = null): Community
    {
        $community = Community::with([
            'owner:id,first_name,last_name',
            'conversation',
            'members' => fn($q) => $q->with('user:id,first_name,last_name')->limit(10),
        ])->findOrFail($id);

        if ($currentUser) {
            $membership = CommunityMember::where('community_id', $community->id)
                ->where('user_id', $currentUser->id)
                ->first();

            $community->is_joined = (bool) $membership;
            $community->current_user_role = $membership?->role;
            $community->is_muted = (bool) ($membership?->is_muted ?? false);
        }

        return $community;
    }

    /**
     * Join a community (Flame version).
     */
    public function joinFlameCommunity(User $user, string $communityId): CommunityMember
    {
        $community = Community::findOrFail($communityId);

        if ($community->is_locked) {
            throw new InvalidRequestException('This community is locked and not accepting new members.');
        }

        return DB::transaction(function () use ($user, $community) {
            $member = CommunityMember::firstOrCreate([
                'community_id' => $community->id,
                'user_id' => $user->id,
            ], [
                'id' => (string) Str::uuid(),
                'role' => 'member',
            ]);

            if ($community->conversation) {
                ConversationParticipant::firstOrCreate([
                    'conversation_id' => $community->conversation->id,
                    'user_id' => $user->id,
                ], [
                    'id' => (string) Str::uuid(),
                    'role' => 'member',
                ]);
            }

            $community->update([
                'members_count' => CommunityMember::where('community_id', $community->id)->count(),
            ]);

            return $member;
        });
    }

    /**
     * Leave a community (Flame version).
     */
    public function leaveFlameCommunity(User $user, string $communityId): void
    {
        $community = Community::findOrFail($communityId);

        if ($community->owner_id === $user->id) {
            throw new InvalidRequestException('The community owner cannot leave the community. Transfer ownership first.');
        }

        DB::transaction(function () use ($user, $community) {
            CommunityMember::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->delete();

            if ($community->conversation) {
                ConversationParticipant::where('conversation_id', $community->conversation->id)
                    ->where('user_id', $user->id)
                    ->delete();
            }

            $community->update([
                'members_count' => CommunityMember::where('community_id', $community->id)->count(),
            ]);
        });
    }

    /**
     * Create a community poll.
     */
    public function createPoll(User $user, string $communityId, string $question, array $options): Poll
    {
        $community = Community::with('conversation')->findOrFail($communityId);

        $member = CommunityMember::where('community_id', $communityId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (count($options) < 2) {
            throw new InvalidRequestException('Polls must have at least 2 options.');
        }

        return DB::transaction(function () use ($user, $community, $question, $options) {
            $poll = Poll::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $community->conversation->id,
                'creator_id' => $user->id,
                'question' => $question,
                'is_closed' => false,
            ]);

            foreach ($options as $optText) {
                PollOption::create([
                    'id' => (string) Str::uuid(),
                    'poll_id' => $poll->id,
                    'option_text' => trim($optText),
                    'votes_count' => 0,
                ]);
            }

            return $poll->load(['options', 'creator']);
        });
    }

    /**
     * Vote on a community poll.
     */
    public function votePoll(User $user, string $pollId, string $optionId): array
    {
        $poll = Poll::with('options')->findOrFail($pollId);

        if ($poll->is_closed) {
            throw new InvalidRequestException('This poll is closed.');
        }

        $option = $poll->options->firstWhere('id', $optionId);
        if (!$option) {
            throw new InvalidRequestException('Invalid poll option.');
        }

        return DB::transaction(function () use ($user, $poll, $option) {
            $existingVote = PollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingVote) {
                if ($existingVote->poll_option_id === $option->id) {
                    throw new InvalidRequestException('You have already voted for this option.');
                }

                PollOption::where('id', $existingVote->poll_option_id)->decrement('votes_count');
                $existingVote->update(['poll_option_id' => $option->id]);
            } else {
                PollVote::create([
                    'id' => (string) Str::uuid(),
                    'poll_id' => $poll->id,
                    'poll_option_id' => $option->id,
                    'user_id' => $user->id,
                ]);
            }

            $option->increment('votes_count');

            return [
                'status' => 'success',
                'message' => 'Vote recorded successfully.',
                'poll' => $poll->fresh('options'),
            ];
        });
    }

    /**
     * Update member role (Flame version).
     */
    public function updateMemberRole(User $admin, string $communityId, string $targetUserId, string $role): CommunityMember
    {
        $community = Community::findOrFail($communityId);

        $adminMember = CommunityMember::where('community_id', $communityId)
            ->where('user_id', $admin->id)
            ->whereIn('role', ['admin', 'owner'])
            ->first();

        if (!$adminMember && $community->owner_id !== $admin->id) {
            throw new InvalidRequestException('Only community admins can change member roles.');
        }

        $member = CommunityMember::where('community_id', $communityId)
            ->where('user_id', $targetUserId)
            ->firstOrFail();

        $member->update(['role' => $role]);

        if ($community->conversation) {
            ConversationParticipant::where('conversation_id', $community->conversation->id)
                ->where('user_id', $targetUserId)
                ->update(['role' => $role]);
        }

        return $member;
    }

    /**
     * Mute or unmute member in community (Flame version).
     */
    public function muteFlameMember(User $admin, string $communityId, string $targetUserId, ?int $minutes = null): CommunityMember
    {
        $community = Community::findOrFail($communityId);

        $adminMember = CommunityMember::where('community_id', $communityId)
            ->where('user_id', $admin->id)
            ->whereIn('role', ['admin', 'moderator', 'owner'])
            ->first();

        if (!$adminMember && $community->owner_id !== $admin->id) {
            throw new InvalidRequestException('Only admins or moderators can mute members.');
        }

        $member = CommunityMember::where('community_id', $communityId)
            ->where('user_id', $targetUserId)
            ->firstOrFail();

        $isMuting = ($minutes === null || $minutes > 0);
        $mutedUntil = ($minutes && $minutes > 0) ? now()->addMinutes($minutes) : null;

        $member->update([
            'is_muted' => $isMuting,
            'muted_until' => $mutedUntil,
        ]);

        return $member;
    }

    // =========================================================================
    // DANIEL CHAT COMMUNITY METHODS
    // =========================================================================

    /**
     * Create a new community (Daniel version).
     */
    public function createChatCommunity(
        string $ownerId,
        string $name,
        ?string $description = null,
        string $privacy = 'public',
        ?string $category = null,
        array $tags = []
    ): ChatCommunity {
        return DB::transaction(function () use ($ownerId, $name, $description, $privacy, $category, $tags) {
            $conversation = ChatConversation::create([
                'id' => (string) Str::uuid(),
                'type' => 'group',
                'title' => $name,
                'description' => $description,
                'created_by' => $ownerId,
                'is_active' => true,
            ]);

            $community = ChatCommunity::create([
                'id' => (string) Str::uuid(),
                'conversation_id' => $conversation->id,
                'owner_id' => $ownerId,
                'name' => $name,
                'description' => $description,
                'privacy' => $privacy,
                'category' => $category,
                'tags' => $tags,
            ]);

            $community->addMember($ownerId, 'owner');

            return $community;
        });
    }

    /**
     * Join a chat community (Daniel version).
     */
    public function joinChatCommunity(string $communityId, string $userId): ChatCommunityMember
    {
        $community = ChatCommunity::findOrFail($communityId);

        if ($community->isLocked()) {
            throw new \Exception('This community is locked');
        }

        if ($community->hasMember($userId)) {
            throw new \Exception('You are already a member of this community');
        }

        if ($community->isBanned($userId)) {
            throw new \Exception('You are banned from this community');
        }

        if ($community->approval_required && !$community->isPublic()) {
            throw new \Exception('This community requires approval to join');
        }

        return $community->addMember($userId, 'member');
    }

    /**
     * Leave a chat community (Daniel version).
     */
    public function leaveChatCommunity(string $communityId, string $userId): void
    {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->hasMember($userId)) {
            throw new \Exception('You are not a member of this community');
        }

        $member = $community->members()->where('user_id', $userId)->first();

        if ($member && $member->isOwner()) {
            throw new \Exception('Community owner cannot leave. Transfer ownership first.');
        }

        $community->removeMember($userId);
    }

    /**
     * Get discoverable communities (Daniel version).
     */
    public function getDiscoverableCommunities(
        ?string $category = null,
        ?string $search = null,
        int $perPage = 20
    ) {
        $query = ChatCommunity::public()
            ->active()
            ->with(['owner']);

        if ($category) {
            $query->byCategory($category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('members_count', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get user's communities (Daniel version).
     */
    public function getUserCommunities(string $userId, int $perPage = 20)
    {
        return ChatCommunity::withMember($userId)
            ->with(['owner'])
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Update community settings (admin only).
     */
    public function updateCommunity(
        string $communityId,
        string $userId,
        array $data
    ): ChatCommunity {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isAdmin($userId)) {
            throw new \Exception('You do not have permission to update this community');
        }

        $community->update($data);

        if (isset($data['name'])) {
            $community->conversation->update(['title' => $data['name']]);
        }

        if (isset($data['description'])) {
            $community->conversation->update(['description' => $data['description']]);
        }

        return $community->fresh();
    }

    /**
     * Ban a member (admin only).
     */
    public function banMember(string $communityId, string $userId, string $bannedByUserId, string $reason): void
    {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isAdmin($bannedByUserId)) {
            throw new \Exception('You do not have permission to ban members');
        }

        if ($community->owner_id === $userId) {
            throw new \Exception('Cannot ban the community owner');
        }

        $community->banMember($userId, $reason);
    }

    /**
     * Unban a member (admin only).
     */
    public function unbanMember(string $communityId, string $userId, string $unbannedByUserId): void
    {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isAdmin($unbannedByUserId)) {
            throw new \Exception('You do not have permission to unban members');
        }

        $community->unbanMember($userId);
    }

    /**
     * Mute a member (admin/moderator only - Daniel version).
     */
    public function muteChatMember(
        string $communityId,
        string $userId,
        string $mutedByUserId,
        ?int $minutes = null
    ): void {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isModerator($mutedByUserId)) {
            throw new \Exception('You do not have permission to mute members');
        }

        $member = $community->members()->where('user_id', $userId)->firstOrFail();
        $member->mute($minutes);
    }

    /**
     * Unmute a member (admin/moderator only).
     */
    public function unmuteMember(string $communityId, string $userId, string $unmutedByUserId): void
    {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isModerator($unmutedByUserId)) {
            throw new \Exception('You do not have permission to unmute members');
        }

        $member = $community->members()->where('user_id', $userId)->firstOrFail();
        $member->unmute();
    }

    /**
     * Promote member to moderator/admin (owner only).
     */
    public function promoteMember(
        string $communityId,
        string $userId,
        string $promotedByUserId,
        string $newRole
    ): void {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isOwner($promotedByUserId)) {
            throw new \Exception('Only the community owner can promote members');
        }

        if (!in_array($newRole, ['moderator', 'admin'])) {
            throw new \Exception('Invalid role');
        }

        $member = $community->members()->where('user_id', $userId)->firstOrFail();
        $member->promote($newRole);
    }

    /**
     * Demote member to regular member (owner only).
     */
    public function demoteMember(string $communityId, string $userId, string $demotedByUserId): void
    {
        $community = ChatCommunity::findOrFail($communityId);

        if (!$community->isOwner($demotedByUserId)) {
            throw new \Exception('Only the community owner can demote members');
        }

        $member = $community->members()->where('user_id', $userId)->firstOrFail();

        if ($member->isOwner()) {
            throw new \Exception('Cannot demote the community owner');
        }

        $member->demote();
    }

    /**
     * Get community members.
     */
    public function getCommunityMembers(string $communityId, int $perPage = 50)
    {
        $community = ChatCommunity::findOrFail($communityId);

        return $community->members()
            ->with(['user'])
            ->active()
            ->orderBy('role', 'desc')
            ->orderBy('joined_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Delete a community (owner only).
     */
    public function deleteCommunity(string $communityId, string $userId): void
    {
        $community = ChatCommunity::findOrFail($communityId);

        if ($community->owner_id !== $userId) {
            throw new \Exception('Only the community owner can delete the community');
        }

        DB::transaction(function () use ($community) {
            $community->conversation->delete();
            $community->delete();
        });
    }

    // =========================================================================
    // OVERLOADED INTERFACES (Seamless support for both controllers)
    // =========================================================================

    /**
     * Overloaded createCommunity:
     * - If called with User $creator, array $data -> calls Flame version
     * - If called with string $ownerId, string $name, ... -> calls Daniel version
     */
    public function createCommunity(
        User|string $creatorOrOwnerId,
        mixed $dataOrName = null,
        ?string $description = null,
        string $privacy = 'public',
        ?string $category = null,
        array $tags = []
    ): mixed {
        if ($creatorOrOwnerId instanceof User) {
            return $this->createFlameCommunity($creatorOrOwnerId, is_array($dataOrName) ? $dataOrName : []);
        }

        return $this->createChatCommunity(
            (string) $creatorOrOwnerId,
            (string) $dataOrName,
            $description,
            $privacy,
            $category,
            $tags
        );
    }

    /**
     * Overloaded getCommunity:
     * - Supports both ChatCommunity and Community lookups.
     */
    public function getCommunity(string $communityId, mixed $userOrUserId = null): mixed
    {
        if ($userOrUserId instanceof User) {
            return $this->getFlameCommunity($communityId, $userOrUserId);
        }

        $chatCommunity = ChatCommunity::with(['owner', 'conversation'])->find($communityId);
        if ($chatCommunity) {
            if ($chatCommunity->isPrivate() && $userOrUserId && !$chatCommunity->hasMember((string) $userOrUserId)) {
                throw new \Exception('You do not have access to this private community');
            }
            return $chatCommunity;
        }

        return $this->getFlameCommunity($communityId, null);
    }

    /**
     * Overloaded joinCommunity.
     */
    public function joinCommunity(User|string $userOrCommunityId, ?string $communityIdOrUserId = null): mixed
    {
        if ($userOrCommunityId instanceof User) {
            return $this->joinFlameCommunity($userOrCommunityId, (string) $communityIdOrUserId);
        }

        return $this->joinChatCommunity((string) $userOrCommunityId, (string) $communityIdOrUserId);
    }

    /**
     * Overloaded leaveCommunity.
     */
    public function leaveCommunity(User|string $userOrCommunityId, ?string $communityIdOrUserId = null): void
    {
        if ($userOrCommunityId instanceof User) {
            $this->leaveFlameCommunity($userOrCommunityId, (string) $communityIdOrUserId);
            return;
        }

        $this->leaveChatCommunity((string) $userOrCommunityId, (string) $communityIdOrUserId);
    }

    /**
     * Overloaded muteMember.
     */
    public function muteMember(
        User|string $adminOrCommunityId,
        string $communityIdOrUserId,
        ?string $targetUserIdOrMutedBy = null,
        ?int $minutes = null
    ): mixed {
        if ($adminOrCommunityId instanceof User) {
            return $this->muteFlameMember($adminOrCommunityId, $communityIdOrUserId, (string) $targetUserIdOrMutedBy, $minutes);
        }

        $this->muteChatMember((string) $adminOrCommunityId, $communityIdOrUserId, (string) $targetUserIdOrMutedBy, $minutes);
        return null;
    }
}
