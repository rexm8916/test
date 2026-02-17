<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction #{{ $transaction->id }} - Print</title>
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
            <div>Date: {{ $transaction->transaction_date->format('d/m/Y H:i') }}</div>
            <div>Trx ID: #{{ $transaction->id }}</div>
            <div>Cust: {{ $transaction->customer->name ?? 'General' }}</div>
        </div>

        <div class="divider"></div>

        <div class="items">
            @foreach($transaction->items as $item)
            <div class="item-row">
                <div class="item-name">
                    {{ $item->product->name }}<br>
                    <small>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</small>
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
                <div>Discount</div>
                <div>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</div>
            </div>
            
            <div class="total-row" style="margin-top: 5px; border-top: 1px dashed #333; padding-top: 5px;">
                <div>Total Amount</div>
                <div>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
            </div>

            @if($transaction->debt)
            <div class="item-row" style="margin-top: 5px;">
                <div>Amount Paid (Bayar)</div>
                <div>Rp {{ number_format($transaction->debt->amount_paid, 0, ',', '.') }}</div>
            </div>
            <div class="item-row">
                <div>Change (Kembalian)</div>
                <div>Rp {{ number_format(max(0, $transaction->debt->amount_paid - $transaction->debt->amount_total), 0, ',', '.') }}</div>
            </div>
            @if($transaction->debt->status !== 'paid')
            <div class="item-row">
                <div>Remaining</div>
                <div>Rp {{ number_format($transaction->debt->amount_total - $transaction->debt->amount_paid, 0, ',', '.') }}</div>
            </div>
            @endif
            @endif
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p>Terima Kasih Telah Berbelanja Di Toko Kami</p>
        </div>

        <button class="no-print" onclick="window.print()" style="display: block; width: 100%; padding: 10px; margin-top: 20px; cursor: pointer;">Print Again</button>
        <a href="{{ route('transactions.show', $transaction->id) }}" class="no-print" style="display: block; text-align: center; margin-top: 10px; text-decoration: none; color: blue;">Back</a>
    </div>
</body>
</html>
