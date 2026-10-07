<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnitSekolah;
use App\Models\TahunAjaran;
use App\Services\BillingGeneratorService;
use Carbon\Carbon;

class GenerateMonthlyBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spp:generate-monthly 
                            {--month= : Bulan tagihan (1-12, default bulan ini)} 
                            {--year= : Tahun tagihan (default tahun ini)} 
                            {--unit= : ID Unit Sekolah spesifik (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tagihan SPP bulanan otomatis untuk seluruh siswa aktif';

    /**
     * Execute the console command.
     */
    public function handle(BillingGeneratorService $billingService): int
    {
        $now = Carbon::now();
        $bulan = (int)($this->option('month') ?: $now->month);
        $tahun = (int)($this->option('year') ?: $now->year);
        $unitOption = $this->option('unit');

        $this->info("=== MEMULAI GENERATOR TAGIHAN SPP PERIODE {$bulan}/{$tahun} ===");

        $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (!$activeTahunAjaran) {
            $this->error('Gagal: Tidak ada Tahun Ajaran aktif yang diset pada sistem!');
            return Command::FAILURE;
        }

        $unitsQuery = UnitSekolah::where('is_active', true);
        if ($unitOption) {
            $unitsQuery->where('id', $unitOption);
        }
        $units = $unitsQuery->get();

        if ($units->isEmpty()) {
            $this->warn('Tidak ada unit sekolah aktif yang ditemukan.');
            return Command::SUCCESS;
        }

        $grandCreated = 0;
        $grandSkipped = 0;

        foreach ($units as $unit) {
            $this->line("Memproses unit: [{$unit->kode_unit}] {$unit->nama_unit}...");

            $result = $billingService->generateMonthlyBilling([
                'unit_sekolah_id' => $unit->id,
                'tahun_ajaran_id' => $activeTahunAjaran->id,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'kelas_id' => null,
                'jatuh_tempo' => Carbon::create($tahun, $bulan, 10)->format('Y-m-d'),
                'nominal_default' => null,
                'user_id' => null, // Scheduled CLI
            ]);

            $this->info(" -> Berhasil dibuat: {$result['created']}, Dilewati (sudah ada): {$result['skipped']}, Total siswa: {$result['total_students']}");
            $grandCreated += $result['created'];
            $grandSkipped += $result['skipped'];
        }

        $this->info("=== SELESAI: Total Tagihan Baru Dibuat: {$grandCreated}, Total Dilewati: {$grandSkipped} ===");

        return Command::SUCCESS;
    }
}
