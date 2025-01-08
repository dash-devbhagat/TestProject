<!-- resources/views/home.blade.php -->
@extends('layouts.master')

@section('title', 'Staff Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6 d-flex">
                        <h1>Bonus Payment History</h1>
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
                                <th>User</th>
                                <th>Bonus</th>
                                <th>Amount</th>
                                <th>Payment Status</th>
                                <th>Referred By</th>
                                <th>Referred To</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($payments as $payment)
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
