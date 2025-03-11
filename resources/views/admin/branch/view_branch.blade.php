@extends('layouts.master')

@section('title', 'Branch Management')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Branch Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('branch.index') }}" class="btn btn-secondary text-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>


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
    </section>

    <section class="content">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="text-primary">Branch Details @if (Auth::user()->branch_id)
                            <!-- Edit Icon -->
                            <a href="#javascript" class="text-primary" data-toggle="modal"
                                data-target="#editBranchModal" data-bs-toggle="tooltip" title="Edit">
                                <i class="fa fa-edit editBranchBtn" data-id="{{ $branch->id }}"></i>
                            </a>
                            @endif
                        </h3>
                        <div class="table-responsive">
                            <table class="table" style="table-layout: fixed; width: 100%;">
                                <tbody>
                                    <tr>
                                        <th style="width: 20%;">Branch Name</th>
                                        <td>{{ $branch->name }}</td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Branch Manager Name</th>
                                        <td>{{ $branch->manager->name }}</td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Branch Address</th>
                                        <td>{{ $branch->address }}</td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Description</th>
                                        <td style="word-wrap: break-word; max-width: 500px;">
                                            {{ $branch->description ?? 'No Description Available' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Open 24x7</th>
                                        @if (Auth::user()->branch_id)
                                        <td class="center">
                                            <!-- Branch Open24*7 Toggle Icon -->
                                            <a href="javascript:void(0);" id="toggle24x7Btn{{ $branch->id }}"
                                                data-id="{{ $branch->id }}" class="text-center"
                                                data-toggle="tooltip"
                                                title="{{ $branch->isOpen24x7 ? '24x7 Enabled' : 'Not 24x7' }}">
                                                <i class="fas {{ $branch->isOpen24x7 ? 'fa-toggle-on text-danger' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                            </a>
                                        </td>
                                        @else
                                        <td>{{ $branch->isOpen24x7 ? 'Yes' : 'No' }}</td>
                                        @endif
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Latitude</th>
                                        <td>{{ $branch->latitude }}</td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Longitude</th>
                                        <td>{{ $branch->longitude }}</td>
                                    </tr>
                                    <tr>
                                        <th style="width: 20%;">Branch Logo</th>
                                        <td>
                                            @if ($branch->logo)
                                            <img src="{{ asset('storage/' . $branch->logo) }}" width="200" height="200">
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

                @if (!$branch->isOpen24x7)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="text-primary">Branch Timings</h3>
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTimingModal">Add Timing</button>
                        </div>


                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Opening Time</th>
                                    <th>Closing Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($branch->timings as $timing)
                                <tr>
                                    <td>{{ $timing->day }}</td>
                                    <td>{{ $timing->opening_time }}</td>
                                    <td>{{ $timing->closing_time }}</td>
                                    <td class="text-center">
                                        <a href="javascript:void(0);" data-id="{{ $timing->id }}" class="toggle-status">
                                            <i class="fas {{ $timing->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="javascript:void(0);" class="text-primary editTimingBtn" data-id="{{ $timing->id }}" data-day="{{ $timing->day }}" data-opening="{{ $timing->opening_time }}" data-closing="{{ $timing->closing_time }}" data-bs-toggle="modal" data-bs-target="#editTimingModal">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('timing.destroy', $timing->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this timing?');">
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
                </div>
                @endif


            </div>
        </div>
    </section>
</div>

<!-- Add Timing Modal -->
<div class="modal fade" id="addTimingModal" tabindex="-1" aria-labelledby="addTimingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Timing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('timing.store') }}" method="POST">
                @csrf
                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                <div id="timingFields" class="px-4 py-3">
                    <div class="timing-entry row mb-4">
                        <div class="col-12 mb-3">
                            <label for="day" class="form-label">Day</label>
                            <select name="timings[0][day]" class="form-control day-select" id="day" required>
                                <option value="">Select Day</option>
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <option value="{{ $day }}" {{ in_array($day, $existingTimings) ? 'disabled' : '' }}>{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="opening_time" class="form-label">Opening Time</label>
                            <input type="time" class="form-control" name="timings[0][opening_time]" id="opening_time" required>
                        </div>
                        <div class="col-12 mb-2">
                            <label for="closing_time" class="form-label">Closing Time</label>
                            <input type="time" class="form-control" name="timings[0][closing_time]" id="closing_time" required>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-success ml-4" id="addMoreTimings">Add More</button>
                <div class="modal-footer mt-3">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Branch Modal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" role="dialog" aria-labelledby="editBranchModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editBranchForm" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editBranchId" name="id">
                    <div class="form-group">
                        <label for="editBranchName">Branch Name</label>
                        <input type="text" class="form-control" id="editBranchName" name="name"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchDescription">Branch Description</label>
                        <input type="text" class="form-control" id="editBranchDescription" name="description"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchAddress">Branch Address</label>
                        <input type="text" class="form-control" id="editBranchAddress" name="address"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchLatitude">Latitude</label>
                        <input type="text" class="form-control" id="editBranchLatitude" name="latitude"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchLongitude">Longitude</label>
                        <input type="text" class="form-control" id="editBranchLongitude" name="longitude"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchImage">Branch Logo</label>
                        <input type="file" class="form-control" id="editBranchImage" name="logo"
                            accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="updateBranchBtn" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>


<!-- Edit Timing Modal -->
<div class="modal fade" id="editTimingModal" tabindex="-1" aria-labelledby="editTimingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Timing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTimingForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="editBranchId" name="branch_id">
                <input type="hidden" id="editTimingId" name="timing_id">

                <div class="modal-body">
                    <label>Day</label>
                    <input type="text" class="form-control" id="editDay" name="day" readonly>
                    <label>Opening Time</label>
                    <input type="time" class="form-control" id="editOpeningTime" name="opening_time" required>
                    <label>Closing Time</label>
                    <input type="time" class="form-control" id="editClosingTime" name="closing_time" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        // Open the Edit Modal and Populate Data
        $('.editBranchBtn').on('click', function() {
            const branchId = $(this).data('id');

            $.ajax({
                url: '/branch/' + branchId + '/edit', // Fetch category data
                type: "GET",
                success: function(response) {
                    const branch = response.branch;
                    const users = response.users;

                    $('#editBranchId').val(branch.id);
                    $('#editBranchName').val(branch.name);
                    $('#editBranchAddress').val(branch.address);
                    $('#editBranchDescription').val(branch.description);
                    $('#editBranchLatitude').val(branch.latitude);
                    $('#editBranchLongitude').val(branch.longitude);

                    $('#editBranchModal').modal('show');
                },
                error: function() {
                    alert('Error fetching branch data.');
                }
            });
        });

        // Update the Branch
        $('#updateBranchBtn').on('click', function() {
            const formData = new FormData($('#editBranchForm')[0]);
            formData.append('_method', 'PUT'); // Add method override for PUT

            $.ajax({
                url: '/branch/' + $('#editBranchId')
                    .val(),
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#editBranchModal').modal('hide');
                    location.reload();
                },
                error: function() {
                    alert('Error updating branch data. Please try again.');
                }
            });
        });

        // Toggle Open24*7
        $(document).on('click', '[id^="toggle24x7Btn"]', function() {
            var branchId = $(this).data('id');

            $.ajax({
                url: `/branch/toggle-24x7/${branchId}`,
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(), // CSRF token
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Error toggling 24x7 status. Please try again.');
                }
            });
        });



        // Add more timing fields
        let timingIndex = 1;
        $('#addMoreTimings').on('click', function() {
            $('#timingFields').append(`
                <div class="timing-entry mt-3">
                    <label>Day</label>
                    <select name="timings[${timingIndex}][day]" class="form-control day-select">
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    <label>Opening Time</label>
                    <input type="time" class="form-control" name="timings[${timingIndex}][opening_time]" required>
                    <label>Closing Time</label>
                    <input type="time" class="form-control" name="timings[${timingIndex}][closing_time]" required>
                </div>
            `);
            timingIndex++;
        });

        // Prevent duplicate days selection
        $(document).on('change', '.day-select', function() {
            let selectedDays = [];
            $('.day-select').each(function() {
                selectedDays.push($(this).val());
            });
            let uniqueDays = [...new Set(selectedDays)];
            if (uniqueDays.length !== selectedDays.length) {
                alert('This day is already selected. Please choose a different day.');
                $(this).val('');
            }
        });

        // Populate the Edit Modal with data
        $('.editTimingBtn').on('click', function() {
            let timingId = $(this).data('id');
            let branchId = $(this).data('branch-id');
            let day = $(this).data('day');
            let openingTime = $(this).data('opening');
            let closingTime = $(this).data('closing');

            openingTime = openingTime ? openingTime.slice(0, 5) : '';
            closingTime = closingTime ? closingTime.slice(0, 5) : '';

            $('#editTimingId').val(timingId);
            $('#editBranchId').val(branchId);
            $('#editDay').val(day);
            $('#editOpeningTime').val(openingTime);
            $('#editClosingTime').val(closingTime);

            $('#editTimingForm').attr('action', '/timing/' + timingId);
        });

        // Update the Timing using AJAX
        $('#editTimingForm').on('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            const formData = new FormData(this);

            $.ajax({
                url: $('#editTimingForm').attr('action'), // The action URL of the form (e.g., /timing/{id})
                type: 'POST', // Use POST since we're overriding the method to PUT
                data: formData,
                contentType: false, // Prevent jQuery from setting content-type header automatically
                processData: false, // Prevent jQuery from processing the data
                success: function(response) {
                    // On success, hide the modal and reload the page or update the table
                    $('#editTimingModal').modal('hide');
                    location.reload(); // Reload the page to reflect the updated timing
                },
                error: function() {
                    alert('An error occurred while updating the timing.');
                }
            });
        });

        // Toggle Status
        $(document).on('click', '.toggle-status', function() {
            var timingId = $(this).data('id');
            $.ajax({
                url: '/timing/' + timingId + '/toggle-status',
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('An error occurred while toggling status.');
                }
            });
        });
    });
</script>
@endsection