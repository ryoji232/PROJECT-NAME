

<?php $__env->startSection('title', 'Borrowed Books - Library System'); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* Apply the same color scheme as dashboard */
    body {
        background: #e9f7ef; 
        color: #00402c;
        min-height: 100vh;
        font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }

    /* Hero Section - matching dashboard */
    .hero-section {
        background: #198754; /* Flat green */
        color: #fff;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        border-radius: 1.5rem;
        margin-top: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        padding: 3rem 2rem;
    }
    .hero-section h1 {
        font-size: 2.5rem;
        font-weight: 800;
    }
    .hero-section p {
        font-size: 1.1rem;
        opacity: 0.95;
    }

    /* Card styling - matching dashboard */
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

    /* Button styling - matching dashboard */
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

    /* Headings */
    h2 {
        color: #00402c;
        font-weight: 700;
        margin-bottom: 1.5rem;
    }

    /* Card content styling */
    .card-body {
        color: #00402c;
        padding: 1.5rem;
    }
    
    .card-body h5 {
        color: #00402c;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .card-body .text-muted {
        color: #6c757d !important;
        font-size: 0.95rem;
    }
    
    .card-body strong {
        color: #198754;
    }
    
    .card-body .text-danger {
        color: #dc3545 !important;
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .hero-section {
            padding: 2rem 1rem;
        }
        
        .card {
            margin-bottom: 1.5rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: stretch !important;
        }
        
        .btn-light {
            width: 100%;
            margin-top: 1rem;
        }
    }
</style>

<!-- Hero Section -->
<div class="hero-section">
    <h1>Borrowed Books</h1>
    <p>Manage and track currently borrowed books</p>
</div>

<div class="container mt-4">
    <!-- Heading -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 id="borrowings">Borrowed List</h2>
        <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light">
            ⬅ Dashboard
        </a>
    </div>

    <!-- Borrowed Books Section -->
    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $borrowings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $borrowing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5><?php echo e($borrowing->book->title ?? 'Unknown Book'); ?></h5>
                            <small class="text-muted">by <?php echo e($borrowing->book->author ?? 'Unknown Author'); ?></small>
                            <hr>
                            <p class="mb-1"><strong>Borrowed by:</strong> <?php echo e($borrowing->student_name); ?></p>
                            <p class="mb-1"><strong>Course:</strong> <?php echo e($borrowing->course); ?> - <?php echo e($borrowing->section); ?></p>
                            <p class="mb-1"><strong>Due Date:</strong> <?php echo e(\Carbon\Carbon::parse($borrowing->due_date)->format('M d, Y')); ?></p>

                            <?php if($borrowing->penalty > 0): ?>
                                <p class="text-danger fw-bold">⚠ Penalty: ₱<?php echo e($borrowing->penalty); ?></p>
                            <?php endif; ?>
                        </div>

                        <form action="<?php echo e(route('borrow.return', $borrowing->id)); ?>" method="POST" class="mt-3">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-warning w-100">RETURN BOOK</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted mb-0">No borrowed books found.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/borrowings/index.blade.php ENDPATH**/ ?>