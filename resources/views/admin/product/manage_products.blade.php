@extends('layouts.master')

@section('title', 'Product Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Manage Products</h1>
                    </div>
                    <!-- Add User Button on the right side -->
                    <div class="col-sm-6 text-right">
                         <!-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addUserModal">Add
                            User</button>  -->
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

                    <form action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data"
                    class="form-horizontal mt-4">
                    @csrf

                    <div class="form-group row">
                        <div class="col-sm-5">
                            <label for="category_id">Category</label>
                            <select class="form-control @error('category_id') is-invalid @enderror" name="category_id"
                                id="category_id">
                                <option value="" disabled selected>Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-5">
                            <label for="sub_category_id">Subcategory</label>
                            <select class="form-control @error('sub_category_id') is-invalid @enderror"
                                name="sub_category_id" id="sub_category_id" disabled>
                                <option value="" disabled selected>Select Subcategory</option>
                            </select>
                            @error('sub_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-5">
                            <label for="product_name">Product Name</label>
                            <input type="text" class="form-control @error('product_name') is-invalid @enderror"
                                name="product_name" id="product_name" placeholder="Enter Product Name">
                            @error('product_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-5">
                            <label for="product_image">Product Image</label>
                            <input type="file" class="form-control @error('product_image') is-invalid @enderror"
                                name="product_image" id="product_image">
                            @error('product_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-10">
                            <label for="product_details">Product Details</label>
                            <input type="text" class="form-control @error('product_details') is-invalid @enderror"
                                name="product_details" id="product_details" placeholder="Enter Product Details">
                            @error('product_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-2 text-end" style="margin-left: -170px; margin-top: 30px">
                            <button type="button" id="addFieldsBtn" class="btn btn-secondary rounded-circle">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div id="extra-fields-container"></div>
                    </div>
                    <div class="card-footer d-flex">
                        {{-- <button type="button" id="saveProductBtn" class="btn btn-primary">Save</button> --}}
                        <button type="submit" id="saveProductBtn" class="btn btn-primary">Save</button>
                    </div>
                </form>


            </div><!-- /.container-fluid -->
        </section>


        <!-- Main content -->
        <section class="content mt-4">

            <div class="card">
                <!-- /.card-header -->

                <div class="card-body">
                    <table id="usersTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Product Name</th>
                                <th>Product Category</th>
                                <th>Product SubCategory</th>
                                <th>Product Details</th>
                                <th>Product Image</th>
                                <th>Active Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 1;
                            @endphp
                            @foreach ($products as $product)
                                <tr>
                                    {{-- <td>{{ $i }}</td> --}}
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                    <td>{{ $product->subCategory->name ?? 'N/A' }}</td>
                                    <td>{{ $product->details ?? 'No Details Available' }}</td>
                                    {{-- <td>{{ $product->image ?? 'N/A' }}</td> --}}
                                    <td class="text-center">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="Category Image"
                                                width="80" height="50" onerror="this.onerror=null; this.src='{{ asset('adminlte/dist/img/inf.png') }}';">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <!-- Active/Inactive Toggle Icon -->
                                        <a href="javascript:void(0);" id="toggleStatusBtn{{ $product->id }}"
                                            data-id="{{ $product->id }}" class="text-center" data-toggle="tooltip"
                                            title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i
                                                class="fas {{ $product->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <!-- View Icon -->
                                        <a href="{{ route('product.show', $product->id) }}" class="text-secondary"
                                            data-bs-toggle="tooltip" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <!-- Edit Icon -->
                                        <a href="{{ route('product.edit', $product->id) }}" class="text-primary"
                                            data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <!-- Delete Icon -->
                                        <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this product?');">
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


@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            // Add & Remove Unit and Price Field
            let fieldCount = 0;

            $('#addFieldsBtn').click(function() {
                // console.log('Button clicked');
                fieldCount++;
                let newFields = `
                    <div class="form-group row mt-3" id="field_${fieldCount}">
                        <div class="col-sm-5">
                            <label for="unit_${fieldCount}">Unit</label>
                            <input type="text" class="form-control" name="unit[]" id="unit_${fieldCount}" placeholder="Enter Unit">
                        </div>
                        <div class="col-sm-5">
                            <label for="price_${fieldCount}">Price</label>
                            <input type="text" class="form-control" name="price[]" id="price_${fieldCount}" placeholder="Enter Price">
                        </div>
                        <div class="col-sm-2 text-center" style="margin-left: -80px;margin-top: 30px;">
                            <button type="button" class="btn btn-danger rounded-circle remove-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#extra-fields-container').append(newFields);
            });

            $(document).on('click', '.remove-btn', function() {
                $(this).closest('.form-group').remove();
            });


            // Add Product
            // $('#saveProductBtn').click(function() {
            //     let formData = {
            //         category_id: $('#category_id').val(),
            //         sub_category_id: $('#sub_category_id').val() ? $('#sub_category_id').val() : null,
            //         product_name: $('#product_name').val(),
            //         product_details: $('#product_details').val(),
            //         _token: $('input[name="_token"]').val(),
            //         product_image: $('#product_image')[0].files[0], // File input
            //         productvarient: [] // Array for variants
            //     };

            //     // Add dynamically created unit and price fields to productvarient
            //     $('#extra-fields-container .form-group').each(function() {
            //         const unit = $(this).find('input[name="unit[]"]').val();
            //         const price = $(this).find('input[name="price[]"]').val();
            //         if (unit && price) {
            //             formData.productvarient.push({
            //                 unit: unit,
            //                 price: price
            //             });
            //         }
            //     });

            //     let ajaxData = new FormData();
            //     ajaxData.append('category_id', formData.category_id);
            //     ajaxData.append('sub_category_id', formData.sub_category_id);
            //     ajaxData.append('product_name', formData.product_name);
            //     ajaxData.append('product_details', formData.product_details);
            //     ajaxData.append('_token', formData._token);
            //     ajaxData.append('product_image', formData.product_image);

            //     // Append each variant
            //     formData.productvarient.forEach((variant, index) => {
            //         ajaxData.append(`productvarient[${index}][unit]`, variant.unit);
            //         ajaxData.append(`productvarient[${index}][price]`, variant.price);
            //     });

            //     $.ajax({
            //         url: '{{ route('product.store') }}', // Replace with the correct route
            //         type: 'POST',
            //         data: ajaxData,
            //         processData: false,
            //         contentType: false,
            //         success: function(response) {
            //             // alert('Product created successfully!');
            //             location.reload();
            //         },
            //         error: function(error) {
            //             console.error(error);
            //             // alert('Error creating the product.');
            //         }
            //     });
            // });

            // Toggle Status
            $(document).on('click', '[id^="toggleStatusBtn"]', function() {
                var productId = $(this).data('id');

                $.ajax({
                    url: '/product/' + productId +
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


            // JavaScript to load subcategories dynamically and reset dropdown
            $('#category_id').change(function() {
                const categoryId = $(this).val();
                const subCategoryDropdown = $('#sub_category_id');

                // Reset and disable subcategory dropdown
                subCategoryDropdown.prop('disabled', true).html(
                    '<option value="" disabled selected>Loading...</option>'
                );

                // If a category is selected, fetch subcategories
                if (categoryId) {
                    $.ajax({
                        url: '/sub-category/fetch/' + categoryId, // Replace with your actual route
                        type: 'GET',
                        success: function(data) {
                            let options =
                                '<option value="" disabled selected>Select Subcategory</option>';
                            data.forEach(subCategory => {
                                options +=
                                    `<option value="${subCategory.id}">${subCategory.name}</option>`;
                            });
                            subCategoryDropdown.html(options).prop('disabled', false);
                        },
                        error: function() {
                            subCategoryDropdown.html(
                                '<option value="" disabled selected>Error loading subcategories</option>'
                            );
                        }
                    });
                } else {
                    // If no category is selected, reset the subcategory dropdown
                    subCategoryDropdown.html(
                        '<option value="" disabled selected>Select Subcategory</option>');
                }
            });




        });
    </script>
@endsection
