<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Login — IETI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="login-panel">
    <h1>LIBRARIAN LOGIN</h1>

    @if(session('attempts_remaining') && !session('account_locked'))
        <div class="attempts-warning">
            ⚠️ Warning: {{ session('attempts_remaining') }} attempt(s) remaining before lockout
        </div>
    @endif

    @if(session('account_locked'))
        <div class="lockout-message">
            🔒 Account temporarily locked.<br>
            Try again in <span id="lockoutTimer" class="lockout-timer">{{ session('lockout_time') }}:00</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error){{ $error }}<br>@endforeach
        </div>
    @endif

    @php $locked = session('account_locked'); @endphp

    <form method="POST" action="{{ route('librarian.login.submit') }}" id="loginForm">
        @csrf
        <div class="mb-3 text-start">
            <label class="form-label">EMAIL</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                   required autofocus {{ $locked ? 'disabled' : '' }}>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label">PASSWORD</label>
            <input type="password" class="form-control" name="password"
                   required {{ $locked ? 'disabled' : '' }}>
        </div>
        <div class="mb-3 form-check text-start">
            <input type="checkbox" class="form-check-input" name="remember" id="remember"
                   {{ $locked ? 'disabled' : '' }}>
            <label class="form-check-label" for="remember">REMEMBER ME</label>
        </div>
        <button type="submit" class="btn btn-login" id="loginButton" {{ $locked ? 'disabled' : '' }}>
            {{ $locked ? 'LOCKED' : 'LOGIN' }}
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@if(session('account_locked') && session('lockout_seconds'))
    <script>window.lockoutSeconds = {{ session('lockout_seconds') }};</script>
@endif
<script src="{{ asset('js/login.js') }}"></script>
</body>
</html>