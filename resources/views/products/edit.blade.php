@extends('layouts.velzon')

@section('title', 'Edit Product')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Edit Product</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
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
                <h5 class="card-title mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" name="name" id="name" value="{{ $product->name }}" class="form-control" required placeholder="Enter product name">
                    </div>
                    
                    <div class="mb-3">
                         <label for="stock" class="form-label">Current Stock</label>
                        <input type="number" name="stock" id="stock" value="{{ $product->stock }}" class="form-control" required placeholder="Enter stock quantity">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="buy_price" class="form-label">Buy Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="buy_price" id="buy_price" value="{{ $product->buy_price }}" class="form-control" required placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="sell_price" class="form-label">Sell Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="sell_price" id="sell_price" value="{{ $product->sell_price }}" class="form-control" required placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-end mt-4">
                        <a href="{{ route('products.index') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
