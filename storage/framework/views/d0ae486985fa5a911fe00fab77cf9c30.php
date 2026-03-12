<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Login — IETI Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>">
</head>
<body>

<div class="login-panel">
    <h1>LIBRARIAN LOGIN</h1>

    <?php if(session('attempts_remaining') && !session('account_locked')): ?>
        <div class="attempts-warning">
            ⚠️ Warning: <?php echo e(session('attempts_remaining')); ?> attempt(s) remaining before lockout
        </div>
    <?php endif; ?>

    <?php if(session('account_locked')): ?>
        <div class="lockout-message">
            🔒 Account temporarily locked.<br>
            Try again in <span id="lockoutTimer" class="lockout-timer"><?php echo e(session('lockout_time')); ?>:00</span>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($error); ?><br><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php $locked = session('account_locked'); ?>

    <form method="POST" action="<?php echo e(route('librarian.login.submit')); ?>" id="loginForm">
        <?php echo csrf_field(); ?>
        <div class="mb-3 text-start">
            <label class="form-label">EMAIL</label>
            <input type="email" class="form-control" name="email" value="<?php echo e(old('email')); ?>"
                   required autofocus <?php echo e($locked ? 'disabled' : ''); ?>>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label">PASSWORD</label>
            <input type="password" class="form-control" name="password"
                   required <?php echo e($locked ? 'disabled' : ''); ?>>
        </div>
        <div class="mb-3 form-check text-start">
            <input type="checkbox" class="form-check-input" name="remember" id="remember"
                   <?php echo e($locked ? 'disabled' : ''); ?>>
            <label class="form-check-label" for="remember">REMEMBER ME</label>
        </div>
        <button type="submit" class="btn btn-login" id="loginButton" <?php echo e($locked ? 'disabled' : ''); ?>>
            <?php echo e($locked ? 'LOCKED' : 'LOGIN'); ?>

        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if(session('account_locked') && session('lockout_seconds')): ?>
    <script>window.lockoutSeconds = <?php echo e(session('lockout_seconds')); ?>;</script>
<?php endif; ?>
<script src="<?php echo e(asset('js/login.js')); ?>"></script>
</body>
</html><?php /**PATH C:\xampp\htdocs\project-name\resources\views/auth/login.blade.php ENDPATH**/ ?>