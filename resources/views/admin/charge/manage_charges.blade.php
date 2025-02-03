@extends('layouts.master')

@section('title', 'Charge Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Charges</h1>
                    </div>

                    <!-- {{-- Bootstrap Alert --}} -->
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

                <form action="{{ route('charge.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">

                            <div class="col-sm-4">
                                <label for="name">Charge Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="Enter Charge Name" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="type">Charge Type</label>
                                <select class="form-control @error('type') is-invalid @enderror" id="type"
                                    name="type" value="{{ old('type') }}">
                                    <option value="" disabled selected>Select Type</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="value">Charge Value</label>
                                <input type="number" class="form-control @error('value') is-invalid @enderror"
                                    id="value" name="value" placeholder="Enter Value" value="{{ old('value') }}">
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" id="saveCatBtn" class="btn btn-primary float-right">Save</button>
                    </div>
                    <!-- /.card-footer -->
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
                                <th>Amount</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp

                            @foreach ($charges as $charge)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $charge->name }}</td>
                                    <td>
                                        @if ($charge->type === 'percentage')
                                            {{ $charge->value }}%
                                        @else
                                            ₹{{ $charge->value }}
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $charge->id }}"
                                            data-id="{{ $charge->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $charge->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $charge->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editChargeBtn" data-id="{{ $charge->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('charge.destroy', $charge->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this charge?');">
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


    <!-- Edit Charge Modal -->
    <div class="modal fade" id="editChargeModal" tabindex="-1" role="dialog" aria-labelledby="editChargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editChargeModalLabel">Edit Charge</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editChargeForm" method="POST" action="">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editChargeId" name="id">
                        <div class="form-group">
                            <label for="editChargeName">Charge Name</label>
                            <input type="text" class="form-control" id="editChargeName" name="name">
                        </div>
                        <div class="form-group">
                            <label for="editChargeType">Type</label>
                            <select class="form-control" id="editChargeType" name="type">
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editChargeValue">Value</label>
                            <input type="number" class="form-control" id="editChargeValue" name="value">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateChargeBtn" class="btn btn-primary">Save changes</button>
                </div>
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
            $('.editChargeBtn').on('click', function() {
                const chargeId = $(this).data('id');
                console.log(chargeId);

                $.ajax({
                    url: '/charge/' + chargeId + '/edit',
                    type: "GET",
                    success: function(response) {
                        const charge = response.charge;
                        $('#editChargeName').val(charge.name);
                        $('#editChargeType').val(charge.type);
                        $('#editChargeValue').val(charge.value);
                        $('#editChargeId').val(charge.id);
                        $('#editChargeForm').attr('action', '/charges/' + charge
                            .id); // Set form action
                        $('#editChargeModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching category data.');
                    }
                });
            });

            // Update the Category
            $('#updateChargeBtn').on('click', function() {
                const formData = new FormData($('#editChargeForm')[0]);
                formData.append('_method', 'PUT'); // Add method override for PUT

                $.ajax({
                    url: '/charge/' + $('#editChargeId')
                        .val(), 
                    type: "POST", // Use POST since we're using _method override for PUT
                    data: formData,
                    contentType: false, // Necessary for file uploads
                    processData: false, // Prevents jQuery from processing the data
                    success: function(response) {
                        $('#editChargeModal').modal('hide');
                        location.reload(); 
                    },
                    error: function() {
                        alert('Error updating category data. Please try again.');
                    }
                });
            });


            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var chargeId = $(this).data('id');

                $.ajax({
                    url: '/charge/' + chargeId +
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
