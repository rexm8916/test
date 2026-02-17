@extends('layouts.velzon')

@section('title', 'Transactions')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Transactions</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Transactions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    @if(request('type') == 'sale')
                        Sales History (Penjualan)
                    @elseif(request('type') == 'purchase')
                        Purchase History (Pembelian)
                    @else
                        Transaction History
                    @endif
                </h5>
                <div>
                    @if(request('type') != 'purchase')
                     <a href="{{ route('transactions.create', ['type' => 'sale']) }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> New Sale
                    </a>
                    @endif
                    @if(request('type') != 'sale')
                    <a href="{{ route('transactions.create', ['type' => 'purchase']) }}" class="btn btn-info add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> New Purchase
                    </a>
                    @endif
                </div>
            </div>
            <div class="card-body border-bottom-dashed border-bottom">
                <form method="GET" action="{{ route('transactions.index') }}">
                    <div class="row g-3">
                        <div class="col-xl-3 col-sm-6">
                            <div class="search-box">
                                <input type="text" class="form-control search flatpickr-date" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date (dd-mm-yyyy)">
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="search-box">
                                <input type="text" class="form-control search flatpickr-date" name="end_date" value="{{ request('end_date') }}" placeholder="End Date (dd-mm-yyyy)">
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="search-box">
                                <select class="form-control" name="customer_id">
                                    <option value="">
                                        @if(request('type') == 'sale')
                                            Filter Customer
                                        @elseif(request('type') == 'purchase')
                                            Filter Supplier
                                        @else
                                            All Customers/Suppliers
                                        @endif
                                    </option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div>
                                <button type="submit" class="btn btn-primary w-100"> <i class="ri-equalizer-fill me-1 align-bottom"></i> Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Customer/Supplier</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date->format('d-m-Y') }}</td>
                                <td>
                                    @if($transaction->type === 'sale')
                                        <span class="badge bg-success-subtle text-success text-uppercase">Sale</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary text-uppercase">Purchase</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->customer->name ?? '-' }}</td>
                                <td>
                                    <span class="fw-semibold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @if($transaction->debt)
                                        @if($transaction->debt->status === 'paid')
                                            <span class="badge bg-success-subtle text-success text-uppercase">Paid</span>
                                        @elseif($transaction->debt->status === 'partial')
                                            <span class="badge bg-warning-subtle text-warning text-uppercase">Partial</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger text-uppercase">Unpaid</span>
                                        @endif
                                    @else
                                        <span class="badge bg-success-subtle text-success text-uppercase">Paid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-sm btn-primary">
                                            <i class="ri-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="btn btn-sm btn-warning">
                                            <i class="ri-printer-fill"></i>
                                        </a>
                                        <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ri-delete-bin-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No transactions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr(".flatpickr-date", {
            dateFormat: "Y-m-d", // Format sent to server
            altInput: true,
            altFormat: "d-m-Y", // Format displayed to user
            allowInput: true
        });
    });
</script>
@endsection
