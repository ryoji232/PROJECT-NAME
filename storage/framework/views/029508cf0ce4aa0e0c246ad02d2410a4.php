
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
                                     Returned
                                </span>
                            <?php elseif($isOverdue): ?>
                                <span class="status-badge status-overdue">⚠ Overdue</span>
                            <?php else: ?>
                                <span class="status-badge status-borrowed">Borrowed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    
    <?php if($historyRecords->hasPages()): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted" style="font-size:0.875rem;">
                Showing
                <strong><?php echo e($historyRecords->firstItem()); ?></strong>–<strong><?php echo e($historyRecords->lastItem()); ?></strong>
                of <strong><?php echo e($historyRecords->total()); ?></strong> records
            </div>
            <nav aria-label="Book history pagination">
                <?php echo e($historyRecords->links('pagination::bootstrap-5')); ?>

            </nav>
        </div>
    <?php else: ?>
        <div class="px-3 py-2 border-top text-muted" style="font-size:0.875rem;">
            Showing all <strong><?php echo e($historyRecords->total()); ?></strong> records
        </div>
    <?php endif; ?>

<?php else: ?>
    <div class="no-records">
        <div class="no-records-icon">📭</div>
        <h3>No Records Found</h3>
        <p>No borrowing history matches your search or filter.</p>
    </div>
<?php endif; ?><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/book-history/partials/table.blade.php ENDPATH**/ ?>