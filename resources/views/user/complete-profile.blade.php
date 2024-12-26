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
          <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
              <label for="phone">Phone</label>
              <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', Auth::user()->phone) }}">
              @error('phone')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="storename">Store Name</label>
              <input type="text" name="storename" id="storename" class="form-control" value="{{ old('storename', Auth::user()->storename) }}">
              @error('storename')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="location">Location</label>
              <input type="text" name="location" id="location" class="form-control" value="{{ old('location', Auth::user()->location) }}">
              @error('location')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="latitude">Latitude</label>
              <input type="text" name="latitude" id="latitude" class="form-control" value="{{ old('latitude', Auth::user()->latitude) }}">
              @error('latitude')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="longitude">Longitude</label>
              <input type="text" name="longitude" id="longitude" class="form-control" value="{{ old('longitude', Auth::user()->longitude) }}">
              @error('longitude')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-group">
              <label for="logo">Logo (100x100 pixels max)</label>
              @if(Auth::user()->logo)
        <div>
            <img src="{{ Storage::url(Auth::user()->logo) }}" alt="Current Logo" style="width: 100px; height: 100px;">
        </div>
    @else
        <!-- If no logo is available, show a message prompting to upload an image -->
        <div class="text-warning">Please upload an image.</div>
    @endif
              <input type="file" name="logo" id="logo" class="form-control">
              @error('logo')
                <div class="text-danger">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
          </form>
        </div>
        <div class="card-footer">
          <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger">Logout</button>
          </form>
        </div>
        
      </div>
    </section>
  </div>
@endsection
