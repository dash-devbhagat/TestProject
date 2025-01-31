@extends('layouts.master')

@section('title',' Bonus History')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Bonus History</h1>
                    </div>
                    <!-- Back button on the right -->
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('bonus.index') }}" class="btn btn-secondary text-light">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bonus History</h3>
                    </div>
                    <div class="card-body">
                        <table id="usersTable" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th>Bonus Name</th>
                                    <th>Bonus Amount</th>
                                    <th>Bonus Percentage</th>
                                    <th>Bonus Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 1;
                                @endphp
                                @forelse ($bonuses as $bonus)
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $bonus->type }}</td>
                                        <td>{{ $bonus->amount }}</td>   
                                        <td>{{ $bonus->percentage }}</td>   
                                        <td>{{ $bonus->created_at->format('d-m-Y') }}</td> 
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No orders found.</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
