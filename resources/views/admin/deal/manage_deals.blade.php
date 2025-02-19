@extends('layouts.master')

@section('title', 'Deal Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6 d-flex align-items-center">
                    <h1 class="me-2" style="margin-top: -6px;">Manage Deals</h1>
                    <!-- Info Icon for Deal Types -->
                    <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#dealTypesModal">
                        <i class="fas fa-info-circle" style="font-size: 1.5em;" data-bs-toggle="tooltip" title="Deal Type Information"></i>
                    </a>
                </div>
                <!-- Add User Button on the right side -->
                <div class="col-sm-6 text-right">
                    <a href="{{ route('deal.create') }}" class="btn btn-success">Add
                        Deal</a>
                </div>
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
                            <th>Title</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Image</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Renewal Time(Days)</th>
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
                            <td>{{ $deal->renewal_time }}</td>
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
                                <a href="{{ route('deal.edit', $deal->id) }}?type={{ $deal->type }}" class="text-primary"
                                    data-bs-toggle="tooltip" title="Edit">
                                    <i class="fa fa-edit"></i>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dealTypesModalLabel">Deal Types Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content for Deal Types Information -->
                <h5><strong>BOGO (Buy One Get One)</strong></h5>
                <p>With this deal type, customers can get a free product after buying one product of equal or lesser value.</p>
                <ul>
                    <li><strong>Buy 1 Get 1 Free:</strong> Purchase 1 product, and get 1 free.</li>
                    <li><strong>Buy 2 Get 1 Free:</strong> Purchase 2 products, and get 1 free.</li>
                    <li><strong>Buy 5 Get 2 Free:</strong> Purchase 5 products, and get 2 free.</li>
                </ul>

                <h5><strong>Combo Deals</strong></h5>
                <p>This deal type allows customers to purchase multiple products in a package at a discounted price.</p>
                <ul>
                    <li><strong>Buy 1 XYZ product + 1 ABC product for 200 rupees:</strong> Purchase both products together at a special price.</li>
                    <li><strong>Buy 3 ABC products for 150 rupees:</strong> Purchase 3 ABC products at a discounted price.</li>
                </ul>

                <h5><strong>Discount</strong></h5>
                <p>Discount deals allow customers to get a certain percentage or fixed amount off the original price of the product.</p>
                <ul>
                    <li><strong>10% discount on 1000 rs cart total:</strong> Get 10% off when your total cart value reaches 1000 rs.</li>
                    <li><strong>200 rs discount on 2000 rs cart total:</strong> Get a 200 rs discount when your total cart value reaches 2000 rs.</li>
                </ul>

                <h5><strong>Flat</strong></h5>
                <p>Flat discount deals provide a fixed amount or percentage discount on a specific product or category.</p>
                <ul>
                    <li><strong>10% flat discount on ABC product:</strong> A 10% flat discount on the price of 1 ABC product, making it cost 450 rs (originally 500 rs).</li>
                    <li><strong>100 rs flat off on XYZ product:</strong> Get 100 rs off the price of 1 XYZ product, making it cost 400 rs (originally 500 rs).</li>
                </ul>
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



        // // Open the modal when the info icon is clicked
        // $('.fas.fa-info-circle').on('click', function() {
        //     $('#dealTypesModal').modal('show');
        // });


    });
</script>
@endsection