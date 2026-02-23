@extends('layouts.velzon')

@section('title', 'Manajemen Cabang')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Manajemen Cabang</h4>
            <div class="page-title-right">
                <a href="{{ route('branches.create') }}" class="btn btn-success btn-sm">
                    <i class="ri-add-line align-bottom me-1"></i> Tambah Cabang
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nama Cabang</th>
                                <th scope="col">Alamat</th>
                                <th scope="col">No Telepon</th>
                                <th scope="col" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                            <tr>
                                <td>{{ $branch->name }}</td>
                                <td>{{ $branch->address }}</td>
                                <td>{{ $branch->phone ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-sm btn-soft-info">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-soft-danger" data-bs-toggle="modal" data-bs-target="#deleteBranchModal{{ $branch->id }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade text-start" id="deleteBranchModal{{ $branch->id }}" tabindex="-1" aria-labelledby="deleteBranchModalLabel{{ $branch->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteBranchModalLabel{{ $branch->id }}">Konfirmasi Hapus Cabang</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Apakah Anda yakin ingin menghapus cabang <strong>{{ $branch->name }}</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <form action="{{ route('branches.destroy', $branch->id) }}" method="POST" class="d-inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Ya, Hapus Cabang</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada data cabang terdaftar.</td>
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
