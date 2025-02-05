@extends('layouts.master')

@section('title', 'Deal Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1>Add Deal</h1>
                </div>
                 <!-- Back button on the right -->
                 <div class="col-sm-6 text-right">
                        <a href="{{ route('deal.index') }}" class="btn btn-secondary text-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
            </div>

            <form action="{{ route('deal.store') }}" method="POST" enctype="multipart/form-data" class="form-horizontal mt-3">
                @csrf

                <div class="form-group row">
                    <div class="col-sm-4 mt-2">
                        <label for="title">Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" id="title" placeholder="Enter Title">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="description">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" name="description" id="description" placeholder="Enter Description">
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" name="image" id="image">
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" id="start_date">
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="end_date">
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="product_id">Product</label>
                        <select class="form-control" name="product_id" id="product_id">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="product_variant_id">Product Variant</label>
                        <select class="form-control" name="product_variant_id" id="product_variant_id">
                            <option value="">Select Variant</option>
                        </select>
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="type">Type</label>
                        <select class="form-control @error('type') is-invalid @enderror" name="type" id="type">
                            <option value="">Select Type</option>
                            <option value="BOGO">BOGO</option>
                            <option value="Combo">Combo</option>
                            <option value="Discount">Discount</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <!-- BOGO Fields -->
                <div id="bogo-fields" class="deal-fields" style="display: none;">
                    <div class="form-group row">
                        <div class="col-sm-4 mt-2">
                            <label for="min_quantity">Minimum Quantity</label>
                            <input type="number" class="form-control" name="min_quantity" id="min_quantity">
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="free_quantity">Free Quantity</label>
                            <input type="number" class="form-control" name="free_quantity" id="free_quantity">
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="b_free_product_id">Free Product</label>
                            <select class="form-control" name="b_free_product_id" id="b_free_product_id">
                                <option value="">Select Free Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="b_free_product_variant_id">Free Product Variant</label>
                            <select class="form-control" name="b_free_product_variant_id" id="b_free_product_variant_id">
                                <option value="">Select Free Product Variant</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Combo Fields -->
                <div id="combo-fields" class="deal-fields" style="display: none;">
                    <div class="form-group row">
                        <div class="col-sm-4 mt-2">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity">
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="camount">Amount</label>
                            <input type="text" class="form-control" name="camount" id="camount">
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="c_free_product_id">Free Product</label>
                            <select class="form-control" name="c_free_product_id" id="c_free_product_id">
                                <option value="">Select Free Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="c_free_product_variant_id">Free Product Variant</label>
                            <select class="form-control" name="c_free_product_variant_id" id="c_free_product_variant_id">
                                <option value="">Select Free Product Variant</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Discount Fields -->
                <div id="discount-fields" class="deal-fields" style="display: none;">
                    <div class="form-group row">
                        <div class="col-sm-4 mt-2">
                            <label for="damount">Amount</label>
                            <input type="text" class="form-control" name="damount" id="damount">
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="percentage">Percentage</label>
                            <input type="number" class="form-control" name="percentage" id="percentage" min="0" max="100">
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary my-2">Save</button>
                </div>
            </form>



        </div><!-- /.container-fluid -->
    </section>
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



         // Toggle Fields based on type
         function toggleFields() {
            let dealType = $('#type').val();
            $('.deal-fields').hide();

            if (dealType === 'BOGO') {
                $('#bogo-fields').show();
            } else if (dealType === 'Combo') {
                $('#combo-fields').show();
            } else if (dealType === 'Discount') {
                $('#discount-fields').show();
            }
        }

        // Trigger the function on change
        $('#type').change(toggleFields);

        // Run on page load in case of form repopulation
        toggleFields();




        // script to load variants dropdown
        // Modify the `loadVariants()` function to handle both Combo and BOGO
        function loadVariants(productSelect, variantSelect, freeProduct = false) {
            let productId = $(productSelect).val();
            let variantDropdown = $(variantSelect);

            variantDropdown.html('<option value="">Loading...</option>');

            if (productId) {
                $.ajax({
                    url: "/get-product-variants/" + productId,
                    type: "GET",
                    success: function(response) {
                        console.log("Variants Loaded: ", response); // Debugging step
                        variantDropdown.html('<option value="">Select Variant</option>');
                        if (response.length > 0) {
                            response.forEach(function(variant) {
                                variantDropdown.append(`<option value="${variant.id}">${variant.unit}</option>`);
                            });
                        } else {
                            variantDropdown.html('<option value="">No Variants Available</option>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading variants:", error);
                        variantDropdown.html('<option value="">Error Loading Variants</option>');
                    }
                });
            } else {
                variantDropdown.html('<option value="">Select Product First</option>');
            }
        }

        // Event listener for normal product selection
        $('#product_id').change(function() {
            loadVariants('#product_id', '#product_variant_id');
        });

        // Event listener for Combo and BOGO free product variant selection
        $('#b_free_product_id, #c_free_product_id').change(function() {
            let freeProductId = $(this).val();
            let variantSelect = $(this).attr('id') === 'b_free_product_id' ? '#b_free_product_variant_id' : '#c_free_product_variant_id';
            loadVariants(this, variantSelect, true); // true indicates this is for a free product
        });




        // Open the modal when the info icon is clicked
        $('.fas.fa-info-circle').on('click', function() {
            $('#dealTypesModal').modal('show');
        });


        
    });
        </script>
@endsection