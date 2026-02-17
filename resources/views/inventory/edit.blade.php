@extends('layouts.velzon')

@section('title', 'Edit Inventory Entry')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Inventory Entry</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.update', $ledger->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="{{ old('date', $ledger->date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required onchange="toggleFields()">
                                <option value="purchase" {{ old('type', $ledger->type) == 'purchase' ? 'selected' : '' }}>Purchase (In)</option>
                                <option value="sale" {{ old('type', $ledger->type) == 'sale' ? 'selected' : '' }}>Sale (Out)</option>
                                <option value="initial" {{ old('type', $ledger->type) == 'initial' ? 'selected' : '' }}>Initial Balance</option>
                            </select>
                        </div>

                        <!-- Purchase Fields -->
                        <div id="purchase_fields">
                            <div class="col-12 mb-3">
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" value="{{ old('item_name', $ledger->item_name) }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="quantity_display" class="form-label">Quantity</label>
                                    <input type="text" class="form-control" id="quantity_display" placeholder="0" value="{{ old('quantity', $ledger->quantity) }}" onkeyup="updateInput(this, 'quantity')">
                                    <input type="hidden" id="quantity" name="quantity" value="{{ old('quantity', $ledger->quantity) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="unit_price_display" class="form-label">Unit Price</label>
                                    <input type="text" class="form-control" id="unit_price_display" placeholder="Rp 0" value="{{ old('unit_price', $ledger->unit_price) }}" onkeyup="updateInput(this, 'unit_price')">
                                    <input type="hidden" id="unit_price" name="unit_price" value="{{ old('unit_price', $ledger->unit_price) }}">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Total Purchase Amount</label>
                                <div class="fs-4 fw-bold text-success" id="purchase_total_display">Rp 0</div>
                            </div>
                        </div>

                        <!-- Direct Amount Field (Sale/Initial) -->
                        <div id="amount_field" style="display: none;">
                            <div class="col-12 mb-3">
                                <label for="amount_display" class="form-label" id="amount_label">Amount</label>
                                <input type="text" class="form-control" id="amount_display" placeholder="Rp 0" value="{{ old('amount', $ledger->amount) }}" onkeyup="updateInput(this, 'amount')">
                                <input type="hidden" id="amount" name="amount" value="{{ old('amount', $ledger->amount) }}">
                            </div>
                        </div>

                        <div class="col-12 text-end">
                            <a href="{{ route('inventory.index') }}" class="btn btn-light me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Entry</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFields() {
        const type = document.getElementById('type').value;
        const purchaseFields = document.getElementById('purchase_fields');
        const amountField = document.getElementById('amount_field');
        const amountLabel = document.getElementById('amount_label');

        if (type === 'purchase') {
            purchaseFields.style.display = 'block';
            amountField.style.display = 'none';
        } else {
            purchaseFields.style.display = 'none';
            amountField.style.display = 'block';
            
            if (type === 'sale') {
                amountLabel.innerText = 'Sales Amount (Out)';
            } else {
                amountLabel.innerText = 'Initial Balance Amount';
            }
        }
    }

    function updateInput(element, hiddenId) {
        let value = element.value.replace(/[^0-9]/g, '');
        document.getElementById(hiddenId).value = value;
        
        if (value) {
            element.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            element.value = '';
        }

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

    function formatInitialValues() {
        const qtyInput = document.getElementById('quantity_display');
        const priceInput = document.getElementById('unit_price_display');
        const amountInput = document.getElementById('amount_display');

        if(qtyInput.value) qtyInput.value = new Intl.NumberFormat('id-ID').format(qtyInput.value);
        if(priceInput.value) priceInput.value = new Intl.NumberFormat('id-ID').format(priceInput.value);
        if(amountInput.value) amountInput.value = new Intl.NumberFormat('id-ID').format(amountInput.value);
        
        calculateTotal();
    }

    // Run on load
    toggleFields();
    formatInitialValues();
</script>
@endsection
