@extends('layouts.velzon')

@section('title', 'Edit Cabang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Cabang</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('branches.update', $branch->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Nama Cabang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $branch->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="address" class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" required>{{ old('address', $branch->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <label for="phone" class="form-label">No. Telepon / HP</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 text-end mt-4">
                            <a href="{{ route('branches.index') }}" class="btn btn-light me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Perbarui Cabang</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
