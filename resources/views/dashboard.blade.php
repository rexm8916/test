@extends('layouts.velzon')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Dasbor</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dasbor</a></li>
                    <li class="breadcrumb-item active">Dasbor</li>
                </ol>
            </div>

        </div>
    </div>
</div>



<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-info-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-info mb-0">Total Pendapatan (Hari Ini)</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-info">Rp {{ number_format($todayPaid, 0, ',', '.') }}</h4>
                         <p class="text-muted mb-0">Total Uang Masuk</p>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info rounded fs-3">
                            <i class="ri-money-dollar-circle-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-success-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-success mb-0">Lunas (Hari Ini)</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-success">{{ $statusMetrics['paid']['count'] }} Transaksi</h4>
                         <p class="text-muted mb-1">Uang Masuk: <span class="fw-bold text-success">Rp {{ number_format($statusMetrics['paid']['cash_in'], 0, ',', '.') }}</span></p>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success rounded fs-3">
                            <i class="ri-check-double-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-danger-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-danger mb-0">Belum Bayar (Hari Ini)</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-danger">{{ $statusMetrics['unpaid']['count'] }} Transaksi</h4>
                         <p class="text-muted mb-1">Belum Bayar: <span class="fw-bold text-danger">Rp {{ number_format($statusMetrics['unpaid']['outstanding'], 0, ',', '.') }}</span></p>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-danger rounded fs-3">
                            <i class="ri-close-circle-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate bg-warning-subtle">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-warning mb-0">Cicilan (Hari Ini)</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-warning">{{ $statusMetrics['partial']['count'] }} Transaksi</h4>
                         <p class="text-muted mb-1">Uang Masuk: <span class="fw-bold text-success">Rp {{ number_format($statusMetrics['partial']['cash_in'], 0, ',', '.') }}</span></p>
                         <p class="text-muted mb-0">Belum Bayar: <span class="fw-bold text-danger">Rp {{ number_format($statusMetrics['partial']['outstanding'], 0, ',', '.') }}</span></p>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning rounded fs-3">
                            <i class="ri-history-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Stok Produk (15 Terendah)</h4>
            </div>
            <div class="card-body">
                <div id="stockChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Aksi Cepat</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <a href="{{ route('products.create') }}" class="btn btn-soft-primary w-100 text-start">
                        <i class="ri-add-line align-bottom me-1"></i> Tambah Produk Baru
                    </a>
                    <a href="{{ route('transactions.create', ['type' => 'sale']) }}" class="btn btn-soft-success w-100 text-start">
                        <i class="ri-shopping-cart-2-line align-bottom me-1"></i> Kasir / Penjualan Baru
                    </a>
                    <a href="{{ route('transactions.create', ['type' => 'purchase']) }}" class="btn btn-soft-info w-100 text-start">
                        <i class="ri-download-2-line align-bottom me-1"></i> Input Pembelian Stok
                    </a>
                     <a href="{{ route('debts.index') }}" class="btn btn-soft-warning w-100 text-start">
                        <i class="ri-money-dollar-circle-line align-bottom me-1"></i> Lihat & Catat Hutang
                    </a>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            series: [{
                name: 'Stok',
                data: @json($chartData['stock'])
            }],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true, // Horizontal bar chart for better name readability
                    distributed: true
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: @json($chartData['labels']),
            },
            colors: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#6366f1'], // Custom palette
            theme: {
                palette: 'palette1'
            }
        };

        var chart = new ApexCharts(document.querySelector("#stockChart"), options);
        chart.render();
    });
</script>
@endpush
