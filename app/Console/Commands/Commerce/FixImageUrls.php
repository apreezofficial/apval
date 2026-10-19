<?php

namespace App\Console\Commands\Commerce;

use Illuminate\Console\Command;

class FixImageUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commerce:fix-image-urls {--force : Force the update without confirmation}';
    protected $description = 'Replace production base URLs with local APP_URL in product_images table';

    public function handle()
    {
        $productionBase = 'https://productionbackend2.agreonpay.com.ng/storage';
        $localBase = rtrim(config('app.url'), '/') . '/storage';

        $this->info("Fixing production URLs...");
        $this->info("Source: $productionBase");
        $this->info("Target: $localBase");

        $images = \App\Models\ProductImage::where('image_url', 'like', "$productionBase%")->get();

        if ($images->isEmpty()) {
            $this->info("No production URLs found.");
            return;
        }

        $this->info("Found " . $images->count() . " images with production URLs.");

        if (!$this->option('force') && !$this->confirm('Do you want to proceed with the update?')) {
            return;
        }

        foreach ($images as $image) {
            $oldUrl = $image->image_url;
            $newUrl = str_replace($productionBase, $localBase, $oldUrl);
            $image->update(['image_url' => $newUrl]);
            $this->line("Updated: $oldUrl -> $newUrl");
        }

        $this->info("Done.");
    }
}
