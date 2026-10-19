<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Ads\AdvertiserStatus;
use App\Models\Ads\Advertiser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdvertiserApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $advertiser = Advertiser::query()->where('user_id', $request->user()->id)->first();

        if (! $advertiser || $advertiser->status !== AdvertiserStatus::Approved) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be an approved advertiser to perform this action.',
            ], 403);
        }

        $request->attributes->set('advertiser', $advertiser);

        return $next($request);
    }
}