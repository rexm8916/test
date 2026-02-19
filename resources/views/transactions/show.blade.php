@extends('layouts.velzon')

@section('title', 'Transaction Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Detail Transaksi</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transaksi</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Transaksi #{{ $transaction->id }}</h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('transactions.index') }}" class="btn btn-soft-secondary btn-sm">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> Kembali ke Daftar
                    </a>
                    <a href="{{ route('transactions.print', $transaction->id) }}" target="_blank" class="btn btn-soft-primary btn-sm">
                        <i class="ri-printer-line align-bottom me-1"></i> Cetak Struk
                    </a>
                    <button type="button" onclick="shareReceipt()" class="btn btn-soft-success btn-sm">
                        <i class="ri-whatsapp-line align-bottom me-1"></i> Share Struk
                    </button>
                    @if($transaction->debt)
                    <a href="{{ route('debts.show', $transaction->debt->id) }}" class="btn btn-soft-info btn-sm">
                        <i class="ri-history-line align-bottom me-1"></i> Lihat Riwayat Hutang
                    </a>
                    @endif
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
                        <h6 class="text-muted text-uppercase fw-semibold mb-3">Pelanggan / Pemasok</h6>
                        <p class="fw-medium mb-2">{{ $transaction->customer->name ?? 'Umum' }}</p>
                    </div>
                    <div class="col-sm-6">
                         <h6 class="text-muted text-uppercase fw-semibold mb-3">Status Pembayaran</h6>
                        @if($transaction->debt)
                            <span class="badge {{ $transaction->debt->status === 'paid' ? 'bg-success' : ($transaction->debt->status === 'partial' ? 'bg-warning' : 'bg-danger') }} fs-12">
                                {{ ucfirst($transaction->debt->status) }}
                            </span>
                             <div class="mt-2 text-muted">
                                @if($transaction->debt->status !== 'paid')
                                     <a href="{{ route('debts.show', $transaction->debt->id) }}" class="link-primary text-decoration-underline">Lihat Detail Hutang</a>
                                @endif
                             </div>
                        @else
                            <span class="badge bg-success fs-12">Lunas</span>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-borderless table-nowrap align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th scope="col">Produk</th>
                                <th scope="col" class="text-end">Jumlah</th>
                                <th scope="col" class="text-end">Harga</th>
                                <th scope="col" class="text-end">Total Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->items as $item)
                            <tr>
                                <td class="fw-medium">
                                    {{ $item->product->name }}
                                    @if($item->discount > 0)
                                        <br><small class="text-danger">Disc: Rp {{ number_format($item->discount, 0, ',', '.') }}</small>
                                    @endif
                                </td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end">
                                    @if($item->discount > 0)
                                        <span class="text-decoration-line-through text-muted small">Rp {{ number_format($item->price, 0, ',', '.') }}</span><br>
                                        Rp {{ number_format($item->price - $item->discount, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($item->price, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                            <tr class="border-top border-top-dashed">
                                <td colspan="3" class="text-end fw-medium">Sub Total</td>
                                <td class="text-end text-muted">Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-medium text-danger">Diskon</td>
                                <td class="text-end text-danger">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="border-top border-top-dashed">
                                <td colspan="3" class="text-end fw-bold">Total Jumlah</td>
                                <td class="text-end fw-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            @if($transaction->debt)
                            <tr>
                                <td colspan="3" class="text-end fw-medium">Jumlah Dibayar</td>
                                <td class="text-end fw-medium">Rp {{ number_format($transaction->debt->amount_paid, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-medium text-success">Kembalian</td>
                                <td class="text-end fw-bold text-success">Rp {{ number_format(max(0, $transaction->debt->amount_paid - $transaction->debt->amount_total), 0, ',', '.') }}</td>
                            </tr>
                            @if($transaction->debt->status !== 'paid')
                            <tr>
                                <td colspan="3" class="text-end fw-medium text-danger">Sisa</td>
                                <td class="text-end fw-bold text-danger">Rp {{ number_format($transaction->debt->amount_total - $transaction->debt->amount_paid, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin? Ini akan menghapus transaksi, mengembalikan perubahan stok, dan menghapus catatan hutang terkait.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-delete-bin-fill align-bottom me-1"></i> Hapus Transaksi (Batalkan)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function shareReceipt() {
        const shareBtn = document.querySelector('button[onclick="shareReceipt()"]');
        const originalText = shareBtn.innerHTML;
        shareBtn.innerHTML = '<i class="ri-loader-4-line align-bottom me-1"></i> Generating...';
        shareBtn.disabled = true;

        // Make the receipt visible for capture (off-screen)
        const receipt = document.getElementById('receipt-container');
        receipt.style.display = 'block';

        html2canvas(receipt, {
            scale: 2,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            canvas.toBlob(blob => {
                 if (navigator.share && navigator.canShare && navigator.canShare({ files: [new File([blob], 'struk.png', { type: blob.type })] })) {
                    const file = new File([blob], 'struk-{{ $transaction->id }}.png', { type: 'image/png' });
                    navigator.share({
                        files: [file],
                        title: 'Struk Transaksi #{{ $transaction->id }}',
                        text: 'Berikut adalah struk transaksi Anda.'
                    }).catch(err => {
                        console.error('Share failed:', err);
                        downloadImage(canvas);
                    });
                 } else {
                     downloadImage(canvas);
                 }
                 
                 // Hide receipt again
                 receipt.style.display = 'none';
                 shareBtn.innerHTML = originalText;
                 shareBtn.disabled = false;
            });
        }).catch(err => {
             console.error('Canvas generation failed:', err);
             alert('Gagal membuat gambar struk via html2canvas');
             receipt.style.display = 'none';
             shareBtn.innerHTML = originalText;
             shareBtn.disabled = false;
        });
    }

    function downloadImage(canvas) {
        const link = document.createElement('a');
        link.download = 'struk-{{ $transaction->id }}.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        alert('Gambar struk telah diunduh. Silakan kirim melalui WhatsApp.');
    }
</script>
@endsection

<!-- Hidden Receipt Container for Catching -->
<div id="receipt-container" style="display: none; position: absolute; top: 0; left: -9999px; width: 58mm; padding: 25px; background: white; color: black; font-family: 'Courier New', Courier, monospace; font-size: 12px; border: 1px solid #ddd; border-radius: 5px;">
    <div style="text-align: center; margin-bottom: 10px;">
        <h1 style="font-size: 16px; font-weight: bold; margin: 0; text-transform: uppercase;">Toko Beras Rizki Mandiri</h1>
        <p style="font-size: 10px; margin: 2px 0;">Jl Wangunsari RT 03 RW 03, Kecamatan Lembang</p>
        <p style="font-size: 10px; margin: 2px 0;">Kabupaten Bandung Barat, Jawa Barat 40391</p>
        <p style="font-size: 10px; margin: 2px 0;">Telp/WA: 085659145523</p>
    </div>
    <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
    <div style="font-size: 10px; margin-bottom: 5px;">
        <div>Tanggal: {{ $transaction->transaction_date->format('d/m/Y H:i') }}</div>
        <div>ID Trx: #{{ $transaction->id }}</div>
        <div>Plgn: {{ $transaction->customer->name ?? 'Umum' }}</div>
    </div>
    <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
    <div>
        @foreach($transaction->items as $item)
        <div style="display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 11px;">
            <div style="flex: 1; padding-right: 5px;">
                {{ $item->product->name }}<br>
                @if($item->discount > 0)
                    <small>{{ $item->quantity }} x <span style="text-decoration: line-through;">{{ number_format($item->price, 0, ',', '.') }}</span> {{ number_format($item->price - $item->discount, 0, ',', '.') }}</small>
                @else
                    <small>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</small>
                @endif
            </div>
            <div style="text-align: right; white-space: nowrap;">
                {{ number_format($item->subtotal, 0, ',', '.') }}
            </div>
        </div>
        @endforeach
    </div>
    <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
    <div style="margin-top: 10px; font-size: 11px;">
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px;">
            <div>Sub Total</div>
            <div>Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</div>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px;">
            <div>Diskon</div>
            <div>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</div>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 5px; border-top: 1px dashed #333; padding-top: 5px;">
            <div>Total Tagihan</div>
            <div>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
        </div>
        @if($transaction->debt)
        <div style="display: flex; justify-content: space-between; margin-top: 5px;">
            <div>Jumlah Dibayar</div>
            <div>Rp {{ number_format($transaction->debt->amount_paid, 0, ',', '.') }}</div>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <div>Kembalian</div>
            <div>Rp {{ number_format(max(0, $transaction->debt->amount_paid - $transaction->debt->amount_total), 0, ',', '.') }}</div>
        </div>
        @if($transaction->debt->status !== 'paid')
        <div style="display: flex; justify-content: space-between;">
            <div>Sisa</div>
            <div>Rp {{ number_format($transaction->debt->amount_total - $transaction->debt->amount_paid, 0, ',', '.') }}</div>
        </div>
        @endif
        @endif
    </div>
    <div style="border-top: 1px dashed #000; margin: 5px 0;"></div>
    <div style="text-align: center; margin-top: 15px; font-size: 10px;">
        <p>Terima Kasih Telah Berbelanja Di Toko Kami</p>
    </div>
</div>
@endsection
