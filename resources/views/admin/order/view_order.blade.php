@extends('layouts.master')

@section('title', 'Order Details')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid">

        <div class="container">
            <!-- Title -->
            <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                <h3 class="text-primary mb-0">Order Details - Order #{{ $order->id }}</h3>
                <a href="{{ route('order.index') }}" class="btn btn-secondary text-light">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <!-- Main content -->
            <div class="row">
                <div class="col-lg-8">
                    <!-- Details -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="mb-3 d-flex justify-content-between">
                                <div>
                                    <span class="me-3">
                                        <span class="fw-bold">Order Date:</span>
                                        {{ $order->created_at->format('d-m-Y') }}
                                    </span>
                                    <span class="me-3"><span class="fw-bold">Order SKU:</span>
                                        {{ $order->order_number }}</span>
                                </div>

                                <div>
                                    <span id="statusText">
                                        <span class="fw-bold">Order Status:</span> {{ ucfirst($order->status) }}
                                    </span>
                                    <a id="changeStatusBtn" class="ml-2"><i class="fa fa-edit editStatusIcon"></i></a>
                                </div>


                            </div>
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center" style="width: 120px;">Product Image</th>
                                        <th style="width: 150px;">Product SKU</th>
                                        <th style="width: 150px;">Product Category</th>
                                        <th style="width: 150px;">Product Subcategory</th>
                                        <th style="width: 120px;">Product Variant</th>
                                        <th style="width: 120px;">Variant Price</th>
                                        <th style="width: 120px;">Product Quantity</th>
                                        <th style="width: 150px;">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                    <tr>
                                        <td class="text-center">
                                            @if ($item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}"
                                                alt="Product Image" width="80">
                                            @else
                                            N/A
                                            @endif
                                        </td>
                                        <td>{{ $item->product->sku }}</td>
                                        <td>{{ $item->product->category->name ?? 'Not Available' }}</td>
                                        <td>{{ $item->product->subCategory->name ?? 'Not Available' }}</td>
                                        <td>{{ $item->productVariant->unit ?? 'No Variant Available' }}</td>
                                        <td>₹{{ number_format($item->productVariant->price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>₹{{ number_format($item->productVariant->price * $item->quantity, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Subtotal</td>
                                        <td class="text-end">₹{{ number_format($order->sub_total, 2) }}</td>
                                    </tr>
                                    @foreach($charges as $charge)
                                    <tr>
                                        <td colspan="7" class="text-end">{{ $charge->name }}</td>
                                        <td class="text-end">
                                            @if ($charge->type === 'percentage')
                                            {{ $charge->value }}%
                                            @else
                                            ₹{{ $charge->value }}
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Total Charges</td>
                                        <td class="text-end">₹{{ number_format($order->charges_total, 2) }}</td>
                                    </tr>
                                    <!-- <tr>
                                            <td colspan="7" class="text-end">Discount (Code: NEWYEAR)</td>
                                            <td class="text-danger text-end">-₹10.00</td>
                                    </tr>  -->
                                    <tr class="fw-bold">
                                        <td colspan="7" class="text-end">Grand Total</td>
                                        <td class="text-end">₹{{ number_format($order->grand_total, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>
                    </div>

                </div>
                <div class="col-lg-4">
                    <!-- Customer Notes -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h3 class="h6">Transaction Details</h3>
                            @foreach ($order->transactions as $transaction)
                            <p>Transaction Number: #{{ $transaction->transaction_number }} <br> Transaction
                                Date: {{ $transaction->created_at->format('d-m-Y') }} <br>Transaction Mode:
                                {{ ucfirst($transaction->payment_mode) }} <br>Transaction Type:
                                {{ ucfirst($transaction->payment_type) }}<br> Transaction Status: <span
                                    class="badge bg-success rounded-pill">{{ ucfirst($transaction->payment_status) }}</span>
                            </p>
                            @endforeach
                        </div>
                    </div>
                    <div class="card mb-4">
                        <!-- Shipping information -->
                        <div class="card-body">
                            <h3 class="h6">User Details</h3>
                            <address>
                                <strong>{{ $order->user->name }}</strong><br>
                                {{ $order->address->address_line }},
                                {{ $order->address->city->name }}, {{ $order->address->state->name }} -
                                {{ $order->address->zip_code }}.<br>
                                {{ $order->user->phone }}<br>
                                {{ $order->user->email }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for status change -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="newStatus" class="form-label">Select Status</label>
                <select id="newStatus" class="form-select">
                    <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in progress" {{ $order->status == 'in progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="saveStatusBtn" class="btn btn-success">Save</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const changeStatusBtn = document.getElementById('changeStatusBtn');
        const saveStatusBtn = document.getElementById('saveStatusBtn');
        const newStatus = document.getElementById('newStatus');
        const statusText = document.getElementById('statusText');
        const orderId = "{{ $order->id }}"; // Get order ID dynamically

        // Open modal when clicking "Change Status"
        changeStatusBtn.addEventListener('click', function() {
            const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            statusModal.show();
        });

        // Save new status
        $('#saveStatusBtn').on('click', function() {
            // Collect form data
            const formData = {
                id: "{{ $order->id }}", // Get Order ID dynamically
                status: $('#newStatus').val(),
                _token: $('input[name="_token"]').val(), // CSRF Token
            };

            // Perform AJAX Request
            $.ajax({
                url: '/order/updatestatus/' + formData.id, // Route to update status
                type: "POST", // Laravel doesn't support PUT via AJAX by default
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Update status text
                        $('#statusText').html(
                            `<span class="fw-bold">Order Status:</span> ${formData.status.charAt(0).toUpperCase() + formData.status.slice(1)}`
                        );

                        // Close the modal
                        $('#statusModal').modal('hide');

                        // Optionally, reload the page or dynamically update the list
                        location.reload();
                    } else {
                        alert('Failed to update order status. Please try again.');
                    }
                },
                error: function(xhr) {
                    // Handle errors
                    alert('Error updating order status. Please try again.');
                }
            });
        });
    });
</script>

@endsection