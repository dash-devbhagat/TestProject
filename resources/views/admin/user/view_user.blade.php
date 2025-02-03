@extends('layouts.master')

@section('title', 'Staff Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <!-- Heading on the left -->
                    <div class="col-sm-6">
                        <h1>Staff Member Details</h1>
                    </div>
                    <!-- Back button on the right -->
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('user.index') }}" class="btn btn-secondary text-light">
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
                                        <td><strong>Name:</strong></td>
                                        <td>{{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td>{{ $user->phone ?? 'Not Available' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Location:</strong></td>
                                        <td>{{ $user->location ?? 'Not Available' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Longtitude:</strong></td>
                                        <td>{{ $user->longitude ?? 'Not Available' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Latitude:</strong></td>
                                        <td>{{ $user->latitude ?? 'Not Available' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Profile Status:</strong></td>
                                        <td>
                                            @if ($user->isProfile)
                                                <p class="text-success">Completed</p>
                                            @else
                                                <p class="text-danger">Incomplete</p>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Logo</th>
                                        <td>
                                            @if ($user->logo)
                                                <img src="{{ asset('storage/' . $user->logo) }}" alt="Logo"
                                                    width="200" height="200" onerror="this.onerror=null; this.src='{{ asset('adminlte/dist/img/inf.png') }}';">
                                            @else
                                                No Image Available
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
                   
                </div>
            </div>
            <!-- /.card -->
        </section>
        <!-- /.content -->
    </div>
@endsection
