@extends('layouts.velzon')

@section('title', 'Tambah Entri Buku Penjualan Manual')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Entri Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Tipe</label>
                            <div class="d-flex flex-column gap-2 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeInitial" value="initial" {{ old('type', 'initial') == 'initial' ? 'checked' : '' }} onchange="toggleFields()">
                                    <label class="form-check-label" for="typeInitial">
                                        Saldo Awal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typePrice" value="purchase" {{ old('type') == 'purchase' ? 'checked' : '' }} onchange="toggleFields()" required>
                                    <label class="form-check-label" for="typePrice">
                                        Pembelian (Masuk)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeSale" value="sale" {{ old('type') == 'sale' ? 'checked' : '' }} onchange="toggleFields()">
                                    <label class="form-check-label" for="typeSale">
                                        Penjualan (Keluar) - Rp Saja
                                    </label>
                                </div>
                                <div class="form-check ms-4">
                                    <input class="form-check-input" type="radio" name="type" id="typeSaleItem" value="sale_item" {{ old('type') == 'sale_item' ? 'checked' : '' }} onchange="toggleFields()">
                                    <label class="form-check-label" for="typeSaleItem">
                                        Keluar Barang (Input Jumlah & Harga Satuan)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase / Sale Item Fields -->
                        <div id="purchase_fields" style="display: none;">
                            <div class="col-12 mb-3">
                                <label for="item_name" class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" value="{{ old('item_name') }}" list="existingItemsList" autocomplete="off" placeholder="Pilih atau ketik nama barang...">
                                <datalist id="existingItemsList">
                                    @foreach($existingItems as $itemName)
                                        <option value="{{ $itemName }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_display" class="form-label">Jumlah</label>
                                    <input type="text" class="form-control" id="quantity_display" placeholder="0" onkeyup="updateInput(this, 'quantity')">
                                    <input type="hidden" id="quantity" name="quantity" value="{{ old('quantity') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="unit_price_display" class="form-label">Harga Satuan</label>
                                    <input type="text" class="form-control" id="unit_price_display" placeholder="Rp 0" onkeyup="updateInput(this, 'unit_price')">
                                    <input type="hidden" id="unit_price" name="unit_price" value="{{ old('unit_price') }}">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Total Amount</label>
                                <div class="fs-4 fw-bold text-success" id="purchase_total_display">Rp 0</div>
                            </div>
                        </div>

                        <!-- Direct Amount Field (Sale/Initial) -->
                        <div id="amount_field" style="display: none;">
                            <div class="col-12 mb-3">
                                <label for="amount_display" class="form-label" id="amount_label">Jumlah (Rp)</label>
                                <input type="text" class="form-control" id="amount_display" placeholder="Rp 0" onkeyup="updateInput(this, 'amount')">
                                <input type="hidden" id="amount" name="amount" value="{{ old('amount') }}">
                            </div>
                        </div>

                        <div class="col-12 text-end mt-3">
                            <a href="{{ route('inventory.index') }}" class="btn btn-light me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        const typeInput = document.querySelector('input[name="type"]:checked');
        const type = typeInput ? typeInput.value : 'initial';
        const purchaseFields = document.getElementById('purchase_fields');
        const amountField = document.getElementById('amount_field');
        const amountLabel = document.getElementById('amount_label');

        // Hide all fields first
        purchaseFields.style.display = 'none';
        amountField.style.display = 'none';

        if (type === 'purchase' || type === 'sale_item') {
            purchaseFields.style.display = 'block';
            let colorClass = type === 'purchase' ? 'text-success' : 'text-danger';
            document.getElementById('purchase_total_display').className = 'fs-4 fw-bold ' + colorClass;
        } else {
            amountField.style.display = 'block';
            if (type === 'sale') {
                amountLabel.innerText = 'Jumlah Penjualan (Keluar) - Rp';
            } else {
                amountLabel.innerText = 'Jumlah Saldo Awal - Rp';
            }
        }
    }

    function updateInput(element, hiddenId) {
        // 1. Get raw value (digits only)
        let value = element.value.replace(/[^0-9]/g, '');
        
        // 2. Update hidden input mapping for quantity/price
        // because we share the same name attribute, let's update a corresponding main hidden input based on what's active.
        let targetId = document.getElementById(hiddenId);
        if (targetId) {
            targetId.value = value;
        } else {
            // Find hidden input within same group if possible
             let h = element.parentNode.querySelector('input[type="hidden"]');
             if(h) h.value = value;
             else {
                 // Or match the hiddenId in DOM directly if we created it globally above
                 let el = document.getElementById(hiddenId);
                 if(el) el.value = value;
             }
        }
        
        // 3. Format display (Thousands separator)
        if (value) {
            element.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            element.value = '';
        }

        // 4. Trigger Total Calc if needed
        if (hiddenId === 'quantity' || hiddenId === 'unit_price') {
            calculateTotal();
        }
    }

    function calculateTotal() {
        const qty = parseFloat(document.getElementById('quantity').value) || 0;
        const price = parseFloat(document.getElementById('unit_price').value) || 0;
        const total = qty * price;
        document.getElementById('purchase_total_display').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
    }

    // Run on load
    toggleFields();
</script>
@endsection
