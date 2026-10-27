<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Services\ExportActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChatActivityController extends Controller
{
    /**
     * Tampilkan halaman aktivitas tanya jawab AI siswa beserta metrik mingguan.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'module_id' => 'nullable|integer',
        ]);

        $search = $request->input('search');
        $moduleId = $request->input('module_id');
        $oneWeekAgo = Carbon::now(config('app.display_timezone'))->subDays(7)->startOfDay();

        // Metrik 7 Hari Terakhir (Mingguan)
        $weeklyQuestionsCount = ChatHistory::where('created_at', '>=', $oneWeekAgo)->questions()->count();
        $weeklyActiveStudentsCount = ChatHistory::where('created_at', '>=', $oneWeekAgo)->questions()->distinct('siswa_id')->count('siswa_id');
        $totalQuestionsAllTime = ChatHistory::questions()->count();

        // Query Log Pertanyaan Siswa
        $query = ChatHistory::with(['siswa:id,name,email,student_number,class_name,avatar', 'referensiChunk:id,module_id', 'referensiChunk.module:id,judul,mapel,kb_nomor'])
            ->questions()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('pertanyaan', 'like', "%{$search}%")
                    ->orWhere('jawaban', 'like', "%{$search}%")
                    ->orWhereHas('siswa', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('student_number', 'like', "%{$search}%")
                            ->orWhere('class_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('module_id')) {
            $query->where(function ($q) use ($moduleId) {
                $q->where('scope_module_id', $moduleId)
                    ->orWhereHas('referensiChunk', fn ($rq) => $rq->where('module_id', $moduleId));
            });
        }

        $chats = $query->paginate(15)->withQueryString();

        $modules = Module::available()->orderBy('kb_nomor')->get(['id', 'judul', 'kb_nomor', 'mapel']);

        $metrics = [
            'weekly_questions' => $weeklyQuestionsCount,
            'weekly_students' => $weeklyActiveStudentsCount,
            'total_all_time' => $totalQuestionsAllTime,
            'start_date' => $oneWeekAgo,
            'end_date' => Carbon::now(config('app.display_timezone')),
        ];

        return view('guru.chat-activities.index', compact('chats', 'modules', 'metrics', 'search', 'moduleId'));
    }

    /**
     * Unduh berkas Excel rekapan pertanyaan siswa selama 1 minggu terakhir.
     */
    public function exportWeekly(Request $request, ExportActivityService $exportService)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        return $exportService->exportWeeklyQuestions($request->input('search'));
    }
}
