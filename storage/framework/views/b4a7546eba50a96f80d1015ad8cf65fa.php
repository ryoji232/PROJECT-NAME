

<?php $__env->startSection('title', 'Borrowed Books — Library System'); ?>

<?php $__env->startSection('content'); ?>

<div class="hero-section">
    <h1>Borrowed Books</h1>
    <p>Currently checked-out books — process returns here</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Active Borrowings</h2>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

<?php echo $__env->make('partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="stats-bar">
    <div class="stat-card stat-active">
        <div class="stat-value"><?php echo e($stats['active']); ?></div>
        <div class="stat-label">Currently Borrowed</div>
    </div>
    <div class="stat-card stat-overdue">
        <div class="stat-value"><?php echo e($stats['overdue']); ?></div>
        <div class="stat-label">Overdue</div>
    </div>
</div>


<div class="borrowings-table-container">
    <?php if($borrowings->count() > 0): ?>
        <div class="table-responsive">
            <table class="borrowings-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Borrower</th>
                        <th>Course & Section</th>
                        <th>Borrowed Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $borrowings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrowing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $isOverdue = $borrowing->due_date && now()->gt($borrowing->due_date); ?>
                        <tr class="<?php echo e($isOverdue ? 'row-overdue' : ''); ?>">
                            <td class="text-muted" style="font-size:.82rem;">
                                <?php echo e($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator
                                    ? ($borrowings->currentPage() - 1) * $borrowings->perPage() + $loop->iteration
                                    : $loop->iteration); ?>

                            </td>
                            <td><strong><?php echo e($borrowing->book->title ?? 'N/A'); ?></strong></td>
                            <td><?php echo e($borrowing->book->author ?? 'N/A'); ?></td>
                            <td><?php echo e($borrowing->student_name); ?></td>
                            <td><?php echo e($borrowing->course); ?> - <?php echo e($borrowing->section); ?></td>
                            <td><?php echo e($borrowing->borrowed_at?->format('M d, Y h:i A') ?? 'N/A'); ?></td>
                            <td>
                                <?php if($borrowing->due_date): ?>
                                    <span <?php if($isOverdue): ?> style="color:#721c24;font-weight:600;" <?php endif; ?>>
                                        <?php echo e($borrowing->due_date->format('M d, Y')); ?>

                                    </span>
                                <?php else: ?> N/A <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo e($isOverdue ? 'status-overdue' : 'status-borrowed'); ?>">
                                    <?php echo e($isOverdue ? '⚠ Overdue' : 'Borrowed'); ?>

                                </span>
                            </td>
                            <td>
                                <form method="POST" action="<?php echo e(route('borrow.return', $borrowing->id)); ?>"
                                      onsubmit="return confirm('Confirm return of this book?')">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn-return">↩ Return</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <?php if($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
            <div class="pagination-container"><?php echo e($borrowings->links()); ?></div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-records">
            <div class="no-records-icon">✅</div>
            <h3>No Active Borrowings</h3>
            <p>All books have been returned or no borrowings match your search.</p>
        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/borrowings/index.blade.php ENDPATH**/ ?>