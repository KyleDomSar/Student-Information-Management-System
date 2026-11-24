<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $logs = AuditLog::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($logQuery) use ($search) {
                    $logQuery
                        ->where('action', 'like', "%{$search}%")
                        ->orWhere('target_type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs', 'search'));
    }
}
