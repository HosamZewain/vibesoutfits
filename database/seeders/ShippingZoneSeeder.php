<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            65 => ['القاهرة', 'الجيزة', 'القليوبية'],
            80 => ['الإسكندرية', 'الدقهلية', 'البحيرة', 'الغربية', 'المنوفية', 'كفر الشيخ', 'الشرقية', 'دمياط', 'بورسعيد', 'الإسماعيلية', 'السويس'],
            95 => ['الفيوم', 'بني سويف', 'المنيا', 'أسيوط', 'سوهاج', 'قنا', 'الأقصر', 'أسوان'],
            115 => ['البحر الأحمر', 'مطروح', 'الوادي الجديد', 'شمال سيناء', 'جنوب سيناء'],
        ];
        foreach ($zones as $fee => $names) {
            foreach ($names as $name) {
                ShippingZone::firstOrCreate(['name' => $name], ['fee' => $fee, 'active' => app()->environment(['local', 'testing'])]);
            }
        }
    }
}
