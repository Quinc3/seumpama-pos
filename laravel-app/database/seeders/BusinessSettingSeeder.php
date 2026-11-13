<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['shop_name', 'Seumpama Bunga', 'string', 'Nama coffee shop'],
            ['shop_address', 'Tigaraksa, Kabupaten Tangerang, Banten', 'string', 'Alamat'],
            ['shop_phone', '0812-0000-0000', 'string', 'Nomor telepon'],
            ['receipt_footer', 'Terima kasih atas kunjungan Anda!', 'string', 'Footer struk'],
            ['currency', 'IDR', 'string', 'Mata uang'],
        ];

        foreach ($settings as [$key, $value, $type, $desc]) {
            BusinessSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value, 'value_type' => $type, 'description' => $desc]
            );
        }
    }
}
