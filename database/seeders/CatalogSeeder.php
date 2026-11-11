<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['after-hours', 'After Hours Tee', 'oversized', 649, 120, 'DROP 01', 'black', 'قصة واسعة، قطن تقيل ورسمة فراشة أحمر على أسود مغسول. للناس اللي ليلها بيبدأ متأخر.'],
            ['apex', 'Apex Racing Tee', 'oversized', 629, 120, null, 'bone', 'تيشيرت أوفرسايز بلون أوف وايت ورسمة سباق بدرجات أسود. قصة واسعة ومريحة.'],
            ['orbit', 'Orbit Hoodie', 'hoodies', 999, 150, 'DROP 01', 'black', 'هودي أسود بقصة واسعة ورسمة أحمر مستوحاة من المدارات، مع جيب أمامي.'],
            ['crimson', 'Crimson Frequency Hoodie', 'hoodies', 949, 150, null, 'burgundy', 'هودي نبيتي بتفاصيل بسيطة ورسمة صغيرة على الصدر.'],
            ['midnight', 'Midnight Classic Tee', 'regular', 489, 110, null, 'charcoal', 'تيشيرت بقصة كلاسيك ورسمة قمر فاتحة على قطن رمادي غامق.'],
            ['signal', 'Signal Classic Tee', 'regular', 459, 110, null, 'bone', 'تيشيرت أوف وايت بقصة كلاسيك وعلامة أحمر صغيرة على الصدر.'],
        ];
        foreach ($catalog as $index => [$slug, $name, $category, $price, $customFee, $badge, $color, $description]) {
            $product = Product::firstOrCreate(['slug' => $slug], [
                'name' => $name, 'category' => $category, 'price' => $price, 'custom_fee' => $customFee,
                'badge' => $badge, 'description' => $description,
                'image_path' => 'assets/img/'.$slug.'.jpg', 'active' => true, 'featured' => $index < 3,
            ]);
            foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size) {
                $product->variants()->firstOrCreate(['color' => $color, 'size' => $size], ['stock' => $size === 'XXL' ? 3 : 12]);
            }
        }
    }
}
