@extends('app')

@section('title', 'Account Settings')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">⚙️ Account Settings</h4>
                </div>
                <div class="card-body">
                    @php
                        $librarian = Auth::guard('librarian')->user();
                    @endphp
                    
                    @if($librarian)
                        <!-- Update Profile Information -->
                        <form action="{{ route('librarian.update.profile') }}" method="POST" class="mb-4">
                            @csrf
                            @method('PUT')
                            
                            <h5 class="mb-3">Update Profile Information</h5>
                            
                            @if(session('profile_success'))
                                <div class="alert alert-success">
                                    {{ session('profile_success') }}
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ $librarian->name }}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ $librarian->email }}" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                        
                        <hr>
                        
                        <!-- Change Password -->
                        <form action="{{ route('librarian.update.password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <h5 class="mb-3">Change Password</h5>
                            
                            @if(session('password_success'))
                                <div class="alert alert-success">
                                    {{ session('password_success') }}
                                </div>
                            @endif
                            
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    @foreach($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                </div>
                            @endif
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="new_password_confirmation" 
                                       name="new_password_confirmation" required>
                            </div>
                            
                            <button type="submit" class="btn btn-warning">Change Password</button>
                        </form>
                        
                        <hr>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="{{ route('librarian.profile') }}" class="btn btn-secondary">
                                ← Back to Profile
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                                ← Back to Dashboard
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="alert alert-warning">
                                <h5>Not Authenticated</h5>
                                <p>Please log in to access account settings.</p>
                                <a href="{{ route('librarian.login') }}" class="btn btn-primary">Login</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
    <div id="passwordSuccessToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> Your password has been updated successfully.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 1rem;
    border: none;
}

.form-control {
    border-radius: 0.5rem;
}

.alert {
    border-radius: 0.5rem;
    border: none;
}

/* Toast customization */
.toast {
    border-radius: 0.75rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast-body {
    font-size: 0.95rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if password was successfully updated
    @if(session('password_success'))
        const passwordToast = new bootstrap.Toast(document.getElementById('passwordSuccessToast'));
        passwordToast.show();
    @endif
    
    // Optional: Auto-hide toast after 5 seconds
    const toastElement = document.getElementById('passwordSuccessToast');
    if (toastElement) {
        toastElement.addEventListener('shown.bs.toast', function () {
            setTimeout(() => {
                const toast = bootstrap.Toast.getInstance(toastElement);
                toast.hide();
            }, 5000);
        });
    }
});
</script>

<!-- Add Font Awesome for the check icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

@endsection
