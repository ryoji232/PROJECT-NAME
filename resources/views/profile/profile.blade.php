@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
@php $librarian = Auth::guard('librarian')->user(); @endphp

<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">👤 Librarian Profile</div>
    <div class="card-body">

        @if(!$librarian)
            <div class="alert alert-warning text-center">
                <h5>Not Authenticated</h5>
                <p>Please log in to view your profile.</p>
                <a href="{{ route('librarian.login') }}" class="btn btn-primary">Login</a>
            </div>
        @else

            <div class="text-center mb-4">
                <div style="font-size:4rem;">👤</div>
                <h4 class="fw-bold mt-2">{{ $librarian->name }}</h4>
                <p class="text-muted mb-0">{{ $librarian->email }}</p>
                <span class="badge bg-success mt-1">Librarian</span>
            </div>

            <hr>

            <table class="table table-borderless mb-0">
                <tr>
                    <th class="text-muted" style="width:40%;">Full Name</th>
                    <td>{{ $librarian->name }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Email</th>
                    <td>{{ $librarian->email }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Member Since</th>
                    <td>{{ $librarian->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                </tr>
            </table>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="{{ route('librarian.account') }}" class="btn btn-warning">⚙️ Account Settings</a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">← Dashboard</a>
            </div>

        @endif

    </div>
</div>
</div>
</div>

@endsection