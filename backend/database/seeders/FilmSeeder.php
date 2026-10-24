<?php

namespace Database\Seeders;

use App\Models\Film;
use Illuminate\Database\Seeder;

class FilmSeeder extends Seeder
{
    public function run(): void
    {
        $films = [
            [
                'title' => 'Akira',
                'director' => 'Katsuhiro Otomo',
                'year' => 1988,
                'status' => 'sudah_ditonton',
                'rating' => 5,
                'review' => 'Animasi cyberpunk yang masih terasa segar sampai sekarang.',
            ],
            [
                'title' => 'Perfect Days',
                'director' => 'Wim Wenders',
                'year' => 2023,
                'status' => 'sedang_ditonton',
                'rating' => null,
                'review' => 'Pelan, tenang, dan penuh perhatian pada hal-hal kecil.',
            ],
            [
                'title' => 'The Grand Budapest Hotel',
                'director' => 'Wes Anderson',
                'year' => 2014,
                'status' => 'ingin_ditonton',
                'rating' => null,
                'review' => null,
            ],
        ];

        foreach ($films as $film) {
            Film::firstOrCreate(['title' => $film['title'], 'year' => $film['year']], $film);
        }
    }
}
