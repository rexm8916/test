@extends('layouts.velzon')

@section('title', 'Ubah Entri Buku Penjualan Manual')
@section('title', 'Ubah Entri Inventaris')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ubah Entri</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.update', $ledger->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ old('date', $ledger->date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            @php
                                $currentType = old('type', $ledger->type);
                                if ($currentType == 'sale') {
                                    if ($ledger->item_name && $ledger->unit_price) {
                                        $currentType = 'sale_item';
                                    }
                                }
                            @endphp

                            <label for="type" class="form-label">Tipe</label>
                            <div class="d-flex flex-column gap-2 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeInitial" value="initial" {{ $currentType == 'initial' ? 'checked' : '' }} onchange="toggleFields()">
                                    <label class="form-check-label" for="typeInitial">
                                        Saldo Awal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typePrice" value="purchase" {{ $currentType == 'purchase' ? 'checked' : '' }} onchange="toggleFields()" required>
                                    <label class="form-check-label" for="typePrice">
                                        Pembelian (Masuk)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeSale" value="sale" {{ $currentType == 'sale' ? 'checked' : '' }} onchange="toggleFields()">
                                    <label class="form-check-label" for="typeSale">
                                        Penjualan (Keluar) - Rp Saja
                                    </label>
                                </div>
                                <div class="form-check ms-4">
                                    <input class="form-check-input" type="radio" name="type" id="typeSaleItem" value="sale_item" {{ $currentType == 'sale_item' ? 'checked' : '' }} onchange="toggleFields()">
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
                                <select class="form-select select2-item-name" id="item_name" name="item_name" style="width: 100%;">
                                    <option value="">Pilih atau ketik nama barang...</option>
                                    @php $itemFound = false; @endphp
                                    @foreach($existingItems as $itemName)
                                        <option value="{{ $itemName }}" {{ old('item_name', $ledger->item_name) == $itemName ? 'selected' : '' }}>{{ $itemName }}</option>
                                        @if(old('item_name', $ledger->item_name) == $itemName)
                                            @php $itemFound = true; @endphp
                                        @endif
                                    @endforeach
                                    {{-- If the ledger's item name is not in the general distinct list for some reason, ensure it still shows up selected --}}
                                    @if($ledger->item_name && !$itemFound)
                                        <option value="{{ $ledger->item_name }}" selected>{{ $ledger->item_name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_display" class="form-label">Jumlah</label>
                                    <input type="text" class="form-control @error('quantity') is-invalid @enderror" id="quantity_display" value="{{ old('quantity', $ledger->quantity ? number_format($ledger->quantity, 0, ',', '.') : '') }}" placeholder="0" onkeyup="updateInput(this, 'quantity')">
                                    <input type="hidden" id="quantity" name="quantity" value="{{ old('quantity', $ledger->quantity) }}">
                                    @error('quantity')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="unit_price_display" class="form-label">Harga Satuan</label>
                                    <input type="text" class="form-control @error('unit_price') is-invalid @enderror" id="unit_price_display" value="{{ old('unit_price', $ledger->unit_price ? number_format($ledger->unit_price, 0, ',', '.') : '') }}" placeholder="Rp 0" onkeyup="updateInput(this, 'unit_price')">
                                    <input type="hidden" id="unit_price" name="unit_price" value="{{ old('unit_price', $ledger->unit_price) }}">
                                    @error('unit_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
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
                                <input type="text" class="form-control" id="amount_display" value="{{ old('amount', ($currentType == 'initial' || $currentType == 'sale') && $ledger->amount ? number_format($ledger->amount, 0, ',', '.') : '') }}" placeholder="Rp 0" onkeyup="updateInput(this, 'amount')">
                                <input type="hidden" id="amount" name="amount" value="{{ old('amount', $ledger->amount) }}">
                            </div>
                        </div>

                        <div class="col-12 text-end mt-3">
                            <a href="{{ route('inventory.index') }}" class="btn btn-light me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let maxQuantity = null;
    let minPrice = null;

    // Original data for edit validation
    const originalItemName = "{{ $ledger->item_name }}";
    const originalQuantity = {{ $ledger->quantity ?? 0 }};

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
        
        // Setup existing calculation if relevant
        if (type === 'purchase' || type === 'sale_item') {
            calculateTotal();
        }

        // Fetch validation info
        maxQuantity = null;
        minPrice = null;
        if (type === 'sale_item' && $('#item_name').val()) {
            fetchItemInfo($('#item_name').val());
        }
    }

    function fetchItemInfo(itemName) {
        if (!itemName) return;
        $.ajax({
            url: "{{ route('api.inventory.item_info') }}",
            data: { item_name: itemName },
            success: function(res) {
                // For editing, if we are looking at the same item, 
                // the current stock in DB already *subtracted* our originalQuantity.
                // So the actual max we can change it to is stock + originalQuantity.
                maxQuantity = res.stock;
                if (itemName === originalItemName) {
                    maxQuantity += originalQuantity;
                }
                minPrice = res.base_price;
                validateInputs();
            }
        });
    }

    function validateInputs() {
        const typeInput = document.querySelector('input[name="type"]:checked');
        if (typeInput && typeInput.value === 'sale_item') {
            const qtyInput = document.getElementById('quantity');
            const priceInput = document.getElementById('unit_price');
            const qtyDisplay = document.getElementById('quantity_display');
            const priceDisplay = document.getElementById('unit_price_display');
            
            let qty = parseFloat(qtyInput.value) || 0;
            let price = parseFloat(priceInput.value) || 0;

            // Clear previous errors
            qtyDisplay.classList.remove('is-invalid');
            priceDisplay.classList.remove('is-invalid');
            $('#qty-error').remove();
            $('#price-error').remove();

            let hasError = false;

            if (maxQuantity !== null && qty > maxQuantity) {
                qtyDisplay.classList.add('is-invalid');
                $(qtyDisplay).after('<div id="qty-error" class="invalid-feedback d-block">Stok tidak mencukupi. Maks: ' + maxQuantity + '</div>');
                hasError = true;
            }

            if (minPrice !== null && price > 0 && price < minPrice) {
                priceDisplay.classList.add('is-invalid');
                $(priceDisplay).after('<div id="price-error" class="invalid-feedback d-block">Harga tidak boleh kurang dari harga beli: Rp ' + new Intl.NumberFormat('id-ID').format(minPrice) + '</div>');
                hasError = true;
            }

            return !hasError;
        }
        return true;
    }

    function updateInput(element, hiddenId) {
        // 1. Get raw value (digits only)
        let value = element.value.replace(/[^0-9]/g, '');
        
        // 2. Update hidden input mapping for quantity/price
        let targetId = document.getElementById(hiddenId);
        if (targetId) {
            targetId.value = value;
        } else {
             let h = element.parentNode.querySelector('input[type="hidden"]');
             if(h) h.value = value;
             else {
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

        // Validate
        validateInputs();

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
    formatInitialValues();

    $(document).ready(function() {
        if ($('.select2-item-name').length) {
            $('.select2-item-name').select2({
                tags: true, /* Allows adding new items not in the list */
                placeholder: "Pilih atau ketik nama barang...",
                allowClear: true
            }).on('change', function() {
                const typeInput = document.querySelector('input[name="type"]:checked');
                if (typeInput && typeInput.value === 'sale_item') {
                    fetchItemInfo(this.value);
                }
            });
        }

        $('form').on('submit', function(e) {
            if (!validateInputs()) {
                e.preventDefault();
                alert('Terdapat peringatan pada input. Silakan periksa kembali Jumlah atau Harga Satuan.');
            }
        });
    });
</script>
@endsection
