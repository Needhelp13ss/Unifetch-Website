<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

/**
 * Sample campus vendors and products for testing the menu, cart and checkout.
 * Safe to run more than once (it updates instead of duplicating).
 *
 *   php artisan db:seed --class=ShopSeeder
 */
class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'vendor' => ['name' => 'Student Bites', 'tagline' => 'Hot meals made for class breaks', 'location' => 'Canteen, Ground Floor'],
                'bg' => '#FDE7C8',
                'products' => [
                    ['Campus Lunch Combo', 'Rice, fried chicken, veggies and a drink', 'meals', 129, '15-20 min', '🍱'],
                    ['Chicken Adobo Rice Meal', 'Classic adobo with steamed rice', 'meals', 95, '15-20 min', '🍛'],
                    ['Pork Sisig with Rice', 'Sizzling sisig, egg on top', 'meals', 110, '20-25 min', '🥘'],
                    ['Fried Chicken with Rice', 'Crispy chicken, rice and gravy', 'meals', 99, '15-20 min', '🍗'],
                    ['Pancit Canton Bowl', 'Stir-fried noodles with veggies', 'meals', 75, '10-15 min', '🍜'],
                    ['Siomai Rice (4 pcs)', 'Steamed siomai with chili garlic', 'meals', 70, '10-15 min', '🥟'],
                ],
            ],
            [
                'vendor' => ['name' => 'Campus Corner', 'tagline' => 'Cold drinks and quick snacks', 'location' => 'Beside the Library'],
                'bg' => '#D9ECFF',
                'products' => [
                    ['Snacks & Drinks Pack', 'Chips, a sandwich and a cold drink', 'drinks', 169, '10-20 min', '🥤'],
                    ['Iced Coffee', 'Smooth cold brew with milk', 'drinks', 65, '5-10 min', '☕'],
                    ['Wintermelon Milk Tea', 'With pearls, 16 oz', 'drinks', 75, '10-15 min', '🧋'],
                    ['Fresh Buko Juice', 'Sweet coconut water and meat', 'drinks', 45, '5-10 min', '🥥'],
                    ['Bottled Water (500ml)', 'Cold purified drinking water', 'drinks', 20, '5-10 min', '💧'],
                ],
            ],
            [
                'vendor' => ['name' => 'Daily Mart', 'tagline' => 'Fresh groceries on campus', 'location' => 'Main Gate Area'],
                'bg' => '#DDF3E4',
                'products' => [
                    ['Fresh Market Basket', 'Fruits, veggies and bread for the week', 'groceries', 245, '20-30 min', '🧺'],
                    ['Pandesal (10 pcs)', 'Freshly baked, still warm', 'groceries', 40, '10-15 min', '🥖'],
                    ['Bananas (1 hand)', 'Sweet lakatan bananas', 'groceries', 60, '10-20 min', '🍌'],
                    ['Eggs (6 pcs)', 'Medium-sized fresh eggs', 'groceries', 54, '10-20 min', '🥚'],
                    ['Instant Noodles (3 pack)', 'Cup noodles, assorted flavors', 'groceries', 45, '10-15 min', '🍲'],
                ],
            ],
            [
                'vendor' => ['name' => 'QuickStop', 'tagline' => 'School and study supplies', 'location' => 'Annex Building, 1st Floor'],
                'bg' => '#E8E1FF',
                'products' => [
                    ['Study Night Essentials', 'Notebook, pens, highlighter and snacks', 'essentials', 189, '15-25 min', '📚'],
                    ['Notebook (80 leaves)', 'Spiral notebook, college ruled', 'essentials', 35, '10-15 min', '📓'],
                    ['Ballpen Set (5 pcs)', 'Black and blue ink', 'essentials', 30, '10-15 min', '🖊️'],
                    ['Bond Paper (25 sheets)', 'Short, 70 gsm', 'essentials', 25, '10-15 min', '📄'],
                    ['Phone Charging Cable', 'USB-C, 1 meter', 'essentials', 120, '15-20 min', '🔌'],
                ],
            ],
            [
                'vendor' => ['name' => 'Campus Health Corner', 'tagline' => 'Basic health and first aid items', 'location' => 'Clinic Hallway'],
                'bg' => '#FFE0E0',
                'products' => [
                    ['Basic First Aid Kit', 'Bandages, gauze, tape and antiseptic', 'medicine', 150, '15-20 min', '🩹'],
                    ['Alcohol 70% (250ml)', 'Isopropyl alcohol for hands and surfaces', 'medicine', 65, '10-15 min', '🧴'],
                    ['Face Masks (10 pcs)', '3-ply disposable masks', 'medicine', 40, '10-15 min', '😷'],
                    ['Vitamin C (10 tablets)', 'Over-the-counter supplement', 'medicine', 55, '10-15 min', '🍊'],
                    ['Adhesive Bandages (20 pcs)', 'Assorted sizes', 'medicine', 25, '10-15 min', '🩹'],
                ],
            ],
        ];

        foreach ($shops as $shop) {
            $vendor = Vendor::updateOrCreate(
                ['name' => $shop['vendor']['name']],
                $shop['vendor'] + ['is_open' => true]
            );

            foreach ($shop['products'] as [$name, $description, $category, $price, $time, $emoji]) {
                Product::updateOrCreate(
                    ['vendor_id' => $vendor->id, 'name' => $name],
                    [
                        'description' => $description,
                        'category' => $category,
                        'price' => $price,
                        'prep_time' => $time,
                        'emoji' => $emoji,
                        'bg_color' => $shop['bg'],
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
