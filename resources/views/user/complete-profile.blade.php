<!-- resources/views/complete-profile.blade.php -->
@extends('layouts.master')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Complete Your Profile</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Profile Completion</h3>
        </div>
        <div class="card-body">
          <!-- Display errors if any -->
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label for="phone">Phone</label>
              <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', Auth::user()->phone) }}">
            </div>
            <div class="form-group">
              <label for="storename">Store Name</label>
              <input type="text" name="storename" id="storename" class="form-control" value="{{ old('storename', Auth::user()->storename) }}">
            </div>
            <div class="form-group">
              <label for="location">Location</label>
              <input type="text" name="location" id="location" class="form-control" value="{{ old('location', Auth::user()->location) }}">
            </div>
            <div class="form-group">
              <label for="latitude">Latitude</label>
              <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', Auth::user()->latitude) }}">
            </div>
            <div class="form-group">
              <label for="longitude">Longitude</label>
              <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', Auth::user()->longitude) }}">
            </div>
            <div class="form-group">
              <label for="logo">Logo (100x100 pixels max)</label>
              <input type="file" name="logo" id="logo" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
          </form>
        </div>
      </div>
    </section>
  </div>
@endsection
