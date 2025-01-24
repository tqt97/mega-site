<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'name' => 'TQT Hotel',
            'email' => 'info@tqthotel.com',
            'phone' => '0909090909',
            'address' => 'Go Vap, Ho Chi Minh, Vietnam',
            'facebook_url' => 'https://facebook.com/tqthotel',
            'instagram_url' => 'https://instagram.com/tqthotel',
            'twitter_url' => 'https://twitter.com/luxuryhotel',
        ]);
    }
}
