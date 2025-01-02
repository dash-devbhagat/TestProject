<!-- resources/views/home.blade.php -->
@extends('layouts.master')

@section('title', 'User Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 d-flex">
                    <h1>User Details</h1>
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
                <div class="row">
                    <!-- Left side: Personal Details -->
                    <div class="col-md-6">
                        <h3 class="text-primary">Personal Details</h3>
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Phone:</strong> {{ $user->phone ?? 'Not Completed' }}</p>
                        <p><strong>Referrel Code:</strong> {{ $user->referral_code ?? 'Not Completed' }}</p>
                        <p><strong>Gender:</strong> {{ $user->gender ?? 'Not Completed' }}</p>
                        <p><strong>Birthdate:</strong> {{ $user->birthdate ?? 'Not Completed' }}</p>

                    </div>

                    <!-- Right side: Bonus Details -->
                    {{-- <div class="col-md-6">
                        <h3 class="text-primary">Bonus Details</h3>

                        @php
                        // Group payments by bonus_id and sum the amount for each bonus_id
                        $bonusTotals = $user->payments->groupBy('bonus_id')->map(function ($payments) {
                        return $payments->sum('amount');
                        });
                        @endphp

                        <p><strong>Total Bonus Amount:</strong> ${{ number_format($bonusTotals->sum(), 2) }}</p>

                        <h4 class="mt-3">Individual Bonuses:</h4>
                        @foreach ($bonusTotals as $bonusId => $totalAmount)
                        <p><strong>Bonus ID:</strong> {{ $bonusId }} - <strong>Total Amount:</strong> ${{
                            number_format($totalAmount, 2) }}</p>
                        @endforeach
                    </div> --}}

                    <div class="col-md-6">
                        <h3 class="text-primary">Bonus Details</h3>

                        @php
                        // Group payments by bonus_id and sum the amount for each bonus_id
                        $bonusTotals = $user->payments->groupBy('bonus_id')->map(function ($payments) {
                        return $payments->sum('amount');
                        });
                        @endphp

                        <p><strong>Total Bonus Amount:</strong> ${{ number_format($bonusTotals->sum(), 2) }}</p>

                        <h4 class="mt-3">Individual Bonuses:</h4>
                        @foreach ($bonusTotals as $bonusId => $totalAmount)
                        @php
                        // Get the bonus name from the payment's bonus relationship
                        $bonus = $user->payments->firstWhere('bonus_id', $bonusId)->bonus;
                        @endphp
                        <p>
                            <strong>Bonus Name:</strong> {{ $bonus ? $bonus->type : 'Unknown' }} -
                            <strong>Amount:</strong> ${{ number_format($totalAmount, 2) }}
                        </p>
                        @endforeach
                    </div>


                </div>


            </div>
            <!-- /.card-body -->


        </div>
        <!-- /.card -->

        <!-- Go Back Icon and Title -->
        <a href="{{ url()->previous() }}" class="btn btn-secondary text-light">
            <i class="fas fa-arrow-left"></i> Back
        </a>

    </section>
    <!-- /.content -->
</div>
@endsection