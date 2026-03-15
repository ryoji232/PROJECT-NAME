<?php $__env->startSection('title', 'Dashboard — Library System'); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
use Carbon\Carbon;

$withPenalties = $borrowings->filter(function ($b) {
    if (!$b->due_date || $b->returned_at) return false;
    $due = Carbon::parse($b->due_date);
    if (!$due->lt(Carbon::now())) return false;

    $days = (int) $due->diffInDays(Carbon::now());
    $b->days_overdue = $days;
    $b->penalty      = ceil($days / 7) * 10;
    return true;
});
?>


<div class="hero-section">
    <h1>IETI College Library</h1>
    <p>Search, discover, and borrow books with ease</p>
</div>


<div class="row mb-4">
    <?php $__currentLoopData = [['label'=>'TOTAL BOOKS','value'=>$books->count()],['label'=>'CURRENTLY BORROWED','value'=>$borrowings->count()],['label'=>'OVERDUE BOOKS','value'=>$withPenalties->count()]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="col-md-4">
        <div class="stats-box text-center">
            <div class="stats-badge"><?php echo e($stat['value']); ?></div>
            <div class="fw-semibold"><?php echo e($stat['label']); ?></div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div class="penalty-info mb-4">
    <h6>📋 Penalty Rate: ₱10 per 7-day period overdue</h6>
    <?php $__currentLoopData = ['1–7 days'=>10,'8–14 days'=>20,'15–21 days'=>30,'22–28 days'=>40]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="penalty-period"><strong><?php echo e($period); ?>:</strong> ₱<?php echo e($amount); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <small class="text-muted">Each additional 7-day period adds ₱10</small>
</div>


<div class="card shadow-sm mb-4">
    <div class="card-header">RECENT BORROWINGS</div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush" id="recentBorrowingsList">
            <?php $__empty_1 = true; $__currentLoopData = $recentBorrowings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo e($b->student_name); ?></strong> borrowed
                            <em>"<?php echo e($b->book->title ?? 'Unknown Book'); ?>"</em>
                        </div>
                        <small class="text-muted">
                            <?php echo e(Carbon::parse($b->borrowed_at ?? $b->created_at)->format('M d, Y h:i A')); ?>

                        </small>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="list-group-item text-center text-muted py-3">
                    <h5>No recent borrowings</h5>
                    <p class="mb-0">Books borrowed will appear here</p>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>


<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger">BORROWERS WITH PENALTIES</div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php $__empty_1 = true; $__currentLoopData = $withPenalties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $days    = $b->days_overdue;
                    $periods = ceil($days / 7);
                    $penalty = $periods * 10;
                    $from    = ($periods - 1) * 7 + 1;
                    $to      = $periods * 7;
                ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?php echo e($b->student_name); ?></strong>
                            <span class="badge-days ms-2"><?php echo e($days); ?> days overdue</span><br>
                            <em>"<?php echo e($b->book->title ?? 'Unknown Book'); ?>"</em><br>
                            <span class="badge-penalty">Penalty: ₱<?php echo e(number_format($penalty, 2)); ?></span>
                            <small class="text-muted ms-1">(<?php echo e($from); ?>–<?php echo e($to); ?> days)</small><br>
                            <small class="text-muted">Due: <?php echo e(Carbon::parse($b->due_date)->format('M d, Y')); ?></small>
                        </div>
                        <div class="text-end">
                            <div class="text-danger fw-bold">₱<?php echo e(number_format($penalty, 2)); ?></div>
                            <small class="text-muted"><?php echo e($days); ?> days</small>
                        </div>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <li class="list-group-item text-center text-muted py-4">
                    <h5>No penalties at the moment</h5>
                    <p class="mb-0">All books are returned on time!</p>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>


<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary">MOST BORROWED BOOKS</div>
    <div class="card-body">
        <?php if(!empty($chartData['labels'])): ?>
            <div class="chart-container">
                <canvas id="borrowedBooksChart"></canvas>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-4">
                <div style="font-size:2.5rem;opacity:.4;">📊</div>
                <p class="mt-2 mb-0">No borrowing data yet. Chart will appear once books are borrowed.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php if($mostBorrowed?->book): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-purple">MOST POPULAR BOOK</div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-6 text-start">
                <h5><?php echo e($mostBorrowed->book->title); ?></h5>
                <p class="text-muted">by <?php echo e($mostBorrowed->book->author); ?></p>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-0"><?php echo e($mostBorrowed->borrow_count); ?></div>
                    <small class="text-muted">Times Borrowed</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-0"><?php echo e($mostBorrowed->book->copies); ?></div>
                    <small class="text-muted">Available Copies</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const ctx = document.getElementById('borrowedBooksChart');
    if (!ctx) return;

    const labels = <?php echo json_encode($chartData['labels'] ?? [], 15, 512) ?>;
    const data   = <?php echo json_encode($chartData['data']   ?? [], 15, 512) ?>;
    const colors = ['#198754','#0d6efd','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0'];

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Times Borrowed',
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: {
                    ticks: {
                        callback: function(val, i) {
                            const l = labels[i] ?? '';
                            return l.length > 15 ? l.slice(0, 15) + '…' : l;
                        }
                    }
                }
            }
        }
    });

    // Refresh every 30 seconds
    setInterval(function () {
        fetch('/borrowings/realtime-data', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(resp => {
            if (!resp.success || !resp.chart_data?.labels?.length) return;
            chart.data.labels = resp.chart_data.labels;
            chart.data.datasets[0].data = resp.chart_data.data;
            chart.data.datasets[0].backgroundColor = colors.slice(0, resp.chart_data.labels.length);
            chart.update();

            // Update stat badges too
            const badges = document.querySelectorAll('.stats-badge');
            if (badges.length >= 2) {
                badges[0].textContent = resp.total_books;
                badges[1].textContent = resp.current_borrowings;
            }

            // Update recent borrowings list
            const list = document.getElementById('recentBorrowingsList');
            if (list && resp.recent_borrowings?.length) {
                list.innerHTML = resp.recent_borrowings.map(b => {
                    const date = new Date(b.borrowed_at).toLocaleDateString('en-US', {
                        month:'short', day:'numeric', year:'numeric',
                        hour:'2-digit', minute:'2-digit'
                    });
                    return `<li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>${b.student_name}</strong> borrowed <em>"${b.book_title}"</em></div>
                            <small class="text-muted">${date}</small>
                        </div>
                    </li>`;
                }).join('');
            }
        })
        .catch(err => console.error('Dashboard refresh error:', err));
    }, 30000);
})();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\PROJECT-NAME-main\resources\views/dashboard/index.blade.php ENDPATH**/ ?>