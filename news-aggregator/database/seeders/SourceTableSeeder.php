<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SourceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = Source::updateOrCreate(
            [
                'name' => 'The News',
                'slug' => 'the_news'
            ],[
                'name' => 'The News',
                'slug' => 'the_news',
                'url' => env('THE_NEWS_API_ENDPOINT', 'https://api.thenewsapi.com/v1'),
                'api_key' => env('THE_NEWS_API_KEY', 'B4l72OY8Fsi71l70ntYyivwMG5cs7lFyRs3k86Ll'),
        ]);
    }
}
