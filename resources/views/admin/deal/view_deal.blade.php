@extends('layouts.master')

@section('title', 'Deal Management')

@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <!-- Heading on the left -->
                <div class="col-sm-6">
                    <h1>Deal Details - {{ $deal->type }}</h1>
                </div>
                <!-- Back button on the right -->
                <div class="col-sm-6 text-right">
                    <a href="{{ route('deal.index') }}" class="btn btn-secondary text-light">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="text-primary">Deal Details</h3>
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $deal->title }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Description</th>
                                    <td style="word-wrap: break-word; max-width: 500px;">
                                        {{ $deal->description ?? 'No Description Available' }}
                    </div>
                    </td>
                    </tr>

                    <tr>
                        <th>Start Date</th>
                        <td>{{ $deal->start_date }}</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>{{ $deal->end_date }}</td>
                    </tr>
                    <tr>
                        <th>Renewal Time</th>
                        <td>{{ $deal->renewal_time ?? 'Not Applicable' }} Day(s)</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>{{ $deal->type }}</td>
                    </tr>
                    <tr>
                        <th>Deal Image</th>
                        <td>
                            @if ($deal->image)
                            <img src="{{ asset('storage/' . $deal->image) }}" alt="Deal Image" width="200" height="200" onerror="this.onerror=null; this.src='{{ asset('adminlte/dist/img/inf.png') }}';">
                            @else
                            No Image Available
                            @endif
                        </td>
                    </tr>

                    @if ($deal->type == 'BOGO')
                    <tr>
                        <th>Buy Product</th>
                        <td>{{ $deal->buyProduct->name ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Buy Product Variant</th>
                        <td>{{ $deal->buyProductVariant->unit ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Buy Quantity</th>
                        <td>{{ $deal->buy_quantity }}</td>
                    </tr>
                    <tr>
                        <th>Get Product</th>
                        <td>{{ $deal->getProduct->name ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Get Product Variant</th>
                        <td>{{ $deal->getProductVariant->unit ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Get Quantity</th>
                        <td>{{ $deal->get_quantity }}</td>
                    </tr>
                    @elseif ($deal->type == 'Combo')
                    <tr>
                        <th>Combo Products</th>
                        <td>
                            @foreach ($deal->dealComboProducts as $comboProduct)
                            <div class="mb-2">
                                <strong>{{ $comboProduct->product->name ?? 'No Name' }}({{ $comboProduct->variant->unit }})</strong> - Quantity: {{ $comboProduct->quantity ?? '0' }}
                            </div>
                            @if (!$loop->last)
                            <hr>
                            @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>Total Actual Amount</th>
                        <td>{{ $deal->actual_amount }}</td>
                    </tr>
                    <tr>
                        <th>Combo Discount Amount</th>
                        <td>{{ $deal->combo_discounted_amount }}</td>
                    </tr>
                    @elseif ($deal->type == 'Discount')
                    <tr>
                        <th>Minimum Cart Amount</th>
                        <td>{{ $deal->min_cart_amount }}</td>
                    </tr>
                    <tr>
                        <th>Discount Type</th>
                        <td>{{ ucfirst($deal->discount_type) }}</td>
                    </tr>
                    <tr>
                        <th>Discount Value</th>
                        <td>
                            {{ $deal->discount_amount }}
                            @if($deal->discount_type === 'percentage')
                            %
                            @elseif($deal->discount_type === 'fixed')
                            ₹
                            @endif
                        </td>
                    </tr>
                    @elseif ($deal->type == 'Flat')
                    <tr>
                        <th>Product</th>
                        <td>{{ $deal->buyProduct->name ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Product Variant</th>
                        <td>{{ $deal->buyProductVariant->unit ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Quantity</th>
                        <td>{{ $deal->buy_quantity ?? 'Not Available' }}</td>
                    </tr>
                    <tr>
                        <th>Discount Type</th>
                        <td>{{ ucfirst($deal->discount_type) }}</td>
                    </tr>
                    <tr>
                        <th>Discount Value</th>
                        <td>
                            {{ $deal->discount_amount }}
                            @if($deal->discount_type === 'percentage')
                            %
                            @elseif($deal->discount_type === 'fixed')
                            ₹
                            @endif
                        </td>
                    </tr>
                    @endif

                    </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card-footer">
            {{-- Optionally, add more actions if required --}}
        </div>
</div>
</section>
</div>
@endsection