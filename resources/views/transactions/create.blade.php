@extends('layouts.velzon')

@section('title', 'New Transaction')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">New Transaction</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('transactions.index') }}">Transactions</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Transaction Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('transactions.store') }}" method="POST" id="transactionForm">
                    @csrf
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="type" class="form-label">Transaction Type</label>
                            <select name="type" id="type" class="form-select">
                                <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Sale (Penjualan)</option>
                                <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>Purchase (Pembelian)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="transaction_date" class="form-label">Date</label>
                            <input type="date" name="transaction_date" id="transaction_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer / Supplier</label>
                        <div class="input-group">
                            <select name="customer_id" id="customer_id" class="form-select">
                                <option value="">-- Generic / None --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ Str::contains(strtolower($customer->name), ['umum', 'walk-in', 'general']) ? 'selected' : '' }}>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-secondary" type="button" onclick="alert('Quick add not implemented yet.')"><i class="ri-add-line"></i></button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h5 class="fs-14 mb-2">Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-nowrap align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 40%">Product</th>
                                        <th scope="col" style="width: 15%">Qty</th>
                                        <th scope="col" style="width: 20%">Price</th>
                                        <th scope="col" style="width: 20%">Subtotal</th>
                                        <th scope="col" style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows added by JS -->
                                </tbody>
                            </table>
                        </div>
                         <button type="button" class="btn btn-soft-secondary mt-2" onclick="addItem()">
                            <i class="ri-add-fill me-1 align-bottom"></i> Add Item
                        </button>
                    </div>

                    <div class="row justify-content-end mb-3">
                        <div class="col-md-4">
                            <div class="mb-2">
                                <label for="discount" class="form-label text-end d-block">Discount (Potongan)</label>
                                <input type="number" name="discount" id="discount" class="form-control text-end" value="0" min="0" oninput="calculateTotal()">
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                <h5 class="fs-16 mb-0">Total:</h5>
                                <h5 class="fs-16 mb-0">Rp <span id="grandTotal">0</span></h5>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-4">
                        <h5 class="fs-14 mb-3">Payment Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Status</label>
                                    <select name="payment_status" id="payment_status" class="form-select" onchange="togglePaymentFields()">
                                        <option value="paid">Fully Paid (Lunas)</option>
                                        <option value="partial">Partial Payment (Hutang)</option>
                                        <option value="unpaid">Unpaid (Full Hutang)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="amountPaidContainer" class="mb-3">
                                    <label for="amount_paid" class="form-label">Amount Paid (Bayar)</label>
                                    <input type="number" name="amount_paid" id="amount_paid" class="form-control" min="0" oninput="calculateChange()">
                                </div>
                                <div id="dueDateContainer" class="mb-3 d-none">
                                    <label for="due_date" class="form-label">Due Date (Jatuh Tempo)</label>
                                    <input type="date" name="due_date" id="due_date" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div id="changeContainer" class="alert alert-success d-none" role="alert">
                                    <strong>Change (Kembalian):</strong> Rp <span id="changeDisplay">0</span>
                                </div>
                                <div id="remainingContainer" class="alert alert-danger d-none" role="alert">
                                    <strong>Remaining Debt (Sisa Hutang):</strong> Rp <span id="remainingDisplay">0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Transaction</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const products = @json($products);
    let rowCount = 0;
    let currentTotal = 0;

    function addItem() {
        const tbody = document.querySelector('#itemsTable tbody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="items[${rowCount}][product_id]" class="form-select product-select" onchange="updatePrice(this)" required>
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}" data-price="${p.sell_price}" data-buy-price="${p.buy_price}">${p.name} (Stock: ${p.stock})</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][quantity]" class="form-control qty-input" value="1" min="1" onchange="calculateRow(this)" required>
            </td>
            <td>
                <input type="number" name="items[${rowCount}][price]" class="form-control price-input" value="0" onchange="calculateRow(this)" required>
            </td>
            <td>
                <span class="subtotal fw-semibold">0</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-soft-danger" onclick="this.closest('tr').remove(); calculateTotal()">
                    <i class="ri-delete-bin-fill"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowCount++;
    }

    function updatePrice(select) {
        const option = select.options[select.selectedIndex];
        const row = select.closest('tr');
        const priceInput = row.querySelector('.price-input');
        const type = document.getElementById('type').value;
        
        if(type === 'sale') {
             priceInput.value = option.dataset.price || 0;
        } else {
             priceInput.value = option.dataset.buyPrice || 0;
        }
        calculateRow(select);
    }

    function calculateRow(element) {
        const row = element.closest('tr');
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        row.querySelector('.subtotal').textContent = subtotal.toLocaleString('id-ID');
        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            subtotal += qty * price;
        });

        const discountInput = document.getElementById('discount');
        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(0, subtotal - discount);
        
        currentTotal = total;
        document.getElementById('grandTotal').textContent = total.toLocaleString('id-ID');
        
        calculateChange();
    }

    function togglePaymentFields() {
        const status = document.getElementById('payment_status').value;
        const amountDiv = document.getElementById('amountPaidContainer');
        const dateDiv = document.getElementById('dueDateContainer');
        const amountInput = document.getElementById('amount_paid');
        
        if (status === 'unpaid') {
            amountDiv.classList.add('d-none');
            dateDiv.classList.remove('d-none');
            amountInput.value = 0;
        } else if (status === 'paid') {
             amountDiv.classList.remove('d-none');
             dateDiv.classList.add('d-none');
        } else {
            amountDiv.classList.remove('d-none');
            dateDiv.classList.remove('d-none');
        }
        calculateChange();
    }

    function calculateChange() {
        const statusSelect = document.getElementById('payment_status');
        const amountInput = document.getElementById('amount_paid');
        const amountPaid = parseFloat(amountInput.value) || 0;
        
        const changeContainer = document.getElementById('changeContainer');
        const changeDisplay = document.getElementById('changeDisplay');
        const remainingContainer = document.getElementById('remainingContainer');
        const remainingDisplay = document.getElementById('remainingDisplay');

        const diff = amountPaid - currentTotal;

        if (diff >= 0) {
            changeContainer.classList.remove('d-none');
            changeDisplay.textContent = diff.toLocaleString('id-ID');
            remainingContainer.classList.add('d-none');
        } else {
            changeContainer.classList.add('d-none');
            remainingContainer.classList.remove('d-none');
            remainingDisplay.textContent = Math.abs(diff).toLocaleString('id-ID');
        }
        
        if (statusSelect.value === 'unpaid') {
             remainingContainer.classList.remove('d-none');
             remainingDisplay.textContent = currentTotal.toLocaleString('id-ID');
             changeContainer.classList.add('d-none');
        }
    }

    // Initialize with one row
    addItem();
</script>
@endsection
