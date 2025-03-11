<div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            @if (Auth::user()->role === 'admin')
            <li class="nav-item">
                <a href="{{ route('bonus.index') }}" class="nav-link {{ request()->routeIs('bonus.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-coins"></i>
                    <p>Bonus Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('ph.index') }}" class="nav-link {{ request()->routeIs('ph.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-gift"></i>
                    <p>Bonus Payment History</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('branch.index') }}" class="nav-link {{ request()->routeIs('branch.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-code-branch"></i>
                    <p>Branch Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('category.index') }}" class="nav-link {{ request()->routeIs('category.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-clipboard"></i>
                    <p>Category Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('charge.index') }}" class="nav-link {{ request()->routeIs('charge.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-hand-holding-usd"></i>
                    <p>Charge Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('city.index') }}" class="nav-link {{ request()->routeIs('city.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-city"></i>
                    <p>City Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('coupon.index') }}" class="nav-link {{ request()->routeIs('coupon.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-ticket-alt"></i>
                    <p>Coupon Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('deal.index') }}" class="nav-link {{ request()->routeIs('deal.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-percent"></i>
                    <p>Deal Management</p>
                </a>
            </li>



            <li class="nav-item">
                <a href="{{ route('oh.index') }}" class="nav-link {{ request()->routeIs('oh.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-receipt"></i>
                    <p>Order Payment History</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('product.index') }}" class="nav-link {{ request()->routeIs('product.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-box"></i>
                    <p>Product Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('state.index') }}" class="nav-link {{ request()->routeIs('state.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-globe"></i>
                    <p>State Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('user.index') }}" class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-tie"></i>
                    <p>Staff Management</p>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('mobileUser.index') }}" class="nav-link {{ request()->routeIs('mobileUser.index') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-user-cog"></i>
                    <p>User Management</p>
                </a>
            </li>
            @endif

        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>