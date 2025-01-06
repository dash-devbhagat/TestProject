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
                        <a href="{{ url()->previous() }}" class="btn btn-secondary text-light">
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
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Product SKU</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->sku }}</td>
                                        @forelse($product->productVarients as $variant)
                                            <td>{{ $variant->unit }}</td>
                                            <td>{{ $variant->price }}</td>
                                        @empty
                                            <td colspan="3">No variants available</td>
                                        @endforelse
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
