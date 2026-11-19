<?php

namespace Tests\Feature;

use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatActivityWeeklyTest extends TestCase
{
    use RefreshDatabase;

    protected User $guru;
    protected User $siswa;
    protected Module $module;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);

        $this->siswa = User::factory()->create([
            'role' => 'siswa',
            'is_active' => true,
            'name' => 'Ahmad Siswa',
            'class_name' => 'XII TKJ 1',
            'student_number' => '10293847',
        ]);

        $this->module = Module::create([
            'guru_id' => $this->guru->id,
            'judul' => 'Perutean Jaringan Nirkabel',
            'kb_nomor' => 'KB 1',
            'mapel' => 'Teknik Komputer dan Jaringan',
            'tp' => 'Tujuan Pembelajaran KB 1',
            'file_path' => 'modules/test.pdf',
            'status_indexing' => 'completed',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_chat_activities(): void
    {
        $response = $this->get(route('guru.chat-activities.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_siswa_cannot_access_chat_activities_page(): void
    {
        $response = $this->actingAs($this->siswa)->get(route('guru.chat-activities.index'));
        $response->assertForbidden();
    }

    public function test_guru_can_access_chat_activities_page_with_metrics_and_list(): void
    {
        // Chat dalam 7 hari terakhir
        ChatHistory::create([
            'siswa_id' => $this->siswa->id,
            'pertanyaan' => 'Bagaimana cara konfigurasi MikroTik gateway?',
            'jawaban' => 'Langkah pertama adalah mengatur IP address pada interface...',
            'mapel' => 'Teknik Komputer dan Jaringan',
            'kind' => 'answer',
            'scope_module_id' => $this->module->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $response = $this->actingAs($this->guru)->get(route('guru.chat-activities.index'));

        $response->assertOk();
        $response->assertSee('Aktivitas Tanya Jawab Siswa');
        $response->assertSee('Unduh Rekap Mingguan (.xlsx)');
        $response->assertSee('Bagaimana cara konfigurasi MikroTik gateway?');
        $response->assertSee('Ahmad Siswa');
        $response->assertSee('XII TKJ 1');
    }

    public function test_guru_can_download_weekly_questions_excel(): void
    {
        // 1 Chat dalam 3 hari (minggu ini)
        ChatHistory::create([
            'siswa_id' => $this->siswa->id,
            'pertanyaan' => 'Apa perbedaan routing statis dan dinamis?',
            'jawaban' => 'Routing statis dikonfigurasi secara manual...',
            'mapel' => 'Teknik Komputer dan Jaringan',
            'kind' => 'answer',
            'created_at' => Carbon::now()->subDays(3),
        ]);

        // 1 Chat 14 hari yang lalu (di luar 7 hari)
        ChatHistory::create([
            'siswa_id' => $this->siswa->id,
            'pertanyaan' => 'Pertanyaan lama di luar rentang seminggu',
            'jawaban' => 'Jawaban pertanyaan lama...',
            'mapel' => 'Teknik Komputer dan Jaringan',
            'kind' => 'answer',
            'created_at' => Carbon::now()->subDays(14),
        ]);

        $response = $this->actingAs($this->guru)->get(route('guru.chat-activities.export-weekly'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('REKAP_PERTANYAAN_MINGGUAN_SMKN1_KINALI', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_dashboard_guru_renders_link_to_chat_activities(): void
    {
        $response = $this->actingAs($this->guru)->get(route('guru.dashboard'));

        $response->assertOk();
        $response->assertSee(route('guru.chat-activities.index'));
        $response->assertSee('Buka Rekap');
        $response->assertSee('Aktivitas Tanya Jawab');
    }

    public function test_weekly_metrics_and_excel_export_include_early_morning_wib_questions_at_boundary(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 19, 10, 0, 0, 'Asia/Jakarta'));

        try {
            // 03:30 WIB on the 7-day start date (2026-11-12 03:30 WIB = 2026-11-11 20:30 UTC) -> MUST be included
            $earlyMorningChat = new ChatHistory([
                'siswa_id' => $this->siswa->id,
                'pertanyaan' => 'Pertanyaan subuh WIB pada batas 7 hari',
                'jawaban' => 'Jawaban pertanyaan subuh',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kind' => 'answer',
            ]);
            $earlyMorningChat->created_at = Carbon::create(2026, 11, 12, 3, 30, 0, 'Asia/Jakarta')->utc();
            $earlyMorningChat->updated_at = $earlyMorningChat->created_at;
            $earlyMorningChat->save();

            // 23:30 WIB one day before the 7-day window (2026-11-11 23:30 WIB = 2026-11-11 16:30 UTC) -> MUST be excluded
            $beforeWindowChat = new ChatHistory([
                'siswa_id' => $this->siswa->id,
                'pertanyaan' => 'Pertanyaan sebelum batas 7 hari WIB',
                'jawaban' => 'Jawaban di luar batas',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kind' => 'answer',
            ]);
            $beforeWindowChat->created_at = Carbon::create(2026, 11, 11, 23, 30, 0, 'Asia/Jakarta')->utc();
            $beforeWindowChat->updated_at = $beforeWindowChat->created_at;
            $beforeWindowChat->save();

            // Regular recent question -> MUST be included
            $recentChat = new ChatHistory([
                'siswa_id' => $this->siswa->id,
                'pertanyaan' => 'Pertanyaan hari ini di jam sekolah',
                'jawaban' => 'Jawaban hari ini',
                'mapel' => 'Teknik Komputer dan Jaringan',
                'kind' => 'answer',
            ]);
            $recentChat->created_at = Carbon::create(2026, 11, 19, 8, 0, 0, 'Asia/Jakarta')->utc();
            $recentChat->updated_at = $recentChat->created_at;
            $recentChat->save();

            $indexResponse = $this->actingAs($this->guru)->get(route('guru.chat-activities.index'));
            $indexResponse->assertOk();
            $indexResponse->assertViewHas('metrics', function (array $metrics) {
                return $metrics['weekly_questions'] === 2
                    && $metrics['weekly_students'] === 1
                    && $metrics['total_all_time'] === 3;
            });

            $exportResponse = $this->actingAs($this->guru)->get(route('guru.chat-activities.export-weekly'));
            $exportResponse->assertOk();

            $binary = $exportResponse->streamedContent();
            $tmpFile = tempnam(sys_get_temp_dir(), 'weekly_xlsx_');
            $this->assertNotFalse($tmpFile);

            try {
                file_put_contents($tmpFile, $binary);
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpFile);
                $sheet = $spreadsheet->getActiveSheet();
                $allText = collect($sheet->toArray())
                    ->flatten()
                    ->filter(fn ($v) => is_string($v))
                    ->implode("\n");

                $this->assertStringContainsString('Pertanyaan subuh WIB pada batas 7 hari', $allText);
                $this->assertStringContainsString('Pertanyaan hari ini di jam sekolah', $allText);
                $this->assertStringNotContainsString('Pertanyaan sebelum batas 7 hari WIB', $allText);
            } finally {
                @unlink($tmpFile);
            }
        } finally {
            Carbon::setTestNow();
        }
    }
}
