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
                                <th>Product Name</th>
                                <th>Order Number</th>
                                <th>Order Date</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                                <th>Amount Paid</th>
                                <th>Payment Status</th>
                                {{-- A unique invoice number for the payment (if applicable). --}}
                                <th>Invoice Number</th>
                                {{-- Identifier provided by the payment gateway (e.g., PayPal, Stripe). --}}
                                <th>Transaction ID</th>
                                {{-- View Invoice (to view or download the invoice as a PDF). --}}
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            {{-- @foreach ($payments as $payment)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $payment->user->name }}</td>
                                <td>{{ $payment->bonus->type }}</td>
                                <td>{{ $payment->amount }}</td>
                                <td>{{ ucfirst($payment->payment_status) }}</td>
                                <td>{{ $payment->paymentParent ? $payment->paymentParent->name : 'N/A' }}</td>
                                <td>{{ $payment->paymentChild ? $payment->paymentChild->name : 'N/A' }}</td>
                                <td>{{ $payment->created_at->format('d-m-Y') }}</td>
                            </tr>
                                @php
                                    $i++;
                                @endphp
                            @endforeach --}}
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
