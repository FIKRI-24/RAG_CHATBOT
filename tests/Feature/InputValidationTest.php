<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    public static function publicEmailEndpoints(): array
    {
        return [['/login'], ['/register'], ['/forgot-password'], ['/reset-password']];
    }

    #[DataProvider('publicEmailEndpoints')]
    public function test_public_email_endpoints_reject_array_input(string $url): void
    {
        config(['security.registration_enabled' => true]);
        Notification::fake();

        $this->postJson($url, [
            'name' => 'Invalid input', 'email' => ['address' => 'student@example.test'],
            'password' => 'password', 'password_confirmation' => 'password', 'token' => 'invalid-token',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('password_reset_tokens', 0);
        Notification::assertNothingSent();
    }

    public function test_profile_rejects_non_string_email_without_updating_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach ([['invalid'], true, 123] as $email) {
            $this->patchJson('/profile', ['name' => 'Changed', 'email' => $email])
                ->assertUnprocessable()->assertJsonValidationErrors('email');
        }

        $this->assertSame($user->name, $user->fresh()->name);
        $this->assertSame($user->email, $user->fresh()->email);

        $this->from('/profile')->patch('/profile', ['name' => $user->name, 'email' => ['invalid']])
            ->assertRedirect('/profile')->assertSessionHasErrors('email');
        $this->get('/profile')->assertOk();
    }

    public function test_teacher_cannot_create_or_update_student_with_array_email(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa']);
        $payload = ['name' => 'Changed', 'email' => ['invalid'], 'password' => 'password'];

        $this->actingAs($teacher)->postJson('/guru/siswa', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->putJson(route('guru.siswa.update', $student), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('users', 2);
        $this->assertSame($student->email, $student->fresh()->email);
        $this->assertSame($student->name, $student->fresh()->name);
    }

    public static function listingFilters(): array
    {
        return [
            ['siswa', '/siswa/modules', 'search'],
            ['siswa', '/siswa/modules', 'mapel'],
            ['siswa', '/siswa/modules', 'kb_nomor'],
            ['guru', '/guru/modules', 'mapel'],
            ['guru', '/guru/modules', 'kb_nomor'],
            ['guru', '/guru/siswa', 'search'],
        ];
    }

    #[DataProvider('listingFilters')]
    public function test_listing_filters_reject_arrays_and_oversized_text(string $role, string $url, string $field): void
    {
        $this->actingAs(User::factory()->create(['role' => $role]));

        foreach ([['invalid'], str_repeat('x', 256)] as $value) {
            $this->getJson($url.'?'.http_build_query([$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
    }

    #[DataProvider('publicEmailEndpoints')]
    public function test_html_email_forms_render_after_validation_failure(string $url): void
    {
        config(['security.registration_enabled' => true]);
        $form = $url === '/reset-password' ? '/reset-password/invalid-token' : $url;
        $this->from($form)->post($url, ['name' => 'Student', 'email' => ['invalid']])
            ->assertRedirect($form)->assertSessionHasErrors('email');
        $this->get($form)->assertOk();
    }

    public function test_html_search_requests_receive_validation_errors_instead_of_server_errors(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->from('/siswa/modules')->get('/siswa/modules?search%5B%5D=invalid')
            ->assertRedirect('/siswa/modules')->assertSessionHasErrors('search');
        $this->get('/siswa/modules')->assertOk();
    }

    public function test_reset_form_ignores_non_string_email_query(): void
    {
        $this->get('/reset-password/invalid-token?email%5B%5D=invalid')->assertOk();
    }

    public function test_valid_email_is_still_trimmed_and_normalized_for_login(): void
    {
        $user = User::factory()->create(['email' => 'student@example.test']);

        $this->post('/login', ['email' => '  STUDENT@EXAMPLE.TEST  ', 'password' => 'password'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_valid_email_is_still_normalized_for_password_reset(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'student@example.test']);

        $this->post('/forgot-password', ['email' => '  STUDENT@EXAMPLE.TEST  '])
            ->assertSessionHasNoErrors()->assertRedirect();
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_normal_and_empty_listing_filters_still_work(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa', 'name' => 'Target student']);
        $module = Module::create(['guru_id' => $teacher->id, 'judul' => 'Target module', 'mapel' => 'TKJ',
            'kb_nomor' => 'KB 1', 'file_path' => 'modules/test.pdf', 'status_indexing' => 'completed']);

        $this->actingAs($student)->get('/siswa/modules?search=Target&mapel=TKJ&kb_nomor=KB+1')
            ->assertOk()->assertViewHas('modules', fn ($modules) => $modules->modelKeys() === [$module->id]);
        $this->get('/siswa/modules?search=&mapel=Semua&kb_nomor=Semua')->assertOk();
        $this->actingAs($teacher)->get('/guru/modules?mapel=TKJ&kb_nomor=KB+1')
            ->assertOk()->assertViewHas('modules', fn ($modules) => $modules->modelKeys() === [$module->id]);
        $this->get('/guru/siswa?search=Target')
            ->assertOk()->assertViewHas('siswas', fn ($students) => $students->modelKeys() === [$student->id]);
    }
}
