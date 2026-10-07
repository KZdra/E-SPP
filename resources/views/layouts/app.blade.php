@extends('adminlte::page')

{{-- Extend and customize the browser title --}}
@section('title')
    {{ config('adminlte.title', 'E-SPP') }}
    @hasSection('subtitle') | @yield('subtitle') @endif
@stop

{{-- Extend and customize the page content header --}}
@section('content_header')
    @hasSection('content_header_title')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="text-body-secondary fw-bold mb-0">
                    @yield('content_header_title')
                    @hasSection('content_header_subtitle')
                        <small class="text-muted fs-6 fw-normal">
                            <i class="bi bi-chevron-right text-muted mx-1"></i>
                            @yield('content_header_subtitle')
                        </small>
                    @endif
                </h1>
            </div>
            <div>
                @yield('content_header_actions')
            </div>
        </div>
    @endif

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3 shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <strong>Perhatian:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-warning alert-dismissible fade show mt-3 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
            <strong>Terdapat kesalahan input:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
@stop

{{-- Rename section content to content_body --}}
@section('content')
    <div class="py-2">
        @yield('content_body')
    </div>
@stop

{{-- Create a common footer --}}
@section('footer')
    <div class="float-end d-none d-sm-inline">
        <span class="badge bg-primary-subtle text-primary border px-2 py-1">Clean Architecture & RBAC Ready</span>
    </div>
    <strong>
        &copy; {{ date('Y') }} Sistem Informasi Manajemen SPP Terpadu
    </strong>
@stop

{{-- Add common CSS customizations --}}
@push('css')
<style type="text/css">
    .card {
        border-radius: 0.6rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card-header {
        font-weight: 600;
        background-color: transparent;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.12);
    }
</style>
@endpush
