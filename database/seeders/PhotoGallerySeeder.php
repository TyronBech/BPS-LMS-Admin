<?php

namespace Database\Seeders;

use App\Models\GalleryFolder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhotoGallerySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $albums = [
            [
                'name'          => 'Feeling Groovy Book Fair',
                'title'         => 'Feeling Groovy Book Fair - Hippie Dress Up Contests',
                'slug'          => 'feeling-groovy-book-fair',
                'description'   => 'A colorful and nostalgic celebration of books and culture, featuring our creative hippie-themed dress up contests.',
                'album_date'    => 'July 22, 2016',
                'cover'         => file_exists(public_path('images/libpic1.jpg')) ? file_get_contents(public_path('images/libpic1.jpg')) : null,
                'fb_url'        => 'https://www.facebook.com/share/18XkSwkHep/',
                'type'          => 'album',
                'category'      => 'photo',
                'sort_order'    => 1,
            ],
            [
                'name'          => '2nd Book Fair Opening',
                'title'         => '2nd Book Fair Opening Ceremony, AEP Star Readers & Reading Logs',
                'slug'          => '2nd-book-fair-opening',
                'description'   => 'Highlighting our academic excellence and the start of our second annual book fair with our star readers.',
                'album_date'    => 'November 10, 2014',
                'cover'         => file_exists(public_path('images/libpic2.jpg')) ? file_get_contents(public_path('images/libpic2.jpg')) : null,
                'fb_url'        => 'https://www.facebook.com/share/14Zr7E9MCEP/',
                'type'          => 'album',
                'category'      => 'photo',
                'sort_order'    => 2,
            ],
            [
                'name'          => 'SNCS Activities Awarding',
                'title'         => 'SNCS Activities - AY2014-2015: Awarding',
                'slug'          => 'sncs-activities-awarding',
                'description'   => 'Recognizing the achievements and participation of our community in the SNCS activities for the academic year 2014-2015.',
                'album_date'    => 'October 2014',
                'cover'         => file_exists(public_path('images/libpic3.jpg')) ? file_get_contents(public_path('images/libpic3.jpg')) : null,
                'fb_url'        => 'https://www.facebook.com/share/1B19sugFVF/',
                'type'          => 'album',
                'category'      => 'photo',
                'sort_order'    => 3,
            ],
            [
                'name'          => 'Fiesta Fair Arch Making',
                'title' => 'Fiesta Fair: Arch Making Contest',
                'slug' => 'fiesta-fair-arch-making',
                'description' => 'Creativity and teamwork on display during our festive arch-making competition.',
                'album_date' => 'November 13, 2014',
                'cover' => file_exists(public_path('images/libpic1.jpg')) ? file_get_contents(public_path('images/libpic1.jpg')) : null,
                'fb_url' => 'https://www.facebook.com/share/17W4A4fskB/',
                'type' => 'album',
                'category' => 'photo',
                'sort_order' => 4,
            ]
        ];

        foreach ($albums as $album) {
            GalleryFolder::updateOrCreate(
                ['slug' => $album['slug']],
                $album
            );
        }
    }
}
