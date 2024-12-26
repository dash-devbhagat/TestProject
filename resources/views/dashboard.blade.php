<!-- resources/views/home.blade.php -->
@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                        @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        // Auto-close alert after 2 seconds
        setTimeout(function () {
            let alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show'); // Close the alert
                alert.addEventListener('transitionend', () => alert.remove()); // Remove from DOM
            }
        }, 2000); // 2 seconds
    </script>
@endif

                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">

            <!-- Default box -->
            <div class="card">
                <div class="card-header">
                    @if (Auth::user()->role === 'admin')
                        <h3 class="card-title">Admin Dashboard</h3>
                    @else
                        <h3 class="card-title">User Dashboard</h3>
                    @endif

        </div>
        <div class="card-body">
         @if (Auth::user()->role === 'admin')
        <p>Welcome, {{ Auth::user()->name }}!</p>
    @else
    <p>Welcome, {{ Auth::user()->name }}!</p>
    <div>
       <ul>
            <li><strong>Name:</strong> {{ Auth::user()->name }}</li>
            <li><strong>Email:</strong> {{ Auth::user()->email }}</li>
            <li><strong>Phone:</strong> {{ Auth::user()->phone ?? 'Not provided' }}</li>
            <li><strong>Store Name:</strong> {{ Auth::user()->storename ?? 'Not provided' }}</li>
            <li><strong>Location:</strong> {{ Auth::user()->location ?? 'Not provided' }}</li>
            <li><strong>Latitude:</strong> {{ Auth::user()->latitude ?? 'Not provided' }}</li>
            <li><strong>Longitude:</strong> {{ Auth::user()->longitude ?? 'Not provided' }}</li>
          </ul>
    </div>
    <div>
                </div>
    @endif


        </div>
        <!-- /.card-body -->
        {{-- <div class="card-footer">
          <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger">Logout</button>
          </form>
        </div> --}}
        <!-- /.card-footer-->
      </div>
      <!-- /.card -->

        </section>
        <!-- /.content -->
    </div>
@endsection
