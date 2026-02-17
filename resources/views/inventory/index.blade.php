@extends('layouts.velzon')

@section('title', 'Inventory Value Ledger')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Inventory Value Ledger</h4>
            <div class="page-title-right">
                <a href="{{ route('inventory.create') }}" class="btn btn-success btn-sm">
                    <i class="ri-add-line align-bottom me-1"></i> Add Entry
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Type</th>
                                <th scope="col">Item Details</th>
                                <th scope="col" class="text-end">Purchase (In)</th>
                                <th scope="col" class="text-end">Sales (Out)</th>
                                <th scope="col" class="text-end">Balance</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledgers as $ledger)
                            <tr>
                                <td>{{ $ledger->date->format('d M Y') }}</td>
                                <td>
                                    @if($ledger->type == 'initial')
                                        <span class="badge bg-info">Initial</span>
                                    @elseif($ledger->type == 'purchase')
                                        <span class="badge bg-success">Purchase</span>
                                    @else
                                        <span class="badge bg-danger">Sale</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ledger->type == 'purchase')
                                        <div class="fw-medium">{{ $ledger->item_name }}</div>
                                        <div class="text-muted fs-12">{{ $ledger->quantity }} x Rp {{ number_format($ledger->unit_price, 0, ',', '.') }}</div>
                                    @elseif($ledger->type == 'initial')
                                        Initial Balance
                                    @else
                                        Direct Sales Input
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
                                <td colspan="6" class="text-center">No inventory records found.</td>
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
