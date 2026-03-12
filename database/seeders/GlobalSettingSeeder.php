<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use App\Models\GlobalCurrency;
use Illuminate\Database\Seeder;
use App\Models\StorageSetting;

class GlobalSettingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        GlobalSetting::firstOrCreate(
            ['hash' => md5('global_setting')], // Use a stable identifier or check if any exists
            [
                'name' => 'RestPOS',
                'theme_hex' => '#f5be22',
                'theme_rgb' => '245, 190, 34',
                'installed_url' => config('app.url'),
                'facebook_link' => 'https://www.facebook.com/',
                'instagram_link' => 'https://www.instagram.com/',
                'twitter_link' => 'https://www.twitter.com/',
                'default_currency_id' => GlobalCurrency::first()->id ?? 1,
            ]
        );

        StorageSetting::firstOrCreate([
            'filesystem' => 'local',
            'status' => 'enabled',
        ]);
    }
}
