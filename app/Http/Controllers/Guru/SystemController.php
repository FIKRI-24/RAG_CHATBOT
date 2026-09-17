<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Services\SystemStatusService;

class SystemController extends Controller
{
    public function index(SystemStatusService $service)
    {
        $status = $service->snapshot();
        $events = AuditEvent::latest('id')->paginate(30);

        return view('guru.system', compact('status', 'events'));
    }
}
