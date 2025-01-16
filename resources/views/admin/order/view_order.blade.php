@extends('layouts.master')

@section('title', 'Order Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <!-- Heading on the left -->
                    <div class="col-sm-6">
                        <h1>Order Details</h1>
                    </div>
                    <!-- Back button on the right -->
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('order.index') }}" class="btn btn-secondary text-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Default box -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Order Details Table -->
                        <div class="col-md-12">
                            <h3 class="text-primary">Order Details - Order #{{ $order->id }}</h3>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span id="statusText">{{ ucfirst($order->status) }}</span>
                                            <select id="statusDropdown" class="form-select d-none"
                                                style="width: auto; display: inline;">
                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>
                                                    Pending</option>
                                                <option value="in progress"
                                                    {{ $order->status == 'in progress' ? 'selected' : '' }}>In Progress
                                                </option>
                                                <option value="delivered"
                                                    {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="cancelled"
                                                    {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                            <i id="editIcon" class="fas fa-edit text-primary ml-2" style="cursor: pointer;"
                                                title="Edit Status"></i>
                                            <i id="saveIcon" class="fas fa-check text-success ml-2 d-none"
                                                style="cursor: pointer;" title="Save Status"></i>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Order SKU</th>
                                        <td>{{ $order->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>User Name</th>
                                        <td>{{ $order->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>User Email</th>
                                        <td>{{ $order->user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>User Phone</th>
                                        <td>{{ $order->user->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td>{{ $order->address->address_line }}</td>
                                    </tr>
                                    <tr>
                                        <th>City</th>
                                        <td>{{ $order->address->city->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>State</th>
                                        <td>{{ $order->address->state->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Zip Code</th>
                                        <td>{{ $order->address->zip_code }}</td>
                                    </tr>
                                    <tr>
                                        <th>Order Items</th>
                                        <td>
                                            @forelse ($order->items as $item)
                                                <div class="mb-3">
                                                    <strong>Product Name:</strong> {{ $item->product->name }} <br>
                                                    <hr>
                                                    <strong>Product SKU:</strong> {{ $item->product->sku }} <br>
                                                    <hr>
                                                    <strong>Product Details:</strong>
                                                    {{ $item->product->details ?? 'No details available' }} <br>
                                                    <hr>
                                                    <strong>Product Category:</strong>
                                                    {{ $item->product->category->name ?? 'Not Available' }} <br>
                                                    <hr>
                                                    <strong>Product SubCategory:</strong>
                                                    {{ $item->product->subCategory->name ?? 'Not Available' }} <br>
                                                    <hr>
                                                    <strong>Variant Unit:</strong>
                                                    {{ $item->productVariant->unit ?? 'No Variant Available' }} <br>
                                                    <hr>
                                                    <strong>Variant Price:</strong>
                                                    ${{ number_format($item->productVariant->price, 2) }} <br>
                                                    <hr>
                                                    <strong>Quantity:</strong> {{ $item->quantity }}
                                                </div>
                                                @if (!$loop->last)
                                                    <hr> <!-- Horizontal line between items -->
                                                @endif
                                            @empty
                                                <p>No Items Available</p>
                                            @endforelse
                                        </td>
                                    </tr>


                                    <tr>
                                        <th>Subtotal</th>
                                        <td>${{ number_format($order->sub_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Charges</th>
                                        <td>${{ number_format($order->charges_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Grand Total</th>
                                        <td>${{ number_format($order->grand_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Transaction Number</th>
                                        <td>{{ $order->transactions->first()->transaction_number ?? 'N/A' }}</td>
                                    </tr>                                    
                                    <tr>
                                        <th>Transaction Status</th>
                                        <td>{{ ucfirst($order->transaction_status) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                    {{-- Optionally, add a back button or any other actions --}}
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
@endsection

@section('scripts')
    {{-- script to update the status --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editIcon = document.getElementById('editIcon');
            const saveIcon = document.getElementById('saveIcon');
            const statusText = document.getElementById('statusText');
            const statusDropdown = document.getElementById('statusDropdown');

            // Show dropdown on edit icon click
            editIcon.addEventListener('click', function() {
                statusText.classList.add('d-none');
                editIcon.classList.add('d-none');
                statusDropdown.classList.remove('d-none');
                saveIcon.classList.remove('d-none');
            });

            // Save status on save icon click
            saveIcon.addEventListener('click', function() {
                const newStatus = statusDropdown.value;

                // AJAX request to update the status
                fetch(`/order/update-status/{{ $order->id }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the UI
                            statusText.textContent = newStatus.charAt(0).toUpperCase() + newStatus
                                .slice(1);
                            statusText.classList.remove('d-none');
                            editIcon.classList.remove('d-none');
                            statusDropdown.classList.add('d-none');
                            saveIcon.classList.add('d-none');
                        } else {
                            alert('Failed to update status');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
@endsection
