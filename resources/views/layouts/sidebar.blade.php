<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <span class="fs-22 fw-bold text-dark">RM</span>
            </span>
            <span class="logo-lg">
                <span class="fs-20 fw-bold text-dark">RizkiMandiri</span>
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                 <span class="fs-22 fw-bold text-white">RM</span>
            </span>
            <span class="logo-lg">
                 <span class="fs-20 fw-bold text-white">RizkiMandiri</span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Pembukuan</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('inventory.index') }}">
                        <i class="ri-store-3-line"></i> <span data-key="t-inventory">Buku Penjualan</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('expenses.index') }}">
                        <i class="ri-wallet-3-line"></i> <span data-key="t-expenses">Pengeluaran</span>
                    </a>
                </li>

                @if(auth()->check() && auth()->user()->isSuperAdmin())
                <li class="menu-title"><span data-key="t-menu">Data Master</span></li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('branches.index') }}">
                        <i class="ri-store-2-line"></i> <span data-key="t-branches">Cabang Toko</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('users.index') }}">
                        <i class="ri-group-line"></i> <span data-key="t-users">Manajemen Pengguna</span>
                    </a>
                </li>
                @endif

                {{-- coming soon --}}
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-timer-line"></i> <span data-key="t-coming-soon">Coming Soon</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarDashboards">
                        <ul class="nav nav-sm flex-column">

                            <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dasbor</span>
                                </a>
                            </li>

                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">POS System</span></li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('transactions.index', ['type' => 'sale']) }}">
                                    <i class="ri-exchange-dollar-line"></i> <span data-key="t-sales">Penjualan</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('debts.index') }}">
                                    <i class="ri-book-mark-line"></i> <span data-key="t-debts">Hutang/Piutang</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('customers.index') }}">
                                    <i class="ri-user-line"></i> <span data-key="t-customers">Pelanggan</span>
                                </a>
                            </li>

                            <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Management Barang</span></li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('transactions.index', ['type' => 'purchase']) }}">
                                    <i class="ri-shopping-cart-line"></i> <span data-key="t-purchases">Pembelian</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products.index') }}">
                                    <i class="ri-shopping-bag-3-line"></i> <span data-key="t-products">Produk</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
                {{-- end coming soon --}}
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
