<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaunchCatalogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (config('launch_catalog') as $slug => $item) {
                $product = Product::firstOrCreate(['slug' => $slug], [
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'description' => $item['description'],
                    'featured' => $item['featured'],
                    'active' => true,
                    'ready_enabled' => true,
                    'custom_enabled' => true,
                    'image_path' => 'assets/img/catalog/'.$item['photos'][0][0].'.jpg',
                    'price' => 899,
                    'custom_fee' => 0,
                ]);

                foreach (array_unique(array_column($item['photos'], 1)) as $color) {
                    foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size) {
                        $product->variants()->firstOrCreate(
                            ['color' => $color, 'size' => $size],
                            ['active' => true, 'stock' => 0],
                        );
                    }
                }
            }
        }, 3);
    }
}
