@extends('layouts.master')

@section('title', 'Product Management - Edit')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <!-- Heading on the left -->
                <div class="col-sm-6">
                    <h1>Edit Product</h1>
                </div>
                <!-- Back button on the right -->
                <div class="col-sm-6 text-right">
                    <a href="{{ route('product.index') }}" class="btn btn-secondary text-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <form method="POST" action="{{ route('product.update', $product->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group row">
                <div class="col-sm-5">
                    <label for="product_name">Product Name</label>
                    <input type="text" class="form-control" name="product_name" id="product_name"
                        value="{{ $product->name }}">
                </div>
                <div class="col-sm-5">
                    <label for="product_image">Product Image</label>
                    <input type="file" class="form-control" name="product_image" id="product_image">
                </div>

                <!-- Category Dropdown -->
                <div class="col-sm-5">
                    <label for="category_id">Category</label>
                    <select class="form-control @error('category_id') is-invalid @enderror" name="category_id"
                        id="category_id">
                        <option value="" disabled>Select Category</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ $category->id == $product->category_id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Subcategory Dropdown -->
                <div class="col-sm-5">
                    <label for="sub_category_id">Subcategory</label>
                    <select class="form-control @error('sub_category_id') is-invalid @enderror" name="sub_category_id"
                        id="sub_category_id" @if (is_null($product->sub_category_id)) disabled @endif>
                        <option value="" disabled @if (is_null($product->sub_category_id)) selected @endif>Select
                            Subcategory</option>
                        @foreach ($subCategories as $subCategory)
                        <option value="{{ $subCategory->id }}"
                            {{ (string) $subCategory->id === (string) $product->sub_category_id ? 'selected' : '' }}>
                            {{ $subCategory->name }}
                        </option>

                        @endforeach
                    </select>
                </div>

            </div>

            <div class="form-group row">
                <div class="col-sm-10">
                    <label for="product_details">Product Details</label>
                    <input type="text" class="form-control" name="product_details" id="product_details"
                        value="{{ $product->details }}">
                </div>

                <div class="col-sm-2 text-end" style="margin-left: -170px; margin-top: 30px">
                    <button type="button" id="addVariantBtn" class="btn btn-secondary rounded-circle">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>

            <div id="product-variants">
                @foreach ($productVariants as $variant)
                <div class="form-group row mt-3">
                    <div class="col-sm-5">
                        <label for="unit_{{ $variant->id }}">Unit</label>
                        <input type="text" class="form-control"
                            name="product_varients[{{ $variant->id }}][unit]" value="{{ $variant->unit }}"
                            required>
                    </div>
                    <div class="col-sm-5">
                        <label for="price_{{ $variant->id }}">Price</label>
                        <input type="text" class="form-control"
                            name="product_varients[{{ $variant->id }}][price]" value="{{ $variant->price }}"
                            required>
                    </div>
                    <div class="col-sm-2 text-center" style="margin-left: -80px;margin-top: 30px;">
                        <button type="button" class="btn btn-danger rounded-circle remove-variant-btn">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="card-footer d-flex mt-4">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </section>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let variantCount = {
            {
                count($productVariants)
            }
        };

        $('#addVariantBtn').click(function() {
            variantCount++;
            let newVariant = `
                    <div class="form-group row mt-3" id="variant_${variantCount}">
                        <div class="col-sm-5">
                            <label for="unit_${variantCount}">Unit</label>
                            <input type="text" class="form-control" name="product_varients[${variantCount}][unit]" placeholder="Enter Unit">
                        </div>
                        <div class="col-sm-5">
                            <label for="price_${variantCount}">Price</label>
                            <input type="text" class="form-control" name="product_varients[${variantCount}][price]" placeholder="Enter Price">
                        </div>
                        <div class="col-sm-2 text-center" style="margin-left: -80px;margin-top: 30px;">
                            <button type="button" class="btn btn-danger rounded-circle remove-variant-btn">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            $('#product-variants').append(newVariant);
        });

        $(document).on('click', '.remove-variant-btn', function() {
            $(this).closest('.form-group').remove();
        });

        // Fetch Sub categories
        $('#category_id').change(function() {
            const categoryId = $(this).val();
            const subCategoryDropdown = $('#sub_category_id');

            // Reset and disable subcategory dropdown if no category is selected
            if (!categoryId) {
                subCategoryDropdown.prop('disabled', true).html(
                    '<option value="" disabled selected>Select Subcategory</option>');
            } else {
                // Fetch subcategories and enable the dropdown
                subCategoryDropdown.prop('disabled', true).html(
                    '<option value="" disabled selected>Loading...</option>');
                $.ajax({
                    url: '/sub-category/fetch/' + categoryId,
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
            }
        });

        // Pre-select the subcategory if one is already selected
        if ($("#category_id").val()) {
            $('#category_id').trigger('change');
        }
    });
</script>
@endsection