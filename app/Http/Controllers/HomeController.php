<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReportService;
use App\Models\UnitSekolah;

class HomeController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Determine unit scope
        if ($user->isYayasan()) {
            $selectedUnitId = $request->get('unit_id'); // null means all units
        } else {
            $selectedUnitId = $user->unit_sekolah_id;
        }

        $units = UnitSekolah::where('is_active', true)->get();
        $stats = $this->reportService->getDashboardStats($selectedUnitId);

        return view('home', compact('stats', 'units', 'selectedUnitId', 'user'));
    }
}
