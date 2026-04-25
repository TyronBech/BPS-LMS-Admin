<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'isFeatured' => true,
                'title' => 'CONGRATULATIONS, MIRACLARA!',
                'subtitle' => 'CHAMPION GOLD AWARDEE',
                'image' => '/images/libpic4.jpg',
                'date' => 'March 4, 2023',
                'category' => 'ACHIEVEMENT',
                'quote' => '"A champion storyteller doesn\'t just tell a tale—they breathe life into words, making the listener see, feel, and believe." - Unknown',
                'content' => "Congratulations to Franevecelart Miraclara A. Plotea, a Grade 3 student from St. Lorenzo Ruiz, for emerging as the Champion out of 22 storytellers nationwide in the Highlights Library Storytelling Competition!\n\nHer hard work, determination, and perseverance played a crucial role in achieving this outstanding accomplishment. This success was possible through the unwavering support of her family and the SNCS administration.",
                'priority' => 'high'
            ],
            [
                'title' => 'National Book Week Celebration 2026',
                'date' => 'November 24, 2026',
                'content' => 'Join us for a week-long celebration filled with book fairs, storytelling competitions, and literacy workshops. Stay tuned for the full schedule of activities!',
                'priority' => 'high',
                'category' => 'Event'
            ],
            [
                'title' => 'Extended Research Hours',
                'date' => 'October 30, 2026',
                'content' => 'To support students with their upcoming research projects, the library will be extending its operating hours until 6:30 PM on weekdays starting next month.',
                'priority' => 'normal',
                'category' => 'Notice'
            ],
            [
                'title' => 'New Digital Collection Available',
                'date' => 'October 25, 2026',
                'content' => 'Check out our latest addition to the GEAP (General Education Advancement Program) series, now accessible through the Digital Storytelling portal.',
                'priority' => 'normal',
                'category' => 'New Arrival'
            ]
        ];

        foreach ($items as $item) {
            Announcement::updateOrCreate(
                ['title' => $item['title']],
                [
                    'content'     => $item['content'],
                    'category'    => $item['category'] ?? 'Notice',
                    'priority'    => $item['priority'] ?? 'normal',
                    'date'        => $item['date'] ?? null,
                    'is_featured' => $item['isFeatured'] ?? false,
                    'is_published' => true,
                    'quote'       => $item['quote'] ?? null,
                    'image'       => !empty($item['image']) && file_exists(public_path($item['image']))
                        ? file_get_contents(public_path($item['image']))
                        : null,
                ]
            );
        }
    }
}
