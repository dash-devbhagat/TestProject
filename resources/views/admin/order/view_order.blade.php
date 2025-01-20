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
                                            <span class="fw-bold">Order Date:</span> {{ $order->created_at->format('d-m-Y') }}
                                        </span>                                        
                                        <span class="me-3"><span class="fw-bold">Order SKU:</span> {{ $order->order_number }}</span>
                                        {{-- <span class="badge rounded-pill bg-info">{{ ucfirst($order->status) }}</span> --}}
                                    </div>

                                    <div>
                                        <span id="statusText"><span class="fw-bold">Order Status:</span> {{ ucfirst($order->status) }}</span>
                                        <select id="statusDropdown" class="form-select d-none"
                                            style="width: auto; display: inline;">
                                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="in progress"
                                                {{ $order->status == 'in progress' ? 'selected' : '' }}>In Progress
                                            </option>
                                            <option value="delivered"
                                                {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                            <option accesskey="" value="cancelled"
                                                {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        <i id="editIcon" class="fas fa-edit text-primary ml-2" style="cursor: pointer;"
                                            title="Edit Status"></i>
                                        <i id="saveIcon" class="fas fa-check text-success ml-2 d-none"
                                            style="cursor: pointer;" title="Save Status"></i>
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
                                                        <img src="{{ asset('storage/' . $item->product->image) }}" alt="Product Image" width="80">
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
                                                <td>₹{{ number_format($item->productVariant->price * $item->quantity, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="7" class="text-end fw-bold">Subtotal</td>
                                            <td class="text-end fw-bold">₹{{ number_format($order->sub_total, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="7" class="text-end">Charges</td>
                                            <td class="text-end">₹{{ number_format($order->charges_total, 2) }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <td colspan="7" class="text-end">Discount (Code: NEWYEAR)</td>
                                            <td class="text-danger text-end">-₹10.00</td>
                                        </tr> --}}
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
                            statusText.textContent = `Order Status: ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`; // Updated line
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
