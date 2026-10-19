<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Community;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin?->id;

        $communities = [
            [
                'name' => 'Farmers Community',
                'category' => 'General Agriculture',
                'description' => 'The general gathering for all Nigerian farmers, producers and agripreneurs.',
            ],
            [
                'name' => 'Fish Farmers',
                'category' => 'Aquaculture',
                'description' => 'Catfish, tilapia breeders, fingerlings and pond management discussion.',
            ],
            [
                'name' => 'Poultry Farmers',
                'category' => 'Livestock',
                'description' => 'Broilers, layers, feed formulation and poultry disease prevention.',
            ],
            [
                'name' => 'Vegetable Farmers',
                'category' => 'Horticulture',
                'description' => 'Tomatoes, peppers, cucumber, lettuce, and leafy vegetable farming in Nigeria.',
            ],
            [
                'name' => 'Livestock Farmers',
                'category' => 'Livestock',
                'description' => 'Cattle, goat, sheep and pig farming, pasture, and veterinary advice.',
            ],
            [
                'name' => 'Rice Farmers',
                'category' => 'Grain & Cereals',
                'description' => 'Paddy cultivation, milling, harvesting and grain pricing updates.',
            ],
            [
                'name' => 'Maize Farmers',
                'category' => 'Grain & Cereals',
                'description' => 'White and yellow maize production, armyworm defense, and bulk grain trade.',
            ],
            [
                'name' => 'Women in Agriculture',
                'category' => 'Community & Empowerment',
                'description' => 'Supporting, financing, and networking female farmers and agro-processors.',
            ],
            [
                'name' => 'Young Agripreneurs',
                'category' => 'Youth & Tech',
                'description' => 'Agritech, innovative greenhouse farming, hydroponics, and agribusiness youth.',
            ],
            [
                'name' => 'Agribusiness & Investment',
                'category' => 'Finance & Investment',
                'description' => 'Farm sponsorship, off-take agreements, export financing, and agricultural returns.',
            ],
            [
                'name' => 'Food Buyers',
                'category' => 'Buyers & Retail',
                'description' => 'Connecting household and commercial bulk buyers directly with farm produce sellers.',
            ],
            [
                'name' => 'Lagos Farmers',
                'category' => 'Regional',
                'description' => 'Urban farmers, peri-urban clusters and fresh supply into Lagos markets.',
            ],
            [
                'name' => 'Ogun Farmers',
                'category' => 'Regional',
                'description' => 'Farmers in Abeokuta, Sagamu, Ijebu, and food corridor producers.',
            ],
            [
                'name' => 'Oyo Farmers',
                'category' => 'Regional',
                'description' => 'Cassava, maize, yam and livestock producers across Ibadan and Oyo State.',
            ],
        ];

        foreach ($communities as $data) {
            $slug = Str::slug($data['name']);

            $community = Community::firstOrCreate([
                'slug' => $slug,
            ], [
                'id' => (string) Str::uuid(),
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'],
                'owner_id' => $adminId,
                'members_count' => 1,
            ]);

            Conversation::firstOrCreate([
                'community_id' => $community->id,
            ], [
                'id' => (string) Str::uuid(),
                'type' => 'community',
                'category' => 'community',
                'name' => $community->name,
                'created_by' => $adminId,
            ]);
        }
    }
}
