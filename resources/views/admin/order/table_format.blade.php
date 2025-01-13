@extends('layouts.master')

@section('title', ucfirst($status) . ' Orders')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>{{ ucfirst($status) }} Orders</h1>
                    </div>
                    <!-- Back button on the right -->
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary text-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ ucfirst($status) }} Orders</h3>
                    </div>
                    <div class="card-body">
                        <table id="usersTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Address</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ $order->user->name }}</td>
                                        <td>${{ number_format($order->grand_total, 2) }}</td>
                                        <td>{{ ucfirst($order->status) }}</td>      
                                        <td> {{ $order->address->address_line }},
                                            {{ $order->address->city->name ?? 'N/A' }}</td>
                                        <td class="text-center"> <a href="{{ route('order.show', $order->id) }}"
                                                class="text-secondary" data-bs-toggle="tooltip" title="View"><i
                                                    class="fa fa-eye"></i></a></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No orders found.</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
