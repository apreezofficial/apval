<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeedCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Agriculture', 'icon' => '🌾', 'color' => '#2E7D32', 'description' => 'Farming, crops, livestock, and agricultural practices', 'sort_order' => 1],
            ['name' => 'Farming', 'icon' => '🚜', 'color' => '#558B2F', 'description' => 'Farming techniques, equipment, and best practices', 'sort_order' => 2],
            ['name' => 'Food & Recipes', 'icon' => '🍲', 'color' => '#E65100', 'description' => 'Recipes, cooking tips, and food culture', 'sort_order' => 3],
            ['name' => 'Business', 'icon' => '💼', 'color' => '#1565C0', 'description' => 'Agribusiness, entrepreneurship, and market insights', 'sort_order' => 4],
            ['name' => 'Market Prices', 'icon' => '📊', 'color' => '#6A1B9A', 'description' => 'Current market prices and trends', 'sort_order' => 5],
            ['name' => 'Finance', 'icon' => '💰', 'color' => '#00695C', 'description' => 'Financial tips, loans, and investment', 'sort_order' => 6],
            ['name' => 'AgreonPay News', 'icon' => '📢', 'color' => '#C62828', 'description' => 'Official AgreonPay announcements and updates', 'sort_order' => 7],
            ['name' => 'Events', 'icon' => '📅', 'color' => '#AD1457', 'description' => 'Agricultural events, seminars, and webinars', 'sort_order' => 8],
            ['name' => 'Education & Tips', 'icon' => '📚', 'color' => '#283593', 'description' => 'Educational content and farming tips', 'sort_order' => 9],
            ['name' => 'Promotions', 'icon' => '🎁', 'color' => '#FF6F00', 'description' => 'Special offers and promotions', 'sort_order' => 10],
            ['name' => 'Community', 'icon' => '🤝', 'color' => '#00838F', 'description' => 'Community discussions and stories', 'sort_order' => 11],
        ];

        foreach ($categories as $category) {
            DB::table('feed_categories')->insert([
                'id' => (string) Str::uuid(),
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'color' => $category['color'],
                'status' => 'active',
                'sort_order' => $category['sort_order'],
                'posts_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ ' . count($categories) . ' feed categories seeded successfully.');
    }
}