@extends('layouts.master')

@section('title', 'Branch Management')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Branches</h1>
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

            <!-- Add New Branch Form -->
            <form action="{{ route('branch.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal mt-4">
                @csrf
                <div class="form-group row">
                    <div class="col-sm-4">
                        <label for="branch_name">Branch Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="branch_name" placeholder="Enter Branch Name" value="{{ old('name') }}">
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4">
                        <label for="branch_address">Branch Address</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" id="branch_address" placeholder="Enter Address" value="{{ old('address') }}">
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4">
                        <label for="branch_logo">Branch Logo</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" name="logo" id="branch_logo">
                        @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6 mt-2">
                        <label for="latitude">Latitude</label>
                        <input type="text" class="form-control @error('latitude') is-invalid @enderror" name="latitude" id="latitude" placeholder="Enter Latitude" value="{{ old('latitude') }}">
                        @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-6 mt-2">
                        <label for="longitude">Longtitude</label>
                        <input type="text" class="form-control @error('longitude') is-invalid @enderror" name="longitude" id="longitude" placeholder="Enter Longtitude" value="{{ old('longitude') }}">
                        @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-12 mt-2">
                        <label for="description">Branch Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" placeholder="Enter Description"></textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right">Save</button>
                </div>
            </form>


        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="card">
            <div class="card-body">
                <table id="usersTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Branch Name</th>
                            <th>Branch Logo</th>
                            <th>Active Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($branches as $branch)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $branch->name }}</td>
                            <td class="text-center">
                                        @if ($branch->logo)
                                            <img src="{{ asset('storage/' . $branch->logo) }}" alt="Branch Image"
                                                width="80" height="50" onerror="this.onerror=null; this.src='{{ asset('adminlte/dist/img/inf.png') }}';">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                            <td class="text-center">
                                <!-- Active/Inactive Toggle Icon -->
                                <a href="javascript:void(0);" id="toggleStatusBtn{{ $branch->id }}"
                                    data-id="{{ $branch->id }}" class="text-center" data-toggle="tooltip"
                                    title="{{ $branch->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i
                                        class="fas {{ $branch->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <!-- View Icon -->
                                <a href="{{ route('branch.show', $branch->id) }}" class="text-secondary"
                                    data-bs-toggle="tooltip" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <!-- Edit Icon -->
                                <a href="#javascript" class="text-primary" data-toggle="modal"
                                    data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fa fa-edit editBranchBtn" data-id="{{ $branch->id }}"></i>
                                </a>
                                <!-- Delete Icon -->
                                <form action="{{ route('branch.destroy', $branch->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this branch?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 m-0"
                                        data-bs-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @php $i++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </section>
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
                        <label for="editBranchAddress">Branch Address</label>
                        <input type="text" class="form-control" id="editBranchAddress" name="address"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchDescription">Branch Description</label>
                        <input type="text" class="form-control" id="editBranchDescription" name="description"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchLatitude">Latitude</label>
                        <input type="text" class="form-control" id="editBranchLatitude" name="latitude"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchLongtitude">Longtitude</label>
                        <input type="text" class="form-control" id="editBranchLongtitude" name="longitude"
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
                    $('#editBranchId').val(branch.id);
                    $('#editBranchName').val(branch.name);
                    $('#editBranchAddress').val(branch.address);
                    $('#editBranchDescription').val(branch.description);
                    $('#editBranchLatitude').val(branch.latitude);
                    $('#editBranchLongtitude').val(branch.longitude);
                    $('#editBranchModal').modal('show');
                },
                error: function() {
                    alert('Error fetching category data.');
                }
            });
        });

         // Update the Branch
         $('#updateBranchBtn').on('click', function() {
            const formData = new FormData($('#editBranchForm')[0]);
            formData.append('_method', 'PUT'); // Add method override for PUT

            $.ajax({
                url: '/branch/' + $('#editBranchId')
                    .val(), // PUT request to update category
                type: "POST", // Use POST since we're using _method override for PUT
                data: formData,
                contentType: false, // Necessary for file uploads
                processData: false, // Prevents jQuery from processing the data
                success: function(response) {
                    $('#editBranchModal').modal('hide');
                    location.reload(); // Reload the page or update dynamically
                },
                error: function() {
                    alert('Error updating branch data. Please try again.');
                }
            });
        });


        // Toggle Status
        $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var branchId = $(this).data('id');

                $.ajax({
                    url: '/branch/' + branchId +
                        '/toggle-status', // Use the route for toggling status
                    method: 'POST',
                    data: {
                        _token: $('input[name="_token"]').val(), // CSRF token
                    },
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                        alert('An error occurred while toggling branch status.');
                    }
                });
            });
    });
</script>
@endsection