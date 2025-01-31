@extends('layouts.master')

@section('title', 'City Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Cities</h1>
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

                <form action="{{ route('city.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <!-- Input Field -->
                        <div class="form-group row">

                            <div class="col-sm-6">
                                <label for="stateCity">Select State</label>
                                <select class="form-control @error('state_id') is-invalid @enderror" id="stateCity"
                                    name="state_id" value="{{ old('state_id') }}">
                                    <option value="" disabled selected>Select State</option>
                                    @foreach ($states as $state)
                                        @if ($state->is_active == '1')
                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('state_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="city_name">City Name</label>
                                <input type="text" class="form-control @error('city_name') is-invalid @enderror"
                                    id="city_name" name="city_name" placeholder="Enter City Name" value="{{ old('city_name') }}">
                                @error('city_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <!-- Save Button -->
                        <div class="card-footer">
                            <button type="submit" id="saveCatBtn" class="btn btn-primary float-right">Save</button>
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
                                <th>City Name</th>
                                <th>State Name</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp

                            @foreach ($cities as $city)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $city->name }}</td>
                                    <td>{{ $city->state->name }}</td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $city->id }}"
                                            data-id="{{ $city->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $city->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $city->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editCityBtn" data-id="{{ $city->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('city.destroy', $city->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this city?');">
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
    <div class="modal fade" id="editCityModal" tabindex="-1" role="dialog" aria-labelledby="editCityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCityModalLabel">Edit State</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCityForm" method="POST" action="">
                        @csrf
                        {{-- @method('PUT') --}}
                        <input type="hidden" id="editCityId" name="id">
                        <div class="form-group">
                            <label for="editCityState">Select State</label>
                            <select class="form-control" id="editCityState" name="state_id" required>
                                <option value="" disabled>Select State</option>
                                <!-- States will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editCityName">City Name</label>
                            <input type="text" class="form-control" id="editCityName" name="name">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="updateCityBtn" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateCityBtn" class="btn btn-primary">Save changes</button>
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
            $('.editCityBtn').on('click', function() {
                const cityId = $(this).data('id');

                $.ajax({
                    url: '/city/' + cityId + '/edit', // Fetch subcategory data
                    type: "GET",
                    success: function(response) {
                        const city = response.city;
                        const states = response.states;

                        $('#editCityName').val(city.name);
                        $('#editCityId').val(city.id);

                        // Populate categories dropdown
                        const stateSelect = $('#editCityState');
                        stateSelect.empty();
                        states.forEach(state => {
                            const isSelected = state.id === city.state_id ?
                                'selected' : '';
                            stateSelect.append(
                                `<option value="${state.id}" ${isSelected}>${state.name}</option>`
                            );
                        });

                        $('#editCityModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching subcategory data.');
                    }
                });
            });

            // Update the city
            $('#updateCityBtn').on('click', function() {
                const formData = new FormData($('#editCityForm')[0]);
                formData.append('_method', 'PUT'); // Add method override for PUT

                $.ajax({
                    url: '/city/' + $('#editCityId')
                        .val(), 
                    type: "POST", // Use POST since we're using _method override for PUT
                    data: formData,
                    contentType: false, // Necessary for file uploads
                    processData: false, // Prevents jQuery from processing the data
                    success: function(response) {
                        $('#editCityModal').modal('hide');
                        location.reload(); 
                    },
                    error: function() {
                        alert('Error updating data. Please try again.');
                    }
                });
            });


            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var cityId = $(this).data('id');

                $.ajax({
                    url: '/city/' + cityId +
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
