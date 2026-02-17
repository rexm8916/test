<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction #{{ $transaction->id }} - Print</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 300px; /* Thermal printer width approx */
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .store-name {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }
        .store-sub {
            font-size: 16px;
            margin: 5px 0;
        }
        .store-address {
            font-size: 12px;
            margin: 0;
        }
        .divider {
            border-top: 1px dashed #333;
            margin: 10px 0;
        }
        .details {
            margin-bottom: 10px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .item-name {
            flex: 1;
        }
        .item-price {
            text-align: right;
        }
        .totals {
            margin-top: 15px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none;
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
            @if($transaction->discount > 0)
            <div class="item-row">
                <div>Subtotal</div>
                <div>{{ number_format($transaction->total_amount + $transaction->discount, 0, ',', '.') }}</div>
            </div>
            <div class="item-row">
                <div>Discount</div>
                <div>-{{ number_format($transaction->discount, 0, ',', '.') }}</div>
            </div>
            @endif
            <div class="total-row">
                <div>TOTAL</div>
                <div>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</div>
            </div>
            @if($transaction->debt)
            <div class="item-row" style="margin-top: 5px;">
                <div>Paid</div>
                <div>{{ number_format($transaction->debt->amount_paid, 0, ',', '.') }}</div>
            </div>
            <div class="item-row">
                <div>Unpaid</div>
                <div>{{ number_format($transaction->debt->amount_total - $transaction->debt->amount_paid, 0, ',', '.') }}</div>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p>terimakasih telah belanja di toko kami</p>
        </div>

        <button class="no-print" onclick="window.print()" style="display: block; width: 100%; padding: 10px; margin-top: 20px; cursor: pointer;">Print Again</button>
        <a href="{{ route('transactions.show', $transaction->id) }}" class="no-print" style="display: block; text-align: center; margin-top: 10px; text-decoration: none; color: blue;">Back</a>
    </div>
</body>
</html>
