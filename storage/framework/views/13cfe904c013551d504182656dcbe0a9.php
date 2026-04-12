
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
                    $rowOffset = ($borrowings instanceof \Illuminate\Pagination\AbstractPaginator)
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
                                <span class="status-badge status-borrowed">Borrowed</span>
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

    
    <?php if($borrowings instanceof \Illuminate\Pagination\AbstractPaginator && $borrowings->hasPages()): ?>
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
    <?php else: ?>
        <div class="px-3 py-2 border-top text-muted" style="font-size:0.875rem;">
            Showing all <strong><?php echo e($borrowings->count()); ?></strong> records
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="no-records">
        <div class="no-records-icon">📭</div>
        <h3>No Borrowed Books Found</h3>
        <p>No results match your search or filter.</p>
    </div>
<?php endif; ?><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/borrowings/partials/table.blade.php ENDPATH**/ ?>