<?php $__env->startSection('title', 'Dashboard - Library System'); ?>

<?php $__env->startSection('content'); ?>
<?php
    use Carbon\Carbon;
    
    // Calculate penalties for overdue books - FORCE 10 pesos per 7 days
    $withPenaltiesOrOverdue = $borrowings->filter(function($b) {
        if (!$b->due_date || $b->returned_at) return false;
        
        $due = Carbon::parse($b->due_date);
        $now = Carbon::now();
        $isOverdue = $due->lt($now);
        
        if ($isOverdue) {
            // Get whole days overdue only
            $daysOverdue = (int) $due->diffInDays($now);
            $b->days_overdue = $daysOverdue;
            
            // FORCE: 10 pesos per 7-day period
            // 1-7 days = 10 pesos, 8-14 days = 20 pesos, 15-21 days = 30 pesos
            if ($daysOverdue <= 7) {
                $b->penalty = 10;
            } elseif ($daysOverdue <= 14) {
                $b->penalty = 20;
            } elseif ($daysOverdue <= 21) {
                $b->penalty = 30;
            } elseif ($daysOverdue <= 28) {
                $b->penalty = 40;
            } else {
                $periods = ceil($daysOverdue / 7);
                $b->penalty = $periods * 10;
            }
            
            return true;
        }
        
        return false;
    });
?>

<style>
    /* Your existing styles remain the same */
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
    .hero-section p { font-size: 1.1rem; opacity: 0.95; }

    .stats-box {
        background: #fff;
        color: #198754;
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        transition: transform 0.2s, box-shadow 0.2s;
        padding: 2rem 1rem !important;
        margin-bottom: 2rem;
    }
    .stats-box:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .stats-badge {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto 0.7em auto;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #198754;
        color: #fff;
        font-size: 2rem;
        font-weight: 700;
        border: 4px solid #fff;
        box-shadow: 0 2px 8px rgba(25,135,84,0.2);
    }

    .card {
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }
    .card-header {
        border-radius: 1rem 1rem 0 0;
        font-weight: 700;
        background: #198754;
        color: #fff !important;
        font-size: 1.1rem;
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
    }
    .card-header.bg-danger { background: #dc3545 !important; }
    .card-header.bg-primary { background: #0d6efd !important; }
    .card-header.bg-purple { background: #6f42c1 !important; }
    .card-header.bg-info { background: #0dcaf0 !important; }

    .list-group-item {
        background: transparent;
        border: none;
        border-bottom: 1px solid #e3e3e3;
        color: #333;
        font-size: 1rem;
        padding: 1rem 1.2rem;
        transition: background-color 0.2s;
    }
    .list-group-item:hover { background-color: #f8f9fa; }
    .list-group-item:last-child { border-bottom: none; }

    .chart-container { height: 350px; position: relative; margin: 1rem 0; }

    .badge-overdue {
        background-color: #dc3545;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-penalty {
        background-color: #ffc107;
        color: #212529;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-days {
        background-color: #6c757d;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .penalty-info {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .penalty-info h6 {
        color: #856404;
        margin-bottom: 0.5rem;
    }

    .penalty-period {
        background: #e9ecef;
        border-radius: 0.25rem;
        padding: 0.5rem;
        margin: 0.25rem 0;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .hero-section { padding: 2rem 1rem; margin: 1rem 0 2rem 0; }
        .hero-section h1 { font-size: 2rem; }
        .stats-badge { width: 60px; height: 60px; font-size: 1.5rem; }
        .chart-container { height: 300px; }
    }
</style>

<!-- Hero Section -->
<div class="hero-section">
    <h1>IETI College Library</h1>
    <p>Search, discover, and borrow books with ease</p>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="row">
            <div class="col-md-4">
                <div class="stats-box text-center">
                    <div class="stats-badge"><?php echo e($books->count() ?? 0); ?></div>
                    <div class="fw-semibold">TOTAL BOOKS</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-box text-center">
                    <div class="stats-badge"><?php echo e($borrowings->count() ?? 0); ?></div>
                    <div class="fw-semibold">CURRENTLY BORROWED</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-box text-center">
                    <div class="stats-badge"><?php echo e($withPenaltiesOrOverdue->count()); ?></div>
                    <div class="fw-semibold">OVERDUE BOOKS</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Penalty Information -->
<div class="row">
    <div class="col-12">
        <div class="penalty-info">
            <h6>    Penalty Rate: 10 pesos per 7 days overdue</h6>
            <div class="penalty-period">
                <strong>1-7 days overdue:</strong> 10 pesos penalty
            </div>
            <div class="penalty-period">
                <strong>8-14 days overdue:</strong> 20 pesos penalty
            </div>
            <div class="penalty-period">
                <strong>15-21 days overdue:</strong> 30 pesos penalty
            </div>
            <div class="penalty-period">
                <strong>22-28 days overdue:</strong> 40 pesos penalty
            </div>
            <small class="text-muted">Each additional 7-day period adds 10 pesos</small>
        </div>
    </div>
</div>

<!-- Recent Borrowings -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">RECENT BORROWINGS</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if($recentBorrowings->count() > 0): ?>
                        <?php $__currentLoopData = $recentBorrowings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrowing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo e($borrowing->student_name); ?></strong> borrowed 
                                        <em>"<?php echo e($borrowing->book->title ?? 'Unknown Book'); ?>"</em>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo e(\Carbon\Carbon::parse($borrowing->borrowed_at ?? $borrowing->created_at)->format('M d, Y h:i A')); ?>

                                    </small>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <li class="list-group-item text-center text-muted py-3">
                            <h5>No recent borrowings</h5>
                            <p class="mb-0">Books that are borrowed will appear here</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Borrowers with Penalties -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger">BORROWERS WITH PENALTIES</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php $__empty_1 = true; $__currentLoopData = $withPenaltiesOrOverdue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrowing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            // RE-CALCULATE penalty to ensure it's correct
                            $daysOverdue = $borrowing->days_overdue;
                            
                            if ($daysOverdue <= 7) {
                                $penalty = 10;
                                $periodRange = "1-7 days";
                            } elseif ($daysOverdue <= 14) {
                                $penalty = 20;
                                $periodRange = "8-14 days";
                            } elseif ($daysOverdue <= 21) {
                                $penalty = 30;
                                $periodRange = "15-21 days";
                            } elseif ($daysOverdue <= 28) {
                                $penalty = 40;
                                $periodRange = "22-28 days";
                            } else {
                                $periods = ceil($daysOverdue / 7);
                                $penalty = $periods * 10;
                                $periodRange = (($periods - 1) * 7 + 1) . "-" . ($periods * 7) . " days";
                            }
                            
                            // OVERRIDE any existing penalty calculation
                            $borrowing->penalty = $penalty;
                        ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <strong class="me-2"><?php echo e($borrowing->student_name); ?></strong>
                                        <span class="badge-days"><?php echo e($borrowing->days_overdue); ?> days overdue</span>
                                    </div>
                                    <div class="mb-2">
                                        Book: <em>"<?php echo e($borrowing->book->title ?? 'Unknown Book'); ?>"</em>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="badge-penalty me-2">Penalty: ₱<?php echo e(number_format($penalty, 2)); ?></span>
                                        <small class="text-muted">
                                            (<?php echo e($periodRange); ?> period)
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        Due: <?php echo e(Carbon::parse($borrowing->due_date)->format('M d, Y')); ?>

                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="text-danger fw-bold mb-1">₱<?php echo e(number_format($penalty, 2)); ?></div>
                                    <small class="text-muted"><?php echo e($borrowing->days_overdue); ?> days</small>
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
    </div>
</div>

<!-- Most Borrowed Books Chart -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary">MOST BORROWED BOOKS</div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="borrowedBooksChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Most Popular Book -->
<?php if($mostBorrowed && $mostBorrowed->book): ?>
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-purple">MOST POPULAR BOOK</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="text-purple"><?php echo e($mostBorrowed->book->title); ?></h5>
                        <p class="text-muted mb-2">by <?php echo e($mostBorrowed->book->author); ?></p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="border rounded p-2">
                            <div class="h5 text-purple mb-0"><?php echo e($mostBorrowed->borrow_count); ?></div>
                            <small class="text-muted">Times Borrowed</small>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="border rounded p-2">
                            <div class="h5 text-primary mb-0"><?php echo e($mostBorrowed->book->copies); ?></div>
                            <small class="text-muted">Available Copies</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let borrowedBooksChart = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupRealTimeUpdates();
});

function initializeDashboard() {
    updateDashboardData();
    initializeChart();
}

function setupRealTimeUpdates() {
    window.addEventListener('bookBorrowed', function(e) {
        updateDashboardData();
    });
    setInterval(updateDashboardData, 30000);
}

function updateDashboardData() {
    fetch('/borrowings/realtime-data', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStats(data);
            updateRecentBorrowings(data.recent_borrowings);
            updateChart(data.chart_data);
        }
    })
    .catch(error => console.error('Error fetching dashboard data:', error));
}

function updateStats(data) {
    const statsBadges = document.querySelectorAll('.stats-badge');
    if (statsBadges.length >= 3) {
        statsBadges[0].textContent = data.total_books;
        statsBadges[1].textContent = data.current_borrowings;
        statsBadges[2].textContent = data.overdue_count || 0;
    }
}

function updateRecentBorrowings(borrowings) {
    const recentBorrowingsList = document.querySelector('.list-group');
    if (!recentBorrowingsList) return;
    
    if (borrowings && borrowings.length > 0) {
        let html = '';
        borrowings.forEach(borrowing => {
            const borrowDate = new Date(borrowing.borrowed_at);
            const formattedDate = borrowDate.toLocaleDateString('en-US', {
                month: 'short', day: 'numeric', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            
            html += `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${borrowing.student_name}</strong> borrowed 
                            <em>"${borrowing.book_title}"</em>
                        </div>
                        <small class="text-muted">${formattedDate}</small>
                    </div>
                </li>
            `;
        });
        recentBorrowingsList.innerHTML = html;
    } else {
        recentBorrowingsList.innerHTML = `
            <li class="list-group-item text-center text-muted py-3">
                <h5>No recent borrowings</h5>
                <p class="mb-0">Books that are borrowed will appear here</p>
            </li>
        `;
    }
}

function initializeChart() {
    const ctx = document.getElementById('borrowedBooksChart');
    if (!ctx) return;
    
    borrowedBooksChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['No data available'],
            datasets: [{
                label: 'Times Borrowed',
                data: [0],
                backgroundColor: ['#6c757d'],
                borderColor: ['#495057'],
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
                        maxRotation: 0,
                        minRotation: 0,
                        callback: function(value, index, values) {
                            const label = this.getLabelForValue(value);
                            return label.length > 15 ? label.substring(0, 15) + '...' : label;
                        }
                    }
                }
            }
        }
    });
}

function updateChart(chartData) {
    if (!borrowedBooksChart) return;
    
    if (chartData && chartData.labels && chartData.labels.length > 0) {
        borrowedBooksChart.data.labels = chartData.labels;
        borrowedBooksChart.data.datasets[0].data = chartData.data;
        borrowedBooksChart.data.datasets[0].backgroundColor = [
            '#198754', '#0d6efd', '#6f42c1', '#d63384', 
            '#fd7e14', '#20c997', '#0dcaf0'
        ];
    } else {
        borrowedBooksChart.data.labels = ['No borrowing data'];
        borrowedBooksChart.data.datasets[0].data = [0];
        borrowedBooksChart.data.datasets[0].backgroundColor = ['#6c757d'];
    }
    
    borrowedBooksChart.update();
}

function showNotification(type, message) {
    // Using Toastr for beautiful notifications
    if (typeof toastr !== 'undefined') {
        toastr[type](message);
    } else {
        // Fallback to alert
        alert(message);
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/welcome.blade.php ENDPATH**/ ?>