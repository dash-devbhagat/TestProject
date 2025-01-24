@extends('layouts.master')

@section('title', 'Bonus Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Bonus</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('bonusHistory') }}" class="btn btn-success">Bonus History</a>
                    </div>

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

                {{-- <form class="form-horizontal"> --}}
                <form action="{{ route('bonus.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <!-- Column 1: Bonus Type -->
                            <div class="col-sm-4">
                                <label for="bonusType" class="col-form-label">Bonus Type</label>
                                <input type="text" class="form-control @error('type') is-invalid @enderror"
                                    id="bonusType" name="type" placeholder="Enter Bonus Type">
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Column 2: Bonus Amount -->
                            <div class="col-sm-4">
                                <label for="bonusAmount" class="col-form-label">Bonus Amount</label>
                                <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                    id="bonusAmount" name="amount" placeholder="Enter Bonus Amount" step="0.01">
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Column 3: Bonus Percentage -->
                            <div class="col-sm-4">
                                <label for="bonusPercentage" class="col-form-label">Bonus Percentage</label>
                                <input type="number" class="form-control @error('percentage') is-invalid @enderror"
                                    id="bonusPercentage" name="percentage" placeholder="Enter Bonus Percentage"
                                    step="0.01">
                                @error('percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->

                    <div class="card-footer">
                        {{-- <button type="buton" id="saveBonusBtn" class="btn btn-primary float-right">Save</button> --}}
                        <button type="submit" id="saveBonusBtn" class="btn btn-primary float-right">Save</button>
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
                                <th>Bonus Name</th>
                                <th>Bonus Amount</th>
                                <th>Bonus Percentage</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($bonuses as $bonus)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $bonus->type }}</td>
                                    <td>₹{{ $bonus->amount }}</td>
                                    <td>{{ $bonus->percentage }}%</td>
                                    {{-- <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $bonus->id }}"
                                            data-id="{{ $bonus->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $bonus->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $bonus->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td> --}}
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $bonus->id }}"
                                            data-id="{{ $bonus->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $bonus->is_active ? 'Deactivate' : 'Activate' }}"
                                            {{ !$bonus->is_active ? 'style="pointer-events: none;"' : '' }}>
                                            <!-- Disable icon if inactive -->
                                            <i
                                                class="fas {{ $bonus->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>

                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-toggle="modal"
                                            data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editBonusBtn" data-id="{{ $bonus->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('bonus.destroy', $bonus->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this bonus?');">
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


    <!-- Edit Bonus Modal -->
    <div class="modal fade" id="editBonusModal" tabindex="-1" role="dialog" aria-labelledby="editBonusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBonusModalLabel">Edit Bonus</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editBonusForm" method="POST">
                        @csrf
                        @method('PUT')
                        <!-- Hidden field to store bonus ID -->
                        <input type="hidden" id="editBonusId" name="id">
                        <div class="form-group row">
                            <!-- Column 1: Bonus Type -->
                            <div class="col-sm-6">
                                <label for="editBonusType" class="col-form-label">Bonus Type</label>
                                <input type="text" class="form-control" id="editBonusType" name="type"
                                    placeholder="Enter Bonus Type">
                            </div>
                            <!-- Column 2: Bonus Amount -->
                            <div class="col-sm-6">
                                <label for="editBonusAmount" class="col-form-label">Bonus Amount</label>
                                <input type="number" class="form-control" id="editBonusAmount" name="amount"
                                    placeholder="Enter Bonus Amount" step="0.01">
                            </div>
                            <!-- Column 3: Bonus Percentage -->
                            <div class="col-sm-6">
                                <label for="editBonusPercentage" class="col-form-label">Bonus Percentage (%)</label>
                                <input type="number" class="form-control" id="editBonusPercentage" name="percentage"
                                    placeholder="Enter Bonus Percentage" step="0.01" min="0" max="100">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateBonusBtn" class="btn btn-primary">Save changes</button>
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

            // Add Bonus
            // $('#saveBonusBtn').on('click', function() {
            //     event.preventDefault();
            //     // Collect form data
            //     let formData = {
            //         type: $('#bonusType').val(),
            //         amount: $('#bonusAmount').val(),
            //         _token: $('input[name="_token"]').val(),
            //     };

            //     // Perform AJAX Request
            //     $.ajax({
            //         url: "{{ route('bonus.store') }}", // Route to store method
            //         type: "POST",
            //         data: formData,
            //         success: function(response) {


            //             // Display success message
            //             // alert(response.message);

            //             // Optionally, reload or dynamically update the user list
            //             location.reload();
            //         },
            //         error: function(xhr) {
            //             // Handle validation errors
            //             let errors = xhr.responseJSON.errors;
            //             if (errors) {
            //                 // if (errors.type) alert("Error: " + errors.type);
            //                 // if (errors.amount) alert("Error: " + errors.amount);
            //                 console.log(
            //                     "error occured in name or email or password. remove above comment and know what is wrong."
            //                 );
            //             } else {
            //                 alert("An error occurred. Please try again.");
            //             }
            //         }
            //     });
            // });


            // On Edit Icon Click Modal Open
            $('.editBonusBtn').on('click', function() {
                // Fetch the user ID from data-id attribute
                const bonusId = $(this).data('id');

                // Fetch user data via AJAX
                $.ajax({
                    url: '/bonus/' + bonusId +
                        '/edit',
                    type: "GET",
                    success: function(response) {
                        // Populate modal fields with fetched data
                        $('#editBonusType').val(response.bonus.type);
                        $('#editBonusAmount').val(response.bonus.amount);
                        $('#editBonusPercentage').val(response.bonus.percentage);
                        $('#editBonusId').val(response.bonus.id);

                        // Show the modal
                        $('#editBonusModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching user data. Please try again.');
                    }
                });
            });



            // Update Bonus
            $('#updateBonusBtn').on('click', function() {
                // Collect form data
                const formData = {
                    id: $('#editBonusId').val(),
                    type: $('#editBonusType').val(),
                    amount: $('#editBonusAmount').val(),
                    percentage: $('#editBonusPercentage').val(),
                    _token: $('input[name="_token"]').val(),
                };

                // Perform AJAX Request
                $.ajax({
                    url: '/bonus/' + formData.id, // Route to update method
                    type: "PUT",
                    data: formData,
                    success: function(response) {
                        // Close the modal
                        $('#editBonusModal').modal('hide');

                        // Optionally display a success message
                        // alert('Bonus updated successfully!');

                        // Optionally, reload the page or dynamically update the bonus list
                        location.reload();
                    },
                    error: function(xhr) {
                        // Handle errors
                        alert('Error updating bonus data. Please try again.');
                    }
                });
            });

            // Toggle Status
            // $(document).on('click', '[id^="toggleStatusBtn"]', function() {
            //     var bonusId = $(this).data('id');
            //     console.log(bonusId)

            //     $.ajax({
            //         url: '/bonus/' + bonusId +
            //             '/toggle-status', // Use the route for toggling status
            //         method: 'POST',
            //         data: {
            //             _token: $('input[name="_token"]').val(), // CSRF token
            //         },
            //         success: function(response) {
            //             // Optionally, display a success message
            //             // alert(response.message);
            //             location.reload();
            //         },
            //         error: function() {
            //             alert('An error occurred while toggling user status.');
            //         }
            //     });
            // });
            // Toggle Status

            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var bonusId = $(this).data('id');

                $.ajax({
                    url: '/bonus/' + bonusId + '/toggle-status', // Route to toggle the bonus status
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(), // CSRF token
                    },
                    success: function(response) {
                        // Update icon based on new status
                        if (response.is_active) {
                            $('#toggleStatusBtn' + bonusId + ' i').removeClass(
                                'fa-toggle-off text-muted').addClass(
                                'fa-toggle-on text-success');
                            $('#toggleStatusBtn' + bonusId).removeAttr(
                                'style'); // Enable the icon again
                        } else {
                            $('#toggleStatusBtn' + bonusId + ' i').removeClass(
                                'fa-toggle-on text-success').addClass(
                                'fa-toggle-off text-muted');
                            $('#toggleStatusBtn' + bonusId).css('pointer-events',
                                'none'); // Disable the icon
                        }

                        // Reload the page to reflect changes
                        window.location.reload();
                    },
                    error: function() {
                        alert('An error occurred while toggling bonus status.');
                    }
                });
            });









        });
    </script>
@endsection
