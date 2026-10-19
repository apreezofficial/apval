<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = User::where('email', 'vendor@agreopay.com')->first();

        if (!$vendor) {
            $vendor = User::create([
                'first_name' => 'Local',
                'last_name' => 'Vendor',
                'email' => 'vendor@agreopay.com',
                'password' => bcrypt('password'),
            ]);
        }

        $products = [
            [
                'name' => 'New Yam',
                'description' => 'Fresh large tubers of yam from Abuja.',
                'category' => 'Tubers',
                'price' => 2500,
                'unit' => 'tuber',
            ],
            [
                'name' => 'Fresh Pepper',
                'description' => 'Spicy red habanero peppers.',
                'category' => 'Vegetables',
                'price' => 500,
                'unit' => 'kg',
            ],
            [
                'name' => 'Organic Honey',
                'description' => 'Pure wildflower honey from Benue.',
                'category' => 'Natural',
                'price' => 3500,
                'unit' => 'bottle',
            ],
        ];

        // Ensure storage directory exists
        Storage::disk('public')->makeDirectory('uploads/images');

        foreach ($products as $productData) {
            $product = Product::create(array_merge($productData, [
                'vendor_id' => $vendor->id,
                'stock_quantity' => 100,
                'min_order_quantity' => 1,
                'status' => 'active',
            ]));

            // Create a dummy image file
            $filename = Str::uuid() . '.jpg';
            $path = 'uploads/images/' . $filename;
            
            // Create a simple colored square placeholder (JPEG signature + some data)
            // Just for testing, a valid 1x1 pixel JPEG is about 300 bytes.
            // But we can just put some placeholder text or use a remote URL if we want.
            // Since we want LOCAL files, let's just touch a file.
            Storage::disk('public')->put($path, 'DUMMY_IMAGE_DATA_FOR_' . $productData['name']);

            $product->images()->create([
                'image_url' => Storage::disk('public')->url($path),
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }
    }
}
