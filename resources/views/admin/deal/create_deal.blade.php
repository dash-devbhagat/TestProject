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

                    <div class="col-sm-4 mt-2">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" name="start_date" id="start_date">
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" name="end_date" id="end_date">
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="renewal_time">Renewal Time</label>
                        <input type="number" class="form-control @error('renewal_time') is-invalid @enderror" name="renewal_time" id="renewal_time" placeholder="Enter Renewal Time (In Days)">
                        @error('renewal_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-sm-4 mt-2">
                        <label for="type">Type</label>
                        <select class="form-control @error('type') is-invalid @enderror" name="type" id="type">
                            <option value="">Select Type</option>
                            <option value="BOGO">BOGO</option>
                            <option value="Combo">Combo</option>
                            <option value="Discount">Discount</option>
                            <option value="Flat">Flat</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <!-- BOGO Fields -->
                <div id="bogo-fields" class="deal-fields" style="display: none;">
                    <div class="form-group row">

                        <div class="col-sm-3 mt-2">
                            <label for="buy_product_id">Buy Product</label>
                            <select class="form-control" name="buy_product_id" id="buy_product_id">
                                <option value="">Select Buy Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="buy_variant_id">Buy Product Variant</label>
                            <select class="form-control" name="buy_variant_id" id="buy_variant_id">
                                <option value="">Select Buy Product Variant</option>
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="buy_quantity">Buy Quantity</label>
                            <input type="number" class="form-control" name="buy_quantity" id="buy_quantity" placeholder="Enter Quantity">
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
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="get_variant_id">Get Product Variant</label>
                            <select class="form-control" name="get_variant_id" id="get_variant_id">
                                <option value="">Select Get Product Variant</option>
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="get_quantity">Get Quantity</label>
                            <input type="number" class="form-control" name="get_quantity" id="get_quantity" placeholder="Enter Quantity">
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="actual_get_amount">Actual Amount</label>
                            <input type="number" class="form-control" name="actual_get_amount" id="actual_get_amount" readonly>
                        </div>

                    </div>
                    <div class="form-group row">

                    </div>
                </div>

                <!-- Combo Fields -->
                <div id="combo-fields" class="deal-fields" style="display: none;">
                    <div id="combo-products-container">
                        <div class="form-group row combo-product align-items-center" id="combo_product_1">
                            <div class="col-sm-3 mt-2">
                                <label for="product_id_1">Product</label>
                                <select class="form-control product-select" name="product_id[]" id="product_id_1">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-3 mt-2">
                                <label for="product_variant_id_1">Product Variant</label>
                                <select class="form-control variant-select" name="product_variant_id[]" id="product_variant_id_1">
                                    <option value="">Select Product Variant</option>
                                </select>
                            </div>

                            <div class="col-sm-2 mt-2">
                                <label for="quantity_1">Quantity</label>
                                <input type="number" class="form-control quantity-input" name="quantity[]" id="quantity_1" placeholder="Enter Quantity">
                            </div>

                            <div class="col-sm-2 mt-2">
                                <!-- <label for="price_1">Price</label> -->
                                <label for="price_1">Actual Amount</label>
                                <input type="text" class="form-control price-input" name="price[]" id="price_1" readonly>
                            </div>

                            <!-- First row: Only Add (+) button, aligned with other fields -->
                            <div class="col-sm-2 mt-4">
                                <button type="button" class="btn btn-danger rounded-circle remove-product-btn" style="display: none;">
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button type="button" id="addComboProductBtn" class="btn btn-secondary rounded-circle">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                         <!-- Total Price Field -->
                    <div class="col-sm-3 mt-3 mb-4">
                        <label for="actual_combo_amount">Total Actual Amount</label>
                        <input type="number" class="form-control" name="actual_combo_amount" id="actual_combo_amount" readonly>
                    </div>

                     <!-- Total Price Field -->
                     <div class="col-sm-3 mt-3 mb-4">
                        <label for="combo_discounted_amount">Combo Discount Amount</label>
                        <input type="number" class="form-control" name="combo_discounted_amount" id="combo_discounted_amount" placeholder="Enter Combo Discount Amount">
                    </div>

                    </div>
                   
                </div>





                <!-- Discount Fields -->
                <div id="discount-fields" class="deal-fields" style="display: none;">
                    <div class="form-group row">

                        <div class="col-sm-4 mt-2">
                            <label for="min_cart_amount">Minimum Cart Amount</label>
                            <input type="number" class="form-control" name="min_cart_amount" id="min_cart_amount" placeholder="Enter Minimum Cart Amount">
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="discount_type">Discount Type</label>
                            <select class="form-control" name="discount_type" id="discount_type">
                                <option value="">Select Type</option>
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>

                        <div class="col-sm-4 mt-2">
                            <label for="discount_amount">Discount Value</label>
                            <input type="number" class="form-control" name="discount_amount" id="discount_amount" placeholder="Enter Discount Amount">
                        </div>

                    </div>
                </div>

                <!-- Flat Fields -->
                <div id="flat-fields" class="deal-fields" style="display: none;">
                    <div class="form-group row">

                        <div class="col-sm-3 mt-2">
                            <label for="flat_product_id">Product</label>
                            <select class="form-control" name="flat_product_id" id="flat_product_id">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="flat_variant_id">Product Variant</label>
                            <select class="form-control" name="flat_variant_id" id="flat_variant_id">
                                <option value="">Select Product Variant</option>
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="flat_quantity">Quantity</label>
                            <input type="number" class="form-control" name="flat_quantity" id="flat_quantity" placeholder="Enter Quantity">
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="actual_flat_amount">Actual Amount</label>
                            <input type="number" class="form-control" name="actual_flat_amount" id="actual_flat_amount" readonly>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="flat_discount_type">Discount Type</label>
                            <select class="form-control" name="flat_discount_type" id="flat_discount_type">
                                <option value="">Select Type</option>
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>

                        <div class="col-sm-3 mt-2">
                            <label for="flat_discount_amount">Discount Value</label>
                            <input type="number" class="form-control" name="flat_discount_amount" id="flat_discount_amount" placeholder="Enter Discount Amount">
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
            } else if (dealType === 'Flat') {
                $('#flat-fields').show();
            }
        }

        // Trigger the function on change
        $('#type').change(toggleFields);

        // Run on page load in case of form repopulation
        toggleFields();


        // -------------------------------------------------------------------------------------------------

        // script to load variants dropdown
        // Modify the `loadVariants()` function to handle both Combo and BOGO


        // Function to fetch variants when a product is selected
        function loadVariants(productSelect, variantSelect) {
            let productId = $(productSelect).val();
            let variantDropdown = $(variantSelect);

            variantDropdown.html('<option value="">Loading...</option>');

            if (productId) {
                $.ajax({
                    url: "/get-product-variants/" + productId,
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
                    error: function(xhr, status, error) {
                        console.error("Error loading variants:", error);
                        variantDropdown.html('<option value="">Error Loading Variants</option>');
                    }
                });
            } else {
                variantDropdown.html('<option value="">Select Product First</option>');
            }
        }

        // Event listener for buy product selection
        $('#buy_product_id').change(function() {
            if ($('#type').val() === 'BOGO' || $('#type').val() === 'Flat') {
                loadVariants('#buy_product_id', '#buy_variant_id');
            }
        });

        // Event listener for get product selection
        $('#get_product_id').change(function() {
            loadVariants('#get_product_id', '#get_variant_id');
        });

        // Event listener for combo product selection
        $('#product_id').change(function() {
            loadVariants('#product_id', '#product_variant_id');
        });

        // Event listener for flat product selection
        $('#flat_product_id').change(function() {
            loadVariants('#flat_product_id', '#flat_variant_id');
        });


        // -------------------------------------------------------------------------------------------------


        // Function to update actual amount based on selected product, variant, and quantity
        function updateActualAmount(productSelect, variantSelect, quantityInput, actualAmountInput) {
            $(productSelect + ', ' + variantSelect + ', ' + quantityInput).change(function() {
                let productId = $(productSelect).val();
                let variantId = $(variantSelect).val();
                let quantity = $(quantityInput).val();

                if (productId && variantId && quantity > 0) {
                    $.ajax({
                        url: `/get-product-price/${variantId}`,
                        type: "GET",
                        success: function(response) {
                            if (response.price) {
                                let totalAmount = response.price * quantity;
                                $(actualAmountInput).val(totalAmount.toFixed(2)); // Set the calculated amount
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching product price:", error);
                        }
                    });
                } else {
                    $(actualAmountInput).val(""); // Reset if inputs are empty
                }
            });
        }

        // Apply the function to different fields
        updateActualAmount('#buy_product_id', '#buy_variant_id', '#buy_quantity', '#actual_buy_amount');
        updateActualAmount('#get_product_id', '#get_variant_id', '#get_quantity', '#actual_get_amount');
        updateActualAmount('#flat_product_id', '#flat_variant_id', '#flat_quantity', '#actual_flat_amount');


        // -------------------------------------------------------------------------------------------------


        // script to add rows in combo 
        
        let comboProductCount = 1; // Track product rows

        // Function to add a new product row
        $('#addComboProductBtn').click(function() {
            comboProductCount++;
            let newRow = `
            <div class="form-group row combo-product align-items-center" id="combo_product_${comboProductCount}">
                <div class="col-sm-3 mt-2">
                    <select class="form-control product-select" name="product_id[]" id="product_id_${comboProductCount}">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-3 mt-2">
                    <select class="form-control variant-select" name="product_variant_id[]" id="product_variant_id_${comboProductCount}">
                        <option value="">Select Product Variant</option>
                    </select>
                </div>

                <div class="col-sm-2 mt-2">
                    <input type="number" class="form-control quantity-input" name="quantity[]" id="quantity_${comboProductCount}" placeholder="Enter Quantity">
                </div>

                <div class="col-sm-2 mt-2">
                    <input type="text" class="form-control price-input" name="price[]" id="price_${comboProductCount}" readonly>
                </div>

                <div class="col-sm-2 mt-2">
                    <button type="button" class="btn btn-danger rounded-circle remove-product-btn">
                        <i class="fa fa-trash"></i>
                    </button>
                    <button type="button" id="addComboProductBtn" class="btn btn-secondary rounded-circle">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
        `;

            $('#combo-products-container').append(newRow);
            updateButtonVisibility();
        });

        // Remove product row
        $(document).on('click', '.remove-product-btn', function() {
            $(this).closest('.combo-product').remove();
            updateButtonVisibility();
        });

        // Ensure delete button is not on the first row
        function updateButtonVisibility() {
            $('.combo-product').each(function(index) {
                if (index === 0) {
                    $(this).find('.remove-product-btn').hide();
                    $(this).find('#addComboProductBtn').show();
                } else {
                    $(this).find('.remove-product-btn').show();
                    $(this).find('#addComboProductBtn').hide();
                }
            });
        }

        updateButtonVisibility(); // Call function on page load


        // -------------------------------------------------------------------------------------------------


        // Function to fetch product price and calculate total
        function updatePrice(variantSelect, quantityInput, priceInput) {
            $(variantSelect + ', ' + quantityInput).change(function() {
                let variantId = $(variantSelect).val();
                let quantity = $(quantityInput).val();

                if (variantId && quantity > 0) {
                    $.ajax({
                        url: `/get-product-price/${variantId}`,
                        type: "GET",
                        success: function(response) {
                            if (response.price) {
                                let totalAmount = response.price * quantity;
                                $(priceInput).val(totalAmount.toFixed(2)); // Set the calculated price
                                updateTotalAmount();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching product price:", error);
                        }
                    });
                } else {
                    $(priceInput).val(""); // Reset if inputs are empty
                    updateTotalAmount();
                }
            });
        }



        // Function to update the total actual amount
        function updateTotalAmount() {
            let totalAmount = 0;
            $('.price-input').each(function() {
                let price = parseFloat($(this).val()) || 0;
                totalAmount += price;
            });
            $('#actual_combo_amount').val(totalAmount.toFixed(2));
        }

        // Event Listeners for dynamically added fields
        $(document).on('change', '.product-select', function() {
            let rowId = $(this).closest('.combo-product').attr('id');
            loadVariants(`#${rowId} .product-select`, `#${rowId} .variant-select`);
        });

        $(document).on('change', '.variant-select, .quantity-input', function() {
            let rowId = $(this).closest('.combo-product').attr('id');
            updatePrice(`#${rowId} .variant-select`, `#${rowId} .quantity-input`, `#${rowId} .price-input`);
        });


        // -------------------------------------------------------------------------------------------------


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


        // -------------------------------------------------------------------------------------------------





        // -------------------------------------------------------------------------------------------------





    });
</script>
@endsection