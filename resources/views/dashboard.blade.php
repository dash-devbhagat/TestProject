@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>
                            @if (Auth::user()->role === 'admin')
                                Admin Dashboard
                            @else
                                Staff Dashboard
                            @endif
                        </h1>
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <script>
                                // Auto-close alert after 2 seconds
                                setTimeout(function() {
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
            <!-- Dashboard Content -->
            @if (Auth::user()->role === 'admin')
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order Overview</h3>
                    </div>
                    {{-- <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card bg-danger text-white mb-3">
                                    <div class="card-header">Pending Orders</div>
                                    <div class="card-body">
                                        <h3>{{ $pendingOrders ?? '0' }}</h3>
                                        <p>Orders that are waiting to be processed.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card bg-warning text-dark mb-3">
                                    <div class="card-header">In Progress Orders</div>
                                    <div class="card-body">
                                        <h3>{{ $inProgressOrders ?? '0' }}</h3>
                                        <p>Orders currently being processed.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card bg-success text-white mb-3">
                                    <div class="card-header">Completed Orders</div>
                                    <div class="card-body">
                                        <h3>{{ $completedOrders ?? '0' }}</h3>
                                        <p>Orders that have been completed successfully.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="container-fluid">
                        <!-- Order Status Cards -->
                        <div class="row mb-4">
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card bg-danger text-white mb-3 status-card" data-status="pending">
                                    <div class="card-header">Pending Orders</div>
                                    <div class="card-body">
                                        <h3>{{ $pendingOrders ?? '0' }}</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card bg-warning text-dark mb-3 status-card" data-status="in-progress">
                                    <div class="card-header">In Progress Orders</div>
                                    <div class="card-body">
                                        <h3>{{ $inProgressOrders ?? '0' }}</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card bg-success text-white mb-3 status-card" data-status="completed">
                                    <div class="card-header">Completed Orders</div>
                                    <div class="card-body">
                                        <h3>{{ $completedOrders ?? '0' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>

                        <!-- Dynamic Cards -->
                        <div id="dynamic-cards">
                            <h5 class="mb-3">Orders</h5>
                            <div class="row">
                                <!-- Default View (All Cards) -->
                                <div class="card text-danger bg-white mb-3 mx-3" style="max-width: 18rem;">
                                    <div class="card-header">Header</div>
                                    <div class="card-body">
                                        <h5 class="card-title">Danger card title</h5>
                                        <p class="card-text">Pending Orders Card Example</p>
                                    </div>
                                </div>
                                <div class="card text-warning bg-white mb-3 mx-3" style="max-width: 18rem;">
                                    <div class="card-header">Header</div>
                                    <div class="card-body">
                                        <h5 class="card-title">Warning card title</h5>
                                        <p class="card-text">In Progress Orders Card Example</p>
                                    </div>
                                </div>
                                <div class="card text-success bg-white mb-3 mx-3" style="max-width: 18rem;">
                                    <div class="card-header">Header</div>
                                    <div class="card-body">
                                        <h5 class="card-title">Success card title</h5>
                                        <p class="card-text">Completed Orders Card Example</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Your Profile</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Name:</strong> {{ Auth::user()->name }}</li>
                            <li class="list-group-item"><strong>Email:</strong> {{ Auth::user()->email }}</li>
                            <li class="list-group-item"><strong>Phone:</strong> {{ Auth::user()->phone ?? 'Not provided' }}
                            </li>
                            <li class="list-group-item"><strong>Store Name:</strong>
                                {{ Auth::user()->storename ?? 'Not provided' }}</li>
                            <li class="list-group-item"><strong>Location:</strong>
                                {{ Auth::user()->location ?? 'Not provided' }}</li>
                            <li class="list-group-item"><strong>Latitude:</strong>
                                {{ Auth::user()->latitude ?? 'Not provided' }}</li>
                            <li class="list-group-item"><strong>Longitude:</strong>
                                {{ Auth::user()->longitude ?? 'Not provided' }}</li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('user.edit-profile') }}" class="btn btn-primary">Update Profile</a>
                        </div>
                    </div>
                </div>
            @endif
        </section>
        <!-- /.content -->
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusCards = document.querySelectorAll('.status-card');
        const dynamicCards = document.getElementById('dynamic-cards');

        const cardTemplates = {
            pending: `
                <div class="card text-danger bg-white mb-3" style="max-width: 18rem;">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h5 class="card-title">Danger card title</h5>
                        <p class="card-text">Pending Orders Card Example</p>
                    </div>
                </div>
            `,
            'in-progress': `
                <div class="card text-warning bg-white mb-3" style="max-width: 18rem;">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h5 class="card-title">Warning card title</h5>
                        <p class="card-text">In Progress Orders Card Example</p>
                    </div>
                </div>
            `,
            completed: `
                <div class="card text-success bg-white mb-3" style="max-width: 18rem;">
                    <div class="card-header">Header</div>
                    <div class="card-body">
                        <h5 class="card-title">Success card title</h5>
                        <p class="card-text">Completed Orders Card Example</p>
                    </div>
                </div>
            `,
        };

        statusCards.forEach(card => {
            card.addEventListener('click', function() {
                const status = this.dataset.status;

                // Update the dynamic cards section
                dynamicCards.innerHTML = cardTemplates[status];
            });
        });
    });
</script>
