<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startSection('content'); ?>
<?php $librarian = Auth::guard('librarian')->user(); ?>

<div class="row justify-content-center">
<div class="col-md-6">
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">👤 Librarian Profile</div>
    <div class="card-body">

        <?php if(!$librarian): ?>
            <div class="alert alert-warning text-center">
                <h5>Not Authenticated</h5>
                <p>Please log in to view your profile.</p>
                <a href="<?php echo e(route('librarian.login')); ?>" class="btn btn-primary">Login</a>
            </div>
        <?php else: ?>

            <div class="text-center mb-4">
                <div style="font-size:4rem;">👤</div>
                <h4 class="fw-bold mt-2"><?php echo e($librarian->name); ?></h4>
                <p class="text-muted mb-0"><?php echo e($librarian->email); ?></p>
                <span class="badge bg-success mt-1">Librarian</span>
            </div>

            <hr>

            <table class="table table-borderless mb-0">
                <tr>
                    <th class="text-muted" style="width:40%;">Full Name</th>
                    <td><?php echo e($librarian->name); ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Email</th>
                    <td><?php echo e($librarian->email); ?></td>
                </tr>
                <tr>
                    <th class="text-muted">Member Since</th>
                    <td><?php echo e($librarian->created_at?->format('M d, Y') ?? 'N/A'); ?></td>
                </tr>
            </table>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="<?php echo e(route('librarian.account')); ?>" class="btn btn-warning">⚙️ Account Settings</a>
                <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-primary">← Dashboard</a>
            </div>

        <?php endif; ?>

    </div>
</div>
</div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/profile/profile.blade.php ENDPATH**/ ?>