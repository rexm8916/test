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
                <h5 class="card-title mb-0 flex-grow-1">Transaction History</h5>
                <div>
                     <a href="{{ route('transactions.create', ['type' => 'sale']) }}" class="btn btn-success add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> New Sale
                    </a>
                    <a href="{{ route('transactions.create', ['type' => 'purchase']) }}" class="btn btn-info add-btn">
                        <i class="ri-add-line align-bottom me-1"></i> New Purchase
                    </a>
                </div>
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
                                <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
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
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a href="{{ route('transactions.show', $transaction->id) }}" class="dropdown-item"><i class="ri-eye-fill align-bottom me-2 text-muted"></i> View</a></li>
                                            <li>
                                                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item remove-item-btn">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
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
