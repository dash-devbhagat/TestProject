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
                        {{-- <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addUserModal">Add
                            User</button> --}}
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

                <form method="POST" enctype="multipart/form-data" class="mt-4">
                    @csrf
                    <div class="form-group row">
                        <div class="col-sm-5">
                            <label for="product_name">Product Name</label>
                            <input type="text" class="form-control @error('product_name') is-invalid @enderror"
                                name="product_name" id="product_name" placeholder="Enter Product Name" required>
                            @error('product_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-5">
                            <label for="product_image">Product Image</label>
                            <input type="file" class="form-control @error('product_image') is-invalid @enderror"
                                name="product_image" id="product_image" required>
                            @error('product_image')
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
                    <div class="card-footer d-flex justify-content-end mt-4">
                        <button type="button" id="saveProductBtn" class="btn btn-primary">Save</button>
                    </div>
                </form>


            </div><!-- /.container-fluid -->
        </section>


        <!-- Main content -->
        <section class="content mt-4">

            <div class="card">
                <!-- /.card-header -->

                <div class="card-body">
                    <table id="bonusTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Sr</th>
                                <th>Product Name</th>
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
                                    {{-- <td>{{ $product->image ?? 'N/A' }}</td> --}}
                                    <td class="text-center">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="Category Image"
                                                width="80" height="50">
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
                                        <a href="#javascript" class="text-primary" data-toggle="modal"
                                            data-target="#editProductModal" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fa fa-edit editProductBtn" data-id="{{ $product->id }}"></i>
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


    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm" method="POST" action="{{ route('product.update', ':data-id') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="editProductId" name="id">

                        <div class="form-group row">
                            <div class="col-sm-10">
                                <label for="editProductName">Product Name</label>
                                <input type="text" class="form-control" id="editProductName" name="name" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-10">
                                <label for="editProductImage">Product Image</label>
                                <input type="file" class="form-control" id="editProductImage" name="product_image">
                                <div id="imagePreview"></div> <!-- Image preview section -->
                            </div>
                        </div>

                        <!-- Product Variant Section -->
                        <div class="form-group row">
                            <div class="col-sm-10">
                                <label>Product Variants</label>
                                <div id="editProductVariantContainer"></div> <!-- Variants will be added dynamically -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            {{-- <button type="button" id="updateProductBtn" class="btn btn-primary">Save changes</button> --}}
                        </div>

                    </form>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="updateProductBtn" class="btn btn-primary">Save changes</button>
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


            // Add & Remove Unit and Price Field
            // let fieldCount = 0;

            // $('#addFieldsBtn').click(function() {
            //     // console.log('Button clicked');
            //     fieldCount++;
            //     let newFields = `
        //             <div class="form-group row mt-3" id="field_${fieldCount}">
        //                 <div class="col-sm-5">
        //                     <label for="unit_${fieldCount}">Unit</label>
        //                     <input type="text" class="form-control" name="unit[]" id="unit_${fieldCount}" placeholder="Enter Unit">
        //                 </div>
        //                 <div class="col-sm-5">
        //                     <label for="price_${fieldCount}">Price</label>
        //                     <input type="text" class="form-control" name="price[]" id="price_${fieldCount}" placeholder="Enter Price">
        //                 </div>
        //                 <div class="col-sm-2 text-center" style="margin-left: -80px;margin-top: 30px;">
        //                     <button type="button" class="btn btn-danger rounded-circle remove-btn">
        //                         <i class="fa fa-trash"></i>
        //                     </button>
        //                 </div>
        //             </div>
        //         `;
            //     $('#extra-fields-container').append(newFields);
            // });

            // $(document).on('click', '.remove-btn', function() {
            //     $(this).closest('.form-group').remove();
            // });

            let fieldCount = 0;

            $('#addFieldsBtn').click(function() {
                // Disable the button after the first click
                $(this).prop('disabled', true);

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

                // Enable the button only if both fields are filled
                $('#unit_' + fieldCount + ', #price_' + fieldCount).on('input', function() {
                    const unitValue = $('#unit_' + fieldCount).val();
                    const priceValue = $('#price_' + fieldCount).val();
                    if (unitValue && priceValue) {
                        $('#addFieldsBtn').prop('disabled', false); // Enable the button
                    }
                });
            });

            $(document).on('click', '.remove-btn', function() {
                $(this).closest('.form-group').remove();
                // Re-enable the button if no fields are left
                if ($('#extra-fields-container .form-group').length === 0) {
                    $('#addFieldsBtn').prop('disabled', false);
                }
            });



            // Add Product
            $('#saveProductBtn').click(function() {
                let formData = {
                    product_name: $('#product_name').val(),
                    _token: $('input[name="_token"]').val(),
                    product_image: $('#product_image')[0].files[0], // File input
                    productvarient: [] // Array for variants
                };

                // Add dynamically created unit and price fields to productvarient
                $('#extra-fields-container .form-group').each(function() {
                    const unit = $(this).find('input[name="unit[]"]').val();
                    const price = $(this).find('input[name="price[]"]').val();
                    if (unit && price) {
                        formData.productvarient.push({
                            unit: unit,
                            price: price
                        });
                    }
                });

                let ajaxData = new FormData();
                ajaxData.append('product_name', formData.product_name);
                ajaxData.append('_token', formData._token);
                ajaxData.append('product_image', formData.product_image);

                // Append each variant
                formData.productvarient.forEach((variant, index) => {
                    ajaxData.append(`productvarient[${index}][unit]`, variant.unit);
                    ajaxData.append(`productvarient[${index}][price]`, variant.price);
                });

                $.ajax({
                    url: '{{ route('product.store') }}', // Replace with the correct route
                    type: 'POST',
                    data: ajaxData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // alert('Product created successfully!');
                        location.reload();
                    },
                    error: function(error) {
                        console.error(error);
                        // alert('Error creating the product.');
                    }
                });
            });

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





            // Fetch data into modal
            $('.editProductBtn').on('click', function() {
                const productId = $(this).data('id'); // Get product ID from the data attribute

                $.ajax({
                    url: '/product/' + productId +
                        '/edit', // Ensure this route returns the correct product data
                    method: 'GET',
                    success: function(response) {
                        console.log(response); // Log the full response to check the structure

                        // Populate modal fields with fetched product data
                        $('#editProductName').val(response.product.name);
                        $('#editProductImage').val(response.product
                            .image); // Assuming image field should be populated here

                        // Populate the Product Variant section dynamically
                        const variantContainer = $('#editProductVariantContainer');
                        variantContainer
                            .empty(); // Clear any existing variants before adding new ones

                        response.product.product_varients.forEach(function(variant, index) {
                            // Create a new input field for each variant
                            variantContainer.append(`
                    <div class="form-group row">
                        <div class="col-sm-10">
                            <label for="editProductVariantUnit${index}">Unit</label>
                            <input type="text" class="form-control" id="editProductVariantUnit${index}" name="product_varients[${index}][unit]" value="${variant.unit}" required>
                        </div>
                        <div class="col-sm-10">
                            <label for="editProductVariantPrice${index}">Price</label>
                            <input type="text" class="form-control" id="editProductVariantPrice${index}" name="product_varients[${index}][price]" value="${variant.price}" required>
                        </div>
                    </div>
                `);
                        });

                        // Set the form action URL to the correct product update route
                        const formActionUrl = '/product/' + response.product.id;
                        $('#editProductForm').attr('action', formActionUrl);

                        // Open the modal
                        $('#editProductModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching product data:', error);
                    }
                });
            });

            // Update Product
            // $('#updateProductBtn').on('click', function() {
            //     // Ensure the form element exists
            //     const form = $('#editProductForm')[0];
            //     if (!form) {
            //         console.error('Form element not found.');
            //         return;
            //     }

            //     // Create a FormData object from the form
            //     const formData = new FormData(form);

            //     // Log the formData for debugging
            //     for (let [key, value] of formData.entries()) {
            //         console.log(`${key}: ${value}`); // Logs key-value pairs
            //     }

            //     // Send AJAX request
            //     $.ajax({
            //         url: form.action.replace(':data-id', $('#editProductId')
            //     .val()), // Replace placeholder with actual ID
            //         type: "POST", // Use POST to send the data and rely on _method for PUT
            //         data: formData,
            //         processData: false, // Prevent jQuery from processing the data
            //         contentType: false, // Prevent jQuery from setting the content type
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
            //                 'content') // Include CSRF token
            //         },
            //         success: function(response) {
            //             console.log('Product updated successfully:', response);
            //             // location.reload(); // Reload page or handle success
            //         },
            //         error: function(xhr, status, error) {
            //             console.error('Error updating product:', xhr.responseText || error);
            //         }
            //     });

            // });



        });


        function SubmitMethod() {
            alert("Hi");
        }
        // onclick="SubmitMethod()"
    </script>
@endsection
