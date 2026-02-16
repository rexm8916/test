@extends('layouts.velzon')

@section('title', 'Create Transaction')

@section('content')
<div class="row justify-content-center">
    <div class="col-xxl-9">
        <div class="card">
            <form action="{{ route('transactions.store') }}" method="POST" id="invoice_form" autocomplete="off">
                @csrf
                <input type="hidden" name="type" value="{{ request('type', 'sale') }}">
                <div class="card-body border-bottom border-bottom-dashed p-4">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="profile-user mx-auto  mb-3">
                                <span class="d-inline-block p-2 bg-soft-light rounded-circle">
                                     @if(request('type') == 'purchase')
                                        <i class="ri-shopping-cart-line display-4 text-primary"></i>
                                    @else
                                        <i class="ri-file-list-3-line display-4 text-primary"></i>
                                    @endif
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-1">{{ request('type') == 'purchase' ? 'New Purchase' : 'New Sale' }}</h5>
                                <p class="text-muted mb-0">Create a new transaction</p>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row g-3">
                                <div class="col-lg-4 col-6">
                                    <label for="transaction_date" class="form-label text-muted text-uppercase fw-semibold">Date</label>
                                    <input type="date" class="form-control bg-light border-0" id="transaction_date" name="transaction_date" placeholder="Select date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                </div>
                                <!--end col-->
                                <div class="col-lg-4 col-6">
                                    <label for="payment_status" class="form-label text-muted text-uppercase fw-semibold">Payment Status</label>
                                    <select class="form-select bg-light border-0" id="payment_status" name="payment_status" onchange="togglePaymentFields()">
                                        <option value="paid">Paid</option>
                                        <option value="unpaid">Unpaid</option>
                                        <option value="partial">Partial</option>
                                    </select>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-lg-4 col-sm-6">
                            <label for="customer_id" class="text-muted text-uppercase fw-semibold">{{ request('type') == 'purchase' ? 'Supplier' : 'Customer' }}</label>
                             <div class="input-group">
                                <select class="form-select bg-light border-0" id="customer_id" name="customer_id">
                                    <option value="">{{ request('type') == 'purchase' ? 'Select Supplier' : 'Walk-in Customer / Select Customer' }}</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="ri-add-line"></i></button>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="invoice-table table table-borderless table-nowrap mb-0">
                            <thead class="align-middle">
                                <tr class="table-active">
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Product Details</th>
                                    <th scope="col" style="width: 120px;">Price</th>
                                    <th scope="col" style="width: 120px;">Quantity</th>
                                    <th scope="col" class="text-end" style="width: 150px;">Amount</th>
                                    <th scope="col" class="text-end" style="width: 105px;"></th>
                                </tr>
                            </thead>
                            <tbody id="newlink">
                                <tr id="1" class="product text-start">
                                    <th scope="row" class="product-id">1</th>
                                    <td class="text-start">
                                        <div class="mb-2">
                                            <select class="form-select bg-light border-0" name="items[0][product_id]" onchange="updatePrice(this)" required>
                                                <option value="">Select Product...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-price="{{ request('type') == 'purchase' ? $product->buy_price : $product->sell_price }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control product-price bg-light border-0" name="items[0][price]" step="100" placeholder="0" onchange="calculateTotal()" required />
                                    </td>
                                    <td>
                                        <div class="input-step">
                                            <button type="button" class='minus'>–</button>
                                            <input type="number" class="product-quantity" name="items[0][quantity]" value="1" min="1" onchange="calculateTotal()">
                                            <button type="button" class='plus'>+</button>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div>
                                            <input type="text" class="form-control bg-light border-0 product-line-price" placeholder="Rp 0" readonly />
                                        </div>
                                    </td>
                                    <td class="product-removal">
                                        <a href="javascript:void(0)" class="btn btn-success" onclick="removeItem(this)">Delete</a>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr id="newForm" style="display: none;"></tr>
                                <tr>
                                    <td colspan="5">
                                        <a href="javascript:new_link()" id="add-item" class="btn btn-soft-secondary fw-medium"><i class="ri-add-fill me-1 align-bottom"></i> Add Item</a>
                                    </td>
                                </tr>
                                <tr class="border-top border-top-dashed mt-2">
                                    <td colspan="3"></td>
                                    <td colspan="2" class="p-0">
                                        <table class="table table-borderless table-sm table-nowrap align-middle mb-0">
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Sub Total</th>
                                                    <td style="width:150px;">
                                                        <input type="text" class="form-control bg-light border-0" id="cart-subtotal" placeholder="Rp 0" readonly />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Discount</th>
                                                    <td>
                                                        <input type="number" class="form-control bg-light border-0" id="cart-discount" name="discount" placeholder="0" onchange="calculateTotal()" />
                                                    </td>
                                                </tr>
                                                <tr class="border-top border-top-dashed">
                                                    <th scope="row">Total Amount</th>
                                                    <td>
                                                        <input type="hidden" name="total_amount" id="total_amount_input">
                                                        <input type="text" class="form-control bg-light border-0" id="cart-total" placeholder="Rp 0" readonly />
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <!--end table-->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!--end table-->
                    </div>
                    <div class="row mt-3" id="payment_details_section">
                        <div class="col-lg-4">
                            <div class="mb-2">
                                <label for="amount_paid_display" class="form-label text-muted text-uppercase fw-semibold">Amount Paid (Bayar)</label>
                                <input type="text" class="form-control bg-light border-0" id="amount_paid_display" placeholder="Rp 0" onkeyup="updateAmountPaid(this)">
                                <input type="hidden" id="amount_paid" name="amount_paid">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-2">
                                <label for="change_amount" class="form-label text-muted text-uppercase fw-semibold">Change (Kembalian)</label>
                                <input type="text" class="form-control bg-light border-0" id="change_amount" placeholder="Rp 0" readonly>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                    <div class="mt-4">
                        <label for="notes" class="form-label text-muted text-uppercase fw-semibold">NOTES</label>
                        <textarea class="form-control alert alert-info" id="notes" name="notes" placeholder="Notes" rows="2"></textarea>
                    </div>
                    <div class="hstack gap-2 justify-content-end d-print-none mt-4">
                        <button type="submit" class="btn btn-success"><i class="ri-save-3-line align-bottom me-1"></i> Save Transaction</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!--end col-->
</div>
<!--end row-->

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">Add New {{ request('type') == 'purchase' ? 'Supplier' : 'Customer' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <input type="hidden" name="type" value="{{ request('type') == 'purchase' ? 'supplier' : 'customer' }}">
                    <div class="mb-3">
                        <label for="new_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="new_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_contact" class="form-label">Contact</label>
                        <input type="text" class="form-control" id="new_contact" name="contact">
                    </div>
                    <div class="mb-3">
                        <label for="new_address" class="form-label">Address</label>
                        <textarea class="form-control" id="new_address" name="address"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveNewCustomer()">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Robust Product Data Transfer
    const products = @json($products);
    const transactionType = "{{ request('type', 'sale') }}";

    function getProductOptions() {
        let options = '<option value="">Select Product...</option>';
        products.forEach(p => {
             // Determine price based on transaction type
             let price = transactionType === 'purchase' ? p.buy_price : p.sell_price;
             // Escape names to prevent JS breakage (basic protection)
             const safeName = p.name.replace(/"/g, '&quot;');
             options += `<option value="${p.id}" data-price="${price}">${safeName}</option>`;
        });
        return options;
    }

    var count = 1;

    function new_link() {
        count++;
        var tr = document.createElement("tr");
        tr.id = count;
        tr.className = "product";
        
        var productOptions = getProductOptions();

        var template = `
            <th scope="row" class="product-id">${count}</th>
            <td class="text-start">
                <div class="mb-2">
                     <select class="form-select bg-light border-0" name="items[${count-1}][product_id]" onchange="updatePrice(this)" required>
                        ${productOptions}
                    </select>
                </div>
            </td>
            <td>
                <input type="number" class="form-control product-price bg-light border-0" name="items[${count-1}][price]" step="100" placeholder="0" onchange="calculateTotal()" required />
            </td>
            <td>
                <div class="input-step">
                    <button type="button" class='minus'>–</button>
                    <input type="number" class="product-quantity" name="items[${count-1}][quantity]" value="1" min="1" onchange="calculateTotal()">
                    <button type="button" class='plus'>+</button>
                </div>
            </td>
            <td class="text-end">
                <div>
                    <input type="text" class="form-control bg-light border-0 product-line-price" placeholder="Rp 0" readonly />
                </div>
            </td>
            <td class="product-removal">
                <a href="javascript:void(0)" class="btn btn-success" onclick="removeItem(this)">Delete</a>
            </td>
        `;
        tr.innerHTML = template;
        document.getElementById("newlink").appendChild(tr);
        calculateTotal(); 
    }

    function removeItem(btn) {
        var row = btn.closest('tr');
        row.remove();
        calculateTotal();
        // Renumber rows (Optional, but good for UI)
        document.querySelectorAll('.product-id').forEach((el, index) => {
            el.textContent = index + 1;
        });
    }

    function updatePrice(select) {
        var price = select.options[select.selectedIndex].getAttribute('data-price');
        var row = select.closest('tr');
        var priceInput = row.querySelector('.product-price');
        if(price) priceInput.value = price;
        calculateTotal();
    }

    function updateQty(btn, change) {
        var input = btn.parentElement.querySelector('.product-quantity');
        
        // Ensure initial value is handled
        var currentVal = parseInt(input.value) || 0;
        var newVal = currentVal + change;
        
        if(newVal < 1) newVal = 1;
        input.value = newVal;
        calculateTotal();
    }

    function calculateTotal() {
        var subtotal = 0;
        var rows = document.querySelectorAll('.product');
        
        rows.forEach(function(row) {
            var price = parseFloat(row.querySelector('.product-price').value) || 0;
            var qty = parseInt(row.querySelector('.product-quantity').value) || 0;
            var lineTotal = price * qty;
            subtotal += lineTotal;
            
            row.querySelector('.product-line-price').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(lineTotal);
        });

        document.getElementById('cart-subtotal').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(subtotal);

        var discount = parseFloat(document.getElementById('cart-discount').value) || 0;
        var total = subtotal - discount;

        document.getElementById('cart-total').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(total);
        document.getElementById('total_amount_input').value = total;
        calculateChange();
    }

    // Event Delegation for Plus/Minus buttons
    // This handles both static and dynamically added rows
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('minus')) {
            updateQty(e.target, -1);
        } else if (e.target.classList.contains('plus')) {
            updateQty(e.target, 1);
        }
    });

    function togglePaymentFields() {
        var status = document.getElementById('payment_status').value;
        var section = document.getElementById('payment_details_section');
        var customerSelect = document.getElementById('customer_id');
        
        var umumOption = Array.from(customerSelect.options).find(option => option.text.toLowerCase().includes('umum') || option.text.toLowerCase().includes('walk-in'));

        // 1. Visibility Logic
        if (status === 'unpaid') {
            section.style.display = 'none';
        } else {
            // Paid or Partial -> Show Payment Fields
            section.style.display = 'flex';
        }

        // 2. Customer Logic
        if (status === 'paid') {
             // Paid -> Auto-select Umum if nothing selected
             if (umumOption && customerSelect.value === "") {
                customerSelect.value = umumOption.value;
             }
        } else {
            // Unpaid or Partial -> Must be real customer, so clear Umum if selected
            if (umumOption && customerSelect.value == umumOption.value) {
                customerSelect.value = "";
            }
        }
    }

    function calculateChange() {
        var total = parseFloat(document.getElementById('total_amount_input').value) || 0;
        var paid = parseFloat(document.getElementById('amount_paid').value) || 0;
        var change = paid - total;
        
        if (change < 0) change = 0; // Don't show negative change for partial

        document.getElementById('change_amount').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(change);
    }

    function updateAmountPaid(element) {
        // 1. Get raw value (digits only)
        let value = element.value.replace(/[^0-9]/g, '');
        
        // 2. Update hidden input (for backend & calcs)
        document.getElementById('amount_paid').value = value;
        
        // 3. Format display value (IDR currency)
        if (value) {
            element.value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value).replace('Rp', 'Rp ').trim();
        } else {
            element.value = '';
        }

        // 4. Trigger calculations
        calculateChange();
    }

    // Modal Logic
    function saveNewCustomer() {
        const form = document.getElementById('addCustomerForm');
        const formData = new FormData(form);

        fetch('{{ route('customers.store') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add to select
                const select = document.getElementById('customer_id');
                const option = new Option(data.customer.name, data.customer.id, true, true);
                select.add(option);

                // Close modal
                const modalElement = document.getElementById('addCustomerModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();

                // Reset form
                form.reset();

                // Show success message
                alert('Customer added successfully!');
            } else {
                alert('Error adding customer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding customer. Please check input.');
        });
    }
    
    // Form Submission Validation
    document.getElementById('invoice_form').addEventListener('submit', function(event) {
        var status = document.getElementById('payment_status').value;
        var total = parseFloat(document.getElementById('total_amount_input').value) || 0;
        var paid = parseFloat(document.getElementById('amount_paid').value) || 0;
        var customerSelect = document.getElementById('customer_id');
        var selectedOption = customerSelect.options[customerSelect.selectedIndex];
        var customerName = selectedOption ? selectedOption.text.toLowerCase() : '';

        // Block Paid if Amount < Total
        if (status === 'paid' && paid < total) {
            event.preventDefault();
            alert("Amount Paid (Bayar) is less than Total Amount.\nPlease change Payment Status to 'Partial' or 'Unpaid', or enter the correct amount.");
            return;
        }

        // Block Unpaid/Partial if Customer is Empty or Umum
        if ((status === 'unpaid' || status === 'partial') && (customerSelect.value === "" || customerName.includes('umum') || customerName.includes('walk-in'))) {
            event.preventDefault();
            alert("For Unpaid or Partial transactions, you must select a valid Customer (Cannot be Umum/Walk-in).");
            return;
        }
    });

    // Run on load
    togglePaymentFields();
    calculateTotal(); // Ensure totals are set on load
</script>
@endsection
