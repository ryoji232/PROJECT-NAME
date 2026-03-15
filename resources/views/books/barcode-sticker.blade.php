<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Book Barcode - {{ $book->title }}</title>
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

        /* ── Copy selector ── */
        .copy-selector-wrap {
            width: 280px;
            margin: 16px auto 0;
            text-align: left;
        }
        .copy-selector-label {
            font-size: 11px;
            font-weight: 700;
            color: #555;
            margin-bottom: 8px;
            display: block;
            text-align: center;
        }
        .copy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }
        .copy-btn {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 8px 10px;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            text-align: left;
            width: 100%;
            font-family: inherit;
        }
        .copy-btn:hover    { border-color: #0d6efd; background: #e8f0fe; }
        .copy-btn.selected { border-color: #0d6efd; background: #e8f0fe;
                             box-shadow: 0 0 0 2px rgba(13,110,253,.18); }
        .cn  { font-weight: 700; font-size: 11px; color: #1a1a2e; margin-bottom: 2px; }
        .cb  { font-family: monospace; font-size: 10px; color: #6c757d;
               margin-bottom: 4px; letter-spacing: .3px; }
        .cs  { font-size: 10px; font-weight: 600; padding: 1px 7px; border-radius: 20px; }
        .badge-available { background: #d4edda; color: #155724; }
        .badge-borrowed  { background: #fff3cd; color: #856404; }
        .badge-damaged   { background: #f8d7da; color: #721c24; }
        .copy-loading {
            font-size: 11px; color: #888;
            text-align: center; padding: 10px 0;
        }
        .copy-error {
            font-size: 11px; color: #856404;
            background: #fff3cd; border-radius: 6px;
            padding: 8px; text-align: center;
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
        .status-available { color: #198754; font-weight: bold; }
        .status-borrowed  { color: #dc3545; font-weight: bold; }

        @media print {
            .button-group       { display: none; }
            .copy-selector-wrap { display: none; }
            .book-info-modal,
            .return-modal       { display: none !important; }
        }
    </style>
</head>
<body>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        ❌ {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- ── Sticker — always visible, same as original ── --}}
    <div class="sticker">
        <h3>{{ Str::limit(e($book->title), 40) }}</h3>
        <p><strong>Author:</strong> {{ Str::limit(e($book->author), 25) }}</p>

        <div class="barcode-box">
            <svg id="barcode"></svg>
            {{-- Shows book ID until a copy is selected, then switches to copy barcode --}}
            <div class="barcode-number" id="barcodeLabel">ID: {{ $book->id }}</div>
        </div>

        <div class="book-info">
            <p><strong>Copies:</strong> {{ $book->copies }} | <strong>Available:</strong> {{ $book->available_copies }}</p>
            <p id="copyLine" style="display:none;"><strong>Copy:</strong> <span id="copyLineName"></span></p>
            <p><strong>Status:</strong>
                <span id="stickerStatus" class="{{ $book->available_copies > 0 ? 'status-available' : 'status-borrowed' }}">
                    {{ $book->available_copies > 0 ? 'Available' : 'Borrowed' }}
                </span>
            </p>
        </div>

        <p class="scan-instruction" id="scanInstruction">
            Scan to {{ $book->available_copies > 0 ? 'borrow' : 'return' }} this book<br>at library desk
        </p>

        <div class="button-group">
            <button class="print-button" onclick="window.print()">Print</button>
            <button class="download-button" onclick="downloadBarcode()">Save</button>
        </div>
    </div>

    {{-- ── Copy selector — sits below sticker, hidden at print time ── --}}
    <div class="copy-selector-wrap">
        <span class="copy-selector-label">📋 Select a copy to update the barcode:</span>
        <div id="copyGrid">
            <div class="copy-loading">
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                Loading copies…
            </div>
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
                        <h4 class="book-info-title">{{ $book->title }}</h4>
                        <p class="text-muted"><strong>Author:</strong> {{ $book->author }}</p>
                        <div class="row mt-3">
                            <div class="col-6">
                                <span class="badge bg-success">Available: {{ $book->available_copies }}</span>
                            </div>
                            <div class="col-6">
                                <span class="badge bg-info">Total: {{ $book->copies }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Book ID: {{ $book->id }}</small>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-success">Available for Borrow</span>
                        </div>
                        <!-- Quick Borrow Form -->
                        <div class="mt-4 p-3 border rounded">
                            <h6 class="mb-3">Quick Borrow</h6>
                            <form class="borrow-form" data-book-id="{{ $book->id }}">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
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
                    <a href="{{ route('books.index') }}?highlight_book={{ $book->id }}" class="btn btn-primary">View in Books</a>
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
                            <h4 class="book-info-title">{{ $book->title }}</h4>
                            <p class="text-muted">by {{ $book->author }}</p>
                        </div>
                        <div class="borrower-info">
                            <h6 class="fw-bold text-success">Book Information</h6>
                            <p class="mb-1"><strong>Book ID:</strong> {{ $book->id }}</p>
                            <p class="mb-1"><strong>Status:</strong> Currently Borrowed</p>
                            <p class="mb-0"><strong>Available Copies:</strong> {{ $book->available_copies }} / {{ $book->copies }}</p>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <strong>Please verify:</strong> Ensure the physical book is being returned before confirming.
                        </div>
                        <form action="{{ route('books.quick-return', $book->id) }}" method="POST">
                            @csrf
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
                    <a href="{{ route('books.index') }}?highlight_book={{ $book->id }}" class="btn btn-primary">View in Books</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        'use strict';

        // ── Shared state ─────────────────────────────────────────────────────────
        // All functions in this IIFE share these variables directly — no closures
        // crossing scope boundaries, no setter/getter pattern needed.
        var bookId               = {{ $book->id }};
        var activeStickerBarcode = String(bookId); // updated by applySelectedCopy()
        var activeCopyStatus     = '{{ $book->available_copies > 0 ? "available" : "borrowed" }}';

        // ── Helpers ───────────────────────────────────────────────────────────────
        function escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function ucFirst(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        }

        function normalizeBarcode(val) {
            return String(val).toUpperCase().replace(/[^A-Z0-9]/g, '');
        }

        // ── Modal openers ─────────────────────────────────────────────────────────
        function openBookInfoModal() {
            var el   = document.getElementById('bookInfoModal');
            var inst = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            inst.show();
        }

        function openReturnModal() {
            var el   = document.getElementById('returnModal');
            var inst = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
            inst.show();
        }

        function handleBarcodeScan() {
            if (activeCopyStatus === 'available') {
                openBookInfoModal();
            } else {
                openReturnModal();
            }
        }

        // ── Apply the selected copy to the sticker ────────────────────────────────
        function applySelectedCopy(copy) {
            var barcodeValue = normalizeBarcode(copy.normalized_barcode || copy.barcode || '');
            var isAvailable  = copy.status === 'available';

            // Update shared state so the scanner listener matches the new barcode
            activeStickerBarcode = barcodeValue;
            activeCopyStatus     = copy.status;

            // Render barcode graphic
            if (barcodeValue) {
                try {
                    JsBarcode('#barcode', barcodeValue, {
                        format:       'CODE128',
                        width:        2,
                        height:       50,
                        displayValue: false,
                        margin:       10,
                        background:   '#f8f9fa',
                        lineColor:    '#000000'
                    });
                } catch (e) {
                    console.warn('JsBarcode error for "' + barcodeValue + '":', e);
                }
            }

            // Update label, copy line, status text, scan instruction
            document.getElementById('barcodeLabel').textContent =
                barcodeValue || copy.barcode;

            var copyLine = document.getElementById('copyLine');
            copyLine.style.display = '';
            document.getElementById('copyLineName').textContent =
                copy.copy_number || ('Copy ' + copy.id);

            var statusEl = document.getElementById('stickerStatus');
            statusEl.textContent = isAvailable ? 'Available' : ucFirst(copy.status);
            statusEl.className   = isAvailable ? 'status-available' : 'status-borrowed';

            document.getElementById('scanInstruction').innerHTML =
                'Scan to ' + (isAvailable ? 'borrow' : 'return') + ' this copy<br>at library desk';
        }

        // ── Copy grid renderer ─────────────────────────────────────────────────────
        function renderCopyGrid(copies, container) {
            var gridEl = document.createElement('div');
            gridEl.className = 'copy-grid';

            copies.forEach(function (copy) {
                var badgeCls = copy.status === 'available' ? 'badge-available'
                             : copy.status === 'borrowed'  ? 'badge-borrowed'
                             : 'badge-damaged';

                var btn       = document.createElement('button');
                btn.type      = 'button';
                btn.className = 'copy-btn';
                var displayBarcode = normalizeBarcode(copy.normalized_barcode || copy.barcode || '');
                btn.innerHTML =
                    '<span class="cn">' + escHtml(copy.copy_number || ('Copy ' + copy.id)) + '</span>' +
                    '<span class="cb">' + escHtml(displayBarcode) + '</span>' +
                    '<span class="cs ' + badgeCls + '">' + ucFirst(copy.status) + '</span>';

                btn.addEventListener('click', function () {
                    gridEl.querySelectorAll('.copy-btn').forEach(function (b) {
                        b.classList.remove('selected');
                    });
                    btn.classList.add('selected');
                    applySelectedCopy(copy);
                });

                gridEl.appendChild(btn);
            });

            container.innerHTML = '';
            container.appendChild(gridEl);

            // Auto-select the first available copy, or the first copy overall
            var autoSelect = copies.find(function (c) { return c.status === 'available'; }) || copies[0];
            var autoIdx    = copies.indexOf(autoSelect);
            var buttons    = gridEl.querySelectorAll('.copy-btn');
            if (buttons[autoIdx]) {
                buttons[autoIdx].click();
            }
        }

        // ── Download barcode PNG ───────────────────────────────────────────────────
        function downloadBarcode() {
            var svg     = document.getElementById('barcode');
            var svgData = new XMLSerializer().serializeToString(svg);
            var canvas  = document.createElement('canvas');
            var ctx     = canvas.getContext('2d');
            var img     = new Image();
            img.onload = function () {
                canvas.width  = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                var link      = document.createElement('a');
                link.download = 'barcode-{{ $book->id }}.png';
                link.href     = canvas.toDataURL('image/png');
                link.click();
            };
            img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
        }

        // ── Expose download to inline onclick ─────────────────────────────────────
        window.downloadBarcode = downloadBarcode;

        // ── DOMContentLoaded ──────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {

            // Render the initial book-level barcode so the sticker shows immediately
            JsBarcode('#barcode', String(bookId), {
                format:       'CODE128',
                width:        2,
                height:       50,
                displayValue: false,
                margin:       10,
                background:   '#f8f9fa',
                lineColor:    '#000000'
            });

            // Load copies and render the selector grid
            fetch('/books/' + bookId + '/copies', { credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    var container = document.getElementById('copyGrid');
                    if (!data.success || !data.copies || data.copies.length === 0) {
                        container.innerHTML = '<div class="copy-error">⚠️ No copies found. Use Repair from the Books page first.</div>';
                        return;
                    }
                    renderCopyGrid(data.copies, container);
                })
                .catch(function (err) {
                    document.getElementById('copyGrid').innerHTML =
                        '<div class="copy-error">⚠️ Could not load copies: ' + escHtml(err.message) + '</div>';
                });

            // Borrow form submit handler
            var borrowForm = document.querySelector('.borrow-form');
            if (borrowForm) {
                borrowForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var formData  = new FormData(this);
                    var borrowBtn = this.querySelector('.borrow-btn');
                    borrowBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Borrowing...';
                    borrowBtn.disabled  = true;
                    fetch("{{ route('borrow.store') }}", {
                        method:  'POST',
                        body:    formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN':     '{{ csrf_token() }}'
                        }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            alert('✅ ' + data.message);
                            var m = bootstrap.Modal.getInstance(document.getElementById('bookInfoModal'));
                            if (m) m.hide();
                            setTimeout(function () { location.reload(); }, 1000);
                        } else {
                            alert('❌ ' + data.message);
                        }
                    })
                    .catch(function () { alert('❌ An error occurred while borrowing the book.'); })
                    .finally(function () {
                        borrowBtn.innerHTML = 'BORROW THIS BOOK';
                        borrowBtn.disabled  = false;
                    });
                });
            }

            // URL param: auto-open modal on page load
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('show_modal') === 'true') {
                handleBarcodeScan();
                window.history.replaceState({}, document.title, window.location.pathname);
            }

            // ── Barcode scanner key listener ──────────────────────────────────────
            // Hardware scanners type characters very fast then send Enter.
            // We accumulate keystrokes in barcodeBuffer and process on Enter.
            // activeStickerBarcode (shared state above) always holds the barcode
            // currently shown on the sticker — updated by applySelectedCopy().
            var barcodeBuffer = '';
            var lastKeyTime   = 0;

            document.addEventListener('keydown', function (event) {
                var now = Date.now();

                // Gap > 100 ms between keys = new scan burst; reset the buffer
                if (now - lastKeyTime > 100) {
                    barcodeBuffer = '';
                }
                lastKeyTime = now;

                if (event.key === 'Enter') {
                    if (barcodeBuffer.length > 0) {
                        var scanned   = normalizeBarcode(barcodeBuffer);
                        barcodeBuffer = '';

                        if (scanned.length > 0 && scanned === activeStickerBarcode) {
                            handleBarcodeScan();
                        }
                    }
                } else if (event.key.length === 1) {
                    // Only accumulate printable characters
                    barcodeBuffer += event.key;
                }
            });

        }); // end DOMContentLoaded

        // ── Global hook for barcodeScanned custom event ───────────────────────────
        window.addEventListener('barcodeScanned', function (event) {
            if (event.detail && event.detail.bookId) {
                handleBarcodeScan();
            }
        });

    })(); // end IIFE
    </script>
</body>
</html>