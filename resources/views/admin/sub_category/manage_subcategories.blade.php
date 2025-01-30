@extends('layouts.master')

@section('title', 'SubCategory Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Subcategories</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    <!-- Back button on the right -->
                    <div class="col-sm-6 text-right">
                        <a href="{{  route('category.index') }}" class="btn btn-secondary text-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
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

                <form action="{{ route('sub-category.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">

                            <div class="col-sm-4">
                                <label for="subcategoryCategory">Select Category</label>
                                <select class="form-control @error('sub_category_name') is-invalid @enderror"
                                    id="subcategoryCategory" name="category_id">
                                    <option value="" disabled selected>Select Category</option>
                                    @foreach ($categories as $category)
                                        @if ($category->is_active == '1')
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="sub_category_name">SubCategory Name</label>
                                <input type="text" class="form-control @error('sub_category_name') is-invalid @enderror"
                                    id="sub_category_name" name="sub_category_name" placeholder="Enter Category Name">
                                @error('sub_category_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="sub_category_image">SubCategory Image</label>
                                <input type="file" class="form-control" id="sub_category_image" name="sub_category_image"
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
                    <table id="usersTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>SubCategory Name</th>
                                <th>Category Name</th>
                                <th>SubCategory Image</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($subcategories as $subcategory)
                                <tr>
                                    <td>{{ $i }}</td>
                                    <td>{{ $subcategory->name }}</td>
                                    <td>{{ $subcategory->category->name ?? 'N/A' }}</td>
                                    {{-- <td>{{ $subcategory->image ?? 'N/A' }}</td> --}}
                                    <td class="text-center">
                                        @if ($subcategory->image)
                                            <img src="{{ asset('storage/' . $subcategory->image) }}" alt="SubCategory Image"
                                                width="80" height="50" onerror="this.onerror=null; this.src='{{ asset('adminlte/dist/img/inf.png') }}';">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $subcategory->id }}"
                                            data-id="{{ $subcategory->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $subcategory->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $subcategory->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- Edit Icon -->
                                        <a href="#javascript" class="text-primary" data-toggle="modal"
                                            data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editSubCatBtn" data-id="{{ $subcategory->id }}"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('sub-category.destroy', $subcategory->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this sub-category?');">
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


    <!-- Edit Modal -->
    <div class="modal fade" id="editSubCategoryModal" tabindex="-1" role="dialog"
        aria-labelledby="editSubCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubCategoryModalLabel">Edit SubCategory</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editSubCategoryForm" method="POST" action="" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editSubCategoryId" name="id">
                        <div class="form-group">
                            <label for="editSubCategoryCategory">Select Category</label>
                            <select class="form-control" id="editSubCategoryCategory" name="category_id" required>
                                <option value="" disabled>Select Category</option>
                                <!-- Categories will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editSubCategoryName">SubCategory Name</label>
                            <input type="text" class="form-control" id="editSubCategoryName" name="sub_category_name"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="editSubCategoryImage">SubCategory Image</label>
                            <input type="file" class="form-control" id="editSubCategoryImage" name="category_image"
                                accept="image/*">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="updateSubCategoryBtn" class="btn btn-primary">Save changes</button>
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
            $('.editSubCatBtn').on('click', function() {
                const subCategoryId = $(this).data('id');

                $.ajax({
                    url: '/sub-category/' + subCategoryId + '/edit', // Fetch subcategory data
                    type: "GET",
                    success: function(response) {
                        const subCategory = response.subcategory;
                        const categories = response.categories;

                        $('#editSubCategoryName').val(subCategory.name);
                        $('#editSubCategoryId').val(subCategory.id);

                        // Populate categories dropdown
                        const categorySelect = $('#editSubCategoryCategory');
                        categorySelect.empty();
                        categories.forEach(category => {
                            const isSelected = category.id === subCategory.category_id ?
                                'selected' : '';
                            categorySelect.append(
                                `<option value="${category.id}" ${isSelected}>${category.name}</option>`
                            );
                        });

                        $('#editSubCategoryModal').modal('show');
                    },
                    error: function() {
                        alert('Error fetching subcategory data.');
                    }
                });
            });

            // Update the SubCategory
            $('#updateSubCategoryBtn').on('click', function() {
                const formData = new FormData($('#editSubCategoryForm')[0]);
                formData.append('_method', 'PUT'); // Add method override for PUT

                $.ajax({
                    url: '/sub-category/' + $('#editSubCategoryId')
                        .val(), // PUT request to update subcategory
                    type: "POST", // Use POST since we're using _method override for PUT
                    data: formData,
                    contentType: false, // Necessary for file uploads
                    processData: false, // Prevents jQuery from processing the data
                    success: function(response) {
                        $('#editSubCategoryModal').modal('hide');
                        location.reload(); // Reload the page or update dynamically
                    },
                    error: function() {
                        alert('Error updating subcategory data. Please try again.');
                    }
                });
            });


            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var subcategoryId = $(this).data('id');

                $.ajax({
                    url: '/sub-category/' + subcategoryId +
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
