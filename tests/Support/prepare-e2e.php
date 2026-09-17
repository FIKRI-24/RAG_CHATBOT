<?php

use App\Models\Module;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

$app = require __DIR__.'/e2e-bootstrap.php';
$kernel = $app->make(Kernel::class);
if (Schema::hasTable('users')) {
    throw new RuntimeException('Fixture file must be a fresh database.');
}
$kernel->call('migrate', ['--force' => true]);
$teacher = User::create(['name' => 'Guru E2E', 'email' => 'guru@e2e.test', 'password' => 'E2e-password-2026', 'role' => 'guru']);
for ($i = 1; $i <= 20; $i++) {
    User::create(['name' => 'Siswa E2E '.$i, 'email' => 'siswa'.$i.'@e2e.test', 'password' => 'E2e-password-2026', 'role' => 'siswa', 'class_name' => 'XI TKJ', 'student_number' => (string) $i]);
}
$word = new PhpWord;
$word->addSection()->addText('VLAN memisahkan jaringan secara logis. Port access membawa satu VLAN dan trunk membawa beberapa VLAN.');
Storage::disk('local')->makeDirectory('modules');
$path = Storage::disk('local')->path('modules/fixture.docx');
IOFactory::createWriter($word, 'Word2007')->save($path);
$module = Module::create(['guru_id' => $teacher->id, 'judul' => 'VLAN E2E', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1', 'file_path' => 'modules/fixture.docx', 'status_indexing' => 'completed']);
$module->chunks()->create(['chunk_text' => 'VLAN memisahkan jaringan secara logis.', 'embedding_vector' => [1, 0], 'chunk_index' => 0, 'embedding_model' => GeminiService::EMBEDDING_MODEL, 'embedding_dimensions' => 2]);
echo "Isolated E2E fixtures ready.\n";
