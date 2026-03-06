

<?php $__env->startSection('title', 'Borrowed Books - Library System'); ?>

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

    .btn-primary, .btn-success {
        background: #198754;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: white;
    }
    .btn-primary:hover, .btn-success:hover {
        background: #157347;
        transform: scale(1.03);
        color: white;
    }

    .btn-warning {
        background: #ffc107;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: #212529;
    }
    .btn-warning:hover {
        background: #ffb300;
        transform: scale(1.03);
        color: #212529;
    }

    .btn-light {
        background: #f8f9fa;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: #212529;
    }
    .btn-light:hover {
        background: #e2e6ea;
        transform: scale(1.03);
        color: #212529;
    }

    h2 { color: #00402c; font-weight: 700; margin-bottom: 1.5rem; }

    .card-body { color: #00402c; padding: 1.5rem; }
    .card-body h5 { color: #00402c; font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem; }
    .card-body .text-muted { color: #6c757d !important; font-size: 0.95rem; }
    .card-body strong { color: #198754; }
    .card-body .text-danger { color: #dc3545 !important; font-weight: 600; }

    /* Returned card state */
    .card.returned-card {
        opacity: 0.6;
        border: 2px solid #198754;
        transition: opacity 0.5s ease;
    }
    .returned-stamp {
        display: inline-block;
        background: #d1e7dd;
        color: #0a3622;
        border: 1px solid #a3cfbb;
        border-radius: 0.5rem;
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 700;
        margin-top: 0.5rem;
    }

    @media (max-width: 767px) {
        .hero-section { padding: 2rem 1rem; }
        .card { margin-bottom: 1.5rem; }
        .d-flex.justify-content-between { flex-direction: column; align-items: stretch !important; }
        .btn-light { width: 100%; margin-top: 1rem; }
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Hero Section -->
<div class="hero-section">
    <h1>Borrowed Books</h1>
    <p>Manage and track currently borrowed books</p>
</div>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 id="borrowings">Borrowed List</h2>
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light">⬅ Dashboard</a>
    </div>

    <div class="row g-3" id="borrowingsList">
        <?php $__empty_1 = true; $__currentLoopData = $borrowings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrowing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-4" id="borrowing-card-<?php echo e($borrowing->id); ?>">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5><?php echo e($borrowing->book->title ?? 'Unknown Book'); ?></h5>
                            <small class="text-muted">by <?php echo e($borrowing->book->author ?? 'Unknown Author'); ?></small>
                            <hr>
                            <p class="mb-1"><strong>Borrowed by:</strong> <?php echo e($borrowing->student_name); ?></p>
                            <p class="mb-1"><strong>Course:</strong> <?php echo e($borrowing->course); ?> - <?php echo e($borrowing->section); ?></p>
                            <p class="mb-1"><strong>Borrowed:</strong>
                                <?php echo e($borrowing->borrowed_at ? $borrowing->borrowed_at->format('M d, Y h:i A') : \Carbon\Carbon::parse($borrowing->created_at)->format('M d, Y h:i A')); ?>

                            </p>
                            <p class="mb-1"><strong>Due Date:</strong> <?php echo e(\Carbon\Carbon::parse($borrowing->due_date)->format('M d, Y')); ?></p>

                            <?php if($borrowing->penalty > 0): ?>
                                <p class="text-danger fw-bold">⚠ Penalty: ₱<?php echo e($borrowing->penalty); ?></p>
                            <?php endif; ?>

                            
                            <div class="returned-info-<?php echo e($borrowing->id); ?>" style="display:none;">
                                <span class="returned-stamp">✓ Returned: <span class="returned-time-<?php echo e($borrowing->id); ?>"></span></span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button
                                type="button"
                                class="btn btn-warning w-100 return-btn"
                                data-borrowing-id="<?php echo e($borrowing->id); ?>"
                                data-return-url="<?php echo e(route('borrow.return', $borrowing->id)); ?>">
                                RETURN BOOK
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12" id="emptyState">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted mb-0">No borrowed books found.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: '4000'
    };

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.return-btn');
        if (!btn) return;

        const borrowingId = btn.dataset.borrowingId;
        const returnUrl   = btn.dataset.returnUrl;

        // Capture exact client-side timestamp the moment button is clicked
        const returnedAt  = new Date();
        const formatted   = returnedAt.toLocaleString('en-PH', {
            month:  'short',
            day:    'numeric',
            year:   'numeric',
            hour:   'numeric',
            minute: '2-digit',
            hour12: true
        });

        if (!confirm('Confirm return of this book?')) return;

        // Disable button and show loading
        btn.disabled     = true;
        btn.innerHTML    = '<span class="spinner-border spinner-border-sm me-1"></span> Returning...';

        fetch(returnUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':     '<?php echo e(csrf_token()); ?>',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/json',
            },
            body: JSON.stringify({ _method: 'POST' })
        })
        .then(r => {
            // returnBook redirects on success — treat any 2xx/3xx as success
            if (r.ok || r.redirected) return { success: true };
            return r.json();
        })
        .then(data => {
            if (data.success !== false) {
                // Show returned timestamp on the card
                const returnedInfo = document.querySelector(`.returned-info-${borrowingId}`);
                const returnedTime = document.querySelector(`.returned-time-${borrowingId}`);
                if (returnedTime) returnedTime.textContent = formatted;
                if (returnedInfo) returnedInfo.style.display = 'block';

                // Grey out the card
                const card = document.querySelector(`#borrowing-card-${borrowingId} .card`);
                if (card) card.classList.add('returned-card');

                // Replace button with returned label
                btn.outerHTML = `<div class="btn btn-success w-100 disabled">✓ Returned</div>`;

                toastr.success(`Book returned at ${formatted}`);

                // Remove the card after 3 seconds
                setTimeout(() => {
                    const col = document.getElementById(`borrowing-card-${borrowingId}`);
                    if (col) {
                        col.style.transition = 'opacity 0.5s ease';
                        col.style.opacity    = '0';
                        setTimeout(() => {
                            col.remove();
                            // Show empty state if no cards left
                            const remaining = document.querySelectorAll('#borrowingsList .col-md-4');
                            if (remaining.length === 0) {
                                document.getElementById('borrowingsList').innerHTML = `
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <p class="text-muted mb-0">No borrowed books found.</p>
                                            </div>
                                        </div>
                                    </div>`;
                            }
                        }, 500);
                    }
                }, 3000);

            } else {
                toastr.error(data.message ?? 'Could not return the book. Please try again.');
                btn.disabled  = false;
                btn.innerHTML = 'RETURN BOOK';
            }
        })
        .catch(() => {
            toastr.error('Network error. Please try again.');
            btn.disabled  = false;
            btn.innerHTML = 'RETURN BOOK';
        });
    });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/borrowings/index.blade.php ENDPATH**/ ?>