@extends('layouts.master')

@section('title', 'State Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage States</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    {{-- <div class="col-sm-6 text-right">
                        <a href="{{ route('sub-category.index') }}" class="btn btn-success">Add Sub
                            Categories</a>
                    </div> --}}

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

                </div>

                <form action="{{ route('state.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <!-- Input Field -->
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <label for="name">State Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="Enter State Name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                
                        <!-- Save Button -->
                        <div class="form-group row mt-3">
                            <div class="col-sm-4">
                                <button type="submit" id="saveCatBtn" class="btn btn-primary">Save</button>
                            </div>
                        </div>
                    </div>
                </form>
                
                
                
                


            </div><!-- /.container-fluid -->
        </section>


        <!-- Main content -->
        <section class="content">

            <div class="card">
                <!-- /.card-header -->

                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Name</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp

                            @foreach ($states as $state)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $state->name }}</td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $state->id }}"
                                            data-id="{{ $state->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $state->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $state->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editStateBtn" data-id="{{ $state->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('state.destroy', $state->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this state?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 m-0"
                                                data-bs-toggle="tooltip" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>

                <!-- /.card-body -->
            </div>

        </section>
        <!-- /.content -->
    </div>


    <!-- Edit Charge Modal -->
    <div class="modal fade" id="editStateModal" tabindex="-1" role="dialog" aria-labelledby="editStateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStateModalLabel">Edit State</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editStateForm" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editStateId" name="id">
                        <div class="form-group">
                            <label for="editStateName">State Name</label>
                            <input type="text" class="form-control" id="editStateName" name="name">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" id="updateStateBtn" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateStateBtn" class="btn btn-primary">Save changes</button>
                </div> --}}
            </div>
        </div>
    </div>



@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Open the Edit Modal and Populate Data
            $('.editStateBtn').on('click', function() {
                const stateId = $(this).data('id');
                // console.log(stateId);

                $.ajax({
                    url: '/state/' + stateId + '/edit',
                    type: "GET",
                    success: function(response) {
                        // console.log(response);
                        const state = response.state;
                        $('#editStateName').val(state.name);
                        $('#editStateId').val(state.id);
                        $('#editStateForm').attr('action', '/state/' + state
                            .id); // Set form action
                        $('#editStateModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching category data.');
                    }
                });
            });

            // Update the state
            $('#updateStateBtn').on('click', function() {
                const formData = new FormData($('#editStateForm')[0]);
                formData.append('_method', 'PUT'); // Add method override for PUT

                $.ajax({
                    url: '/state/' + $('#editStateId')
                        .val(), 
                    type: "POST", // Use POST since we're using _method override for PUT
                    data: formData,
                    contentType: false, // Necessary for file uploads
                    processData: false, // Prevents jQuery from processing the data
                    success: function(response) {
                        $('#editStateModal').modal('hide');
                        location.reload(); 
                    },
                    error: function() {
                        alert('Error updating data. Please try again.');
                    }
                });
            });


            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var stateId = $(this).data('id');

                $.ajax({
                    url: '/state/' + stateId +
                        '/toggle-status', // Use the route for toggling status
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
