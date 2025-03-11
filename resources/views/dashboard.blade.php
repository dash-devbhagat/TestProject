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
                        @if (Auth::user()->branch_id)
                        - Branch Manager ({{ Auth::user()->branch->name }})
                        @endif
                        @endif
                    </h1>
                </div>
            </div>
        </div>

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
                    alert.classList.remove('show');
                    alert.addEventListener('transitionend', () => alert.remove());
                }
            }, 2000);
        </script>
        @endif

    </section>

    <!-- Main content -->
    <section class="content">
        @if (Auth::user()->role === 'admin')
        <div class="container-fluid">
            <!-- Order Overview Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">Order Overview</h3>
                    <!-- Show All Orders Button -->
                    <div class="text-center float-end">
                        <button class="btn btn-secondary" id="showAllOrdersButton">Show All Orders</button>
                    </div>
                    <!-- View Cancelled Orders -->
                    <div class="text-center float-end mr-3">
                        <a href="{{ route('cancelled-orders') }}" class="btn btn-outline-danger">View Cancelled
                            Orders ({{ $cancelledOrdersCount }})</a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Order Status Filter Cards -->
                    <div class="row mb-4">
                        <!-- Pending Orders Filter Card -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card bg-white text-danger h-100 filter-card" data-status="pending">
                                <div class="card-header">
                                    <h5 class="mb-0">Pending Orders</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h3 class="mb-3">{{ $pendingOrders }}</h3>
                                </div>
                            </div>
                        </div>

                        <!-- In Progress Orders Filter Card -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card bg-white text-dark h-100 filter-card" data-status="in progress">
                                <div class="card-header">
                                    <h5 class="mb-0">In Progress Orders</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h3 class="mb-3">{{ $inProgressOrders }}</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Completed Orders Filter Card -->
                        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                            <div class="card bg-white text-success h-100 filter-card" data-status="delivered">
                                <div class="card-header">
                                    <h5 class="mb-0">Completed Orders</h5>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h3 class="mb-3">{{ $completedOrders }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Order Cards -->
                    <div id="dynamic-cards">
                        <h5 class="mb-3">Orders</h5>
                        <div class="row" id="ordersContainer">
                            @forelse ($orders as $order)
                            @php
                            // Determine card classes based on order status
                            switch ($order->status) {
                            case 'pending':
                            $cardClass = 'bg-danger text-white';
                            $headerText = 'Pending Order';
                            break;
                            case 'in progress':
                            $cardClass = 'bg-warning text-dark';
                            $headerText = 'In Progress Order';
                            break;
                            case 'delivered':
                            $cardClass = 'bg-success text-white';
                            $headerText = 'Completed Order';
                            break;
                            default:
                            $cardClass = 'bg-secondary text-white';
                            $headerText = 'Cancelled Order';
                            break;
                            }
                            @endphp
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4 order-card"
                                data-status="{{ $order->status }}">
                                <a href="{{ route('order.show', $order->id) }}"
                                    class="text-decoration-none">
                                    <div class="card {{ $cardClass }} h-100">
                                        <div class="card-header">{{ $headerText }}</div>
                                        <div class="card-body">
                                            <h5 class="card-title">Order #{{ $order->id }}</h5>
                                            <p class="card-text">
                                                <strong>User:</strong> {{ $order->user->name }} <br>
                                                <strong>Total:</strong>
                                                ₹{{ number_format($order->grand_total, 2) }}<br>
                                                <strong>Status:</strong> {{ ucfirst($order->status) }} <br>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="alert alert-info" role="alert">
                                    No orders available.
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if (Auth::user()->role === 'user')
        <!-- Staff Profile Card -->
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your Profile</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Name:</strong> {{ Auth::user()->name }}</li>
                        <li class="list-group-item"><strong>Email:</strong> {{ Auth::user()->email }}</li>
                        <li class="list-group-item"><strong>Phone:</strong>
                            {{ Auth::user()->phone ?? 'Not provided' }}
                        </li>
                        <li class="list-group-item"><strong>Store Name:</strong>
                            {{ Auth::user()->storename ?? 'Not provided' }}
                        </li>
                        <li class="list-group-item"><strong>Location:</strong>
                            {{ Auth::user()->location ?? 'Not provided' }}
                        </li>
                        <li class="list-group-item"><strong>Latitude:</strong>
                            {{ Auth::user()->latitude ?? 'Not provided' }}
                        </li>
                        <li class="list-group-item"><strong>Longitude:</strong>
                            {{ Auth::user()->longitude ?? 'Not provided' }}
                        </li>
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('user.edit-profile') }}" class="btn btn-primary">Update Profile</a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </section>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterCards = document.querySelectorAll('.filter-card');
        const showAllOrdersButton = document.getElementById('showAllOrdersButton');

        // Add event listeners for the filter cards
        filterCards.forEach(card => {
            card.addEventListener('click', function(event) {
                const status = event.target.closest('.filter-card').getAttribute('data-status');
                filterOrders(status);
            });
        });

        // Event listener for the Show All Orders button
        showAllOrdersButton.addEventListener('click', function() {
            filterOrders('all', true);
        });
    });

    function filterOrders(status, showCancelled = false) {
        const allCards = document.querySelectorAll('.order-card');

        if (status === 'all' && showCancelled) {
            allCards.forEach(card => card.style.display = 'block');
        } else if (status === 'all') {
            allCards.forEach(card => {
                card.style.display = card.getAttribute('data-status') === 'cancelled' ? 'none' : 'block';
            });
        } else {
            allCards.forEach(card => {
                card.style.display = card.getAttribute('data-status') === status ? 'block' : 'none';
            });
        }
    }
</script>
@endsection