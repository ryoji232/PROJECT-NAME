@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
@php $librarian = Auth::guard('librarian')->user(); @endphp

<div class="row justify-content-center">
<div class="col-md-8">
<div class="card shadow-sm">
    <div class="card-header bg-warning">⚙️ Account Settings</div>
    <div class="card-body">

        @if(!$librarian)
            <div class="alert alert-warning text-center">
                <h5>Not Authenticated</h5>
                <p>Please log in to access account settings.</p>
                <a href="{{ route('librarian.login') }}" class="btn btn-primary">Login</a>
            </div>
        @else

            {{-- Update Profile --}}
            <h5 class="mb-3">Update Profile Information</h5>
            @if(session('profile_success'))
                <div class="alert alert-success">{{ session('profile_success') }}</div>
            @endif
            <form action="{{ route('librarian.update.profile') }}" method="POST" class="mb-4">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="{{ $librarian->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="{{ $librarian->email }}" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>

            <hr>

            {{-- Change Password --}}
            <h5 class="mb-3">Change Password</h5>
            @if(session('password_success'))
                <div class="alert alert-success">{{ session('password_success') }}</div>
            @endif
            @include('partials.alerts')
            <form action="{{ route('librarian.update.password') }}" method="POST">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="new_password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-warning">Change Password</button>
            </form>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="{{ route('librarian.profile') }}" class="btn btn-secondary">← Profile</a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">← Dashboard</a>
            </div>

        @endif

    </div>
</div>
</div>
</div>
@endsection