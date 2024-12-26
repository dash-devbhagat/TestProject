    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        {{-- <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="adminlte/dist/img/{{ Auth::user()->logo }}" class="img-circle elevation-2" alt="User Image"> --}}
                {{-- @if (Auth::check())
        @if (Auth::user()->role === 'admin')
          <!-- Admin logo -->
          <img src="{{ Auth::user()->logo ? asset('storage/' . Auth::user()->logo) : asset('adminlte/dist/img/admin-default.jpg') }}" class="img-circle elevation-2" alt="Admin Logo">
        @else
          <!-- User logo -->
          <img src="{{ Auth::user()->logo ? asset('storage/' . Auth::user()->logo) : asset('adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Logo">
        @endif
      @else
        <!-- Default image for guest -->
        <img src="{{ asset('adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="Guest Image">
      @endif --}}
            {{-- </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div> --}}


        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->

                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>

                @if(Auth::user()->role === 'admin')
                <li class="nav-item">
                  <a href="{{ route('user.index') }}" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                      <p>
                          User Managemant
                      </p>
                  </a>
              </li>
              @endif

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon far fa-image"></i>
                        <p>
                            Gallery
                        </p>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
