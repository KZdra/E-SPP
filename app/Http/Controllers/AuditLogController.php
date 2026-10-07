<?php

namespace App\Http\Controllers;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['causer', 'subject'])
            ->orderBy('id', 'desc');

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('user_name', function ($q, $keyword) {
                    $q->whereHas('causer', function ($cq) use ($keyword) {
                        $cq->where('name', 'like', "%{$keyword}%")
                          ->orWhere('username', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('tgl_format', fn($row) => $row->created_at->format('d/m/Y H:i:s'))
                ->addColumn('user_name', fn($row) => $row->causer ? '<strong>' . e($row->causer->name) . '</strong> (' . e($row->causer->username ?? '') . ')' : '<span class="text-muted">Sistem</span>')
                ->addColumn('log_badge', fn($row) => '<span class="badge bg-primary">' . e($row->log_name) . '</span>')
                ->addColumn('event_badge', function ($row) {
                    $color = match($row->event) {
                        'created' => 'success',
                        'updated' => 'warning text-dark',
                        'deleted' => 'danger',
                        default => 'secondary'
                    };
                    return '<span class="badge bg-' . $color . '">' . strtoupper($row->event ?? '-') . '</span>';
                })
                ->addColumn('description_text', fn($row) => e($row->description))
                ->rawColumns(['user_name', 'log_badge', 'event_badge', 'description_text'])
                ->make(true);
        }

        $logs = $query->paginate(25);
        $logTypes = Activity::select('log_name')->distinct()->pluck('log_name');

        return view('audit-logs.index', compact('logs', 'logTypes'));
    }
}
