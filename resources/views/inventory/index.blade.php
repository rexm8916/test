@extends('layouts.velzon')

@section('title', 'Buku Penjualan')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Buku Penjualan</h4>
            <div class="page-title-right">
                <a href="{{ route('inventory.create') }}" class="btn btn-success btn-sm">
                    <i class="ri-add-line align-bottom me-1"></i> Tambah Entri
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card card-animate h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Total Saldo</p>
                        <h2 class="mt-4 ff-secondary fw-semibold">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h2>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded-circle fs-2">
                                <i class="ri-wallet-3-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->

    <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
        <div class="card card-animate h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Pembelian (Masuk)</p>
                        <h2 class="mt-4 ff-secondary fw-semibold text-success">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</h2>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                <i class="ri-arrow-right-down-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->

    <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
        <div class="card card-animate h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Penjualan (Keluar)</p>
                        <h2 class="mt-4 ff-secondary fw-semibold text-danger">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</h2>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle rounded-circle fs-2">
                                <i class="ri-arrow-right-up-line text-danger"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="fw-medium text-muted mb-0">Total Stock</p>
                        <h2 class="mt-4 ff-secondary fw-semibold">{{ number_format($totalStock, 0, ',', '.') }}</h2>
                    </div>
                    <div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded-circle fs-2">
                                <i class="ri-inbox-archive-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Tanggal</th>
                                <th scope="col">Tipe</th>
                                <th scope="col">Detail Barang</th>
                                <th scope="col" class="text-end">Pembelian (Masuk)</th>
                                <th scope="col" class="text-end">Penjualan (Keluar)</th>
                                <th scope="col" class="text-end">Saldo</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgers as $ledger)
                            <tr>
                                <td>{{ $ledger->date->translatedFormat('d F Y') }}</td>
                                <td>
                                    @if($ledger->type == 'initial')
                                        <span class="badge bg-info">Awal</span>
                                    @elseif($ledger->type == 'purchase')
                                        <span class="badge bg-success">Pembelian</span>
                                    @else
                                        @if($ledger->item_name)
                                            <span class="badge bg-danger">Keluar Barang</span>
                                        @else
                                            <span class="badge bg-danger">Harian</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($ledger->type == 'purchase')
                                        <div class="fw-medium">{{ $ledger->item_name }}</div>
                                        <div class="text-muted fs-12">{{ $ledger->quantity }} x Rp {{ number_format($ledger->unit_price, 0, ',', '.') }}</div>
                                    @elseif($ledger->type == 'initial')
                                        Saldo Awal
                                    @elseif($ledger->type == 'sale')
                                        @if($ledger->item_name && $ledger->unit_price)
                                            <!-- Sale Item (Keluar Barang) -->
                                            <div class="fw-medium">{{ $ledger->item_name }}</div>
                                            <div class="text-muted fs-12">{{ $ledger->quantity }} x Rp {{ number_format($ledger->unit_price, 0, ',', '.') }}</div>
                                        @else
                                            <!-- Regular Sale (Rp Saja) -->
                                            Penjualan (Keluar) - Harian
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end text-success">
                                    @if($ledger->type == 'initial' || $ledger->type == 'purchase')
                                        + Rp {{ number_format($ledger->amount, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end text-danger">
                                    @if($ledger->type == 'sale')
                                        - Rp {{ number_format($ledger->amount, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    Rp {{ number_format($ledger->balance, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('inventory.edit', $ledger->id) }}" class="btn btn-sm btn-soft-info">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <form action="{{ route('inventory.destroy', $ledger->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada catatan inventaris yang ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
