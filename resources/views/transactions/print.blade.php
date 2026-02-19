<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi #{{ $transaction->id }} - Cetak</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 5px;
            width: 58mm;
            color: #000;
        }
        .container {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .store-address {
            font-size: 10px;
            margin: 2px 0;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .details {
            font-size: 10px;
            margin-bottom: 5px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 11px;
        }
        .item-name {
            flex: 1;
            padding-right: 5px;
        }
        .item-price {
            text-align: right;
            white-space: nowrap;
        }
        .totals {
            margin-top: 10px;
            font-size: 11px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 12px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                width: auto;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header">
            <h1 class="store-name">Toko Beras Rizki Mandiri</h1>
            <p class="store-address">Jl Wangunsari RT 03 RW 03, Kecamatan Lembang</p>
            <p class="store-address">Kabupaten Bandung Barat, Jawa Barat 40391</p>
            <p class="store-address">Telp/WA: 085659145523</p>
        </div>

        <div class="divider"></div>

        <div class="details">
            <div>Tanggal: {{ $transaction->transaction_date->format('d/m/Y H:i') }}</div>
            <div>ID Trx: #{{ $transaction->id }}</div>
            <div>Plgn: {{ $transaction->customer->name ?? 'Umum' }}</div>
        </div>

        <div class="divider"></div>

        <div class="items">
            @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $item->product->name }}<br>
                    @if($item->discount > 0)
                        <small>{{ $item->quantity }} x <span style="text-decoration: line-through;">{{ number_format($item->price, 0, ',', '.') }}</span> {{ number_format($item->price - $item->discount, 0, ',', '.') }}</small>
                    @else
                        <small>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</small>
                    @endif
                </div>
                <div class="item-price">
                    {{ number_format($item->subtotal, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="totals">
        <div class="totals">
            <div class="item-row">
                <div>Sub Total</div>
                <div>Rp {{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</div>
            </div>
            <div class="item-row">
                <div>Diskon</div>
                <div>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</div>
            </div>
            
            <div class="total-row" style="margin-top: 5px; border-top: 1px dashed #333; padding-top: 5px;">
                <div>Total Tagihan</div>
                <div>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
            </div>

            @if($transaction->debt)
            <div class="item-row" style="margin-top: 5px;">
                <div>Jumlah Dibayar</div>
                <div>Rp {{ number_format($transaction->debt->amount_paid, 0, ',', '.') }}</div>
            </div>
            <div class="item-row">
                <div>Kembalian</div>
                <div>Rp {{ number_format(max(0, $transaction->debt->amount_paid - $transaction->debt->amount_total), 0, ',', '.') }}</div>
            </div>
            @if($transaction->debt->status !== 'paid')
            <div class="item-row">
                <div>Sisa</div>
                <div>Rp {{ number_format($transaction->debt->amount_total - $transaction->debt->amount_paid, 0, ',', '.') }}</div>
            </div>
            @endif
            @endif
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p>Terima Kasih Telah Berbelanja Di Toko Kami</p>
        </div>

        <button class="no-print" onclick="window.print()" style="display: block; width: 100%; padding: 10px; margin-top: 20px; cursor: pointer;">Cetak Lagi</button>
        <button class="no-print" id="shareBtn" onclick="shareReceipt()" style="display: block; width: 100%; padding: 10px; margin-top: 10px; cursor: pointer; background-color: #25D366; color: white; border: none;">Download/Share Struk (Image)</button>
        <a href="{{ route('transactions.show', $transaction->id) }}" class="no-print" style="display: block; text-align: center; margin-top: 10px; text-decoration: none; color: blue;">Kembali</a>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function shareReceipt() {
             const shareBtn = document.getElementById('shareBtn');
             const originalText = shareBtn.innerText;
             shareBtn.innerText = 'Generating...';
             shareBtn.disabled = true;

             // Select the body to capture (includes padding)
             const element = document.body;
             
             // Use html2canvas to generate the image
             html2canvas(element, {
                 scale: 2, // Higher scale for better quality
                 backgroundColor: '#ffffff',
                 ignoreElements: (node) => {
                     return node.classList && node.classList.contains('no-print');
                 },
                 onclone: (clonedDoc) => {
                     // Add extra padding to the cloned body for better aesthetics
                     clonedDoc.body.style.padding = '25px';
                     clonedDoc.body.style.width = 'auto'; // Allow width to adjust
                     clonedDoc.body.style.maxWidth = '65mm'; // Constrain slightly so it doesn't get too wide
                     clonedDoc.body.style.margin = '0 auto';
                 }
             }).then(canvas => {
                 canvas.toBlob(blob => {
                     if (navigator.share && navigator.canShare && navigator.canShare({ files: [new File([blob], 'struk.png', { type: blob.type })] })) {
                        // Use Web Share API if supported
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
                         // Fallback to download
                         downloadImage(canvas);
                     }
                     
                     shareBtn.innerText = originalText;
                     shareBtn.disabled = false;
                 });
             }).catch(err => {
                 console.error('Canvas generation failed:', err);
                 alert('Gagal membuat gambar struk via html2canvas');
                 shareBtn.innerText = originalText;
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
</body>
</html>
