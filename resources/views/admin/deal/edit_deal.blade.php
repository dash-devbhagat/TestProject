@extends('layouts.master')

@section('title', 'Deal Management - Edit')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1>Edit Deal- {{ $type }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('deal.index') }}" class="btn btn-secondary text-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <form action="{{ route('deal.update', $deal->id) }}" method="POST" enctype="multipart/form-data" class="form-horizontal mt-3">
                @csrf
                @method('PUT')

                <input type="hidden" name="type" value="{{ $deal->type }}">

                <div class="form-group row">
                    <div class="col-sm-4 mt-2">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $deal->title) }}">
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="description">Description</label>
                        <input type="text" class="form-control" name="description" id="description" value="{{ old('description', $deal->description) }}">
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="image">Image</label>
                        <input type="file" class="form-control" name="image" id="image">
                        <!-- @if($deal->image)
                        <img src="{{ asset('storage/'.$deal->image) }}" alt="Deal Image" width="100" class="mt-2">
                        @endif -->
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="{{ old('start_date', $deal->start_date) }}">
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{ old('end_date', $deal->end_date) }}">
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="renewal_time">Renewal Time</label>
                        <input type="number" class="form-control" name="renewal_time" id="renewal_time" value="{{ old('renewal_time', $deal->renewal_time) }}">
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="type">Type</label>
                        <select class="form-control" name="type" id="type" disabled>
                            <option value="BOGO" {{ $deal->type == 'BOGO' ? 'selected' : '' }}>BOGO</option>
                            <option value="Combo" {{ $deal->type == 'Combo' ? 'selected' : '' }}>Combo</option>
                            <option value="Discount" {{ $deal->type == 'Discount' ? 'selected' : '' }}>Discount</option>
                            <option value="Flat" {{ $deal->type == 'Flat' ? 'selected' : '' }}>Flat</option>
                        </select>
                    </div>
                </div>


                @if($type == 'BOGO')
                <!-- BOGO Edit Fields -->
                <div id="bogo-fields" class="deal-fields">
                    <div class="form-group row">
                        <div class="col-sm-3 mt-2">
                            <label for="buy_product_id">Buy Product</label>
                            <select class="form-control" name="buy_product_id" id="buy_product_id">
                                <option value="">Select Buy Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $deal->buy_product_id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="buy_variant_id">Buy Product Variant</label>
                            <select class="form-control" name="buy_variant_id" id="buy_variant_id">
                                <option value="">Select Buy Product Variant</option>
                                @foreach(App\Models\ProductVarient::where('product_id', $deal->buy_product_id)->get() as $variant)
                                <option value="{{ $variant->id }}" {{ $deal->buy_variant_id == $variant->id ? 'selected' : '' }}>
                                    {{ $variant->unit }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="buy_quantity">Buy Quantity</label>
                            <input type="number" class="form-control" name="buy_quantity" id="buy_quantity"
                                value="{{ $deal->buy_quantity }}">
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="actual_buy_amount">Actual Amount</label>
                            <input type="number" class="form-control" name="actual_buy_amount" id="actual_buy_amount" readonly>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="get_product_id">Get Product</label>
                            <select class="form-control" name="get_product_id" id="get_product_id">
                                <option value="">Select Get Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $deal->get_product_id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="get_variant_id">Get Product Variant</label>
                            <select class="form-control" name="get_variant_id" id="get_variant_id">
                                <option value="">Select Get Product Variant</option>
                                @foreach(App\Models\ProductVarient::where('product_id', $deal->get_product_id)->get() as $variant)
                                <option value="{{ $variant->id }}" {{ $deal->get_variant_id == $variant->id ? 'selected' : '' }}>
                                    {{ $variant->unit }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="get_quantity">Get Quantity</label>
                            <input type="number" class="form-control" name="get_quantity" id="get_quantity"
                                value="{{ $deal->get_quantity }}">
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="actual_get_amount">Actual Amount</label>
                            <input type="number" class="form-control" name="actual_get_amount" id="actual_get_amount" readonly>
                        </div>
                    </div>
                </div>
                @endif

                @if($type == 'Combo')
                <!-- Combo Edit Fields -->
                <div id="combo-fields" class="deal-fields">
                    <div id="combo-products-container">
                        @foreach($comboProducts as $index => $combo)
                        <div class="form-group row combo-product align-items-center" id="combo_product_{{ $index }}">
                            <div class="col-sm-3 mt-2">
                                <label for="product_id_{{ $index }}">Product</label>
                                <select class="form-control product-select" name="product_id[]" id="product_id_{{ $index }}">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ $combo->product_id == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-3 mt-2">
                                <label for="product_variant_id_{{ $index }}">Product Variant</label>
                                <select class="form-control variant-select" name="product_variant_id[]" id="product_variant_id_{{ $index }}">
                                    <option value="">Select Product Variant</option>
                                    @foreach(App\Models\ProductVarient::where('product_id', $combo->product_id)->get() as $variant)
                                    <option value="{{ $variant->id }}" {{ $combo->variant_id == $variant->id ? 'selected' : '' }}>
                                        {{ $variant->unit }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-2 mt-2">
                                <label for="quantity_{{ $index }}">Quantity</label>
                                <input type="number" class="form-control quantity-input" name="quantity[]" id="quantity_{{ $index }}"
                                    value="{{ $combo->quantity }}">
                            </div>

                            <div class="col-sm-2 mt-2">
                                <label for="price_{{ $index }}">Actual Amount</label>
                                <input type="number" class="form-control price-input" name="price[]" id="price_{{ $index }}" readonly>
                            </div>

                            <div class="col-sm-2 mt-4">
                                <button type="button" class="btn btn-danger rounded-circle remove-product-btn">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button type="button" class="btn btn-secondary rounded-circle add-product-btn">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Total Amount Fields -->
                    <div class="form-group row">
                        <div class="col-sm-3 mt-3 mb-4">
                            <label for="actual_combo_amount">Total Actual Amount</label>
                            <input type="number" class="form-control" name="actual_combo_amount" id="actual_combo_amount" readonly>
                        </div>

                        <div class="col-sm-4 mt-3 mb-4">
                            <label for="combo_discounted_amount">Combo Discount Amount</label>
                            <input type="number" class="form-control" name="combo_discounted_amount" id="combo_discounted_amount"
                                value="{{ $deal->combo_discounted_amount }}" placeholder="Enter Combo Discount Amount">
                        </div>
                    </div>
                </div>
                @endif


                @if($type == 'Discount')
                <div class="form-group row">

                    <div class="col-sm-4 mt-2">
                        <label for="min_cart_amount">Minimum Cart Amount</label>
                        <input type="number" class="form-control" name="min_cart_amount" id="min_cart_amount" value="{{ old('min_cart_amount', $deal->min_cart_amount) }}" placeholder="Enter Minimum Cart Amount">
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="discount_type">Discount Type</label>
                        <select class="form-control" name="discount_type" id="discount_type">
                            <option value="">Select Type</option>
                            <option value="fixed" {{ old('discount_type', $deal->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="percentage" {{ old('discount_type', $deal->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                        </select>
                    </div>


                    <div class="col-sm-4 mt-2">
                        <label for="discount_amount">Discount Value</label>
                        <input type="number" class="form-control" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $deal->discount_amount) }}" placeholder="Enter Discount Amount">
                    </div>

                </div>
                @endif

                @if($type == 'Flat')
                <div class="form-group row">

                    <div class="col-sm-3 mt-2">
                        <label for="flat_product_id">Product</label>
                        <select class="form-control" name="flat_product_id" id="flat_product_id">
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ $deal->buy_product_id == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="flat_variant_id">Buy Product Variant</label>
                        <select class="form-control" name="flat_variant_id" id="flat_variant_id">
                            <option value="">Select Buy Product Variant</option>
                            @if($deal->buy_product_id)
                            @foreach(App\Models\ProductVarient::where('product_id', $deal->buy_product_id)->get() as $variant)
                            <option value="{{ $variant->id }}" {{ $deal->buy_variant_id == $variant->id ? 'selected' : '' }}>
                                {{ $variant->unit }}
                            </option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="flat_quantity">Quantity</label>
                        <input type="number" class="form-control" name="flat_quantity" id="flat_quantity"
                            value="{{ old('buy_quantity', $deal->buy_quantity) }}" placeholder="Enter Quantity">
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="actual_flat_amount">Actual Amount</label>
                        <input type="number" class="form-control" name="actual_flat_amount" id="actual_flat_amount"
                            value="{{ old('actual_flat_amount', $deal->actual_flat_amount) }}" readonly>
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="flat_discount_type">Discount Type</label>
                        <select class="form-control" name="flat_discount_type" id="flat_discount_type">
                            <option value="">Select Type</option>
                            <option value="fixed" {{ old('discount_type', $deal->discount_type) == 'fixed' ? 'selected' : '' }}>
                                Fixed</option>
                            <option value="percentage" {{ old('discount_type', $deal->discount_type) == 'percentage' ? 'selected' : '' }}>
                                Percentage</option>
                        </select>
                    </div>

                    <div class="col-sm-3 mt-2">
                        <label for="flat_discount_amount">Discount Value</label>
                        <input type="number" class="form-control" name="flat_discount_amount" id="flat_discount_amount"
                            value="{{ old('discount_amount', $deal->discount_amount) }}" placeholder="Enter Discount Amount">
                    </div>

                </div>
                @endif



                <div class="card-footer">
                    <button type="submit" class="btn btn-primary my-2">Update</button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {

        updateActualAmount('#buy_product_id', '#buy_variant_id', '#buy_quantity', '#actual_buy_amount');
        updateActualAmount('#get_product_id', '#get_variant_id', '#get_quantity', '#actual_get_amount');
        updateActualAmount('#flat_product_id', '#flat_variant_id', '#flat_quantity', '#actual_flat_amount');


        // Update price for all combo products when page loads
        $('.combo-product').each(function() {
            updatePrice($(this));
        });

        // Call updateActualAmount when product, variant, or quantity changes for combo rows
        $(document).on('change', '.product-select, .variant-select, .quantity-input', function() {
            let row = $(this).closest('.combo-product');
            let productSelect = row.find('.product-select');
            let variantSelect = row.find('.variant-select');
            let quantityInput = row.find('.quantity-input');
            let actualAmountInput = row.find('.price-input');
            updateActualAmount(productSelect, variantSelect, quantityInput, actualAmountInput);
            updatePrice(row); // Update price for the combo row
        });



        function loadVariants(productSelect, variantSelect) {
            let productId = $(productSelect).val();
            let variantDropdown = $(variantSelect);
            variantDropdown.html('<option value="">Loading...</option>');

            if (productId) {
                $.ajax({
                    url: `/get-product-variants/${productId}`,
                    type: "GET",
                    success: function(response) {
                        variantDropdown.html('<option value="">Select Variant</option>');
                        if (response.length > 0) {
                            response.forEach(function(variant) {
                                variantDropdown.append(`<option value="${variant.id}">${variant.unit}</option>`);
                            });
                        } else {
                            variantDropdown.html('<option value="">No Variants Available</option>');
                        }
                    },
                    error: function() {
                        variantDropdown.html('<option value="">Error Loading Variants</option>');
                    }
                });
            } else {
                variantDropdown.html('<option value="">Select Product First</option>');
            }
        }




        //  calling loadVariants for other type dropdowns
        $('#buy_product_id').change(function() {
            loadVariants(this, '#buy_variant_id');
        });

        $('#get_product_id').change(function() {
            loadVariants(this, '#get_variant_id');
        });

        $('#flat_product_id').change(function() {
            loadVariants(this, '#flat_variant_id');
        });


        // // call loadVariants for existing rows in combo
        // $(document).on('change', '.product-select', function() {
        //     let rowId = $(this).closest('.combo-product').attr('id');
        //     loadVariants(`#${rowId} .product-select`, `#${rowId} .variant-select`);
        // });

        // Call loadVariants for dynamically added rows in combo
        $(document).on('change', '.product-select', function() {
            let row = $(this).closest('.combo-product');
            loadVariants($(this), row.find('.variant-select'));
        });





        // for other types
        function updateActualAmount(productSelect, variantSelect, quantityInput, actualAmountInput) {
            // $(productSelect + ', ' + variantSelect + ', ' + quantityInput).change(function() {
            let variantId = $(variantSelect).val();
            let quantity = $(quantityInput).val();

            if (variantId && quantity > 0) {
                $.ajax({
                    url: `/get-product-price/${variantId}`,
                    type: "GET",
                    success: function(response) {
                        if (response.price) {
                            let totalAmount = response.price * quantity;
                            $(actualAmountInput).val(totalAmount.toFixed(2));
                        }
                    },
                    error: function() {
                        $(actualAmountInput).val("");
                    }
                });
            } else {
                $(actualAmountInput).val("");
            }
            // });
        }


        // For other types (buy, get, flat)
        $(document).on('change', '#buy_product_id, #buy_variant_id, #buy_quantity', function() {
            updateActualAmount('#buy_product_id', '#buy_variant_id', '#buy_quantity', '#actual_buy_amount');
        });

        $(document).on('change', '#get_product_id, #get_variant_id, #get_quantity', function() {
            updateActualAmount('#get_product_id', '#get_variant_id', '#get_quantity', '#actual_get_amount');
        });

        $(document).on('change', '#flat_product_id, #flat_variant_id, #flat_quantity', function() {
            updateActualAmount('#flat_product_id', '#flat_variant_id', '#flat_quantity', '#actual_flat_amount');
        });




        // -----------------------------------------------------------------------------------------------------------





        // for combo 

        let comboProductCount = @json(count($comboProducts));

        $(document).on('click', '.add-product-btn', function() {
            comboProductCount++;
            let newRow = `
        <div class="form-group row combo-product align-items-center" id="combo_product_${comboProductCount}">
            <div class="col-sm-3 mt-2">
             <label for="product_id_${comboProductCount}">Product</label>
                <select class="form-control product-select" name="product_id[]" id="product_id_${comboProductCount}">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-3 mt-2">
             <label for="product_variant_id_${comboProductCount}">Product Variant</label>
                <select class="form-control variant-select" name="product_variant_id[]" id="product_variant_id_${comboProductCount}">
                    <option value="">Select Product Variant</option>
                </select>
            </div>

            <div class="col-sm-2 mt-2">
            <label for="quantity_${comboProductCount}">Quantity</label>
                <input type="number" class="form-control quantity-input" name="quantity[]" id="quantity_${comboProductCount}" placeholder="Enter Quantity">
            </div>

            <div class="col-sm-2 mt-2">
             <label for="price_${comboProductCount}">Actual Amount</label>
                <input type="number" class="form-control price-input" name="price[]" id="price_${comboProductCount}" readonly>
            </div>

            <div class="col-sm-2 mt-4">
                <button type="button" class="btn btn-danger rounded-circle remove-product-btn">
                    <i class="fa fa-trash"></i>
                </button>
                <button type="button" class="btn btn-secondary rounded-circle add-product-btn">
                    <i class="fa fa-plus"></i>
                </button>
            </div>
        </div>`;

            $('#combo-products-container').append(newRow);
        });

        // Remove combo product row
        // $(document).on('click', '.remove-product-btn', function() {
        //     $(this).closest('.combo-product').remove();
        //     updateTotalAmount();
        // });
        $(document).on('click', '.remove-product-btn', function() {
            if ($('#combo-products-container .combo-product').length > 1) {
                $(this).closest('.combo-product').remove();
                updateTotalAmount();
            } else {
                alert('At least one product is required in the combo.');
            }
        });







        // -----------------------------------------------------------------------------------------------------------


        // for combo 


        // Function to fetch product price and update total
        function updatePrice(row) {
            let variantSelect = row.find('.variant-select');
            let quantityInput = row.find('.quantity-input');
            let priceInput = row.find('.price-input');

            let variantId = variantSelect.val();
            let quantity = quantityInput.val();

            if (variantId && quantity > 0) {
                $.ajax({
                    url: `/get-product-price/${variantId}`,
                    type: "GET",
                    success: function(response) {
                        if (response.price) {
                            let totalAmount = response.price * quantity;
                            priceInput.val(totalAmount.toFixed(2)); // Update the price input
                            updateTotalAmount(); // Update total combo amount
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching product price:", error);
                    }
                });
            } else {
                priceInput.val(""); // Reset price if inputs are empty
                updateTotalAmount();
            }
        }


        // Function to update total combo amount
        function updateTotalAmount() {
            let totalAmount = 0;
            $('.price-input').each(function() {
                totalAmount += parseFloat($(this).val()) || 0;
            });
            $('#actual_combo_amount').val(totalAmount.toFixed(2));
        }



        // // Update price when variant or quantity changes
        // $(document).on('change', '.product-select, .variant-select, .quantity-input', function() {
        //     let row = $(this).closest('.combo-product');
        //     updatePrice(row);
        // });







        // -----------------------------------------------------------------------------------------------------------



        //  script to preventing user from selecting previous dates

        // Get today's date in YYYY-MM-DD format
        let today = new Date().toISOString().split('T')[0];

        // Set the min attribute for start_date and end_date
        $('#start_date, #end_date').attr('min', today);

        // Ensure end_date is always after start_date
        $('#start_date').change(function() {
            let startDate = $(this).val();
            $('#end_date').attr('min', startDate);
        });



        // -----------------------------------------------------------------------------------------------------------


    });
</script>
@endsection