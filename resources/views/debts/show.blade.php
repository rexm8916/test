@extends('layouts.velzon')

@section('title', 'Debt Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Debt Details</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('debts.index') }}">Debts</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaction Information</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th class="ps-0" scope="row">Customer</th>
                                <td class="text-muted">{{ $debt->transaction->customer->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Transaction Date</th>
                                <td class="text-muted">{{ $debt->transaction->transaction_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Total Purchase</th>
                                <td class="text-success fw-bold">Rp {{ number_format($debt->amount_total, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <h6 class="fs-14 mb-3">Items Purchased</h6>
                    <div class="table-responsive">
                         <table class="table table-bordered table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($debt->transaction->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }} <span class="text-muted">(x{{ $item->quantity }})</span></td>
                                    <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                     <table class="table table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debt->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="text-success fw-semibold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td>{{ $payment->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No payments recorded yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-success-subtle">
                <h5 class="card-title mb-0 text-success">Record Payment</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p class="text-muted mb-1 text-uppercase fw-semibold fs-12">Remaining Debt</p>
                    <h3 class="fw-bold text-danger">Rp {{ number_format($debt->amount_total - $debt->amount_paid, 0, ',', '.') }}</h3>
                </div>

                @if($debt->status !== 'paid')
                <form action="{{ route('debts.payment.store', $debt->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (Rp)</label>
                        <input type="number" name="amount" id="amount" max="{{ $debt->amount_total - $debt->amount_paid }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="ri-money-dollar-circle-line align-bottom me-1"></i> Submit Payment
                    </button>
                </form>
                @else
                <div class="alert alert-success mb-0" role="alert">
                    <i class="ri-check-double-line me-1 align-bottom"></i> This debt is fully paid!
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
