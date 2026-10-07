@extends('layouts.app')

@section('subtitle', 'Audit Trail Log')
@section('content_header_title', 'Audit Trail Log Aktivitas')
@section('content_header_subtitle', 'Sistem Keamanan & Transparansi')

@section('content_body')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-body-secondary"><i class="bi bi-shield-lock-fill text-danger me-2"></i>Rekam Jejak Transaksi & Data (Audit Log)</h5>
            <form method="GET" action="{{ route('audit-logs.index') }}" class="d-flex align-items-center gap-2">
                <select name="log_name" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kategori Log</option>
                    @foreach($logTypes as $lt)
                        <option value="{{ $lt }}" {{ request('log_name') == $lt ? 'selected' : '' }}>
                            {{ ucfirst($lt) }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 font-monospace" style="font-size: 0.875rem;">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase font-sans-serif">
                            <th>Waktu & Tanggal</th>
                            <th>Pengguna (Actor)</th>
                            <th>Modul / Log</th>
                            <th>Aksi (Event)</th>
                            <th>Deskripsi Aktivitas</th>
                            <th>Rincian Perubahan (Properties)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-nowrap text-muted">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->causer->name ?? 'System' }}</div>
                                    <small class="text-muted">{{ $log->causer->email ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $log->log_name }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $badgeEvent = match($log->event) {
                                            'created' => 'bg-success',
                                            'updated' => 'bg-warning text-dark',
                                            'deleted' => 'bg-danger',
                                            default => 'bg-info text-white'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeEvent }}">
                                        {{ $log->event ?? 'action' }}
                                    </span>
                                </td>
                                <td class="font-sans-serif">
                                    {{ $log->description }}
                                </td>
                                <td>
                                    @if($log->properties && $log->properties->count() > 0)
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" type="button" data-bs-toggle="collapse" data-bs-target="#prop-{{ $log->id }}">
                                            <i class="bi bi-code-slash"></i> JSON Data
                                        </button>
                                        <div class="collapse mt-2" id="prop-{{ $log->id }}">
                                            <pre class="bg-dark text-light p-2 rounded small mb-0" style="max-width: 320px; max-height: 150px; overflow: auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted font-sans-serif">Belum ada riwayat aktivitas yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-transparent py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@stop
