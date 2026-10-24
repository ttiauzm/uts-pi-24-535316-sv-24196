<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Idempoten: aman dijalankan berkali-kali (mis. tiap container restart).
     * Akun demo -> email: test@example.com | password: password
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => Hash::make('password')]
        );

        $this->call(FilmSeeder::class);
    }
}
