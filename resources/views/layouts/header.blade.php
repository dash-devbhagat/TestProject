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
                  <img src="adminlte/dist/img/{{ Auth::user()->logo }}" class="user-image img-circle elevation-2"
                      alt="User Image">
                  <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                  <!-- User image -->
                  <li class="user-header bg-primary">
                      <img src="adminlte/dist/img/{{ Auth::user()->logo }}" class="img-circle elevation-2" alt="User Image">

                      <p>
                          Email: {{ Auth::user()->email }}
                      </p>
                      <p>
                          Phone: {{ Auth::user()->phone }}
                      </p>
                  </li>
                  <li class="user-footer">
                    <form action="{{ route('logout') }}" method="POST">
                      @csrf
                      <button type="submit" class="btn btn-default btn-flat float-right">Sign out</button>
                    </form>
                    {{-- <a href="#" class="btn btn-default btn-flat">Profile</a> --}}
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
