<!-- resources/views/home.blade.php -->
@extends('layouts.master')

@section('title', 'Order Payment History')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6 d-flex">
                        <h1>Order Payment History</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">

            <!-- Default box -->
            <div class="card">
                {{-- <div class="card-header">
                   Test
                </div> --}}
                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Transaction Number</th>
                                <th>User Name</th>
                                <th>Order Number</th>
                                <th>Order Status</th>
                                <th>Order Date</th>
                                <th>Payment Date</th>
                                <th>Payment Mode</th>
                                <th>Payment Type</th>
                                <th>Amount Paid</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($transactions as $transaction)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $transaction->transaction_number }}</td>
                                    <td>{{ $transaction->user->name }}</td>
                                    <td>{{ $transaction->order->order_number }}</td>
                                    <td
                                        class="
                                    {{ $transaction->order->status === 'delivered' ? 'text-success' : '' }}
                                    {{ $transaction->order->status === 'in progress' ? 'text-info' : '' }}
                                    {{ in_array($transaction->order->status, ['pending', 'cancelled']) ? 'text-danger' : '' }}">
                                        {{ ucfirst($transaction->order->status) }}
                                    </td>
                                    <td>{{ $transaction->order->created_at->format('d-m-Y') }}</td>
                                    <td>{{ $transaction->created_at->format('d-m-Y') }}</td>
                                    <td>{{ ucfirst($transaction->payment_mode) }}</td>
                                    <td>{{ ucfirst($transaction->payment_type) }}</td>
                                    <td>₹{{ $transaction->order->grand_total }}</td>
                                    <td
                                        class="{{ $transaction->payment_status === 'success' ? 'text-success' : ($transaction->payment_status === 'failed' || $transaction->payment_status === 'pending' ? 'text-danger' : '') }}">
                                        {{ ucfirst($transaction->payment_status) }}
                                    </td>
                                </tr>
                                @php
                                    $i++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->


            </div>
            <!-- /.card -->

        </section>
        <!-- /.content -->
    </div>
@endsection
