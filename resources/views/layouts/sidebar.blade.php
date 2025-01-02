<div class="sidebar">
    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Dashboard Link -->
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>
                        Dashboard
                    </p>
                </a>
            </li>

            @if (Auth::user()->role === 'admin')
                <!-- User Management Link -->
                <li class="nav-item">
                    <a href="{{ route('mobileUser.index') }}"
                        class="nav-link {{ request()->routeIs('mobileUser.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>
                            User Management
                        </p>
                    </a>
                </li>
            @endif

            @if (Auth::user()->role === 'admin')
                <!-- Staff Management Link -->
                <li class="nav-item">
                    <a href="{{ route('user.index') }}"
                        class="nav-link {{ request()->routeIs('user.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>
                            Staff Management
                        </p>
                    </a>
                </li>
            @endif

            @if (Auth::user()->role === 'admin')
                <!-- Bonus Management Link -->
                <li class="nav-item">
                    <a href="{{ route('bonus.index') }}"
                        class="nav-link {{ request()->routeIs('bonus.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>
                            Bonus Management
                        </p>
                    </a>
                </li>
            @endif

            @if (Auth::user()->role === 'admin')
                <!-- Bonus Management Link -->
                <li class="nav-item">
                    <a href="{{ route('ph.index') }}"
                        class="nav-link {{ request()->routeIs('ph.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-wallet"></i>
                        <p>
                            Payment History
                        </p>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
