  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
          <li class="nav-item">
              <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
              <a href="{{ route('dashboard') }}" class="nav-link">Home</a>
          </li>
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
          <!-- Navbar Search -->
          <li class="nav-item">
              <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                  <i class="fas fa-search"></i>
              </a>
              <div class="navbar-search-block">
                  <form class="form-inline">
                      <div class="input-group input-group-sm">
                          <input class="form-control form-control-navbar" type="search" placeholder="Search"
                              aria-label="Search">
                          <div class="input-group-append">
                              <button class="btn btn-navbar" type="submit">
                                  <i class="fas fa-search"></i>
                              </button>
                              <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                  <i class="fas fa-times"></i>
                              </button>
                          </div>
                      </div>
                  </form>
              </div>
          </li>

          {{-- start profile page --}}
          <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                  {{-- <img src="adminlte/dist/img/{{ Auth::user()->logo }}" class="user-image img-circle elevation-2"
                      alt="User Image" onerror="this.onerror=null; this.src='adminlte/dist/img/user.png';"> --}}
                      @if (Auth::user()->logo && file_exists(public_path('storage/' . Auth::user()->logo)))
    <img src="{{ asset('storage/' . Auth::user()->logo) }}" class="user-image img-circle elevation-2" alt="User Image">
@else
    <img src="{{ asset('adminlte/dist/img/user.png') }}" class="user-image img-circle elevation-2" alt="Default User Image">
@endif

                  <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                  <!-- User image -->
                  <li class="user-header bg-primary">
                      {{-- <img src="adminlte/dist/img/{{ Auth::user()->logo }}" class="img-circle elevation-2" alt="User Image" onerror="this.onerror=null; this.src='adminlte/dist/img/user.png';"> --}}
                      @if (Auth::user()->logo && file_exists(public_path('storage/' . Auth::user()->logo)))
    <img src="{{ asset('storage/' . Auth::user()->logo) }}" class="user-image img-circle elevation-2" alt="User Image">
@else
    <img src="{{ asset('adminlte/dist/img/user.png') }}" class="user-image img-circle elevation-2" alt="Default User Image">
@endif
                      <p>
                          Email: {{ Auth::user()->email }}
                      </p>
                  </li>
 <li class="user-footer d-flex justify-content-between">
    <!-- Change Password Button -->
 <a class="btn btn-default btn-flat" href="{{ route('change.password') }}" role="button">
        Change Password
    </a>

    <!-- Sign Out Button -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-default btn-flat">Sign out</button>
    </form>
</li>

              </ul>
          </li>
          {{-- end profile page --}}
          <li class="nav-item">
              <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                  <i class="fas fa-expand-arrows-alt"></i>
              </a>
          </li>
          {{-- <li class="nav-item">
              <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                  <i class="fas fa-th-large"></i>
              </a>
          </li> --}}
      </ul>
  </nav>
