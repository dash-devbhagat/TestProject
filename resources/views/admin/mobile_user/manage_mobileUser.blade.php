@extends('layouts.master')

@section('title', 'User Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage User</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        {{-- Bootstrap Alert --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main content -->
        <section class="content">

            <div class="card">
                {{-- <div class="card-header">
                    <h3 class="card-title">DataTable with minimal features & hover style</h3>
                </div> --}}
                <!-- /.card-header -->
                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Birthdate</th>
                                <th>Referral Code</th>
                                <th>Profile Status</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($mobileUsers as $user)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->gender }}</td>
                                    <td>{{ $user->birthdate }}</td>
                                    <td>{{ $user->referral_code }}</td>
                                    <td>
                                        @if ($user->is_profile_complete)
                                            <p class="text-success">Completed</p>
                                        @else
                                            <p class="text-danger">Incomplete</p>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $user->id }}"
                                            data-id="{{ $user->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $user->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- View Icon -->
                                        <a href="{{ route('mobileUser.show', $user->id) }}" class="text-secondary"
                                            data-bs-toggle="tooltip" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
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

        </section>
        <!-- /.content -->
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var userId = $(this).data('id');

                $.ajax({
                    url: '/mobileUser/' + userId +
                        '/toggle-status', // Use the route for toggling user status
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(), // CSRF token
                    },
                    success: function(response) {
                        // Optionally, display a success message
                        // alert(response.message);
                        location.reload();
                    },
                    error: function() {
                        alert('An error occurred while toggling user status.');
                    }
                });
            });

        });
    </script>
@endsection
