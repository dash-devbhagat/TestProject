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
                        <h1>Staff Member Details</h1>
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
                            <p><strong>Name:</strong> {{ $user->name  }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Phone:</strong> {{ $user->phone ?? 'Not Completed' }}</p>
                            <p><strong>Location:</strong> {{ $user->location ?? 'Not Completed' }}</p>
                        </div>

                        <!-- Right side: Bonus Details -->
                        {{-- <div class="col-md-6">
                            <h3 class="text-primary">Bonus Details</h3>
                            <p><strong>Bonus Amount:</strong> $1500.00</p>
                            <p><strong>Bonus Type:</strong> Year-End Bonus</p>
                            <p><strong>Bonus Status:</strong> Approved</p>
                        </div> --}}
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
