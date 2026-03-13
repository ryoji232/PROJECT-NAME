<?php $__env->startSection('title', 'Book History — Library System'); ?>

<?php $__env->startSection('content'); ?>

<div class="hero-section">
    <h1>Book History</h1>
    <p>Complete borrowing history — all transactions ever recorded</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>All Borrowing Records</h2>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

<?php echo $__env->make('partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="stats-bar">
    <div class="stat-card" style="border-top: 4px solid #198754;">
        <div class="stat-value" style="color:#198754;"><?php echo e($historyStats['total']); ?></div>
        <div class="stat-label">Total Records</div>
    </div>
    <div class="stat-card" style="border-top: 4px solid #0d6efd;">
        <div class="stat-value" style="color:#0d6efd;"><?php echo e($historyStats['returned']); ?></div>
        <div class="stat-label">Returned</div>
    </div>
    <div class="stat-card stat-active" style="border-top: 4px solid #856404;">
        <div class="stat-value"><?php echo e($historyStats['active']); ?></div>
        <div class="stat-label">Still Borrowed</div>
    </div>
    <div class="stat-card stat-overdue" style="border-top: 4px solid #721c24;">
        <div class="stat-value"><?php echo e($historyStats['overdue']); ?></div>
        <div class="stat-label">Overdue</div>
    </div>
</div>


<div class="filter-card">
    <form method="GET" action="<?php echo e(route('book-history.index')); ?>" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Student name, course, section, book title or author…"
                   value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-control">
                <option value="">— All Records —</option>
                <option value="borrowed"  <?php echo e(request('status') === 'borrowed'  ? 'selected' : ''); ?>>Currently Borrowed</option>
                <option value="returned"  <?php echo e(request('status') === 'returned'  ? 'selected' : ''); ?>>Returned</option>
                <option value="overdue"   <?php echo e(request('status') === 'overdue'   ? 'selected' : ''); ?>>Overdue</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">🔍 Filter</button>
        </div>
        <div class="col-md-2">
            <a href="<?php echo e(route('book-history.index')); ?>" class="btn btn-outline-secondary w-100">✖ Clear</a>
        </div>
    </form>
</div>


<div class="borrowings-table-container">
    <?php if($historyRecords->count() > 0): ?>
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
                        <th>Returned Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $historyRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isReturned = !is_null($record->returned_at);
                            $isOverdue  = !$isReturned && $record->due_date && now()->gt($record->due_date);
                        ?>
                        <tr class="<?php echo e($isOverdue ? 'row-overdue' : ''); ?>">
                            <td class="text-muted" style="font-size:.82rem;">
                                <?php echo e(($historyRecords->currentPage() - 1) * $historyRecords->perPage() + $loop->iteration); ?>

                            </td>
                            <td><strong><?php echo e($record->book->title ?? 'N/A'); ?></strong></td>
                            <td><?php echo e($record->book->author ?? 'N/A'); ?></td>
                            <td><?php echo e($record->student_name); ?></td>
                            <td><?php echo e($record->course); ?> - <?php echo e($record->section); ?></td>
                            <td><?php echo e($record->borrowed_at?->format('M d, Y h:i A') ?? $record->created_at?->format('M d, Y h:i A') ?? 'N/A'); ?></td>
                            <td>
                                <?php if($record->due_date): ?>
                                    <span <?php if($isOverdue && !$isReturned): ?> style="color:#721c24;font-weight:600;" <?php endif; ?>>
                                        <?php echo e($record->due_date->format('M d, Y')); ?>

                                    </span>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($isReturned): ?>
                                    <span style="color:#198754;">
                                        <?php echo e($record->returned_at->format('M d, Y h:i A')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($isReturned): ?>
                                    <span class="status-badge" style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;">
                                        ✅ Returned
                                    </span>
                                <?php elseif($isOverdue): ?>
                                    <span class="status-badge status-overdue">⚠ Overdue</span>
                                <?php else: ?>
                                    <span class="status-badge status-borrowed">📖 Borrowed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>


    <?php else: ?>
        <div class="no-records">
            <div class="no-records-icon">📭</div>
            <h3>No Records Found</h3>
            <p>No borrowing history matches your search or filter.</p>
        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/book-history/index.blade.php ENDPATH**/ ?>