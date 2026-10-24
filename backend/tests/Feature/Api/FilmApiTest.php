<?php

namespace Tests\Feature\Api;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FilmApiTest extends TestCase
{
    use RefreshDatabase;

    private function login(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Akira',
            'director' => 'Katsuhiro Otomo',
            'year' => 1988,
            'status' => 'sudah_ditonton',
            'rating' => 5,
            'review' => 'Animasi cyberpunk.',
        ], $override);
    }

    public function test_tanpa_token_semua_endpoint_films_ditolak_401(): void
    {
        $film = Film::factory()->create();

        $this->getJson('/api/films')->assertStatus(401);
        $this->postJson('/api/films', $this->payload())->assertStatus(401);
        $this->getJson("/api/films/{$film->id}")->assertStatus(401);
        $this->putJson("/api/films/{$film->id}", $this->payload())->assertStatus(401);
        $this->deleteJson("/api/films/{$film->id}")->assertStatus(401);
    }

    public function test_tanpa_token_dan_tanpa_header_accept_tetap_401_json(): void
    {
        // Meniru Postman default: tidak mengirim "Accept: application/json".
        $this->get('/api/films')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_index_mengembalikan_daftar_film_json(): void
    {
        $this->login();
        Film::factory()->count(3)->create();

        $this->getJson('/api/films')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonStructure([[
                'id', 'title', 'director', 'year', 'status', 'rating', 'review',
                'created_at', 'updated_at',
            ]]);
    }

    public function test_store_membuat_film_baru_201(): void
    {
        $this->login();

        $this->postJson('/api/films', $this->payload())
            ->assertStatus(201)
            ->assertJsonFragment(['title' => 'Akira', 'director' => 'Katsuhiro Otomo']);

        $this->assertDatabaseHas('films', ['title' => 'Akira', 'year' => 1988]);
    }

    public function test_store_validasi_gagal_422(): void
    {
        $this->login();

        $this->postJson('/api/films', [
            'title' => '',
            'director' => '',
            'year' => 1800,
            'status' => 'status-tidak-dikenal',
            'rating' => 9,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'director', 'year', 'status', 'rating']);

        $this->assertDatabaseCount('films', 0);
    }

    public function test_show_mengembalikan_satu_film(): void
    {
        $this->login();
        $film = Film::factory()->create(['title' => 'Perfect Days']);

        $this->getJson("/api/films/{$film->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $film->id, 'title' => 'Perfect Days']);
    }

    public function test_show_film_tidak_ada_mengembalikan_404_json(): void
    {
        $this->login();

        $this->getJson('/api/films/9999')
            ->assertStatus(404)
            ->assertJsonStructure(['message']);
    }

    public function test_update_mengubah_film(): void
    {
        $this->login();
        $film = Film::factory()->create(['title' => 'Judul Lama']);

        $this->putJson("/api/films/{$film->id}", $this->payload([
            'title' => 'Judul Baru',
            'status' => 'sedang_ditonton',
            'rating' => 4,
        ]))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Judul Baru', 'status' => 'sedang_ditonton']);

        $this->assertDatabaseHas('films', [
            'id' => $film->id,
            'title' => 'Judul Baru',
            'status' => 'sedang_ditonton',
        ]);
    }

    public function test_update_validasi_gagal_422(): void
    {
        $this->login();
        $film = Film::factory()->create();

        $this->putJson("/api/films/{$film->id}", $this->payload(['title' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_destroy_menghapus_film(): void
    {
        $this->login();
        $film = Film::factory()->create();

        $this->deleteJson("/api/films/{$film->id}")
            ->assertOk()
            ->assertJson(['message' => 'Film berhasil dihapus.']);

        $this->assertDatabaseMissing('films', ['id' => $film->id]);
    }
}
