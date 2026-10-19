<?php

namespace App\Providers;

use App\Services\Kyc\BvnVerificationService;
use App\Services\Kyc\MonnifySanitizer;
use App\Services\Security\TwoFactorService;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Ads\Advertisement;
use App\Policies\Ads\AdvertisementPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BvnVerificationService::class);
        $this->app->singleton(MonnifySanitizer::class);
        $this->app->singleton(TwoFactorService::class);
        $this->app->singleton(ImageManager::class, function () {
            return new ImageManager(new Driver());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Advertisement::class, AdvertisementPolicy::class);
    }
}
