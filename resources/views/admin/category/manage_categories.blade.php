@extends('layouts.master')

@section('title', 'Category Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Categories and Subcategories</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    <div class="col-sm-6 text-right">
                        {{-- <button type="button" class="btn btn-success" data-toggle="modal"
                            data-target="#addSubcategoryModal">Add Sub
                            Categories</button> --}}
                        <a href="{{ route('sub-category.index') }}" class="btn btn-success">Add Sub
                            Categories</a>
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

                <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">

                            <div class="col-sm-6">
                                <label for="category_name">Category Name</label>
                                <input type="text" class="form-control @error('category_name') is-invalid @enderror"
                                    id="category_name" name="category_name" placeholder="Enter Category Name">
                                @error('category_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="category_image">Category Image</label>
                                <input type="file" class="form-control" id="category_image" name="category_image"
                                    accept="image/*">
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
                    <h3>Manage Categories</h3>
                    <table id="bonusTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Category Name</th>
                                <th>Category Image</th>
                                <th>SubCategories</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($categories as $category)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $category->name }}</td>
                                    {{-- <td>{{ $category->image ?? 'N/A' }}</td> --}}
                                    <td class="text-center">
                                        @if ($category->image)
                                            <img src="{{ asset('storage/' . $category->image) }}" alt="Category Image"
                                                width="80" height="50">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $category->subCategories->count() }}</td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $category->id }}"
                                            data-id="{{ $category->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $category->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-toggle="modal"
                                            data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editCatBtn" data-id="{{ $category->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('category.destroy', $category->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this category?');">
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
                            <input type="text" class="form-control" id="editCategoryName" name="category_name"
                                required>
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
