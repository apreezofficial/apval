<?php

declare(strict_types=1);

namespace App\Policies\Ads;

use App\Models\Ads\Advertisement;
use App\Models\User;

class AdvertisementPolicy
{
    public function view(User $user, Advertisement $advertisement): bool
    {
        return $user->role === 'admin' || $advertisement->user_id === $user->id;
    }

    public function update(User $user, Advertisement $advertisement): bool
    {
        return $advertisement->user_id === $user->id;
    }

    public function delete(User $user, Advertisement $advertisement): bool
    {
        return $advertisement->user_id === $user->id;
    }
}