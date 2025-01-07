@extends('layouts.master')

@section('title', 'Product Management')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <!-- Heading on the left -->
                    <div class="col-sm-6">
                        <h1>Product Details</h1>
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

        <!-- Main content -->
        <section class="content">
            <!-- Default box -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <!-- Product Details Table -->
                        <div class="col-md-12">
                            <h3 class="text-primary">Product Details</h3>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>Product Name</th>
                                        <td>{{ $product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product SKU</th>
                                        <td>{{ $product->sku }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Details</th>
                                        <td>{{ $product->details ?? 'No Details Available' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product Variants</th>
                                        <td>
                                            @forelse ($product->productVarients as $variant)
                                                <div class="mb-2">
                                                    <strong>Unit:</strong> {{ $variant->unit }} <br>
                                                    <strong>Price:</strong> {{ $variant->price }}
                                                </div>
                                                @if (!$loop->last)
                                                    <hr> <!-- Add horizontal line between variants -->
                                                @endif
                                            @empty
                                                <p>No Variants Available</p>
                                            @endforelse
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Product Image</th>
                                        <td>
                                            @if ($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" alt="Category Image"
                                                    width="200" height="200">
                                            @else
                                                No Image Available
                                            @endif
                                        </td>
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
