@extends('layouts.velzon')

@section('title', request('type') == 'purchase' ? 'Pembelian Baru' : 'Kasir Penjualan')

@section('content')
<style>
    /* --- POS System Custom Styles --- */
    
    /* 1. Global & Utilities */
    .cursor-pointer { cursor: pointer; }
    .transition-all { transition: all 0.2s ease; }
    
    /* 2. Product Card */
    .product-card {
        border: 1px solid #eee;
        transition: all 0.2s;
        background: #fff;
    }
    .product-card:active { transform: scale(0.98); }
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        border-color: var(--vz-primary);
    }
    .product-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        height: 38px; /* Fixed height for 2 lines */
        line-height: 1.3;
        font-size: 13px;
    }

    /* 3. Search Box */
    .search-box { position: relative; }
    .search-box .search-icon { 
        position: absolute; 
        right: 15px; 
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px; 
        color: #878a99; 
        pointer-events: none;
    }

    /* 4. Desktop Layout (lg+) */
    @media (min-width: 992px) {
        /* Fix the height of the main content area to viewport minus header */
        .pos-container {
            height: calc(100vh - 160px); /* Adjust based on topbar/footer height */
            overflow: hidden;
        }
        
        /* Scrollable areas for catalog and cart */
        .scrollable-content {
            height: 100%;
            overflow-y: auto;
            /* Custom Scrollbar */
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        .scrollable-content::-webkit-scrollbar { width: 6px; }
        .scrollable-content::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
        
        .cart-sidebar {
            height: 100%;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #eee;
        }
         
        /* Hide mobile elements */
        .mobile-only { display: none !important; }
    }

    /* 5. Mobile Layout (max-width: 991.98px) */
    @media (max-width: 991.98px) {
        .pos-container {
            height: auto;
            min-height: 100vh;
            padding-bottom: 80px; /* Space for floating button */
        }
        
        .scrollable-content {
            height: auto;
            overflow: visible;
        }

        /* Cart Sidebar acts as Offcanvas on Mobile */
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 85%;
            max-width: 380px;
            background: #fff;
            z-index: 1060; /* Higher than Velzon overlay */
            transform: translateX(110%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: -5px 0 25px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
        }
        
        .cart-sidebar.show {
            transform: translateX(0);
        }
        
        /* Mobile Overlay Backdrop */
        .pos-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1055;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            backdrop-filter: blur(2px);
        }
        .pos-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Floating Info/Button */
        .mobile-cart-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            left: 20px;
            z-index: 1050;
        }
    }
</style>

<!-- Header Section -->
<div class="row mb-3">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">{{ request('type') == 'purchase' ? 'Pembelian Baru' : 'Kasir Penjualan' }}</h4>
            <div class="page-title-right d-none d-sm-block">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dasbor</a></li>
                    <li class="breadcrumb-item active">{{ request('type') == 'purchase' ? 'Pembelian' : 'Kasir' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('transactions.store') }}" method="POST" id="pos_form" autocomplete="off" class="h-100">
    @csrf
    <input type="hidden" name="type" value="{{ request('type', 'sale') }}">
    
    <div class="row g-0 pos-container card mb-0 overflow-hidden shadow-sm">
        
        <!-- LEFT COLUMN: Product Catalog -->
        <div class="col-lg-8 border-end h-100 d-flex flex-column bg-light-subtle">
            <!-- Search Header -->
            <div class="p-3 bg-white border-bottom sticky-top" style="z-index: 10;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="ri-search-line"></i></span>
                    <input type="search" class="form-control bg-light border-0" id="searchProduct" placeholder="Cari nama produk / kode..." autocomplete="off">
                </div>
            </div>

            <!-- Product Grid -->
            <div class="p-3 scrollable-content" id="catalogRegion">
                <div class="row row-cols-xxl-4 row-cols-xl-3 row-cols-lg-3 row-cols-md-3 row-cols-2 g-2" id="productGrid">
                    <!-- Javascript will render products here -->
                </div>

                <!-- Empty State -->
                <div id="noProductsFound" class="d-none flex-column align-items-center justify-content-center py-5 mt-5">
                    <div class="avatar-lg bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 text-muted">
                        <i class="ri-search-2-line fs-1"></i>
                    </div>
                    <h5 class="text-muted">Produk tidak ditemukan</h5>
                    <p class="text-muted small">Coba kata kunci lain atau reset pencarian</p>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="resetSearch()">Reset Pencarian</button>
                </div>
            </div>
        </div>


        <!-- RIGHT COLUMN: Cart (Sidebar) -->
        <div class="col-lg-4 cart-sidebar" id="cartSidebar">
            <!-- Mobile Toggle Header -->
            <div class="d-lg-none d-flex align-items-center justify-content-between p-3 bg-primary text-white">
                <h5 class="mb-0 text-white">Keranjang Belanja</h5>
                <button type="button" class="btn-close btn-close-white" onclick="toggleCart()"></button>
            </div>

            <!-- Cart Header (Desktop) / Info -->
            <div class="p-3 border-bottom bg-white d-none d-lg-block">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Keranjang</h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill span-item-count">0 Item</span>
                </div>
            </div>

            <!-- Customer & Metadata Info -->
            <div class="p-3 bg-light-subtle border-bottom">
                 <div class="mb-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="ri-user-line"></i></span>
                        <select class="form-select border-start-0 ps-0" id="customer_id" name="customer_id" required>
                            <option value="">{{ request('type') == 'purchase' ? 'Pilih Pemasok' : 'Pilih Pelanggan' }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="ri-add-line"></i></button>
                    </div>
                </div>
                <div class="row g-2">
                     <div class="col-6">
                        <input type="date" class="form-control form-control-sm" id="transaction_date" name="transaction_date" value="{{ date('Y-m-d') }}" required>
                    </div>
                     <div class="col-6">
                         <select class="form-select form-select-sm" id="payment_status" name="payment_status" onchange="togglePaymentFields()">
                            <option value="paid">Lunas</option>
                            <option value="unpaid">Hutang</option>
                            <option value="partial">Bayar Sebagian</option>
                        </select>
                     </div>
                </div>
            </div>

            <!-- Cart Items List (Scrollable) -->
            <div class="p-0 flex-grow-1 overflow-auto bg-white" style="min-height: 0;">
                <table class="table table-borderless align-middle mb-0">
                    <tbody id="cartTableBody">
                        <!-- JS Rendered Items -->
                    </tbody>
                </table>
                
                <!-- Empty Cart State -->
                <div id="emptyCartMessage" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted p-4">
                    <i class="ri-shopping-basket-line fs-1 opacity-25"></i>
                    <p class="small mt-2">Belum ada barang</p>
                </div>
            </div>

            <!-- Cart Footer / Summary -->
            <div class="p-3 bg-white border-top shadow-lg">
                <div class="row g-1 mb-2">
                    <div class="col-6">
                        <span class="text-muted small">Subtotal</span>
                        <div class="fw-semibold" id="subTotalDisplay">Rp 0</div>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted small">Diskon</span>
                        <input type="number" class="form-control form-control-sm text-end p-0 border-0 bg-transparent text-muted" 
                            id="discountInput" name="discount" placeholder="0" oninput="calculateCart()">
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-end mb-3 p-2 bg-soft-primary rounded border border-primary-subtle">
                    <div>
                        <span class="d-block small text-primary">Total Tagihan</span>
                        <h4 class="mb-0 text-primary fw-bold" id="totalDisplay">Rp 0</h4>
                        <input type="hidden" name="total_amount" id="totalAmountInput">
                    </div>
                    <!-- Increased Size for Pay/Change -->
                    <div class="text-end">
                         <span class="d-block text-muted mb-1 fs-14">Bayar / Terima</span>
                         <input type="text" class="form-control form-control-lg text-end fw-bold border-primary" 
                            id="payAmountDisplay" placeholder="Rp 0" onkeyup="formatPayAmount(this)" style="max-width: 140px; font-size: 1.2rem;">
                         <input type="hidden" name="amount_paid" id="payAmountInput">
                         <div class="mt-2 text-success fw-bold fs-16" id="changeDisplay">Kembali: 0</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                    <i class="ri-check-double-line me-1"></i> Proses Transaksi
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Floating Button -->
    <div class="mobile-cart-float d-lg-none">
        <button type="button" class="btn btn-primary w-100 shadow-lg rounded-pill py-3 px-4 d-flex justify-content-between align-items-center" onclick="toggleCart()">
            <div class="d-flex align-items-center">
                <span class="badge bg-white text-primary rounded-pill me-2 span-item-count">0</span>
                <span class="fw-medium">Item</span>
            </div>
            <span class="fw-bold" id="mobileTotalDisplay">Rp 0</span>
        </button>
    </div>

    <!-- Mobile Backdrop -->
    <div class="pos-backdrop d-lg-none" id="posBackdrop" onclick="toggleCart()"></div>

</form>

<!-- Modal Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    <input type="hidden" name="type" value="{{ request('type') == 'purchase' ? 'supplier' : 'customer' }}">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Telp/WA</label>
                        <input type="text" class="form-control" name="contact">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="address"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" onclick="saveNewCustomer()">Simpan</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const allProducts = @json($products);
    const transactionType = "{{ request('type', 'sale') }}";
    let cart = [];

    // DOM cache
    const els = {
        grid: document.getElementById('productGrid'),
        search: document.getElementById('searchProduct'),
        cartBody: document.getElementById('cartTableBody'),
        emptyMsg: document.getElementById('emptyCartMessage'),
        noProducts: document.getElementById('noProductsFound'),
        total: document.getElementById('totalDisplay'),
        subTotal: document.getElementById('subTotalDisplay'),
        mobileTotal: document.getElementById('mobileTotalDisplay'),
        totalInput: document.getElementById('totalAmountInput'),
        discount: document.getElementById('discountInput'),
        payDisplay: document.getElementById('payAmountDisplay'),
        payInput: document.getElementById('payAmountInput'),
        change: document.getElementById('changeDisplay'),
        counts: document.querySelectorAll('.span-item-count'),
        sidebar: document.getElementById('cartSidebar'),
        backdrop: document.getElementById('posBackdrop')
    };

    document.addEventListener('DOMContentLoaded', () => {
        renderProducts(allProducts);
        togglePaymentFields();
    });

    // Mobile Toggle
    window.toggleCart = () => {
        els.sidebar.classList.toggle('show');
        els.backdrop.classList.toggle('show');
    };

    // Search
    let searchTimer;
    els.search.addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const term = e.target.value.toLowerCase();
            const filtered = allProducts.filter(p => 
                p.name.toLowerCase().includes(term) || 
                (p.code && p.code.toLowerCase().includes(term))
            );
            renderProducts(filtered);
        }, 150);
    });

    window.resetSearch = () => {
        els.search.value = '';
        renderProducts(allProducts);
        els.search.focus();
    };

    function renderProducts(list) {
        els.grid.innerHTML = '';
        
        if (list.length === 0) {
            els.noProducts.classList.remove('d-none');
            els.noProducts.classList.add('d-flex');
            return;
        }
        els.noProducts.classList.add('d-none');
        els.noProducts.classList.remove('d-flex');

        list.forEach(p => {
             const price = transactionType === 'purchase' ? p.buy_price : p.sell_price;
             const stockClass = p.stock < 5 ? 'text-danger' : 'text-muted';
             const safeName = p.name.replace(/"/g, '&quot;');
             const initials = p.name.substring(0,2).toUpperCase();
             
             const html = `
                <div class="col">
                    <div class="product-card h-100 rounded position-relative p-2" onclick="addToCart(${p.id})">
                        <div class="d-flex flex-column h-100">
                             <h6 class="text-dark product-title mb-1" title="${safeName}">${safeName}</h6>
                             <div class="mt-auto d-flex align-items-end justify-content-between">
                                <div>
                                    <div class="fw-bold text-primary">${formatMoney(price)}</div>
                                    <small class="${stockClass}" style="font-size: 11px;">Stok: ${p.stock}</small>
                                </div>
                                <div class="avatar-xs flex-shrink-0">
                                    <div class="avatar-title bg-primary-subtle text-primary rounded fs-6">
                                        <i class="ri-add-line"></i>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
             `;
             els.grid.insertAdjacentHTML('beforeend', html);
        });
    }

    // Cart Logic
    window.addToCart = (id) => {
        const product = allProducts.find(p => p.id === id);
        if (!product) return;

        const price = transactionType === 'purchase' ? product.buy_price : product.sell_price;
        const exists = cart.find(i => i.id === id);

        if (exists) {
            exists.qty++;
        } else {
            cart.push({ id: product.id, name: product.name, price: price, qty: 1 });
        }
        renderCart();
    };

    window.updateQty = (id, delta) => {
        const item = cart.find(i => i.id === id);
        if(!item) return;
        const newQty = item.qty + delta;
        if(newQty > 0) {
            item.qty = newQty;
            renderCart();
        }
    };

    window.manualQty = (id, val) => {
        const item = cart.find(i => i.id === id);
        if(!item) return;
        let q = parseInt(val);
        if(isNaN(q) || q < 1) q = 1;
        item.qty = q;
        renderCart();
    };

    window.removeItem = (id) => {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    };

    function renderCart() {
        els.cartBody.innerHTML = '';
        
        if (cart.length === 0) {
            els.emptyMsg.classList.remove('d-none');
            // Hide table? No, just empty body
            calculateTotals();
            return;
        }
        els.emptyMsg.classList.add('d-none');

        cart.forEach((item, idx) => {
            const html = `
                <tr class="border-bottom border-light">
                    <td class="ps-3 py-2">
                        <div class="fw-medium text-dark text-truncate" style="max-width: 140px; font-size:13px;">${item.name}</div>
                        <div class="text-muted small">${formatMoney(item.price)}</div>
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.id}">
                        <input type="hidden" name="items[${idx}][price]" value="${item.price}">
                    </td>
                    <td style="width: 140px;">
                        <div class="input-group bg-light rounded border-0">
                            <button type="button" class="btn btn-ghost-dark px-3" onclick="updateQty(${item.id}, -1)"><i class="ri-subtract-line align-bottom"></i></button>
                            <input type="number" class="form-control border-0 bg-transparent text-center px-0 shadow-none fs-16 fw-bold" 
                                value="${item.qty}" name="items[${idx}][quantity]" onchange="manualQty(${item.id}, this.value)">
                            <button type="button" class="btn btn-ghost-primary px-3" onclick="updateQty(${item.id}, 1)"><i class="ri-add-line align-bottom"></i></button>
                        </div>
                    </td>
                    <td class="text-end pe-3">
                        <div class="fw-bold text-dark fs-14">${formatMoney(item.price * item.qty)}</div>
                        <i class="ri-close-circle-line text-danger cursor-pointer fs-4" onclick="removeItem(${item.id})"></i>
                    </td>
                </tr>
            `;
            els.cartBody.insertAdjacentHTML('beforeend', html);
        });
        calculateTotals();
    }

    window.calculateCart = calculateTotals; // Alias

    function calculateTotals() {
        let sub = 0;
        let count = 0;
        cart.forEach(i => { sub += i.price * i.qty; count += i.qty; });

        const disc = parseFloat(els.discount.value) || 0;
        const total = Math.max(0, sub - disc);

        els.subTotal.textContent = formatMoney(sub);
        els.total.textContent = formatMoney(total);
        els.mobileTotal.textContent = formatMoney(total);
        els.totalInput.value = total;
        
        // Update all counters
        els.counts.forEach(e => e.textContent = count + (count === 1 ? ' Item' : ' Items'));

        // Recalc Change
        const paid = parseFloat(els.payInput.value) || 0;
        const change = Math.max(0, paid - total);
        els.change.textContent = 'Kembali: ' + formatMoney(change);
    }

    window.formatPayAmount = (el) => {
        let raw = el.value.replace(/\D/g, '');
        els.payInput.value = raw;
        if(raw) {
            el.value = formatMoney(raw).replace(',00', ''); // Simple format
        } else {
            el.value = '';
        }
        calculateTotals();
    };

    function formatMoney(amount) {
        return new Intl.NumberFormat('id-ID', { 
            style: 'currency', 
            currency: 'IDR', 
            minimumFractionDigits: 0,
            maximumFractionDigits: 0 
        }).format(amount);
    }

    // Toggle Payment Logic
    window.togglePaymentFields = () => {
         const status = document.getElementById('payment_status').value;
         const select = document.getElementById('customer_id');
         // Check if 'Umum' exists
         let general = Array.from(select.options).find(o => o.text.match(/umum|walk-in/i));
         
         if (status === 'paid') {
             if (general && !select.value) select.value = general.value;
         } else {
             if (general && select.value === general.value) select.value = "";
         }
    };
    
    // Form Submit
    document.getElementById('pos_form').addEventListener('submit', function(e) {
        if (cart.length === 0) {
            e.preventDefault();
            Swal.fire('Keranjang Kosong', 'Pilih minimal satu produk.', 'warning');
            return;
        }
        const pay = parseFloat(els.payInput.value) || 0;
        const tot = parseFloat(els.totalInput.value) || 0;
        const stat = document.getElementById('payment_status').value;

        if (stat === 'paid' && pay < tot) {
             e.preventDefault();
             Swal.fire('Pembayaran Kurang', 'Jumlah bayar lebih kecil dari total.', 'error');
        }
    });

    // Customer Save
    window.saveNewCustomer = () => {
         const form = document.getElementById('addCustomerForm');
         const fd = new FormData(form);

         fetch('{{ route('customers.store') }}', {
             method: 'POST',
             headers: {
                 'X-Requested-With': 'XMLHttpRequest',
                 'Accept': 'application/json',
                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
             },
             body: fd
         })
         .then(r => r.json())
         .then(d => {
             if(d.success) {
                 const opt = new Option(d.customer.name, d.customer.id, true, true);
                 document.getElementById('customer_id').add(opt);
                 
                 const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                 modal.hide();
                 form.reset();
                 Swal.fire('Berhasil', 'Data tersimpan', 'success');
             } else {
                 Swal.fire('Gagal', 'Gagal menyimpan data', 'error');
             }
         })
         .catch(e => {
             console.error(e);
             Swal.fire('Error', 'Terjadi kesalahan server', 'error');
         });
    };
</script>
@endsection
