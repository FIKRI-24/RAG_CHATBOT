<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('Informasi Profil Akun');
        $response->assertSee('Perbarui Kata Sandi');
    }

    public function test_guru_can_view_profile_page_with_premium_layout(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this
            ->actingAs($guru)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('Panel Kendali Guru');
        $response->assertSee('Pengajar TKJ');
        $response->assertSee('SMK Negeri 1 Kinali');
    }

    public function test_siswa_can_view_profile_page_with_siswa_layout(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this
            ->actingAs($siswa)
            ->get('/profile');

        $response->assertOk();
        $response->assertSee('Panel Belajar Siswa');
        $response->assertSee('Siswa TKJ');
        $response->assertSee('SMK Negeri 1 Kinali');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_siswa_cannot_see_or_delete_their_own_account(): void
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $this->actingAs($siswa)
            ->get('/profile')
            ->assertOk()
            ->assertDontSee('Status & Keamanan')
            ->assertDontSee('Enkripsi Bcrypt')
            ->assertDontSee('Zona Bahaya: Hapus Akun')
            ->assertDontSee('Hapus Akun Saya');

        $response = $this
            ->actingAs($siswa)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response->assertForbidden();
        $this->assertAuthenticatedAs($siswa);
        $this->assertNotNull($siswa->fresh());
    }

    public function test_guru_can_delete_their_account_when_another_guru_exists(): void
    {
        User::factory()->create(['role' => 'guru']);
        $guru = User::factory()->create(['role' => 'guru']);

        $this->actingAs($guru)
            ->get('/profile')
            ->assertOk()
            ->assertSee('Zona Bahaya: Hapus Akun');

        $response = $this
            ->actingAs($guru)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNull($guru->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);

        $response = $this
            ->actingAs($guru)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($guru->fresh());
    }
}
