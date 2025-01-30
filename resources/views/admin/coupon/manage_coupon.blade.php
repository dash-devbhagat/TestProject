@extends('layouts.master')

@section('title', 'Coupon Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Coupons</h1>
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

            <form action="{{ route('coupon.store') }}" method="POST" enctype="multipart/form-data"
                class="form-horizontal mt-4">
                @csrf

                <div class="form-group row">
                    <div class="col-sm-4">
                        <label for="coupon_name">Coupon Name</label>
                        <input type="text" class="form-control @error('coupon_name') is-invalid @enderror"
                            name="coupon_name" id="coupon_name" placeholder="Enter Coupon Name">
                        @error('coupon_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="coupon_amount">Coupon Amount</label>
                        <input type="text" class="form-control @error('coupon_amount') is-invalid @enderror"
                            name="coupon_amount" id="coupon_amount" placeholder="Enter Coupon Amount">
                        @error('coupon_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="coupon_image">Coupon Image</label>
                        <input type="file" class="form-control @error('coupon_image') is-invalid @enderror"
                            name="coupon_image" id="coupon_image">
                        @error('coupon_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-sm-12 mt-2">
                        <label for="coupon_details">Coupon Details</label>
                        <input type="text" class="form-control @error('coupon_details') is-invalid @enderror"
                            name="coupon_details" id="coupon_details" placeholder="Enter Coupon Details">
                        @error('coupon_details')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right">Save</button>
                </div>
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
                            <th>Coupon Name</th>
                            <th>Coupon Amount</th>
                            <th>Coupon Code</th>
                            <th>Coupon Details</th>
                            <th>Coupon Image</th>
                            <th>Active Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $i = 1;
                        @endphp
                        @foreach ($coupons as $coupon)
                        <tr>
                            <td>{{ $i }}</td>
                            <td>{{ $coupon->name }}</td>
                            <td>{{ $coupon->amount }}</td>
                            <td>{{ $coupon->coupon_code }}</td>
                            <td>{{ $coupon->description ?? 'No Details Available' }}</td>
                            <td class="text-center">
                                @if ($coupon->image)
                                <img src="{{ asset('storage/' . $coupon->image) }}" alt="Coupon Image"
                                    width="80" height="50">
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="text-center">
                                <!-- Active/Inactive Toggle Icon -->
                                <a href="javascript:void(0);" id="toggleStatusBtn{{ $coupon->id }}"
                                    data-id="{{ $coupon->id }}" class="text-center" data-toggle="tooltip"
                                    title="{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i
                                        class="fas {{ $coupon->is_active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-muted' }} fa-2x"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <!-- Edit Icon -->
                                <a href="#javascript" class="text-primary" data-toggle="modal"
                                    data-target="#exampleModal" data-bs-toggle="tooltip" title="Edit">
                                    <i class="fa fa-edit editCouponBtn" data-id="{{ $coupon->id }}"></i>
                                </a>
                                <!-- Delete Icon -->
                                <form action="{{ route('coupon.destroy', $coupon->id) }}" method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this coupon?');">
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

<!-- Edit Coupon Modal -->
<div class="modal fade" id="editCouponModal" tabindex="-1" role="dialog" aria-labelledby="editCouponModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCouponModalLabel">Edit Coupon</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editCouponForm" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editCouponId" name="id">
                    <div class="form-group">
                        <label for="editCouponName">Coupon Name</label>
                        <input type="text" class="form-control" id="editCouponName" name="coupon_name"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="editCouponAmount">Coupon Amount</label>
                        <input type="number" class="form-control" id="editCouponAmount" name="coupon_amount" required>
                    </div>
                    <div class="form-group">
                        <label for="editCouponDetails">Coupon Details</label>
                        <textarea class="form-control" id="editCouponDetails" name="coupon_details"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editCouponImage">Coupon Image</label>
                        <input type="file" class="form-control" id="editCouponImage" name="coupon_image"
                            accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="updateCouponBtn" class="btn btn-primary">Save changes</button>
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
            var productId = $(this).data('id');

            $.ajax({
                url: '/coupon/' + productId +
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






    });
</script>
@endsection