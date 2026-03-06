<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Librarian Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('{{ asset('images/ieti-bg1.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            overflow: hidden;
        }

        .right-section {
            position: absolute;
            bottom: 8%;
            right: 6%; 
            width: 900px;
            background: #006400;
            color: #fff;
            padding: 170px 150px;
            border-radius: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
        }

        .login-card {
            width: 100%;
            text-align: center;
        }

        h1 {
            margin-bottom: 1.9rem;
            font-weight: 800;
            color: #f8f9fa;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
        }

        .btn-login {
            background-color: #fff000;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            padding: 12px 0;
        }

        .btn-login:hover {
            background-color: #cfee43ff;
            transform: scale(1.03);
        }

        .btn-login:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .attempts-warning {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .lockout-message {
            background-color: #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .lockout-timer {
            font-size: 1.2rem;
            font-weight: bold;
            color: #dc3545;
        }

        @media (max-width: 992px) {
            .right-section {
                position: static;
                width: 90%;
                margin: 50px auto;
                background: rgba(237, 255, 80, 0.9);
            }
        }
    </style>
</head>
<body>
    <div class="right-section">
        <div class="login-card">
            <h1>LIBRARIAN LOGIN</h1>
            
            <!-- Display login attempt warnings -->
            @if(session('attempts_remaining') && !session('account_locked'))
                <div class="attempts-warning">
                    ⚠️ Warning: {{ session('attempts_remaining') }} attempt(s) remaining before lockout
                </div>
            @endif

            @if(session('account_locked'))
                <div class="lockout-message">
                    🔒 Account temporarily locked due to multiple failed login attempts.
                    <br>
                    Please try again in <span id="lockoutTimer" class="lockout-timer">{{ session('lockout_time') }}:00</span>
                </div>
            @endif

            <!-- Display general errors -->
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('librarian.login.submit') }}" id="loginForm">
                @csrf
                <div class="mb-3 text-start">
                    <label for="email" class="form-label">EMAIL</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus
                           @if(session('account_locked')) disabled @endif>
                </div>
                <div class="mb-3 text-start">
                    <label for="password" class="form-label">PASSWORD</label>
                    <input type="password" class="form-control" name="password" required
                           @if(session('account_locked')) disabled @endif>
                </div>
                <div class="mb-3 form-check text-start">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember"
                           @if(session('account_locked')) disabled @endif>
                    <label class="form-check-label" for="remember">REMEMBER ME</label>
                </div>
                <button type="submit" class="btn btn-login w-50" id="loginButton"
                        @if(session('account_locked')) disabled @endif>
                    @if(session('account_locked'))
                        LOCKED
                    @else
                        LOGIN
                    @endif
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const lockoutTimer = document.getElementById('lockoutTimer');
            
            // If account is locked, show countdown timer
            @if(session('account_locked') && session('lockout_seconds'))
                let lockoutTime = {{ session('lockout_seconds') }};
                
                function updateLockoutTimer() {
                    const minutes = Math.floor(lockoutTime / 60);
                    const seconds = lockoutTime % 60;
                    
                    if (lockoutTimer) {
                        lockoutTimer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    }
                    
                    if (lockoutTime > 0) {
                        lockoutTime--;
                        setTimeout(updateLockoutTimer, 1000);
                    } else {
                        // Reload the page when lockout expires
                        location.reload();
                    }
                }
                
                updateLockoutTimer();
            @endif

            // Add throttling to prevent rapid submissions
            let canSubmit = true;
            loginForm.addEventListener('submit', function(e) {
                if (!canSubmit) {
                    e.preventDefault();
                    return;
                }
                
                // Show loading state
                if (loginButton && !loginButton.disabled) {
                    loginButton.innerHTML = 'LOGGING IN...';
                    loginButton.disabled = true;
                }
                
                canSubmit = false;
                setTimeout(() => {
                    canSubmit = true;
                }, 2000); // 2 second delay between submissions
            });
        });
    </script>
</body>
</html>
