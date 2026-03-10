

<?php $__env->startSection('title', 'Book History - Library System'); ?>

<?php $__env->startSection('content'); ?>
<style>
    body {
        background: #e9f7ef;
        color: #00402c;
        min-height: 100vh;
        font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }

    .hero-section {
        background: #198754;
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        border-radius: 1.5rem;
        margin-top: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        padding: 3rem 2rem;
    }
    .hero-section h1 { font-size: 2.5rem; font-weight: 800; }
    .hero-section p  { font-size: 1.1rem; opacity: 0.95; }

    .stats-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .stat-card {
        flex: 1; min-width: 140px; background: #fff;
        border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 1.25rem 1rem; text-align: center;
    }
    .stat-card .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 0.3rem; }
    .stat-card .stat-label { font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
    .stat-total    .stat-value { color: #198754; }
    .stat-returned .stat-value { color: #0a3622; }
    .stat-active   .stat-value { color: #856404; }
    .stat-overdue  .stat-value { color: #721c24; }

    .filter-card {
        border-radius: 1rem; border: 1px solid #dee2e6;
        background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 1.5rem; margin-bottom: 2rem;
    }
    .filter-label { font-weight: 600; color: #00402c; margin-bottom: 0.5rem; display: block; }
    .filter-input, .filter-select {
        width: 100%; padding: 0.75rem; border: 1px solid #dee2e6;
        border-radius: 0.5rem; font-size: 0.95rem; background: #f8f9fa;
    }
    .filter-input:focus, .filter-select:focus {
        outline: none; border-color: #198754;
        box-shadow: 0 0 0 3px rgba(25,135,84,0.1); background: white;
    }

    .btn-primary {
        background: #198754; border: none; border-radius: 8px;
        font-weight: 600; transition: all 0.2s ease; padding: 10px 18px; color: white;
    }
    .btn-primary:hover { background: #157347; transform: scale(1.03); color: white; }
    .btn-light {
        background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
        font-weight: 600; transition: all 0.2s ease; padding: 10px 18px; color: #212529;
    }
    .btn-light:hover { background: #e2e6ea; transform: scale(1.03); color: #212529; }

    .history-table-container {
        background: #fff; border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 2rem;
    }
    .history-table { width: 100%; border-collapse: collapse; margin: 0; }
    .history-table thead { background: #198754; color: white; }
    .history-table th {
        padding: 1rem; text-align: left; font-weight: 700;
        font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 3px solid #157347;
    }
    .history-table tbody tr { border-bottom: 1px solid #e9ecef; transition: background 0.2s ease; }
    .history-table tbody tr:hover { background: #f8f9fa; }
    .history-table tbody tr.row-returned { background: #f0faf4; }
    .history-table tbody tr.row-returned:hover { background: #e6f4ec; }
    .history-table tbody tr.row-overdue { background: #fff8f8; }
    .history-table tbody tr.row-overdue:hover { background: #fef2f2; }
    .history-table td { padding: 1rem; color: #00402c; vertical-align: middle; }
    .history-table tbody tr:last-child { border-bottom: none; }

    .status-badge {
        display: inline-block; padding: 0.4rem 0.8rem; border-radius: 0.5rem;
        font-size: 0.85rem; font-weight: 600; text-transform: uppercase;
    }
    .status-borrowed { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    .status-returned { background: #d1e7dd; color: #0a3622; border: 1px solid #a3cfbb; }
    .status-overdue  { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; }

    .pagination-container {
        padding: 1.5rem; background: #f8f9fa; border-top: 1px solid #dee2e6;
        display: flex; justify-content: center; align-items: center;
    }
    .pagination { margin: 0; }
    .page-link {
        color: #198754; border: 1px solid #dee2e6; padding: 0.5rem 0.75rem;
        border-radius: 0.375rem; margin: 0 0.25rem; background: white; transition: all 0.2s ease;
    }
    .page-link:hover { background: #198754; color: white; border-color: #198754; transform: translateY(-1px); }
    .page-item.active .page-link { background: #198754; border-color: #198754; color: white; }

    .no-records { text-align: center; padding: 3rem; color: #6c757d; }
    .no-records-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }

    @media (max-width: 767px) {
        .hero-section { padding: 2rem 1rem; }
        .hero-section h1 { font-size: 1.8rem; }
        .history-table { font-size: 0.85rem; }
        .history-table th, .history-table td { padding: 0.75rem 0.5rem; }
        .stats-bar { flex-direction: column; }
    }
</style>

<div class="hero-section">
    <h1>Book History</h1>
    <p>Return and borrowing records for all books</p>
</div>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Transactions</h2>
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light">⬅ Dashboard</a>
    </div>

    
    <div class="stats-bar">
        <div class="stat-card stat-total">
            <div class="stat-value"><?php echo e($historyStats['total']); ?></div>
            <div class="stat-label">Total Records</div>
        </div>
        <div class="stat-card stat-returned">
            <div class="stat-value"><?php echo e($historyStats['returned']); ?></div>
            <div class="stat-label">Returned</div>
        </div>
        <div class="stat-card stat-active">
            <div class="stat-value"><?php echo e($historyStats['active']); ?></div>
            <div class="stat-label">Still Borrowed</div>
        </div>
        <div class="stat-card stat-overdue">
            <div class="stat-value"><?php echo e($historyStats['overdue']); ?></div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="<?php echo e(route('book-history.index')); ?>">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="filter-label">Search</label>
                    <input type="text" name="search" class="filter-input"
                        placeholder="Student name, book title, or author..."
                        value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Records</option>
                        <option value="borrowed" <?php echo e(request('status') == 'borrowed' ? 'selected' : ''); ?>>Currently Borrowed</option>
                        <option value="returned" <?php echo e(request('status') == 'returned' ? 'selected' : ''); ?>>Returned</option>
                        <option value="overdue"  <?php echo e(request('status') == 'overdue'  ? 'selected' : ''); ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">🔍 Filter</button>
                    <a href="<?php echo e(route('book-history.index')); ?>" class="btn btn-light">Clear</a>
                </div>
            </div>
        </form>
    </div>

    
    <div class="history-table-container">
        <?php if($historyRecords->count() > 0): ?>
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrower</th>
                            <th>Course &amp; Section</th>
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
                                $isOverdue  = !$isReturned && $record->due_date && now()->greaterThan($record->due_date);
                                $rowClass   = $isReturned ? 'row-returned' : ($isOverdue ? 'row-overdue' : '');
                            ?>
                            <tr class="<?php echo e($rowClass); ?>">
                                <td style="color:#adb5bd; font-size:0.85rem;">
                                    <?php if($historyRecords instanceof \Illuminate\Pagination\LengthAwarePaginator): ?>
                                        <?php echo e(($historyRecords->currentPage() - 1) * $historyRecords->perPage() + $loop->iteration); ?>

                                    <?php else: ?>
                                        <?php echo e($loop->iteration); ?>

                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo e($record->book->title ?? 'N/A'); ?></strong></td>
                                <td><?php echo e($record->book->author ?? 'N/A'); ?></td>
                                <td><?php echo e($record->student_name); ?></td>
                                <td><?php echo e($record->course); ?> - <?php echo e($record->section); ?></td>
                                <td>
                                    <?php echo e($record->borrowed_at ? $record->borrowed_at->format('M d, Y h:i A') : 'N/A'); ?>

                                </td>
                                <td>
                                    <?php if($record->due_date): ?>
                                        <span <?php if($isOverdue): ?> style="color:#721c24; font-weight:600;" <?php endif; ?>>
                                            <?php echo e($record->due_date->format('M d, Y')); ?>

                                        </span>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($isReturned): ?>
                                        <span style="color:#198754; font-weight:600;">
                                            <?php echo e($record->returned_at->format('M d, Y h:i A')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span style="color:#6c757d; font-style:italic;">Not yet returned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($isReturned): ?>
                                        <span class="status-badge status-returned">✓ Returned</span>
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

            <div class="pagination-container">
                <?php echo e($historyRecords->links()); ?>

            </div>
        <?php else: ?>
            <div class="no-records">
                <div class="no-records-icon">📭</div>
                <h3>No Records Found</h3>
                <p>No borrowing records match your criteria.</p>
            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/book-history/index.blade.php ENDPATH**/ ?>