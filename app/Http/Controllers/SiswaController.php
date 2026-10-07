<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\UnitSekolah;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Siswa::with(['unitSekolah', 'kelas']);

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
                ->rawColumns(['unit_name', 'kelas_name', 'status_badge', 'action'])
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required|string|max:30',
            'nisn' => 'nullable|string|max:30',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_wali' => 'nullable|string|max:150',
            'telepon_wali' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,lulus,pindah',
        ]);

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
            'kelas',
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

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'kelas_id' => 'required|exists:kelas,id',
            'nis' => 'required|string|max:30',
            'nisn' => 'nullable|string|max:30',
            'nama' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:L,P',
            'nama_wali' => 'nullable|string|max:150',
            'telepon_wali' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,lulus,pindah',
        ]);

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
}
