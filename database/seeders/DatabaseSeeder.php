<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@haqbahoo.test'],
            [
                'name' => 'Shop Owner',
                'password' => 'password',
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        // One account per role so permissions can be exercised straight away.
        User::firstOrCreate(
            ['email' => 'manager@haqbahoo.test'],
            [
                'name' => 'Shop Manager',
                'password' => 'password',
                'role' => UserRole::Manager,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@haqbahoo.test'],
            [
                'name' => 'Counter Cashier',
                'password' => 'password',
                'role' => UserRole::Cashier,
                'is_active' => true,
            ]
        );

        // Realistic dairy catalogue rather than faker noise, so the dashboard
        // reads sensibly straight after a fresh install.
        $products = collect([
            ['name' => 'Fresh Cow Milk', 'unit' => 'litre', 'default_rate' => 220],
            ['name' => 'Buffalo Milk', 'unit' => 'litre', 'default_rate' => 260],
            ['name' => 'Skimmed Milk', 'unit' => 'litre', 'default_rate' => 190],
            ['name' => 'Yogurt (Dahi)', 'unit' => 'kg', 'default_rate' => 240],
            ['name' => 'Butter (Makhan)', 'unit' => 'kg', 'default_rate' => 1600],
            ['name' => 'Desi Ghee', 'unit' => 'kg', 'default_rate' => 3200],
            ['name' => 'Cream (Balai)', 'unit' => 'kg', 'default_rate' => 900],
            ['name' => 'Cheese Block', 'unit' => 'packet', 'default_rate' => 750],
        ])->map(fn (array $attributes) => Product::firstOrCreate(
            ['name' => $attributes['name']],
            $attributes
        ));

        $dealers = collect([
            ['name' => 'Bilal Dairy Farm', 'phone' => '0300 1234567', 'address' => 'Sahiwal Road'],
            ['name' => 'Rehan Milk Supply', 'phone' => '0321 7654321', 'address' => 'Chak No. 42'],
            ['name' => 'Faizan Traders', 'phone' => '0333 9988776', 'address' => 'Main Bazaar'],
            ['name' => 'Subhan Dairy', 'phone' => '0345 5566778', 'address' => 'Model Town'],
        ])->map(fn (array $attributes) => Dealer::firstOrCreate(
            ['name' => $attributes['name']],
            $attributes
        ));

        if (Sale::exists()) {
            return;
        }

        // ~3 months of history so the dashboard and date filters have
        // something meaningful to show.
        collect(range(0, 89))->each(function (int $daysAgo) use ($products, $dealers, $owner): void {
            $date = now()->subDays($daysAgo)->toDateString();

            Sale::factory()
                ->count(random_int(2, 6))
                ->on($date)
                ->sequence(fn ($sequence) => [
                    'product_id' => $products->random()->id,
                    'dealer_id' => $dealers->random()->id,
                    'user_id' => $owner->id,
                ])
                ->create();
        });

        // A handful of unpaid and partially paid entries so the outstanding
        // figure on the dashboard is non-zero.
        Sale::factory()->count(6)->unpaid()->sequence(fn () => [
            'product_id' => $products->random()->id,
            'dealer_id' => $dealers->random()->id,
            'user_id' => $owner->id,
        ])->create();

        Sale::factory()->count(4)->partiallyPaid()->sequence(fn () => [
            'product_id' => $products->random()->id,
            'dealer_id' => $dealers->random()->id,
            'user_id' => $owner->id,
        ])->create();
    }
}
