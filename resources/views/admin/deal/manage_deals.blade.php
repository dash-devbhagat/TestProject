@extends('layouts.master')

@section('title', 'Deal Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6 d-flex align-items-center">
                    <h1 class="me-2" style="margin-top: -6px;">Manage Deals</h1>
                    <!-- Info Icon for Deal Types -->
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#dealTypesModal">
                        <i class="fas fa-info-circle" style="font-size: 1.5em;" title="Click to see deal type details"></i>
                    </a>
                </div>
                <!-- Add User Button on the right side -->
                <div class="col-sm-6 text-right">
                    <a href="{{ route('deal.create') }}" class="btn btn-success">Add
                        Deal</a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

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


    <!-- Main content -->
    <section class="content">

        <div class="card">
            <!-- /.card-header -->

            <div class="card-body">
                <table id="usersTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Sr</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Image</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Active Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $i = 1;
                        @endphp
                        @foreach ($deals as $deal)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $deal->title }}</td>
                            <td>{{ $deal->description ?? 'No Details Available' }}</td>
                            <td>{{ $deal->type }}</td>
                            <td class="text-center">
                                @if ($deal->image)
                                <img src="{{ asset('storage/' . $deal->image) }}" alt="Deal Image"
                                    width="80" height="50" onerror="this.onerror=null; this.src='{{ asset('adminlte/dist/img/inf.png') }}';">
                                @else
                                N/A
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($deal->start_date)->format('d-m-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($deal->end_date)->format('d-m-Y') }}</td>
                            <td class="text-center">
                                <!-- Active/Inactive Toggle Icon -->
                                <a href="javascript:void(0);" id="toggleStatusBtn{{ $deal->id }}"
                                    data-id="{{ $deal->id }}" class="text-center" data-toggle="tooltip"
                                    title="{{ $deal->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i
                                        class="fas {{ $deal->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <!-- View Icon -->
                                <a href="{{ route('deal.show', $deal->id) }}" class="text-secondary"
                                    data-bs-toggle="tooltip" title="View">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <!-- Edit Icon -->
                                <a href="#javascript" class="text-primary" data-toggle="modal"
                                    data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fa fa-edit editCouponBtn" data-id="{{ $deal->id }}"></i>
                                </a>
                                <!-- Delete Icon -->
                                <form action="{{ route('deal.destroy', $deal->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this deal?');">
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



<!-- Modal for Showing Deal Types Information -->
<div class="modal fade" id="dealTypesModal" tabindex="-1" aria-labelledby="dealTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dealTypesModalLabel">Deal Types Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content for Deal Types Information -->
                <h5><strong>BOGO (Buy One Get One)</strong></h5>
                <p>With this deal type, customers can get a free product after buying one product of equal or lesser value.</p>

                <h5><strong>Combo Deals</strong></h5>
                <p>This deal type allows customers to purchase multiple products in a package at a discounted price.</p>

                <h5><strong>Discount</strong></h5>
                <p>Discount deals allow customers to get a certain percentage or fixed amount off the original price of the product.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        $('.editCouponBtn').on('click', function() {
            const couponId = $(this).data('id');

            $.ajax({
                url: '/coupon/' + couponId + '/edit',
                type: "GET",
                success: function(response) {
                    const coupon = response.coupon;
                    $('#editCouponId').val(coupon.id);
                    $('#editCouponName').val(coupon.name);
                    $('#editCouponAmount').val(coupon.amount);
                    $('#editCouponCode').val(coupon.coupon_code);
                    $('#editCouponDetails').val(coupon.description);
                    $('#editCouponModal').modal('show');
                },
                error: function() {
                    alert('Error fetching category data.');
                }
            });
        });

        // Update the Coupon
        $('#updateCouponBtn').on('click', function() {
            const formData = new FormData($('#editCouponForm')[0]);
            formData.append('_method', 'PUT'); // Add method override for PUT

            $.ajax({
                url: '/coupon/' + $('#editCouponId')
                    .val(), // PUT request to update category
                type: "POST", // Use POST since we're using _method override for PUT
                data: formData,
                contentType: false, // Necessary for file uploads
                processData: false, // Prevents jQuery from processing the data
                success: function(response) {
                    $('#editCouponModal').modal('hide');
                    location.reload(); // Reload the page or update dynamically
                },
                error: function() {
                    alert('Error updating coupon data. Please try again.');
                }
            });
        });


        // Toggle Status
        $(document).on('click', '[id^="toggleStatusBtn"]', function() {
            var dealId = $(this).data('id');

            $.ajax({
                url: '/deal/' + dealId +
                    '/toggle-status', // Use the route for toggling user status
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(), // CSRF token
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('An error occurred while toggling deal status.');
                }
            });
        });

        // // Toggle Fields based on type
        // function toggleFields() {
        //     let dealType = $('#type').val();
        //     $('.deal-fields').hide();

        //     if (dealType === 'BOGO') {
        //         $('#bogo-fields').show();
        //     } else if (dealType === 'Combo') {
        //         $('#combo-fields').show();
        //     } else if (dealType === 'Discount') {
        //         $('#discount-fields').show();
        //     }
        // }

        // // Trigger the function on change
        // $('#type').change(toggleFields);

        // // Run on page load in case of form repopulation
        // toggleFields();




        // // script to load variants dropdown
        // // Modify the `loadVariants()` function to handle both Combo and BOGO
        // function loadVariants(productSelect, variantSelect, freeProduct = false) {
        //     let productId = $(productSelect).val();
        //     let variantDropdown = $(variantSelect);

        //     variantDropdown.html('<option value="">Loading...</option>');

        //     if (productId) {
        //         $.ajax({
        //             url: "/get-product-variants/" + productId,
        //             type: "GET",
        //             success: function(response) {
        //                 console.log("Variants Loaded: ", response); // Debugging step
        //                 variantDropdown.html('<option value="">Select Variant</option>');
        //                 if (response.length > 0) {
        //                     response.forEach(function(variant) {
        //                         variantDropdown.append(`<option value="${variant.id}">${variant.unit}</option>`);
        //                     });
        //                 } else {
        //                     variantDropdown.html('<option value="">No Variants Available</option>');
        //                 }
        //             },
        //             error: function(xhr, status, error) {
        //                 console.error("Error loading variants:", error);
        //                 variantDropdown.html('<option value="">Error Loading Variants</option>');
        //             }
        //         });
        //     } else {
        //         variantDropdown.html('<option value="">Select Product First</option>');
        //     }
        // }

        // // Event listener for normal product selection
        // $('#product_id').change(function() {
        //     loadVariants('#product_id', '#product_variant_id');
        // });

        // // Event listener for Combo and BOGO free product variant selection
        // $('#b_free_product_id, #c_free_product_id').change(function() {
        //     let freeProductId = $(this).val();
        //     let variantSelect = $(this).attr('id') === 'b_free_product_id' ? '#b_free_product_variant_id' : '#c_free_product_variant_id';
        //     loadVariants(this, variantSelect, true); // true indicates this is for a free product
        // });




        // // Open the modal when the info icon is clicked
        // $('.fas.fa-info-circle').on('click', function() {
        //     $('#dealTypesModal').modal('show');
        // });




    });
</script>
@endsection