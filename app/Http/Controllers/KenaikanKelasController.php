<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\UnitSekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KenaikanKelasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $units = $user->isYayasan()
            ? UnitSekolah::where('is_active', true)->get()
            : UnitSekolah::where('id', $user->unit_sekolah_id)->get();

        $selectedUnitId = (!$user->isYayasan() && $user->unit_sekolah_id)
            ? $user->unit_sekolah_id
            : ($request->unit_id ?? $units->first()?->id);

        $kelasList = Kelas::where('unit_sekolah_id', $selectedUnitId)
            ->orderBy('nama_kelas')
            ->get();

        $selectedKelasId = $request->kelas_asal_id;
        $students = collect();

        if ($selectedKelasId) {
            $students = Siswa::where('kelas_id', $selectedKelasId)
                ->where('status', 'aktif')
                ->orderBy('nama')
                ->get();
        }

        return view('kelas.kenaikan', compact('units', 'selectedUnitId', 'kelasList', 'selectedKelasId', 'students'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'unit_sekolah_id' => 'required|exists:unit_sekolahs,id',
            'kelas_asal_id' => 'required|exists:kelas,id',
            'aksi' => 'required|in:naik_kelas,lulus',
            'kelas_tujuan_id' => 'required_if:aksi,naik_kelas|nullable|exists:kelas,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa untuk diproses.',
            'kelas_tujuan_id.required_if' => 'Pilih kelas tujuan untuk kenaikan kelas.',
        ]);

        $aksi = $request->aksi;
        $siswaIds = $request->siswa_ids;
        $kelasAsal = Kelas::findOrFail($request->kelas_asal_id);
        $kelasTujuan = $request->kelas_tujuan_id ? Kelas::find($request->kelas_tujuan_id) : null;

        DB::beginTransaction();
        try {
            $count = count($siswaIds);

            if ($aksi === 'naik_kelas') {
                Siswa::whereIn('id', $siswaIds)->update([
                    'kelas_id' => $kelasTujuan->id,
                ]);

                activity('kenaikan_kelas')
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'kelas_asal' => $kelasAsal->nama_kelas,
                        'kelas_tujuan' => $kelasTujuan->nama_kelas,
                        'total_siswa' => $count,
                    ])
                    ->log("Kenaikan Kelas: {$count} siswa dari {$kelasAsal->nama_kelas} berhasil dinaikkan ke {$kelasTujuan->nama_kelas}.");

                $message = "Sukses! {$count} siswa berhasil dipindahkan ke Kelas {$kelasTujuan->nama_kelas}.";
            } else {
                // Aksi Lulus
                Siswa::whereIn('id', $siswaIds)->update([
                    'status' => 'lulus',
                ]);

                activity('kelulusan_siswa')
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'kelas_asal' => $kelasAsal->nama_kelas,
                        'total_siswa' => $count,
                    ])
                    ->log("Kelulusan Siswa: {$count} siswa dari {$kelasAsal->nama_kelas} dinyatakan LULUS.");

                $message = "Sukses! {$count} siswa dari Kelas {$kelasAsal->nama_kelas} berhasil diubah statusnya menjadi LULUS.";
            }

            DB::commit();
            return redirect()->route('kenaikan-kelas.index', [
                'unit_id' => $request->unit_sekolah_id,
                'kelas_asal_id' => $request->kelas_asal_id,
            ])->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memproses kenaikan kelas: ' . $e->getMessage());
        }
    }
}
