profile.blade (1).php
@extends('app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">👤 Librarian Profile</h4>
                </div>
                <div class="card-body">
                    @php
                        $librarian = Auth::guard('librarian')->user();
                    @endphp
                    
                    @if($librarian)
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <div class="profile-icon-large mx-auto mb-3">
                                    👤 Profile
                                </div>
                                <h5>{{ $librarian->name }}</h5>
                                <p class="text-muted">Librarian</p>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label"><strong>Full Name:</strong></label>
                                        <p class="form-control-plaintext">{{ $librarian->name }}</p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label"><strong>Email Address:</strong></label>
                                        <p class="form-control-plaintext">{{ $librarian->email }}</p>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label"><strong>Account Created:</strong></label>
                                        <p class="form-control-plaintext">{{ $librarian->created_at->format('F d, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                           
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                ← Back to Dashboard
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="alert alert-warning">
                                <h5>Not Authenticated</h5>
                                <p>Please log in to view your profile.</p>
                                <a href="{{ route('librarian.login') }}" class="btn btn-primary">Login</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.profile-icon-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.5rem;
    border: 4px solid rgba(102, 126, 234, 0.2);
}

.card {
    border-radius: 1rem;
    border: none;
}

.form-control-plaintext {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}
</style>
@endsection