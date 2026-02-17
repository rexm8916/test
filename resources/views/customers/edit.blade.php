@extends('layouts.velzon')

@section('title', 'Edit Customer')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Edit Customer</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('customers.update', $customer->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ $customer->name }}" required placeholder="Enter customer name">
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Info</label>
                        <input type="text" name="contact" id="contact" class="form-control" value="{{ $customer->contact }}" placeholder="Enter phone number or email">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3" placeholder="Enter address">{{ $customer->address }}</textarea>
                    </div>
                    <!-- Hidden Type field to prevent validation error if not present in form but required in controller -->
                    <input type="hidden" name="type" value="{{ $customer->type ?? 'customer' }}">

                    <div class="d-flex align-items-center justify-content-end mt-4">
                        <a href="{{ route('customers.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
