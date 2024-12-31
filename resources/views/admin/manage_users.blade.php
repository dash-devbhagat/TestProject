@extends('layouts.master')

@section('title', 'Staff Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Staff</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    <div class="col-sm-6 text-right">
                        {{-- <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            Add User
                        </button> --}}
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addUserModal">Add
                            Staff</button>
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
                                <th>Profile Status</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->isProfile)
                                            True
                                        @else
                                            False
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
                                        <a href="{{ route('user.show', $user->id) }}" class="text-secondary"
                                            data-bs-toggle="tooltip" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-toggle="modal"
                                            data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editUserBtn" data-id="{{ $user->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('user.destroy', $user->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 m-0"
                                                data-bs-toggle="tooltip" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
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


    {{-- Add User modal --}}
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New Staff Member</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="col-form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control"
                                placeholder="Enter Name">
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                placeholder="Enter Email">
                        </div>
                        <div class="form-group">
                            <label for="password" class="col-form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Enter Password">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="saveUserBtn" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit Staff</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- Hidden field to store user ID -->
                        <input type="hidden" id="editUserId" name="id">
                        <div class="form-group">
                            <label for="editName" class="col-form-label">Name</label>
                            <input type="text" name="name" id="editName" class="form-control"
                                placeholder="Enter Name">
                        </div>
                        <div class="form-group">
                            <label for="editEmail" class="col-form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-control"
                                placeholder="Enter Email">
                        </div>
                        <div class="form-group">
                            <label for="editPhone" class="col-form-label">Phone</label>
                            <input type="text" name="phone" id="editPhone" class="form-control"
                                placeholder="Enter Phone">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateUserBtn" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Add User
            $('#saveUserBtn').on('click', function() {
                // Collect form data
                let formData = {
                    name: $('#name').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    _token: $('input[name="_token"]').val(),
                };

                // Perform AJAX Request
                $.ajax({
                    url: "{{ route('user.store') }}", // Route to store method
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        // Close the modal
                        $('#addUserModal').modal('hide');

                        // Display success message
                        // alert(response.message);

                        // Optionally, reload or dynamically update the user list
                        location.reload();
                    },
                    error: function(xhr) {
                        // Handle validation errors
                        let errors = xhr.responseJSON.errors;
                        if (errors) {
                            // if (errors.name) alert("Error: " + errors.name);
                            // if (errors.email) alert("Error: " + errors.email);
                            // if (errors.password) alert("Error: " + errors.password);
                            console.log(
                                "error occured in name or email or password. remove above comment and know what is wrong."
                            );
                        } else {
                            alert("An error occurred. Please try again.");
                        }
                    }
                });
            });

            // On Edit Icon Click Modal Open
            $('.editUserBtn').on('click', function() {
                // Fetch the user ID from data-id attribute
                const userId = $(this).data('id');

                // Fetch user data via AJAX
                $.ajax({
                    url: '/user/' + userId +
                        '/edit', // Replace with your route for fetching user data
                    type: "GET",
                    success: function(response) {
                        // Populate modal fields with fetched data
                        $('#editName').val(response.user.name);
                        $('#editEmail').val(response.user.email);
                        $('#editPhone').val(response.user.phone);
                        $('#editUserId').val(response.user
                            .id); // Store the user ID in a hidden field

                        // Show the modal
                        $('#editUserModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching user data. Please try again.');
                    }
                });
            });


            // Update User
            $('#updateUserBtn').on('click', function() {
                const formData = {
                    id: $('#editUserId').val(),
                    name: $('#editName').val(),
                    email: $('#editEmail').val(),
                    _token: $('input[name="_token"]').val(),
                };

                $.ajax({
                    url: '/user/' + formData.id,
                    type: "PUT",
                    data: formData,
                    success: function(response) {
                        // Close the modal
                        $('#editUserModal').modal('hide');

                        // Display success message
                        // alert('User updated successfully!');

                        // Optionally, reload or update the user list
                        location.reload();
                    },
                    error: function(xhr) {
                        // Handle errors
                        alert('Error updating user data. Please try again.');
                    }
                });
            });

            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var userId = $(this).data('id');

                $.ajax({
                    url: '/user/' + userId +
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
