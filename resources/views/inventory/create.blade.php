@extends('layouts.velzon')

@section('title', 'Tambah Entri Buku Penjualan')

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
                        <div class="col-md-4">
                            <label for="date" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
                        </div>
                        
                        @if(auth()->user() && auth()->user()->isSuperAdmin())
                        <div class="col-md-4">
                            <label for="branch_id" class="form-label">Cabang Transaksi</label>
                            <select name="branch_id" id="branch_id" class="form-select" onchange="if($('#item_name_sale').val()) fetchItemInfo($('#item_name_sale').val());" required>
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                        @else
                        <div class="col-md-8">
                        @endif
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
                                        Penjualan (Keluar) - Harian
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="type" id="typeSaleItem" value="sale_item" {{ old('type') == 'sale_item' ? 'checked' : '' }} onchange="toggleFields()">
                                    <label class="form-check-label" for="typeSaleItem">
                                        Keluar Barang (Input Jumlah & Harga Satuan)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Items Table -->
                        <div id="purchase_fields" style="display: none;" class="col-12 mt-4">
                            <h6 class="mb-3 border-bottom pb-2">Daftar Barang Masuk</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="purchase_items_table">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 40%">Nama Barang</th>
                                            <th style="width: 20%">Jumlah</th>
                                            <th style="width: 25%">Harga Satuan (Rp)</th>
                                            <th style="width: 10%">Subtotal</th>
                                            <th style="width: 5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rows will be added dynamically by JS -->
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Total Pembelian:</td>
                                            <td colspan="2" class="fw-bold text-success fs-15 text-end" id="purchase_grand_total">Rp 0</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-soft-primary mt-2" onclick="addPurchaseRow()">
                                <i class="ri-add-line align-middle me-1"></i> Tambah Barang
                            </button>
                        </div>

                        <!-- Sale Item Fields (Single Input as before) -->
                        <div id="sale_item_fields" style="display: none;" class="col-12 mt-3">
                            <div class="mb-3" id="sale_item_container">
                                <label for="item_name_sale" class="form-label">Nama Barang</label>
                                <select class="form-select select2-item-name" id="item_name_sale" name="item_name" style="width: 100%;">
                                    <option value="">Pilih barang...</option>
                                    @foreach($existingItems as $itemName)
                                        <option value="{{ $itemName }}" {{ old('item_name') == $itemName ? 'selected' : '' }}>{{ $itemName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_display" class="form-label">Jumlah</label>
                                    <input type="text" class="form-control @error('quantity') is-invalid @enderror" id="quantity_display" placeholder="0" onkeyup="updateInput(this, 'quantity')">
                                    <input type="hidden" id="quantity" name="quantity" value="{{ old('quantity') }}">
                                    @error('quantity')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="unit_price_display" class="form-label">Harga Satuan</label>
                                    <input type="text" class="form-control @error('unit_price') is-invalid @enderror" id="unit_price_display" placeholder="Rp 0" onkeyup="updateInput(this, 'unit_price')">
                                    <input type="hidden" id="unit_price" name="unit_price" value="{{ old('unit_price') }}">
                                    @error('unit_price')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Total Amount</label>
                                <div class="fs-4 fw-bold text-danger" id="sale_total_display">Rp 0</div>
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
    let purchaseRowIndex = 0;

    function addPurchaseRow() {
        const tbody = document.querySelector('#purchase_items_table tbody');
        const rowId = `purchase_row_${purchaseRowIndex}`;
        
        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" name="items[${purchaseRowIndex}][item_name]" placeholder="Nama barang..." required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm purchase-qty-display" placeholder="0" onkeyup="updatePurchaseRow('${rowId}', 'quantity', ${purchaseRowIndex})">
                <input type="hidden" name="items[${purchaseRowIndex}][quantity]" id="purchase_qty_${purchaseRowIndex}" class="purchase-qty-val" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="text" class="form-control purchase-price-display" placeholder="0" onkeyup="updatePurchaseRow('${rowId}', 'unit_price', ${purchaseRowIndex})">
                </div>
                <input type="hidden" name="items[${purchaseRowIndex}][unit_price]" id="purchase_price_${purchaseRowIndex}" class="purchase-price-val" required>
            </td>
            <td class="text-end align-middle fw-medium">
                <span id="purchase_subtotal_${purchaseRowIndex}" class="purchase-subtotal">Rp 0</span>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-ghost-danger btn-icon" onclick="removePurchaseRow('${rowId}')">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
        purchaseRowIndex++;
    }

    function removePurchaseRow(rowId) {
        document.getElementById(rowId).remove();
        calculatePurchaseTotal();
    }

    function updatePurchaseRow(rowId, fieldType, index) {
        const row = document.getElementById(rowId);
        let displayInput;
        let hiddenId;
        
        if (fieldType === 'quantity') {
            displayInput = row.querySelector('.purchase-qty-display');
            hiddenId = `purchase_qty_${index}`;
        } else {
            displayInput = row.querySelector('.purchase-price-display');
            hiddenId = `purchase_price_${index}`;
        }

        let value = displayInput.value.replace(/[^0-9]/g, '');
        document.getElementById(hiddenId).value = value;
        
        if (value) {
            displayInput.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            displayInput.value = '';
        }

        // Calc subtotal for this row
        const qty = parseFloat(document.getElementById(`purchase_qty_${index}`).value) || 0;
        const price = parseFloat(document.getElementById(`purchase_price_${index}`).value) || 0;
        const subtotal = qty * price;
        
        document.getElementById(`purchase_subtotal_${index}`).innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(subtotal);
        
        calculatePurchaseTotal();
    }

    function calculatePurchaseTotal() {
        let grandTotal = 0;
        const tbody = document.querySelector('#purchase_items_table tbody');
        const rows = tbody.querySelectorAll('tr');
        
        rows.forEach((row) => {
            const qtyVal = row.querySelector('.purchase-qty-val');
            const priceVal = row.querySelector('.purchase-price-val');
            if (qtyVal && priceVal) {
                const qty = parseFloat(qtyVal.value) || 0;
                const price = parseFloat(priceVal.value) || 0;
                grandTotal += (qty * price);
            }
        });
        
        document.getElementById('purchase_grand_total').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(grandTotal);
    }

    let maxQuantity = null;
    let minPrice = null;

    function toggleFields() {
        const typeInput = document.querySelector('input[name="type"]:checked');
        const type = typeInput ? typeInput.value : 'initial';
        const purchaseFields = document.getElementById('purchase_fields');
        const saleItemFields = document.getElementById('sale_item_fields');
        const amountField = document.getElementById('amount_field');
        const amountLabel = document.getElementById('amount_label');

        // Hide all fields first
        purchaseFields.style.display = 'none';
        saleItemFields.style.display = 'none';
        amountField.style.display = 'none';
        document.getElementById('item_name_sale').disabled = true;

        if (type === 'purchase') {
            purchaseFields.style.display = 'block';
            // Auto-add first row if empty
            if (document.querySelector('#purchase_items_table tbody').children.length === 0) {
                addPurchaseRow();
            }
        } else if (type === 'sale_item') {
            saleItemFields.style.display = 'block';
            document.getElementById('item_name_sale').disabled = false;
        } else {
            amountField.style.display = 'block';
            if (type === 'sale') {
                amountLabel.innerText = 'Jumlah Penjualan (Keluar) - Harian';
            } else {
                amountLabel.innerText = 'Jumlah Saldo Awal - Rp';
            }
        }

        maxQuantity = null;
        minPrice = null;
        if (type === 'sale_item' && $('#item_name_sale').val()) {
            fetchItemInfo($('#item_name_sale').val());
        }
    }

    function fetchItemInfo(itemName) {
        if (!itemName) return;
        
        let branchId = '';
        if (document.getElementById('branch_id')) {
            branchId = document.getElementById('branch_id').value;
        }

        $.ajax({
            url: "{{ route('api.inventory.item_info') }}",
            data: { item_name: itemName, branch_id: branchId },
            success: function(res) {
                maxQuantity = res.stock;
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

    $(document).ready(function() {
        if ($('.select2-item-name').length) {
            $('.select2-item-name').select2({
                tags: true, /* Allows adding new items not in the list */
                placeholder: "Pilih barang...",
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
