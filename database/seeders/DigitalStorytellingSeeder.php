<?php

namespace Database\Seeders;

use App\Models\GalleryFolder;
use App\Models\GalleryVideo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DigitalStorytellingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tree = [
            'name' => 'Digital Storytelling',
            'slug' => 'digital-storytelling',
            'type' => 'folder',
            'children' => [
                [
                    'name' => 'S.Y. 2021-2023',
                    'slug' => 's-y-2021-2023',
                    'type' => 'folder',
                    'description' => 'A curated collection of digital storytelling masterpieces from the Highlights Library Storytelling Competition during SY 2021-2022.',
                    'videos' => [
                        [
                            'title' => 'HLL Storytelling - Boysillo',
                            'url' => 'https://drive.google.com/file/d/1Zhfio15t5B-ZVjS2LW32kTPyJz1AecJ4/preview'
                        ]
                    ],
                    'children' => [
                        [
                            'name' => 'November 2021',
                            'slug' => 'november-2021',
                            'type' => 'album',
                            'cover' => '/images/libpic1.jpg',
                            'description' => 'Student digital storytelling entries submitted during November 2021.',
                            'videos' => [
                                ['title' => 'Storytelling - Quimpo', 'url' => 'https://drive.google.com/file/d/1Nh2Ls4sK4-r2uHCot24zQyk_GYoCkO0R/preview'],
                                ['title' => 'Storytelling - Tambis', 'url' => 'https://drive.google.com/file/d/1rVh0iVfDYGxO7J9_Rj5s3KT2CVix-Nfr/preview'],
                                ['title' => 'Storytelling - Comullo', 'url' => 'https://drive.google.com/file/d/1OHKJLbxQOnuC5suqkMx6q4p20f8hMO7S/preview'],
                                ['title' => 'Storytelling - Quitiquit', 'url' => 'https://drive.google.com/file/d/1YsE8NmdgLTPG7DjXC9WfRcxH0g4oLsxy/preview'],
                                ['title' => 'Storytelling - Alcera', 'url' => 'https://drive.google.com/file/d/1ZIQZ83FPx1VE4ub7m5WJBgXFazBbh7lw/preview'],
                                ['title' => 'Storytelling - Boysillo', 'url' => 'https://drive.google.com/file/d/1oy7tjSD7kBUd9mh8_1SNvGh3QJJC4zTc/preview']
                            ]
                        ],
                        [
                            'name' => 'December 2021',
                            'slug' => 'december-2021',
                            'type' => 'album',
                            'cover' => '/images/libpic2.jpg',
                            'description' => 'Student digital storytelling entries submitted during December 2021.',
                            'videos' => [
                                ['title' => 'December Storyteller - Anasarias', 'url' => 'https://drive.google.com/file/d/1mQcgAtF81TkqwCMRD_SWrHbNjqCIxTPq/preview'],
                                ['title' => 'December Storyteller - Delos Reyes', 'url' => 'https://drive.google.com/file/d/1I5B1WOnNAfnN7nn5R_QWUN-jgFxdQt7y/preview']
                            ]
                        ],
                        [
                            'name' => 'January 2022',
                            'slug' => 'january-2022',
                            'type' => 'album',
                            'cover' => '/images/libpic3.jpg',
                            'description' => 'Student digital storytelling entries submitted during January 2022.',
                            'videos' => [
                                ['title' => 'Storytelling - Aguila', 'url' => 'https://drive.google.com/file/d/1GgYyTQXVys7iJGbsLGs3BbHG-jLHtums/preview'],
                                ['title' => 'Storytelling - Ibarreta', 'url' => 'https://drive.google.com/file/d/1d8WCqnHNMYINETl3XRDSQ0eQcaHt2uoP/preview'],
                                ['title' => 'Storytelling - Dumancas', 'url' => 'https://drive.google.com/file/d/1HDvFwmVe2iVRhOa-RdWLYnQVNXiRflMs/preview'],
                                ['title' => 'Storytelling - Nobleza', 'url' => 'https://drive.google.com/file/d/1WSL0T7khpwEL8Vf7Evcb3mv-Rypi7PfP/preview'],
                                ['title' => 'Storytelling - Lanuza', 'url' => 'https://drive.google.com/file/d/1cR_P6aIdxL46hJrglMhiQnpGGSYLomeI/preview']
                            ]
                        ],
                        [
                            'name' => 'February 2022',
                            'slug' => 'february-2022',
                            'type' => 'album',
                            'cover' => '/images/libpic4.jpg',
                            'description' => 'Student digital storytelling entries submitted during February 2022.',
                            'videos' => [
                                ['title' => 'Storytelling - Dimasangal', 'url' => 'https://drive.google.com/file/d/1zYg_QQ2xF6YYTT-PlgfTZhSXfvFoXlyT/preview'],
                                ['title' => 'Storytelling - Llanes', 'url' => 'https://drive.google.com/file/d/1P9S4iETDvqO4102mcYfBrPacWCLfz_s8/preview']
                            ]
                        ],
                        [
                            'name' => 'March 2022',
                            'slug' => 'march-2022',
                            'type' => 'album',
                            'cover' => '/images/libpic1.jpg',
                            'description' => 'Student digital storytelling entries submitted during March 2022.',
                            'videos' => [
                                ['title' => 'Storytelling - Parco', 'url' => 'https://drive.google.com/file/d/1j5o-jOkWk64vEZmi3BjsWl3sbSYp8CO7/preview'],
                                ['title' => 'Storytelling - Torres', 'url' => 'https://drive.google.com/file/d/1ru4cEpOff0dY0e3i2E9RLXBY7Q5qHf6e/preview']
                            ]
                        ],
                        [
                            'name' => 'April 2022',
                            'slug' => 'april-2022',
                            'type' => 'album',
                            'cover' => '/images/libpic2.jpg',
                            'description' => 'Student digital storytelling entries submitted during April 2022.',
                            'videos' => [
                                ['title' => 'Storytelling - Tugna', 'url' => 'https://drive.google.com/file/d/1j3VmQer3RFGvAVkFbiPxgJQCtBkGHME1/preview'],
                                ['title' => 'Storytelling - Esguerra', 'url' => 'https://drive.google.com/file/d/1L3RqrsuuLtzDw69U_T-k7xZES3rXXofT/preview']
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'S.Y. 2022-2023',
                    'slug' => 's-y-2022-2023',
                    'type' => 'album',
                    'cover' => '/images/libpic3.jpg',
                    'description' => 'A showcase of digital storytelling masterpieces created by SNCS students during SY 2022-2023, featuring entries from the Highlights Library Storytelling Contest.',
                    'videos' => [
                        ['title' => 'Belez - Bronze Awardee', 'url' => 'https://drive.google.com/file/d/1ieu6cEAzgAaJWguyNPhpXZ5azyig2bq0/preview'],
                        ['title' => 'Alexandria Brianna Belez (Grade 1 - St. Peter, 1st Place)', 'url' => 'https://drive.google.com/file/d/1RlD-YA3taFPtN1Hl8KC7f6KJc5TM392C/preview'],
                        ['title' => 'Bella Grace Anasarias (Grade 3 - St. Lorenzo Ruiz, 3rd Place)', 'url' => 'https://drive.google.com/file/d/1X7r-FIH3NJtihgDlg_TcjXr5Up-lr8SN/preview'],
                        ['title' => 'Mia Eissen L. Pamonag (Grade 3 - St. Pedro Calungsod)', 'url' => 'https://drive.google.com/file/d/1RfndT3piILqa6bq-iPhPwb1qaXcm9SSA/preview'],
                        ['title' => 'Josemarie Louis A. Quimpo (Grade 3 - St. Lorenzo Ruiz)', 'url' => 'https://drive.google.com/file/d/1LEkG2w1GSlPsgP559uSUbqHjaxNVD8cT/preview'],
                        ['title' => 'Brayden B. Pulido (Grade 1 - St. Peter)', 'url' => 'https://drive.google.com/file/d/1YjqMzdtRX3F4J8Q0UzVO1rqIBsZFvC2H/preview'],
                        ['title' => 'Franevecelart Miraclara Plotea (Grade 1 - St. Peter, 2nd Place)', 'url' => 'https://drive.google.com/file/d/14JPtJLiCqc3J_4nnu-bXN5hkbQsyvepi/preview'],
                        ['title' => 'Evan John C. Dimasupil (Grade 2 - St. Luke)', 'url' => 'https://drive.google.com/file/d/1ts2xcYwkStHeR1vHBP5aO0rqvPrWllFK/preview'],
                        ['title' => 'Serlanz M. Lanuza (Grade 3 - St. Lorenzo Ruiz)', 'url' => 'https://drive.google.com/file/d/1OpqgLD9uSrH2Z9DOstGbL25KZTLAMdZp/preview'],
                        ['title' => 'Ethan Kiel Ramos (Grade 1 - St. Peter)', 'url' => 'https://drive.google.com/file/d/1GIVeiurf9HFvxbDb1RMAu4SlvPdCp76F/preview']
                    ]
                ],
                [
                    'name' => 'S.Y. 2024-2025',
                    'slug' => 's-y-2024-2025',
                    'type' => 'album',
                    'cover' => '/images/libpic4.jpg',
                    'description' => 'Digital storytelling entries for the current school year. New content coming soon.',
                    'videos' => []
                ]
            ]
        ];

        // Clear existing data to avoid duplicates
        GalleryVideo::query()->delete();
        GalleryFolder::query()->delete();

        $this->seedChildren($tree['children'] ?? [], null, 0);
    }

    /**
 * Recursively seed folder children and their videos.
 */
private function seedChildren(array $children, ?int $parentId, int $order): void
{
    foreach ($children as $index => $node) {
        $folder = \App\Models\GalleryFolder::create([
            'parent_id'   => $parentId,
            'name'        => $node['name'],
            'slug'        => $node['slug'],
            'type'        => $node['type'],
            'category'    => 'video',
            'description' => $node['description'] ?? null,
            'sort_order'  => $order + $index,
            'cover'       => !empty($node['cover']) && file_exists(public_path($node['cover']))
                             ? file_get_contents(public_path($node['cover']))
                             : null,
        ]);

        if (!empty($node['videos'])) {
            foreach ($node['videos'] as $vIndex => $video) {
                \App\Models\GalleryVideo::create([
                    'folder_id'  => $folder->id,
                    'title'      => $video['title'],
                    'url'        => $video['url'],
                    'sort_order' => $vIndex,
                ]);
            }
        }

        if (!empty($node['children'])) {
            $this->seedChildren($node['children'], $folder->id, 0);
        }
    }
}
}
