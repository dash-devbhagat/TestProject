@extends('layouts.master')

@section('title', 'Charges Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Charges</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    <div class="col-sm-6 text-right">
                        {{-- <a href="{{ route('sub-category.index') }}" class="btn btn-success">Add Sub
                            Categories</a> --}}
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

                <form action="{{ route('charge.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">

                            <div class="col-sm-4">
                                <label for="name">Charge Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" placeholder="Enter Charge Name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="type">Type</label>
                                <select class="form-control @error('name') is-invalid @enderror" id="type"
                                    name="type">
                                    <option value="" disabled selected>Select Type</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="value">Value</label>
                                <input type="number" class="form-control @error('value') is-invalid @enderror"
                                    id="value" name="value" placeholder="Enter Value">
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
                    <h3>Manage Value</h3>
                    <table id="bonusTable" class="table table-bordered table-hover">
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
                                    <td>{{ $charge->value }}</td>
                                    <td>
                                        <a href="{{ route('charges.edit', $charge) }}"
                                            class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ route('charges.destroy', $charge) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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


    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm" method="POST" action="" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editCategoryId" name="id">
                        <div class="form-group">
                            <label for="editCategoryName">Category Name</label>
                            <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
                        </div>
                        <div class="form-group">
                            <label for="editCategoryImage">Category Image</label>
                            <input type="file" class="form-control" id="editCategoryImage" name="category_image"
                                accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateCategoryBtn" class="btn btn-primary">Save changes</button>
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
            $('.editCatBtn').on('click', function() {
                const categoryId = $(this).data('id');

                $.ajax({
                    url: '/category/' + categoryId + '/edit', // Fetch category data
                    type: "GET",
                    success: function(response) {
                        const category = response.category;
                        $('#editCategoryName').val(category.name);
                        $('#editCategoryId').val(category.id);
                        $('#editCategoryModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching category data.');
                    }
                });
            });

            // Update the Category
            $('#updateCategoryBtn').on('click', function() {
                const formData = new FormData($('#editCategoryForm')[0]);
                formData.append('_method', 'PUT'); // Add method override for PUT

                $.ajax({
                    url: '/category/' + $('#editCategoryId')
                        .val(), // PUT request to update category
                    type: "POST", // Use POST since we're using _method override for PUT
                    data: formData,
                    contentType: false, // Necessary for file uploads
                    processData: false, // Prevents jQuery from processing the data
                    success: function(response) {
                        $('#editCategoryModal').modal('hide');
                        location.reload(); // Reload the page or update dynamically
                    },
                    error: function() {
                        alert('Error updating category data. Please try again.');
                    }
                });
            });


            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var categoryId = $(this).data('id');

                $.ajax({
                    url: '/category/' + categoryId +
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
