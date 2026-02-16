@extends('layouts.velzon')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Dashboard</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboards</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>

        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-3 col-md-6">
        <!-- card -->
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Total Products</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ \App\Models\Product::count() }}</h4>
                        <a href="{{ route('products.index') }}" class="text-decoration-underline text-muted">View all products</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-soft-success rounded fs-3">
                            <i class="ri-shopping-bag-3-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->

    <div class="col-xl-3 col-md-6">
        <!-- card -->
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Sales Today</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        {{-- Placeholder logic for sales today --}}
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">{{ \App\Models\Transaction::whereDate('created_at', today())->where('type', 'sale')->count() }}</h4>
                        <a href="{{ route('transactions.index') }}" class="text-decoration-underline text-muted">View sales</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-soft-info rounded fs-3">
                            <i class="ri-shopping-cart-2-line text-info"></i>
                        </span>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->
    
     <div class="col-xl-3 col-md-6">
        <!-- card -->
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Total Debt (Piutang)</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            Rp {{ number_format(\App\Models\Debt::where('status', '!=', 'paid')->get()->sum(fn($debt) => $debt->amount_total - $debt->amount_paid), 0, ',', '.') }}
                        </h4>
                        <a href="{{ route('debts.index') }}" class="text-decoration-underline text-muted">View debts</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-soft-warning rounded fs-3">
                            <i class="ri-money-dollar-circle-line text-warning"></i>
                        </span>
                    </div>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->

</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Quick Actions</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="ri-add-line align-bottom me-1"></i> Add Product
                    </a>
                    <a href="{{ route('transactions.create', ['type' => 'sale']) }}" class="btn btn-success">
                        <i class="ri-shopping-cart-2-line align-bottom me-1"></i> New Sale
                    </a>
                    <a href="{{ route('transactions.create', ['type' => 'purchase']) }}" class="btn btn-info">
                        <i class="ri-download-2-line align-bottom me-1"></i> New Purchase
                    </a>
                     <a href="{{ route('debts.index') }}" class="btn btn-warning">
                        <i class="ri-money-dollar-circle-line align-bottom me-1"></i> Record Payment
                    </a>
                </div>
            </div><!-- end card body -->
        </div><!-- end card -->
    </div><!-- end col -->
</div>
@endsection
