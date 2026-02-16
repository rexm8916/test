@extends('layouts.velzon')

@section('title', 'Transaction Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Transaction Details</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Transaction #{{ $transaction->id }}</h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="btn btn-soft-primary btn-sm">
                        <i class="ri-printer-line align-bottom me-1"></i> Print Receipt
                    </a>
                     <span class="badge {{ $transaction->type === 'sale' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} fs-12">
                        {{ ucfirst($transaction->type) }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary fs-12">
                        {{ $transaction->transaction_date->format('d M Y') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3">Customer / Supplier</h6>
                        <p class="fw-medium mb-2">{{ $transaction->customer->name ?? 'General' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <h6 class="text-muted text-uppercase fw-semibold mb-3">Payment Status</h6>
                        @if($transaction->debt)
                            <span class="badge {{ $transaction->debt->status === 'paid' ? 'bg-success' : ($transaction->debt->status === 'partial' ? 'bg-warning' : 'bg-danger') }} fs-12">
                                {{ ucfirst($transaction->debt->status) }}
                            </span>
                             <div class="mt-2 text-muted">
                                <p class="mb-1">Total Debt: <span class="fw-semibold">Rp {{ number_format($transaction->debt->amount_total, 0, ',', '.') }}</span></p>
                                <p class="mb-1">Paid: <span class="fw-semibold">Rp {{ number_format($transaction->debt->amount_paid, 0, ',', '.') }}</span></p>
                                <a href="{{ route('debts.show', $transaction->debt->id) }}" class="link-primary text-decoration-underline">View Debt Details</a>
                             </div>
                        @else
                            <span class="badge bg-success fs-12">Fully Paid</span>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-nowrap align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col" class="text-end">Quantity</th>
                                <th scope="col" class="text-end">Price</th>
                                <th scope="col" class="text-end">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->items as $item)
                            <tr>
                                <td class="fw-medium">{{ $item->product->name }}</td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            @if($transaction->discount > 0)
                            <tr class="border-top border-top-dashed">
                                <td colspan="3" class="text-end fw-medium">Subtotal</td>
                                <td class="text-end text-muted">Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-medium text-danger">Discount</td>
                                <td class="text-end text-danger">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr class="border-top border-top-dashed">
                                <td colspan="3" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end fw-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will delete the transaction, revert stock changes, and remove any associated debt records.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-delete-bin-fill align-bottom me-1"></i> Delete Transaction (Void)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
