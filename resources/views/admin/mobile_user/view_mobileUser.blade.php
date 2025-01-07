@extends('layouts.master')

@section('title', 'User Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <!-- Heading on the left -->
                    <div class="col-sm-6">
                        <h1>User Details</h1>
                    </div>
                    <!-- Back button on the right -->
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('mobileUser.index') }}" class="btn btn-secondary text-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>


        <!-- Main content -->
        <section class="content">

            <!-- Default box -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Personal Details Table -->
                        <div class="col-md-6">
                            <h3 class="text-primary">Personal Details</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Name</strong></td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email</strong></td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone</strong></td>
                                        <td>{{ $user->phone ?? 'Not Completed' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Referral Code</strong></td>
                                        <td>{{ $user->referral_code ?? 'Not Completed' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gender</strong></td>
                                        <td>{{ $user->gender ?? 'Not Completed' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Birthdate</strong></td>
                                        <td>{{ $user->birthdate ?? 'Not Completed' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bonus Details Table -->
                        <div class="col-md-6">
                            <h3 class="text-primary">Bonus Details</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Bonus Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Group payments by bonus_id and sum the amount for each bonus_id
                                        $bonusTotals = $user->payments->groupBy('bonus_id')->map(function ($payments) {
                                            return $payments->sum('amount');
                                        });
                                    @endphp

                                    @foreach ($bonusTotals as $bonusId => $totalAmount)
                                        @php
                                            // Get the bonus name from the payment's bonus relationship
                                            $bonus = $user->payments->firstWhere('bonus_id', $bonusId)->bonus;
                                            $bonusType = $bonus ? ucfirst($bonus->type) : 'Unknown';
                                        @endphp
                                        <tr>
                                            <td>
                                                @if ($bonus && $bonus->type == 'Signup')
                                                    Signup Bonus
                                                @elseif ($bonus && $bonus->type == 'Referral')
                                                    Referral Bonus
                                                @else
                                                    {{ $bonusType }} Bonus
                                                @endif
                                            </td>
                                            {{-- <td>${{ number_format($totalAmount, 2) }}</td> --}}
                                            <td>
                                                @php
                                                    $payment = $user->payments->firstWhere('bonus_id', $bonusId);
                                                @endphp
                                                @if ($payment && $payment->payment_status === 'pending')
                                                    <span class="text-danger">${{ number_format($totalAmount, 2) }}</span>
                                                @elseif ($payment && $payment->payment_status === 'completed')
                                                    <span class="text-success">${{ number_format($totalAmount, 2) }}</span>
                                                @endif
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td><strong>Total Bonus Amount</strong></td>
                                        <td>
                                            @if ($bonusTotals->sum() == 0)
                                                <span class="text-danger"><strong>${{ number_format($bonusTotals->sum(), 2) }}</strong></span>
                                            @else
                                                <strong>${{ number_format($bonusTotals->sum(), 2) }}</strong>
                                            @endif
                                        </td>
                                    </tr>                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    {{-- <a href="{{ url()->previous() }}" class="btn btn-secondary text-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a> --}}
                </div>
            </div>
            <!-- /.card -->
        </section>
        <!-- /.content -->
    </div>
@endsection
