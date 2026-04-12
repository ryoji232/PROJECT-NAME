<?php $__env->startSection('title', 'Account Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php $librarian = Auth::guard('librarian')->user(); ?>

<div class="row justify-content-center">
<div class="col-md-8">
<div class="card shadow-sm">
    <div class="card-header bg-warning">⚙️ Account Settings</div>
    <div class="card-body">

        <?php if(!$librarian): ?>
            <div class="alert alert-warning text-center">
                <h5>Not Authenticated</h5>
                <p>Please log in to access account settings.</p>
                <a href="<?php echo e(route('librarian.login')); ?>" class="btn btn-primary">Login</a>
            </div>
        <?php else: ?>

            
            <h5 class="mb-3">Update Profile Information</h5>
            <?php if(session('profile_success')): ?>
                <div class="alert alert-success"><?php echo e(session('profile_success')); ?></div>
            <?php endif; ?>
            <form action="<?php echo e(route('librarian.update.profile')); ?>" method="POST" class="mb-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo e($librarian->name); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo e($librarian->email); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>

            <hr>

            
            <h5 class="mb-3">Change Password</h5>
            <?php if(session('password_success')): ?>
                <div class="alert alert-success"><?php echo e(session('password_success')); ?></div>
            <?php endif; ?>
            <?php echo $__env->make('partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <form action="<?php echo e(route('librarian.update.password')); ?>" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
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
                <a href="<?php echo e(route('librarian.profile')); ?>" class="btn btn-secondary">← Profile</a>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-primary">← Dashboard</a>
            </div>

        <?php endif; ?>

    </div>
</div>
</div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/profile/account.blade.php ENDPATH**/ ?>