<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\UnitSekolah;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Services\SiswaImportService;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Siswa::with(['unitSekolah', 'kelas.jurusan']);

        // Scope by unit
        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $query->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_sekolah_id', $request->unit_id);
        }

        // Filter by class
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search NIS or Name (for non-ajax standard GET requests)
        if (!$request->ajax() && $request->filled('search') && is_string($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('unit_name', function ($q, $keyword) {
                    $q->whereHas('unitSekolah', function ($uq) use ($keyword) {
                        $uq->where('nama_unit', 'like', "%{$keyword}%")
                          ->orWhere('kode_unit', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('kelas_name', function ($q, $keyword) {
                    $q->whereHas('kelas', function ($kq) use ($keyword) {
                        $kq->where('nama_kelas', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('unit_name', fn($row) => $row->unitSekolah ? '<span class="badge bg-primary me-1">' . e($row->unitSekolah->kode_unit) . '</span> ' . e($row->unitSekolah->nama_unit) : '-')
                ->addColumn('kelas_name', function ($row) {
                    $jurusan = ($row->kelas && $row->kelas->jurusan) ? ' <span class="badge bg-success-subtle text-success border">' . e($row->kelas->jurusan->kode_jurusan) . '</span>' : '';
                    return '<strong>' . e($row->kelas?->nama_kelas ?? '-') . '</strong>' . $jurusan;
                })
                ->addColumn('kategori_badge', function ($row) {
                    return match($row->kategori_spp) {
                        'beasiswa' => '<span class="badge bg-warning text-dark"><i class="bi bi-award-fill me-1"></i>Beasiswa ' . ($row->diskon_tipe === 'persen' ? $row->diskon_nilai . '%' : 'Rp ' . number_format($row->diskon_nilai, 0, ',', '.')) . '</span>',
                        'yatim' => '<span class="badge bg-info text-white"><i class="bi bi-heart-fill me-1"></i>Yatim (100%)</span>',
                        'keringanan' => '<span class="badge bg-secondary"><i class="bi bi-percent me-1"></i>Keringanan</span>',
                        default => '<span class="badge bg-light text-muted border">Reguler</span>'
                    };
                })
                ->addColumn('status_badge', function ($row) {
                    $badge = match($row->status) {
                        'aktif' => 'success',
                        'lulus' => 'primary',
                        'pindah' => 'danger',
                        default => 'secondary'
                    };
                    return '<span class="badge bg-' . $badge . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('siswas.show', $row->id);
                    $editUrl = route('siswas.edit', $row->id);
                    $deleteUrl = route('siswas.destroy', $row->id);
                    $csrf = csrf_field();
                    $method = method_field('DELETE');

                    return '
                        <div class="btn-group btn-group-sm">
                            <a href="' . $showUrl . '" class="btn btn-outline-info" title="Detail Siswa"><i class="bi bi-eye"></i></a>
                            <a href="' . $editUrl . '" class="btn btn-outline-warning" title="Edit Siswa"><i class="bi bi-pencil"></i></a>
                            <form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'Hapus data siswa ini?\');" class="d-inline">
                                ' . $csrf . '
                                ' . $method . '
                                <button type="submit" class="btn btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    ';
                })
                ->rawColumns(['unit_name', 'kelas_name', 'kategori_badge', 'status_badge', 'action'])
                ->make(true);
        }

        $siswas = $query->orderBy('nama')->paginate(15)->withQueryString();
        $units = UnitSekolah::where('is_active', true)->get();

        $kelasQuery = Kelas::query();
        if (!$user->isYayasan() && $user->unit_sekolah_id) {
            $kelasQuery->where('unit_sekolah_id', $user->unit_sekolah_id);
        } elseif ($request->filled('unit_id')) {
            $kelasQuery->where('unit_sekolah_id', $request->unit_id);
        }
        $kelasList = $kelasQuery->orderBy('nama_kelas')->get();

        return view('siswas.index', compact('siswas', 'units', 'kelasList'));
    }

    public function create()
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('siswas.create', compact('units', 'kelasList'));
    }

    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

        // Check unique NIS in unit
        $exists = Siswa::where('unit_sekolah_id', $validated['unit_sekolah_id'])
            ->where('nis', $validated['nis'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['nis' => 'NIS sudah terdaftar pada unit sekolah ini.']);
        }

        Siswa::create($validated);

        return redirect()->route('siswas.index')->with('success', 'Data siswa berhasil didaftarkan.');
    }

    public function show(Siswa $siswa)
    {
        $siswa->load([
            'unitSekolah',
            'kelas.jurusan',
            'tagihans.tahunAjaran',
            'pembayarans.petugas'
        ]);

        return view('siswas.show', compact('siswa'));
    }

    public function edit(Siswa $siswa)
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $kelasList = Kelas::where('unit_sekolah_id', $siswa->unit_sekolah_id)->get();

        return view('siswas.edit', compact('siswa', 'units', 'kelasList'));
    }

    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $validated = $request->validated();

        // Check unique NIS in unit excluding this student
        $exists = Siswa::where('unit_sekolah_id', $validated['unit_sekolah_id'])
            ->where('nis', $validated['nis'])
            ->where('id', '!=', $siswa->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['nis' => 'NIS sudah terdaftar pada unit sekolah ini.']);
        }

        $siswa->update($validated);

        return redirect()->route('siswas.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('siswas.index')->with('success', 'Data siswa berhasil dihapus (Soft Delete).');
    }

    /**
     * Download template import siswa Excel
     */
    public function downloadTemplate(Request $request, SiswaImportService $importService)
    {
        $user = auth()->user();
        $unitId = (!$user->isYayasan() && $user->unit_sekolah_id) ? $user->unit_sekolah_id : ($request->unit_id ?? UnitSekolah::first()?->id);
        $unit = UnitSekolah::findOrFail($unitId);

        return $importService->generateTemplate($unit);
    }

    /**
     * Import siswa dari file Excel
     */
    public function importExcel(Request $request, SiswaImportService $importService)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
            'unit_id' => 'required|exists:unit_sekolahs,id',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        $result = $importService->import(
            $request->file('file_excel'),
            (int)$request->unit_id,
            $request->kelas_id ? (int)$request->kelas_id : null
        );

        if (!$result['success']) {
            return redirect()->route('siswas.index')->with('error', $result['errors'][0] ?? 'Gagal mengimpor file Excel.');
        }

        $msg = "Import berhasil: {$result['imported']} siswa baru ditambahkan, {$result['updated']} diperbarui.";
        if ($result['failed'] > 0) {
            $msg .= " ({$result['failed']} baris dilewati karena format tidak sesuai).";
        }

        return redirect()->route('siswas.index')->with('success', $msg);
    }
}

