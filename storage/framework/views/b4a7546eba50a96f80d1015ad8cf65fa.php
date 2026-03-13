<?php $__env->startSection('title', 'Borrowed Books — Library System'); ?>

<?php $__env->startSection('content'); ?>

<div class="hero-section">
    <h1>Borrowed Books</h1>
    <p>All currently borrowed books — process returns from here</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Active Borrowings</h2>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

<?php echo $__env->make('partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="stats-bar">
    <div class="stat-card stat-active" style="border-top: 4px solid #856404;">
        <div class="stat-value"><?php echo e($stats['active']); ?></div>
        <div class="stat-label">Currently Borrowed</div>
    </div>
    <div class="stat-card stat-overdue" style="border-top: 4px solid #721c24;">
        <div class="stat-value"><?php echo e($stats['overdue']); ?></div>
        <div class="stat-label">Overdue</div>
    </div>
</div>


<div class="filter-card">
    <form method="GET" action="<?php echo e(route('borrowings.index')); ?>" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Student name, course, section, book title or author…"
                   value="<?php echo e(request('search')); ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Filter</label>
            <select name="filter" class="form-control">
                <option value="">— All Active —</option>
                <option value="overdue" <?php echo e(request('filter') === 'overdue' ? 'selected' : ''); ?>>Overdue Only</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">🔍 Search</button>
        </div>
        <div class="col-md-2">
            <a href="<?php echo e(route('borrowings.index')); ?>" class="btn btn-outline-secondary w-100">✖ Clear</a>
        </div>
    </form>
</div>


<div class="borrowings-table-container">
    <?php if($borrowings->count() > 0): ?>
        <div class="table-responsive">
            <table class="borrowings-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Copy</th>
                        <th>Borrower</th>
                        <th>Course & Section</th>
                        <th>Borrowed Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        // Safe row offset: works whether $borrowings is a paginator or plain collection
                        $rowOffset = method_exists($borrowings, 'currentPage')
                            ? ($borrowings->currentPage() - 1) * $borrowings->perPage()
                            : 0;
                    ?>
                    <?php $__currentLoopData = $borrowings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrowing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $isOverdue = $borrowing->due_date && now()->gt($borrowing->due_date);
                        ?>
                        <tr class="<?php echo e($isOverdue ? 'row-overdue' : ''); ?>">
                            <td class="text-muted" style="font-size:.82rem;">
                                <?php echo e($rowOffset + $loop->iteration); ?>

                            </td>
                            <td><strong><?php echo e($borrowing->book->title ?? 'N/A'); ?></strong></td>
                            <td>
                                <?php if($borrowing->bookCopy): ?>
                                    <span class="text-muted" style="font-size:.82rem;">
                                        <?php echo e($borrowing->bookCopy->copy_number ?? ('Copy #' . $borrowing->bookCopy->id)); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($borrowing->student_name); ?></td>
                            <td><?php echo e($borrowing->course); ?> - <?php echo e($borrowing->section); ?></td>
                            <td>
                                <?php echo e($borrowing->borrowed_at?->format('M d, Y h:i A')
                                   ?? $borrowing->created_at?->format('M d, Y h:i A')
                                   ?? 'N/A'); ?>

                            </td>
                            <td>
                                <?php if($borrowing->due_date): ?>
                                    <span <?php if($isOverdue): ?> style="color:#721c24;font-weight:600;" <?php endif; ?>>
                                        <?php echo e($borrowing->due_date->format('M d, Y')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($isOverdue): ?>
                                    <span class="status-badge status-overdue">⚠ Overdue</span>
                                <?php else: ?>
                                    <span class="status-badge status-borrowed">📖 Borrowed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-return"
                                        onclick="openReturnModal(<?php echo e($borrowing->id); ?>)">
                                    ↩ Return
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        
        <?php if(method_exists($borrowings, 'hasPages') && $borrowings->hasPages()): ?>
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted" style="font-size:0.875rem;">
                    Showing
                    <strong><?php echo e($borrowings->firstItem()); ?></strong>–<strong><?php echo e($borrowings->lastItem()); ?></strong>
                    of <strong><?php echo e($borrowings->total()); ?></strong> records
                </div>
                <nav aria-label="Borrowings pagination">
                    <?php echo e($borrowings->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                </nav>
            </div>
        <?php elseif(method_exists($borrowings, 'total')): ?>
            <div class="px-3 py-2 border-top text-muted" style="font-size:0.875rem;">
                Showing all <strong><?php echo e($borrowings->total()); ?></strong> records
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-records">
            <div class="no-records-icon">📭</div>
            <h3>No Borrowed Books Found</h3>
            <p>
                <?php if(request('search') || request('filter')): ?>
                    No results match your search or filter.
                    <a href="<?php echo e(route('borrowings.index')); ?>">Clear filters</a>
                <?php else: ?>
                    There are no active borrowings right now.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openReturnModal(borrowingId) {
    fetch('/borrowings/' + borrowingId + '/data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;

        const b  = data.borrowing;
        const bk = data.book;

        document.getElementById('returnBookTitle').textContent    = bk.title;
        document.getElementById('returnBookAuthor').textContent   = bk.author;
        document.getElementById('borrowerNameText').textContent   = b.student_name;
        document.getElementById('borrowerCourseText').textContent = b.course + ' - ' + b.section;
        document.getElementById('dueDateText').textContent        = b.due_date;

        const form = document.getElementById('barcodeReturnForm');
        form.action = '/return/' + borrowingId;

        // Reset modal state
        document.getElementById('confirmReturnCheckbox').checked = false;
        document.getElementById('confirmReturnBtn').disabled     = true;
        document.getElementById('statusBorrowed').classList.remove('d-none');
        document.getElementById('statusProcessing').classList.add('d-none');
        document.getElementById('statusCompleted').classList.add('d-none');

        const modal = new bootstrap.Modal(document.getElementById('barcodeReturnModal'));
        modal.show();
    })
    .catch(err => console.error('Error loading borrowing data:', err));
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/borrowings/index.blade.php ENDPATH**/ ?>