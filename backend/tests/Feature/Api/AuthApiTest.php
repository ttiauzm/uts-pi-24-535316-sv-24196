<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_dengan_kredensial_benar_mengembalikan_token(): void
    {
        User::factory()->create([
            'email' => 'tia@example.com',
            'password' => 'rahasia123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'tia@example.com',
            'password' => 'rahasia123',
        ])
            ->assertOk()
            ->assertJsonStructure(['message', 'token', 'token_type', 'user'])
            ->assertJson(['token_type' => 'Bearer']);
    }

    public function test_login_dengan_password_salah_ditolak_401(): void
    {
        User::factory()->create(['email' => 'tia@example.com', 'password' => 'rahasia123']);

        $this->postJson('/api/login', [
            'email' => 'tia@example.com',
            'password' => 'salah-total',
        ])->assertStatus(401)->assertJson(['message' => 'Email atau password salah.']);
    }

    public function test_login_validasi_gagal_422(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_me_tanpa_token_401_dan_dengan_token_200(): void
    {
        $this->getJson('/api/me')->assertStatus(401);

        $token = User::factory()->create()->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/me')->assertOk();
    }

    public function test_logout_menghapus_token_yang_dipakai(): void
    {
        $token = User::factory()->create()->createToken('test')->plainTextToken;
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_register_bukan_endpoint_publik(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@example.com',
            'password' => 'rahasia123',
        ])->assertStatus(401);

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.com']);
    }

    public function test_register_oleh_pengguna_terautentikasi_membuat_user_baru_201(): void
    {
        $token = User::factory()->create()->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/register', [
            'name' => 'Anggota Baru',
            'email' => 'anggota@example.com',
            'password' => 'rahasia123',
        ])
            ->assertStatus(201)
            ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'anggota@example.com']);
    }
}
