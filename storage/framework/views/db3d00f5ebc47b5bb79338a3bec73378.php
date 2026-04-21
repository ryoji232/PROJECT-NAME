<?php $__env->startSection('title', 'Edit Book'); ?>

<?php $__env->startSection('content'); ?>
<style>
    body {
        background: #e9f7ef;
        color: #00402c;
        min-height: 100vh;
        font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }

    .card {
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .card-header {
        border-radius: 1rem 1rem 0 0;
        font-weight: 700;
        background: #198754;
        color: #fff !important;
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

    .btn-secondary {
        background: #6c757d;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: scale(1.03);
        color: white;
    }

    /* Current copies info box */
    .copies-info-box {
        background: #f0fdf4;
        border: 1px solid #a5d6a7;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #155724;
    }
    .copies-info-box .copies-count {
        font-size: 1.4rem;
        font-weight: 800;
        color: #198754;
        line-height: 1;
    }
    .copies-info-box .copies-breakdown {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.15rem;
    }

    /* Max-reached alert */
    #maxCopiesAlert {
        display: none;
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c2c7;
        border-radius: 0.5rem;
        padding: 0.6rem 1rem;
        font-size: 0.88rem;
        font-weight: 600;
        margin-top: 0.4rem;
        animation: shake 0.3s ease;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25%       { transform: translateX(-6px); }
        75%       { transform: translateX(6px); }
    }

    /* Preview badge */
    #copyPreview {
        display: none;
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 0.4rem;
        padding: 0.35rem 0.75rem;
        font-size: 0.83rem;
        font-weight: 600;
        margin-top: 0.4rem;
        transition: opacity 0.2s;
    }
</style>

<?php
    $currentCopies   = (int) $book->copies;
    $maxAllowed      = 10;
    $canAddMore      = $currentCopies < $maxAllowed;
    $maxCanAdd       = $maxAllowed - $currentCopies;   // how many the librarian can still add
    $activeBorrowings = $book->borrowings()->whereNull('returned_at')->count();
?>

<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header">
            <h4 class="mb-0">Edit Book</h4>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('books.update', $book->id)); ?>" method="POST" id="editBookForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                
                <div class="mb-3">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" class="form-control"
                           value="<?php echo e(old('title', $book->title)); ?>" required>
                </div>

                
                <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-control"
                           value="<?php echo e(old('author', $book->author)); ?>" required>
                </div>

                
                <div class="mb-3">

                    
                    <div class="copies-info-box">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <div class="copies-count"><?php echo e($currentCopies); ?></div>
                                <div class="copies-breakdown">current total copies</div>
                            </div>
                            <div style="border-left:2px solid #a5d6a7;padding-left:1rem;">
                                <div><strong><?php echo e($book->available_copies); ?></strong> available</div>
                                <div><strong><?php echo e($activeBorrowings); ?></strong> borrowed</div>
                            </div>
                            <?php if($currentCopies >= $maxAllowed): ?>
                                <div class="ms-auto">
                                    <span class="badge"
                                          style="background:#f8d7da;color:#721c24;border:1px solid #f5c2c7;font-size:.8rem;padding:.35rem .7rem;">
                                        🔒 Maximum reached (<?php echo e($maxAllowed); ?>)
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="ms-auto text-muted" style="font-size:.82rem;">
                                    Can add up to <strong><?php echo e($maxCanAdd); ?></strong> more
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($canAddMore): ?>
                        <label class="form-label fw-semibold">
                            Add Copies
                            <span class="text-muted fw-normal" style="font-size:.85rem;">(max <?php echo e($maxAllowed); ?> total)</span>
                        </label>
                        <input type="number"
                               name="copies_to_add"
                               id="copiesToAdd"
                               class="form-control"
                               value="<?php echo e(old('copies_to_add', 0)); ?>"
                               min="0"
                               max="<?php echo e($maxCanAdd); ?>"
                               placeholder="How many copies to add (0 = no change)"
                               data-current="<?php echo e($currentCopies); ?>"
                               data-max="<?php echo e($maxAllowed); ?>">

                        
                        <div id="copyPreview"></div>

                        
                        <div id="maxCopiesAlert">
                            ⚠️ Maximum of <?php echo e($maxAllowed); ?> copies reached — capped automatically.
                        </div>

                        <small class="text-muted d-block mt-1">
                            Enter how many <strong>additional</strong> copies to add. Leave at 0 to keep the current count.
                        </small>
                    <?php else: ?>
                        
                        <input type="hidden" name="copies_to_add" value="0">
                        <div class="alert alert-warning py-2 px-3 mb-0" style="font-size:.88rem;">
                            🔒 This book already has the maximum of <strong><?php echo e($maxAllowed); ?></strong> copies. No more copies can be added.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?php echo e(route('books.index')); ?>" class="btn btn-secondary">⬅ Back</a>
                    <button type="submit" class="btn btn-success">Update Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var input      = document.getElementById('copiesToAdd');
    var preview    = document.getElementById('copyPreview');
    var alertBox   = document.getElementById('maxCopiesAlert');

    if (!input) return; // already at max — no input rendered

    var current    = parseInt(input.getAttribute('data-current'), 10);
    var maxAllowed = parseInt(input.getAttribute('data-max'), 10);
    var maxCanAdd  = maxAllowed - current;

    function update() {
        var raw   = parseInt(input.value, 10);
        var capped = false;

        // Clamp to valid range
        if (isNaN(raw) || raw < 0) raw = 0;
        if (raw > maxCanAdd) {
            raw    = maxCanAdd;
            capped = true;
        }

        // Write capped value back silently
        input.value = raw;

        // Show/hide max warning
        if (capped) {
            alertBox.style.display = '';
            // Re-trigger the shake animation each time we cap
            alertBox.style.animation = 'none';
            void alertBox.offsetWidth; // reflow
            alertBox.style.animation  = '';
        } else {
            alertBox.style.display = 'none';
        }

        // Show live preview
        if (raw > 0) {
            var newTotal = current + raw;
            preview.style.display  = '';
            preview.textContent    = '→ New total: ' + newTotal + ' ' + (newTotal === 1 ? 'copy' : 'copies');
        } else {
            preview.style.display  = 'none';
        }
    }

    input.addEventListener('input',  update);
    input.addEventListener('change', update);

    // Run once on load in case old() populates a value
    update();
}());
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/books/edit.blade.php ENDPATH**/ ?>