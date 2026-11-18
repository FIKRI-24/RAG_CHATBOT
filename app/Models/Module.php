<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'judul',
        'mapel',
        'topik',
        'kb_nomor',
        'tp',
        'file_path',
        'indexed_file_path',
        'video_url',
        'kuis_url',
        'status_indexing',
        'reindexing_status',
        'indexing_version',
        'indexing_error',
        'berlaku_sampai',
        'additional_videos',
    ];

    protected $casts = [
        'berlaku_sampai' => 'date',
        'additional_videos' => 'array',
    ];

    public function scopeAvailable($query)
    {
        return $query->where('status_indexing', 'completed')->where(function ($query) {
            $query->whereNull('berlaku_sampai')->orWhereDate('berlaku_sampai', '>=', today(config('app.display_timezone'))->toDateString());
        });
    }

    public function isExpired(): bool
    {
        return $this->berlaku_sampai !== null
            && $this->berlaku_sampai->toDateString() < today(config('app.display_timezone'))->toDateString();
    }

    public static function curriculumElements(): array
    {
        return config('curriculum.elements', []);
    }

    public static function defaultTopics(): array
    {
        return config('curriculum.default_topics', []);
    }

    public static function defaultKbOptions(): array
    {
        return config('curriculum.kb_options', ['KB 1', 'KB 2', 'KB 3']);
    }

    public static function mergeWithCurriculumElements(iterable $existing): \Illuminate\Support\Collection
    {
        return collect(self::curriculumElements())
            ->merge(collect($existing)->filter(fn ($v) => is_string($v) && trim($v) !== ''))
            ->unique()
            ->values();
    }

    public static function mergeWithDefaultKbOptions(iterable $existing): \Illuminate\Support\Collection
    {
        return collect(self::defaultKbOptions())
            ->merge(collect($existing)->filter(fn ($v) => is_string($v) && trim($v) !== ''))
            ->unique()
            ->sort(fn ($a, $b) => strnatcasecmp((string) $a, (string) $b))
            ->values();
    }

    public function additionalVideos(): array
    {
        $videos = $this->additional_videos;
        if ($videos === null && in_array($this->mapel, [
            'Teknik Komputer dan Jaringan',
            'Elemen 2: Teknologi Jaringan Kabel dan Nirkabel',
        ], true)) {
            // Config keys contain dots in file names, so access the literal array key.
            $videos = config('module-media', [])[$this->file_path] ?? [];
        }

        return array_map(fn ($video) => $video + ['icon' => 'fa-solid fa-video', 'color' => 'text-blue-500'], $videos ?? []);
    }

    /**
     * Get the user (guru) that owns the module.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Get the chunks for the module.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(ModuleChunk::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(ModuleQuiz::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(ModuleQuizAttempt::class);
    }

    /**
     * Check whether an already-published module has an active background re-index.
     */
    public function isReindexing(): bool
    {
        return in_array($this->reindexing_status, ['pending', 'processing'], true);
    }

    /**
     * Check whether the latest background re-index failed while keeping the old index live.
     */
    public function reindexingFailed(): bool
    {
        return $this->reindexing_status === 'failed';
    }

    /**
     * Compare two modules naturally by kb_nomor, title, and id (so KB 2 comes before KB 10).
     */
    public static function compareNaturalKb(self $a, self $b): int
    {
        $cmp = strnatcasecmp((string) $a->kb_nomor, (string) $b->kb_nomor);
        if ($cmp !== 0) {
            return $cmp;
        }
        $titleCmp = strnatcasecmp((string) $a->judul, (string) $b->judul);

        return $titleCmp !== 0 ? $titleCmp : ($a->id <=> $b->id);
    }

    /**
     * Sort a collection of modules using natural KB ordering.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function sortNaturallyByKb(iterable $modules): \Illuminate\Support\Collection
    {
        return collect($modules)
            ->sort(fn (self $a, self $b): int => self::compareNaturalKb($a, $b))
            ->values();
    }

    /**
     * Retrieve modules relevant to quiz score recaps (published quizzes or recorded attempts).
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function quizRecapModules(?int $guruId = null, ?string $mapel = null, ?string $topik = null): \Illuminate\Support\Collection
    {
        return static::sortNaturallyByKb(
            static::query()
                ->when($guruId !== null, fn ($q) => $q->where('guru_id', $guruId))
                ->when($mapel !== null && $mapel !== '', fn ($q) => $q->where('mapel', $mapel))
                ->when($topik !== null && $topik !== '', fn ($q) => $q->where('topik', $topik))
                ->where(function ($q) {
                    $q->whereHas('quiz', fn ($quiz) => $quiz->where('is_published', true))
                        ->orWhereHas('quizAttempts');
                })
                ->with('quiz:id,module_id,is_published')
                ->get(['id', 'guru_id', 'judul', 'kb_nomor', 'mapel', 'topik', 'berlaku_sampai', 'status_indexing', 'reindexing_status'])
        );
    }

    public static function ensureKb4Module(?int $guruId = null): ?self
    {
        try {
            $guruId = $guruId
                ?? (auth()->check() && auth()->user()->isGuru() ? auth()->id() : null)
                ?? self::whereIn('kb_nomor', ['KB 1', 'KB 2', 'KB 3'])->value('guru_id')
                ?? User::where('role', 'guru')->value('id');

            if (! $guruId) {
                return null;
            }

            $defaultDocx = base_path('resources/default-modules/kb4_keamanan_jaringan.docx');
            if (is_file($defaultDocx) && ! \Illuminate\Support\Facades\Storage::disk('local')->exists('modules/kb4_keamanan_jaringan.docx')) {
                \Illuminate\Support\Facades\Storage::disk('local')->put(
                    'modules/kb4_keamanan_jaringan.docx',
                    file_get_contents($defaultDocx)
                );
            }

            $tpContent = "Setelah mempelajari KB 4 ini peserta didik dapat:\n"
                ."1) Menjelaskan prinsip dasar keamanan jaringan (CIA Triad: Confidentiality, Integrity, Availability)\n"
                ."2) Mengidentifikasi jenis ancaman dan serangan jaringan (Port Scanning, DoS/DDoS, SYN Flood Attack)\n"
                ."3) Menganalisis alur pemrosesan rantai paket firewall (Chain Input, Forward, Output)\n"
                ."4) Membedakan tindakan aturan penyaringan firewall (Action Accept, Drop, Reject)\n"
                ."5) Mengonfigurasi aturan filtering firewall untuk mengamankan router gateway.";

            $module = self::where('kb_nomor', 'KB 4')
                ->whereIn('mapel', ['Elemen 3: Keamanan Jaringan', 'Teknik Komputer dan Jaringan'])
                ->first();

            if (! $module) {
                $module = self::create([
                    'guru_id' => $guruId,
                    'judul' => 'Keamanan Jaringan dan Konfigurasi Firewall Filtering pada Router Gateway',
                    'mapel' => 'Elemen 3: Keamanan Jaringan',
                    'topik' => 'Keamanan Jaringan dan Firewall',
                    'kb_nomor' => 'KB 4',
                    'tp' => $tpContent,
                    'file_path' => 'modules/kb4_keamanan_jaringan.docx',
                    'video_url' => 'https://www.youtube.com/watch?v=0h9bQx6eTfM',
                    'kuis_url' => null,
                    'status_indexing' => 'completed',
                    'indexing_version' => (string) \Illuminate\Support\Str::uuid(),
                    'indexing_error' => null,
                ]);
            }

            if (! $module->quiz()->exists()) {
                $questions = [
                    [
                        'text' => 'Dalam konsep dasar keamanan informasi (CIA Triad), pilar yang bertugas menjamin bahwa data atau informasi tidak dimanipulasi, diubah, atau dirusak oleh pihak yang tidak sah selama proses transmisi adalah...',
                        'options' => [
                            'A' => 'Confidentiality (Kerahasiaan)',
                            'B' => 'Integrity (Keutuhan)',
                            'C' => 'Availability (Ketersediaan)',
                            'D' => 'Authentication (Autentikasi)',
                        ],
                        'correct_answer' => 'B',
                    ],
                    [
                        'text' => 'Jenis serangan Denial of Service (DoS) yang mengeksploitasi kelemahan mekanisme three-way handshake TCP dengan membanjiri target dengan paket SYN tanpa pernah mengirimkan paket ACK balasan adalah...',
                        'options' => [
                            'A' => 'SYN Flood Attack',
                            'B' => 'ARP Spoofing Attack',
                            'C' => 'Port Scanning Attack',
                            'D' => 'SQL Injection Attack',
                        ],
                        'correct_answer' => 'A',
                    ],
                    [
                        'text' => 'Rantai (chain) pada konfigurasi firewall filtering yang menangani paket data yang ditujukan secara spesifik ke router gateway itu sendiri (misalnya teknisi mengakses port SSH 22 atau Winbox) adalah...',
                        'options' => [
                            'A' => 'Chain Forward',
                            'B' => 'Chain Output',
                            'C' => 'Chain Input',
                            'D' => 'Chain Prerouting',
                        ],
                        'correct_answer' => 'C',
                    ],
                    [
                        'text' => 'Apa perbedaan mendasar antara tindakan filtering Action DROP dan Action REJECT pada firewall router?',
                        'options' => [
                            'A' => 'DROP menolak paket dengan pesan balasan ICMP error, sedangkan REJECT membuang paket secara diam-diam',
                            'B' => 'DROP membuang paket secara diam-diam tanpa pesan respon, sedangkan REJECT menolak paket disertai kiriman pesan ICMP error ke pengirim',
                            'C' => 'DROP mengizinkan paket lewat setelah diperiksa, sedangkan REJECT langsung mematikan port antarmuka',
                            'D' => 'DROP hanya berlaku untuk protokol UDP, sedangkan REJECT khusus untuk protokol TCP',
                        ],
                        'correct_answer' => 'B',
                    ],
                    [
                        'text' => 'Nomor port default standar yang digunakan oleh protokol Secure Shell (SSH) untuk administrasi jarak jauh router gateway secara terenkripsi dan aman adalah...',
                        'options' => [
                            'A' => 'Port 21',
                            'B' => 'Port 22',
                            'C' => 'Port 23',
                            'D' => 'Port 80',
                        ],
                        'correct_answer' => 'B',
                    ],
                ];

                $module->quiz()->create([
                    'title' => 'Kuis Evaluasi Pemahaman KB 4: Keamanan Jaringan & Firewall',
                    'questions' => $questions,
                    'is_published' => true,
                    'version' => 1,
                ]);
            }

            if ($module->wasRecentlyCreated || ! $module->chunks()->exists()) {
                try {
                    \App\Jobs\ProcessModuleJob::dispatch($module);
                } catch (\Throwable) {
                    // Ignore queue dispatch failure during seeding if queue table is not ready
                }
            }

            return $module;
        } catch (\Throwable) {
            return null;
        }
    }
}
