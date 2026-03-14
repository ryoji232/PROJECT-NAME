<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Book Barcode - <?php echo e($book->title); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .print-button {
            background: #007bff;
        }
        .download-button {
            background: #28a745;
        }
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
        
        /* Modal Styles */
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

        .status-available {
            color: #198754;
            font-weight: bold;
        }

        .status-borrowed {
            color: #dc3545;
            font-weight: bold;
        }

        @media print {
            .button-group {
                display: none;
            }
            .book-info-modal,
            .return-modal {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    ✅ <?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if(session('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    ❌ <?php echo e(session('error')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
    <div class="sticker">
        <h3><?php echo e(Str::limit(e($book->title), 40)); ?></h3>
        <p><strong>Author:</strong> <?php echo e(Str::limit(e($book->author), 25)); ?></p>
        
        <div class="barcode-box">
            <svg id="barcode"></svg>
            <div class="barcode-number">ID: <?php echo e($book->id); ?></div>
        </div>
        
        <div class="book-info">
            <p><strong>Copies:</strong> <?php echo e($book->copies); ?> | <strong>Available:</strong> <?php echo e($book->available_copies); ?></p>
            <p><strong>Status:</strong> 
                <span class="<?php echo e($book->available_copies > 0 ? 'status-available' : 'status-borrowed'); ?>">
                    <?php echo e($book->available_copies > 0 ? 'Available' : 'Borrowed'); ?>

                </span>
            </p>
        </div>
        
        <p class="scan-instruction">
            Scan to <?php echo e($book->available_copies > 0 ? 'borrow' : 'return'); ?> this book<br>at library desk
        </p>

        <div class="button-group">
            <button class="print-button" onclick="window.print()">Print</button>
            <button class="download-button" onclick="downloadBarcode()">Save</button>
        </div>
    </div>

    <!-- Book Information Modal (For Available Books) -->
    <div class="modal fade book-info-modal" id="bookInfoModal" tabindex="-1" aria-labelledby="bookInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookInfoModalLabel">Book Information</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="bookInfoContent">
                    <div class="book-info-card">
                        <h4 class="book-info-title"><?php echo e($book->title); ?></h4>
                        <p class="text-muted"><strong>Author:</strong> <?php echo e($book->author); ?></p>
                        <div class="row mt-3">
                            <div class="col-6">
                                <span class="badge bg-success">Available: <?php echo e($book->available_copies); ?></span>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-info">Total: <?php echo e($book->copies); ?></span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Book ID: <?php echo e($book->id); ?></small>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success">Available for Borrow</span>
                        </div>
                        
                        <!-- Quick Borrow Form -->
                        <div class="mt-4 p-3 border rounded">
                            <h6 class="mb-3">Quick Borrow</h6>
                            <form class="borrow-form" data-book-id="<?php echo e($book->id); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="book_id" value="<?php echo e($book->id); ?>">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <input type="text" name="student_name" class="form-control form-control-sm" placeholder="Your Name" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="course" class="form-control form-control-sm" placeholder="Course" required>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="section" class="form-control form-control-sm" placeholder="Section" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success btn-sm w-100 borrow-btn">
                                            BORROW THIS BOOK
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="<?php echo e(route('books.index')); ?>?highlight_book=<?php echo e($book->id); ?>" class="btn btn-primary">View in Books</a>
                    <button type="button" class="btn btn-success" onclick="window.print()">Print Barcode</button>
                </div>
            </div>
        </div>
    </div>

  <!-- Return Confirmation Modal (For Borrowed Books) -->
<div class="modal fade return-modal" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="returnModalLabel">Confirm Book Return</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="returnContent">
                <div class="return-card">
                    <div class="text-center mb-4">
                        <h4 class="book-info-title"><?php echo e($book->title); ?></h4>
                        <p class="text-muted">by <?php echo e($book->author); ?></p>
                    </div>
                    
                    <div class="borrower-info">
                        <h6 class="fw-bold text-success">Book Information</h6>
                        <p class="mb-1"><strong>Book ID:</strong> <?php echo e($book->id); ?></p>
                        <p class="mb-1"><strong>Status:</strong> Currently Borrowed</p>
                        <p class="mb-0"><strong>Available Copies:</strong> <?php echo e($book->available_copies); ?> / <?php echo e($book->copies); ?></p>
                    </div>
                    
                    <div class="alert alert-warning mt-3">
                        <strong>Please verify:</strong> Ensure the physical book is being returned before confirming.
                    </div>

                    <!-- Simple POST form -->
                    <form action="<?php echo e(route('books.quick-return', $book->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                Confirm Book Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="<?php echo e(route('books.index')); ?>?highlight_book=<?php echo e($book->id); ?>" class="btn btn-primary">View in Books</a>
            </div>
        </div>
    </div>
</div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Generate barcode for current book
    JsBarcode("#barcode", "<?php echo e($book->id); ?>", {
        format: "CODE128",
        width: 2,
        height: 50,
        displayValue: false,
        margin: 10,
        background: "#f8f9fa",
        lineColor: "#000000"
    });

    // Check if we should open the modal automatically
    const urlParams = new URLSearchParams(window.location.search);
    const showModal = urlParams.get('show_modal');
    
    if (showModal === 'true') {
        handleBarcodeScan("<?php echo e($book->id); ?>");
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }

    // Handle borrow form submission
    const borrowForm = document.querySelector('.borrow-form');
    if (borrowForm) {
        borrowForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const bookId = this.dataset.bookId;
            const formData = new FormData(this);
            const borrowBtn = this.querySelector('.borrow-btn');
            
            borrowBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Borrowing...';
            borrowBtn.disabled = true;
            
            fetch("<?php echo e(route('borrow.store')); ?>", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('bookInfoModal'));
                    modal.hide();
                    // Refresh the page to update status
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('❌ ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred while borrowing the book.');
            })
            .finally(() => {
                borrowBtn.innerHTML = 'BORROW THIS BOOK';
                borrowBtn.disabled = false;
            });
        });
    }

    // Barcode scanner detection
    let barcodeBuffer = '';
    let lastKeyTime = 0;
    
    document.addEventListener('keydown', function(event) {
        const currentTime = new Date().getTime();
        
        if (currentTime - lastKeyTime > 100) {
            barcodeBuffer = '';
        }
        
        lastKeyTime = currentTime;
        
        if (event.key !== 'Enter') {
            barcodeBuffer += event.key;
        }
        
        if (event.key === 'Enter' && barcodeBuffer.length > 0) {
            const scannedBookId = barcodeBuffer.trim();
            barcodeBuffer = '';
            
            if (scannedBookId === "<?php echo e($book->id); ?>") {
                handleBarcodeScan(scannedBookId);
            }
        }
    });
});

function handleBarcodeScan(bookId) {
    // Check current book status and show appropriate modal
    const isAvailable = <?php echo e($book->available_copies > 0 ? 'true' : 'false'); ?>;
    
    if (isAvailable) {
        openBookInfoModal();
    } else {
        openReturnModal();
    }
}

function openBookInfoModal() {
    const bookInfoModal = new bootstrap.Modal(document.getElementById('bookInfoModal'));
    bookInfoModal.show();
}

function openReturnModal() {
    // Simply show the modal - no API calls needed
    const returnModal = new bootstrap.Modal(document.getElementById('returnModal'));
    returnModal.show();
}

function downloadBarcode() {
    const svg = document.getElementById('barcode');
    const svgData = new XMLSerializer().serializeToString(svg);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();
    
    img.onload = function() {
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        const pngFile = canvas.toDataURL('image/png');
        const downloadLink = document.createElement('a');
        downloadLink.download = `barcode-<?php echo e($book->id); ?>.png`;
        downloadLink.href = pngFile;
        downloadLink.click();
    };
    
    img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
}

// Global function to handle barcode scan from external scanners
window.handleBarcodeScan = handleBarcodeScan;

    // Listen for custom barcode scan events
    window.addEventListener('barcodeScanned', function(event) {
        if (event.detail && event.detail.bookId) {
            window.handleBarcodeScan(event.detail.bookId);
        }
    });
    </script>
</body>
</html><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/books/barcode-sticker.blade.php ENDPATH**/ ?>