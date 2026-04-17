<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Copy Sticker - <?php echo e($copy->copy_number ?? 'Copy'); ?> — <?php echo e($book->title); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            background: #fff;
            text-align: center;
            margin: 0;
            padding: 20px;
        }
        .sticker {
            width: 280px;
            border: 2px solid #333;
            background: #fff;
            padding: 12px 8px;
            margin: 0 auto;
        }
        h3 {
            margin: 4px 0;
            color: #333;
            font-size: 15px;
            font-weight: bold;
            line-height: 1.2;
        }
        p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        .copy-label {
            display: inline-block;
            background: #198754;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 3px;
            margin: 4px 0 6px;
            letter-spacing: 0.5px;
        }
        .barcode-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 12px 0;
            margin: 6px 0;
        }
        .book-info {
            text-align: left;
            margin: 6px 0;
            padding: 6px;
            background: #f8f9fa;
            border-radius: 3px;
            font-size: 10px;
        }
        .button-group {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .print-button, .download-button {
            padding: 6px 12px;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .print-button  { background: #007bff; }
        .download-button { background: #28a745; }
        .scan-instruction {
            font-size: 9px;
            color: #999;
            margin-top: 8px;
            font-style: italic;
            line-height: 1.2;
        }
        .barcode-number {
            font-family: monospace;
            font-size: 9px;
            margin-top: 5px;
            font-weight: bold;
            color: #333;
        }

        /* Modal Styles — identical to barcode-sticker */
        .book-info-modal .modal-content,
        .return-modal .modal-content {
            border-radius: 1rem;
            border: 2px solid #198754;
        }
        .book-info-modal .modal-header,
        .return-modal .modal-header {
            background: #198754;
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        .book-info-card,
        .return-card {
            background: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            margin: 1rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .book-info-title {
            color: #00402c;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .borrower-info {
            background: #f8f9fa;
            border-radius: 0.75rem;
            padding: 1rem;
            margin: 1rem 0;
        }
        .status-available { color: #198754; font-weight: bold; }
        .status-borrowed  { color: #dc3545; font-weight: bold; }

        @media print {
            .button-group { display: none; }
            .book-info-modal,
            .return-modal  { display: none !important; }
        }
    </style>
</head>
<body>

<?php
    $copyBarcode  = \App\Models\BookCopy::normalizeBarcode($copy->barcode);
    $isCopyAvail  = ($copy->status === 'available');
?>


<div class="sticker">
    <h3><?php echo e(Str::limit(e($book->title), 40)); ?></h3>
    <p><strong>Author:</strong> <?php echo e(Str::limit(e($book->author), 25)); ?></p>

    
    <div class="copy-label"><?php echo e($copy->copy_number ?? 'Copy'); ?></div>

    <div class="barcode-box">
        <svg id="barcode"></svg>
        <div class="barcode-number"><?php echo e($copyBarcode); ?></div>
    </div>

    <div class="book-info">
        <p><strong>Copy:</strong> <?php echo e($copy->copy_number ?? 'N/A'); ?> | <strong>Total copies:</strong> <?php echo e($book->copies); ?></p>
        <p><strong>Status:</strong>
            <span class="<?php echo e($isCopyAvail ? 'status-available' : 'status-borrowed'); ?>">
                <?php echo e($isCopyAvail ? 'Available' : 'Borrowed'); ?>

            </span>
        </p>
    </div>

    <p class="scan-instruction">
        Scan to <?php echo e($isCopyAvail ? 'borrow' : 'return'); ?> this copy<br>at library desk
    </p>

    <div class="button-group">
        <button class="print-button"    onclick="window.print()">Print</button>
        <button class="download-button" onclick="downloadBarcode()">Save</button>
    </div>
</div>


<div class="modal fade book-info-modal" id="bookInfoModal" tabindex="-1"
     aria-labelledby="bookInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookInfoModalLabel">Borrow This Copy</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="book-info-card">
                    <h4 class="book-info-title"><?php echo e($book->title); ?></h4>
                    <p class="text-muted"><strong>Author:</strong> <?php echo e($book->author); ?></p>
                    <div class="row mt-3">
                        <div class="col-6">
                            <span class="badge bg-success">Copy: <?php echo e($copy->copy_number ?? 'N/A'); ?></span>
                        </div>
                        <div class="col-6">
                            <span class="badge bg-info">Total: <?php echo e($book->copies); ?></span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Copy Barcode: <strong><?php echo e($copyBarcode); ?></strong></small>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success">✅ This copy is available for borrowing</span>
                    </div>

                    
                    <div class="mt-4 p-3 border rounded">
                        <h6 class="mb-3">Quick Borrow</h6>
                        <form id="borrowForm">
                            <?php echo csrf_field(); ?>
                            
                            <input type="hidden" name="book_copy_barcode" value="<?php echo e($copyBarcode); ?>">
                            <div class="row g-2">
                                <div class="col-12">
                                    <input type="text" name="student_name"
                                           class="form-control form-control-sm"
                                           placeholder="Student Name" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="course"
                                           class="form-control form-control-sm"
                                           placeholder="Course" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="section"
                                           class="form-control form-control-sm"
                                           placeholder="Section" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" id="borrowBtn"
                                            class="btn btn-success btn-sm w-100">
                                        BORROW THIS COPY
                                    </button>
                                </div>
                            </div>
                            <div id="borrowAlert" class="alert d-none mt-2" role="alert"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
                <a href="<?php echo e(route('books.index')); ?>?highlight_book=<?php echo e($book->id); ?>"
                   class="btn btn-primary">View in Books</a>
                <button type="button" class="btn btn-success"
                        onclick="window.print()">Print Sticker</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade return-modal" id="returnModal" tabindex="-1"
     aria-labelledby="returnModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnModalLabel">Confirm Copy Return</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="return-card">
                    <div class="text-center mb-4">
                        <h4 class="book-info-title"><?php echo e($book->title); ?></h4>
                        <p class="text-muted">by <?php echo e($book->author); ?></p>
                    </div>

                    <div class="borrower-info">
                        <h6 class="fw-bold text-success">Copy Information</h6>
                        <p class="mb-1"><strong>Copy:</strong> <?php echo e($copy->copy_number ?? 'N/A'); ?></p>
                        <p class="mb-1"><strong>Copy Barcode:</strong> <?php echo e($copyBarcode); ?></p>
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="status-borrowed">Currently Borrowed</span>
                        </p>
                        <p class="mb-0"><strong>Book Copies:</strong> <?php echo e($book->available_copies); ?> available / <?php echo e($book->copies); ?> total</p>
                    </div>

                    
                    <div id="borrowerDetails" class="borrower-info d-none">
                        <h6 class="fw-bold text-success">Current Borrower</h6>
                        <p class="mb-1"><strong>Name:</strong> <span id="borrowerName">—</span></p>
                        <p class="mb-1"><strong>Course &amp; Section:</strong> <span id="borrowerCourse">—</span></p>
                        <p class="mb-0"><strong>Due Date:</strong> <span id="borrowerDue">—</span></p>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <strong>Please verify:</strong> Ensure the physical book copy is being returned.
                    </div>

                    <div class="form-check mb-3 mt-2 p-3 border rounded" style="background:#f8f9fa;">
                        <input class="form-check-input" type="checkbox" id="quickReturnCheckbox">
                        <label class="form-check-label fw-semibold" for="quickReturnCheckbox">
                            I confirm the physical book copy has been returned
                        </label>
                    </div>

                    
                    <form id="quickReturnForm" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="d-grid">
                            <button type="submit" id="quickReturnBtn"
                                    class="btn btn-success btn-lg" disabled>
                                ✅ Confirm Copy Return
                            </button>
                        </div>
                    </form>

                    <div id="returnLoadingMsg" class="text-center text-muted mt-2" style="font-size:0.85rem;">
                        <span class="spinner-border spinner-border-sm me-1"></span>
                        Loading borrower info…
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo e(route('books.index')); ?>?highlight_book=<?php echo e($book->id); ?>"
                   class="btn btn-primary">View in Books</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Render the copy's unique barcode ──────────────────────────────
    JsBarcode('#barcode', '<?php echo e($copyBarcode); ?>', {
        format:       'CODE128',
        width:        2,
        height:       50,
        displayValue: false,
        margin:       10,
        background:   '#f8f9fa',
        lineColor:    '#000000'
    });

    // ── 2. Auto-open modal if ?show_modal=true is in the URL ─────────────
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('show_modal') === 'true') {
        handleBarcodeScan();
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // ── 3. Borrow form (POST /borrow/by-barcode) ─────────────────────────
    //
    // Uses the copy-barcode endpoint so the exact physical copy is reserved.
    // This mirrors the existing BorrowingController::borrowByBarcode() flow.
    const borrowForm  = document.getElementById('borrowForm');
    const borrowBtn   = document.getElementById('borrowBtn');
    const borrowAlert = document.getElementById('borrowAlert');

    if (borrowForm) {
        borrowForm.addEventListener('submit', function (e) {
            e.preventDefault();

            borrowBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Borrowing…';
            borrowBtn.disabled  = true;

            const formData = new FormData(this);

            fetch('<?php echo e(route("borrow.by.barcode")); ?>', {
                method:  'POST',
                body:    formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     '<?php echo e(csrf_token()); ?>'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showBorrowAlert('success', '✅ ' + (data.message || 'Book borrowed successfully!'));
                    borrowBtn.innerHTML = 'BORROW THIS COPY';
                    // Reload after a short pause so the sticker status reflects the borrow
                    setTimeout(() => location.reload(), 1200);
                } else {
                    showBorrowAlert('danger', '❌ ' + (data.message || 'Could not borrow this copy.'));
                    borrowBtn.innerHTML = 'BORROW THIS COPY';
                    borrowBtn.disabled  = false;
                }
            })
            .catch(err => {
                console.error('[CopySticker] Borrow error:', err);
                showBorrowAlert('danger', '❌ Network error. Please try again.');
                borrowBtn.innerHTML = 'BORROW THIS COPY';
                borrowBtn.disabled  = false;
            });
        });
    }

    function showBorrowAlert(type, msg) {
        borrowAlert.className   = 'alert alert-' + type + ' mt-2';
        borrowAlert.textContent = msg;
    }

    // ── 4. Return modal: load borrower info via AJAX when opened ─────────
    //
    // Fetches the active borrowing for THIS specific copy using the existing
    // /copies/scan/{code} endpoint which returns the copy + book, then we
    // look up the borrowing via /borrowings?book_copy_id=...
    // Simpler: we hit /books/{bookId}/current-borrowing but filtered by copy.
    // Since that endpoint only returns the latest active borrowing for the book,
    // and we know the copy barcode, we use the dedicated copy scan endpoint
    // to resolve the correct borrowing ID.
    const returnModal = document.getElementById('returnModal');
    if (returnModal) {
        returnModal.addEventListener('show.bs.modal', function () {
            loadBorrowingForCopy();
        });

        returnModal.addEventListener('show.bs.modal', function () {
            // Reset checkbox & button on every open
            const cb  = document.getElementById('quickReturnCheckbox');
            const btn = document.getElementById('quickReturnBtn');
            if (cb)  cb.checked  = false;
            if (btn) btn.disabled = true;
        });
    }

    function loadBorrowingForCopy() {
        const borrowerDiv  = document.getElementById('borrowerDetails');
        const loadingMsg   = document.getElementById('returnLoadingMsg');
        const nameEl       = document.getElementById('borrowerName');
        const courseEl     = document.getElementById('borrowerCourse');
        const dueEl        = document.getElementById('borrowerDue');
        const form         = document.getElementById('quickReturnForm');

        if (loadingMsg) loadingMsg.style.display = '';
        if (borrowerDiv) borrowerDiv.classList.add('d-none');

        // Fetch the active borrowing for this specific copy by looking up
        // /books/{bookId}/current-borrowing — the server returns the latest
        // unreturned borrowing for the book. Since each copy only has one
        // active borrowing at a time, and we're on the specific copy's page,
        // we also verify the book_copy_id matches our copy.
        fetch('/books/<?php echo e($book->id); ?>/copy/<?php echo e($copy->id); ?>/current-borrowing', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (loadingMsg) loadingMsg.style.display = 'none';

            if (!data.success || !data.borrowing) {
                if (borrowerDiv) {
                    borrowerDiv.classList.remove('d-none');
                    if (nameEl)   nameEl.textContent   = 'Unknown — record not found';
                    if (courseEl) courseEl.textContent = '—';
                    if (dueEl)    dueEl.textContent    = '—';
                }
                return;
            }

            const b = data.borrowing;

            if (nameEl)   nameEl.textContent   = b.student_name || '—';
            if (courseEl) courseEl.textContent  = (b.course || '') + (b.section ? ' - ' + b.section : '');
            if (dueEl)    dueEl.textContent     = b.due_date ? b.due_date.substring(0, 10) : 'N/A';

            // Wire the return form to the correct borrowing record
            if (form) form.action = '/return/' + b.id;

            if (borrowerDiv) borrowerDiv.classList.remove('d-none');
        })
        .catch(err => {
            console.error('[CopySticker] Load borrowing error:', err);
            if (loadingMsg) loadingMsg.style.display = 'none';
        });
    }

    // ── 5. Return form AJAX submit ────────────────────────────────────────
    //
    // Uses the same POST /return/{borrowingId} route as the rest of the app
    // (BorrowingController::returnBook). This ensures available_copies is
    // incremented and the BookCopy status is flipped back to 'available'.
    const quickReturnForm = document.getElementById('quickReturnForm');
    if (quickReturnForm) {
        quickReturnForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn  = document.getElementById('quickReturnBtn');
            if (btn) btn.disabled = true;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            fetch(this.action, {
                method:  'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     csrf,
                    'Accept':           'application/json',
                    'Content-Type':     'application/x-www-form-urlencoded',
                },
                body: '_token=' + encodeURIComponent(csrf),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + (data.message || 'Copy returned successfully!'));
                    setTimeout(() => location.reload(), 600);
                } else {
                    alert('❌ ' + (data.message || 'Return failed. Please try again.'));
                    if (btn) btn.disabled = false;
                }
            })
            .catch(err => {
                console.error('[CopySticker] Return error:', err);
                alert('❌ Network error. Please try again.');
                if (btn) btn.disabled = false;
            });
        });
    }

    // ── 6. Checkbox guard on the return button ────────────────────────────
    (function () {
        const cb  = document.getElementById('quickReturnCheckbox');
        const btn = document.getElementById('quickReturnBtn');
        if (!cb || !btn) return;
        cb.addEventListener('change', function () {
            btn.disabled = !this.checked;
        });
    }());

    // ── 7. Barcode scanner listener ───────────────────────────────────────
    //
    // Listens for keyboard input on this page. Scanners type fast (< 50 ms).
    // When the scanned string matches THIS copy's barcode, open the
    // appropriate modal (borrow or return).
    let barcodeBuffer = '';
    let lastKeyTime   = 0;

    document.addEventListener('keydown', function (event) {
        const now = Date.now();

        if (now - lastKeyTime > 100) {
            barcodeBuffer = '';
        }
        lastKeyTime = now;

        if (event.key === 'Enter') {
            const scanned = barcodeBuffer.trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
            barcodeBuffer = '';

            if (scanned && scanned === '<?php echo e($copyBarcode); ?>') {
                handleBarcodeScan();
            }
            return;
        }

        if (event.key.length === 1) {
            barcodeBuffer += event.key;
        }
    });

});

// ── handleBarcodeScan ─────────────────────────────────────────────────────
// Opens the borrow modal when the copy is available,
// or the return modal when it is currently borrowed.
function handleBarcodeScan() {
    const isCopyAvailable = <?php echo e($isCopyAvail ? 'true' : 'false'); ?>;

    if (isCopyAvailable) {
        new bootstrap.Modal(document.getElementById('bookInfoModal')).show();
    } else {
        new bootstrap.Modal(document.getElementById('returnModal')).show();
    }
}

// ── downloadBarcode ───────────────────────────────────────────────────────
function downloadBarcode() {
    const svg     = document.getElementById('barcode');
    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas  = document.createElement('canvas');
    const ctx     = canvas.getContext('2d');
    const img     = new Image();

    img.onload = function () {
        canvas.width  = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        const pngFile      = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        downloadLink.download = 'barcode-<?php echo e($book->id); ?>-<?php echo e($copy->copy_number ?? $copy->id); ?>.png';
        downloadLink.href = pngFile;
        downloadLink.click();
    };

    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
}

window.handleBarcodeScan = handleBarcodeScan;
</script>
</body>
</html><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/books/copy-sticker.blade.php ENDPATH**/ ?>