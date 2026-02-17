@extends('layouts.velzon')

@section('title', 'Debts (Piutang)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Accounts Receivable (Debts)</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Debts</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Debt List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Transaction Date</th>
                                <th>Total Amount</th>
                                <th>Amount Paid</th>
                                <th>Remaining</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debts as $debt)
                            <tr>
                                <td>{{ $debt->transaction->customer->name ?? 'Unknown' }}</td>
                                <td>{{ $debt->created_at->format('d M Y') }}</td>
                                <td>Rp {{ number_format($debt->amount_total, 0, ',', '.') }}</td>
                                <td class="text-success">Rp {{ number_format($debt->amount_paid, 0, ',', '.') }}</td>
                                <td class="text-danger fw-bold">Rp {{ number_format($debt->amount_total - $debt->amount_paid, 0, ',', '.') }}</td>
                                <td>
                                    @if($debt->status === 'paid')
                                        <span class="badge bg-success-subtle text-success text-uppercase">Paid</span>
                                    @elseif($debt->status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning text-uppercase">Partial</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger text-uppercase">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-sm btn-primary">
                                            Details & Pay
                                        </a>
                                        <form action="{{ route('debts.destroy', $debt->id) }}" method="POST" class="delete-form">
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
                                <td colspan="7" class="text-center">No debts found.</td>
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
