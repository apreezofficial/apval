<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketplaceProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendor = User::where('role', 'vendor')->first();

        if (!$vendor) {
            $vendor = User::firstOrCreate(
                ['email' => 'vendor@agreonpay.com.ng'],
                [
                    'first_name' => 'AgroMall',
                    'last_name' => 'Direct',
                    'role' => 'vendor',
                    'phone' => '+2348012345678',
                    'password' => bcrypt('password123'),
                    'status' => 'active',
                ]
            );
        }

        $items = [
            // Vegetables
            [
                'name' => 'Jos Fresh Tomatoes',
                'category' => 'Vegetables',
                'description' => 'Grade A ripe plum tomatoes harvested straight from Jos farms. Ideal for rich stews, sauces, and salads.',
                'price' => 2400,
                'unit' => 'basket',
                'stock_quantity' => 150,
                'min_order_quantity' => 1,
                'pickup_location' => 'Mile 12 International Market, Lagos',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Red Habanero Pepper (Rodo)',
                'category' => 'Vegetables',
                'description' => 'Aromatic, ultra-spicy hot rodo peppers directly sourced from northern growers.',
                'price' => 1600,
                'unit' => 'basket',
                'stock_quantity' => 200,
                'min_order_quantity' => 1,
                'pickup_location' => 'Bodija Market, Ibadan',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1588252303782-cb80119abd6d?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Farm Crisp Carrots',
                'category' => 'Vegetables',
                'description' => 'Fresh, crunchy, and sweet carrots rich in Vitamin A and antioxidants.',
                'price' => 1500,
                'unit' => 'kg',
                'stock_quantity' => 85,
                'min_order_quantity' => 1,
                'pickup_location' => 'Jos Main Market, Plateau',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1598170845058-32b9d6a5c317?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Garden Fresh Cucumber',
                'category' => 'Vegetables',
                'description' => 'Crisp, hydrating dark green organic cucumbers, pesticide-free.',
                'price' => 1200,
                'unit' => 'pack',
                'stock_quantity' => 110,
                'min_order_quantity' => 1,
                'pickup_location' => 'Ketu Fruit Market, Lagos',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1604977042946-1eecc30f269e?w=600&auto=format&fit=crop&q=80',
            ],

            // Tubers
            [
                'name' => 'Fresh Abuja White Yam',
                'category' => 'Tubers',
                'description' => 'Premium large dry white yam tubers. Pounded yam texture guaranteed smooth and stretchy.',
                'price' => 3500,
                'unit' => 'tuber',
                'stock_quantity' => 300,
                'min_order_quantity' => 1,
                'pickup_location' => 'Zuba Yam Market, Abuja',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Sweet Orange Flesh Potatoes',
                'category' => 'Tubers',
                'description' => 'Nutritious bio-fortified sweet potatoes, high fiber and naturally delicious.',
                'price' => 1800,
                'unit' => 'bag',
                'stock_quantity' => 90,
                'min_order_quantity' => 1,
                'pickup_location' => 'Wuse Market, Abuja',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1596097635121-14b63b7a0c19?w=600&auto=format&fit=crop&q=80',
            ],

            // Fruits
            [
                'name' => 'Sweet Crisp Fuji Apples',
                'category' => 'Fruits',
                'description' => 'Imported top-grade sweet red apples, carefully packed and refrigerated.',
                'price' => 2800,
                'unit' => 'pack',
                'stock_quantity' => 120,
                'min_order_quantity' => 1,
                'pickup_location' => 'Alaba Rago, Lagos',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Sweet Juicy Oranges',
                'category' => 'Fruits',
                'description' => 'Fresh sweet citrus oranges picked ripe from Benue orchards.',
                'price' => 1500,
                'unit' => 'bag',
                'stock_quantity' => 140,
                'min_order_quantity' => 1,
                'pickup_location' => 'Gboko Road Market, Benue',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1582979512210-99b6a53386f9?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Organic Sugarloaf Pineapples',
                'category' => 'Fruits',
                'description' => 'Naturally sweet, low-acidity pineapples ripened under tropical sunshine.',
                'price' => 1800,
                'unit' => 'item',
                'stock_quantity' => 75,
                'min_order_quantity' => 1,
                'pickup_location' => 'Epe Farm Center, Lagos',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?w=600&auto=format&fit=crop&q=80',
            ],

            // Meat & Poultry
            [
                'name' => 'Prime Cut Fresh Cow Beef',
                'category' => 'Meat',
                'description' => 'Hygienically slaughtered, inspected and dressed wholesome prime cuts of beef.',
                'price' => 5200,
                'unit' => 'kg',
                'stock_quantity' => 95,
                'min_order_quantity' => 1,
                'pickup_location' => 'Oko Oba Abattoir, Agege, Lagos',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Fresh Goat Meat (Dressed)',
                'category' => 'Meat',
                'description' => 'Lean and tender goat meat, thoroughly washed and partitioned for easy cooking.',
                'price' => 5800,
                'unit' => 'kg',
                'stock_quantity' => 60,
                'min_order_quantity' => 1,
                'pickup_location' => 'Oja Oba Market, Ilorin',
                'is_perishable' => true,
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Smoked Catfish Premium Pack',
                'category' => 'Meat',
                'description' => 'Oven-dried sand-free smoked catfish with rich traditional firewood aroma.',
                'price' => 4500,
                'unit' => 'pack',
                'stock_quantity' => 80,
                'min_order_quantity' => 1,
                'pickup_location' => 'Badagry Fish Market, Lagos',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1534483509719-3feaee7c30da?w=600&auto=format&fit=crop&q=80',
            ],

            // Grains
            [
                'name' => 'Abakaliki Parboiled Rice (50kg)',
                'category' => 'Grains',
                'description' => 'Stone-free, locally milled long grain Nigerian parboiled rice with exceptional taste.',
                'price' => 68000,
                'unit' => 'bag',
                'stock_quantity' => 45,
                'min_order_quantity' => 1,
                'pickup_location' => 'Rice Mill Industry, Abakaliki',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Brown Honey Beans (Oloyin)',
                'category' => 'Grains',
                'description' => 'Sweet Nigerian brown beans, weevil-free and fast-cooking.',
                'price' => 3800,
                'unit' => 'paint',
                'stock_quantity' => 120,
                'min_order_quantity' => 1,
                'pickup_location' => 'Dawanau Grain Market, Kano',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=600&auto=format&fit=crop&q=80',
            ],

            // Natural / Honey & Oils
            [
                'name' => 'Benue Wildflower Pure Honey',
                'category' => 'Natural',
                'description' => '100% unadulterated raw wildflower honey from traditional Benue apiaries.',
                'price' => 4200,
                'unit' => 'bottle',
                'stock_quantity' => 90,
                'min_order_quantity' => 1,
                'pickup_location' => 'Makurdi Modern Market, Benue',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Pure Okija Red Palm Oil (5L)',
                'category' => 'Natural',
                'description' => 'Dense, unbleached, cholesterol-free red palm oil straight from Anambra oil mills.',
                'price' => 7500,
                'unit' => 'keg',
                'stock_quantity' => 110,
                'min_order_quantity' => 1,
                'pickup_location' => 'Onitsha Main Market, Anambra',
                'is_perishable' => false,
                'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($items as $data) {
            $imageUrl = $data['image'];
            unset($data['image']);

            $product = Product::updateOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'name' => $data['name'],
                ],
                array_merge($data, [
                    'status' => 'active',
                ])
            );

            $product->images()->updateOrCreate(
                ['product_id' => $product->id, 'is_primary' => true],
                [
                    'image_url' => $imageUrl,
                    'sort_order' => 0,
                ]
            );
        }
    }
}
