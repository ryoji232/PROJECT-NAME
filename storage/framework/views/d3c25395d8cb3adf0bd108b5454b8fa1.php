<?php $__env->startSection('title', 'Books'); ?>

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
    }

    .book-card { margin-bottom: 1.5rem; }

    .book-card-inner {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 1.4rem 1.5rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: box-shadow 0.2s, transform 0.2s, border-color 0.2s;
        cursor: pointer;
    }
    .book-card-inner:hover {
        box-shadow: 0 6px 20px rgba(25,135,84,0.22);
        transform: translateY(-3px);
        border-color: #198754;
    }

    /* Subtle "tap to open" hint at bottom of card */
    .card-click-hint {
        margin-top: .85rem;
        text-align: center;
        font-size: .78rem;
        font-weight: 600;
        color: #000000;
        letter-spacing: .4px;
        opacity: .7;
        user-select: none;
    }
    .book-card-inner:hover .card-click-hint { opacity: 1; }

    .book-title {
        color: #00402c;
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 0.15em;
        line-height: 1.3;
    }

    .badge.bg-success   { background:#198754!important; font-size:.85rem; border-radius:.5rem; padding:.35em .75em; }
    .badge.bg-secondary { background:#6c757d!important; font-size:.85rem; border-radius:.5rem; padding:.35em .75em; }
    .badge.bg-warning   { background:#ffc107!important; color:#212529!important; font-size:.85rem; border-radius:.5rem; padding:.35em .75em; }

    .btn-primary, .btn-success {
        background: #198754; border: none; border-radius: 8px;
        font-weight: 600; transition: all .2s; padding: 10px 18px; color: #fff;
    }
    .btn-primary:hover, .btn-success:hover { background:#157347; transform:scale(1.03); color:#fff; }

    .btn-warning {
        background: #ffc107; border: none; border-radius: 8px;
        font-weight: 600; transition: all .2s; padding: 10px 18px; color: #212529;
    }
    .btn-warning:hover { background:#ffb300; transform:scale(1.03); }

    .btn-danger {
        background: #dc3545; border: none; border-radius: 8px;
        font-weight: 600; transition: all .2s; padding: 10px 18px; color: #fff;
    }
    .btn-danger:hover { background:#c82333; transform:scale(1.03); }

    .btn-light {
        background: #f8f9fa; border: none; border-radius: 8px;
        font-weight: 600; transition: all .2s; padding: 10px 18px; color: #212529;
    }
    .btn-light:hover { background:#e2e6ea; transform:scale(1.03); }

    .btn-info-custom {
        background: #0dcaf0; border: none; border-radius: 8px;
        font-weight: 600; transition: all .2s; padding: 10px 18px; color: #fff;
    }
    .btn-info-custom:hover { background:#0ba9cc; transform:scale(1.03); color:#fff; }

    .form-control {
        border-radius: 8px; padding: 10px 14px; font-size: .95rem;
        border: 1px solid #dee2e6; background: #fff; color: #00402c;
    }
    .form-control:focus { border-color:#198754; box-shadow:0 0 0 .2rem rgba(25,135,84,.25); }

    .add-book-form {
        background: #fff; border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 1.5rem; margin-bottom: 2rem;
    }

    .search-box {
        background: #fff; border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 1rem; margin-bottom: 1.5rem;
    }

    h2 { color: #00402c; font-weight: 700; margin-bottom: 1.5rem; }

    .modal-content {
        border-radius: 1rem; border: 1px solid #dee2e6; background: #fff; color: #00402c;
    }
    .modal-header {
        background: #198754; color: #fff;
        border-radius: 1rem 1rem 0 0; border-bottom: none;
    }
    .modal-title { font-weight: 700; }

    .show-modal-header-sub { font-size: .88rem; opacity: .85; margin-top: .15rem; }
    .show-modal-meta { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; margin-bottom: 1.2rem; }
    .show-modal-divider { border: none; border-top: 1px solid #dee2e6; margin: 1.1rem 0; }
    .action-section-label {
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .6px; color: #6c757d; margin-bottom: .6rem;
    }

    .history-list-item { border-bottom: 1px solid #e9ecef; padding: 0.9rem 0.25rem; font-size: 0.9rem; }
    .history-list-item:last-child { border-bottom: none; }

    .history-status-returned {
        display: inline-block; background: #d1e7dd; color: #0a3622;
        border: 1px solid #a3cfbb; border-radius: 0.4rem;
        padding: 0.2rem 0.5rem; font-size: 0.78rem; font-weight: 600;
    }
    .history-status-active {
        display: inline-block; background: #fff3cd; color: #856404;
        border: 1px solid #ffeaa7; border-radius: 0.4rem;
        padding: 0.2rem 0.5rem; font-size: 0.78rem; font-weight: 600;
    }
    .history-status-overdue {
        display: inline-block; background: #f8d7da; color: #721c24;
        border: 1px solid #f5c2c7; border-radius: 0.4rem;
        padding: 0.2rem 0.5rem; font-size: 0.78rem; font-weight: 600;
    }

    .history-summary-bar {
        display: flex; gap: 0.75rem; flex-wrap: wrap;
        margin-bottom: 1rem; padding: 0.75rem 1rem;
        background: #f8f9fa; border-radius: 0.6rem; font-size: 0.85rem;
    }
    .history-summary-bar span { font-weight: 700; }

    .list-group-item {
        background: transparent; border: none;
        border-bottom: 1px solid #e3e3e3; color: #333;
        font-size: 1rem; padding: 1rem 1.2rem;
    }
    .list-group-item:last-child { border-bottom: none; }
    .list-group-item strong { color: #198754; }

    .text-muted   { color: #6c757d !important; }
    .text-primary { color: #198754 !important; }

    @media (max-width: 767px) {
        .hero-section { padding: 2rem 1rem; }
        .modal-content { border-radius: .7rem; }
    }
</style>

<!-- Hero -->
<div class="hero-section">
    <h1>Book Resource Library</h1>
    <p>Borrow Today, Learn for a Lifetime</p>
</div>

<!-- Add Book -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Add a New Book</h2>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-light">⬅ Dashboard</a>
</div>

<form action="<?php echo e(route('books.store')); ?>" method="POST" class="add-book-form">
    <?php echo csrf_field(); ?>
    <div class="row g-3">
        <div class="col-md-4">
            <input type="text" name="title" class="form-control" placeholder="Book Title" required>
        </div>
        <div class="col-md-4">
            <input type="text" name="author" class="form-control" placeholder="Author" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="copies" class="form-control" placeholder="Number of copies (max 10)" min="1" max="10" oninput="if(this.value>10)this.value=10;">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Add Book</button>
        </div>
    </div>
</form>

<!-- Books List -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 id="books">Available Books</h2>
    <div class="search-box">
        <input type="text" id="bookSearch" class="form-control" placeholder="🔍 Search by title...">
    </div>
</div>

<div class="row g-3" id="availableBooks">
    <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $notReturnedCount = $book->borrowings->whereNull('returned_at')->count(); ?>

        
        <div class="col-md-4 book-card" data-book-id="<?php echo e($book->id); ?>" id="book-card-<?php echo e($book->id); ?>">
            <div class="book-card-inner"
                 data-bs-toggle="modal"
                 data-bs-target="#showModal<?php echo e($book->id); ?>">
                <div>
                    <h5 class="book-title"><?php echo e($book->title); ?></h5>
                    <small class="text-muted">by <?php echo e($book->author); ?></small>
                    <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                        <?php if($book->available_copies > 0): ?>
                            <span class="badge bg-success available-badge" id="available-badge-<?php echo e($book->id); ?>">
                                Available: <?php echo e($book->available_copies); ?>

                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary available-badge" id="available-badge-<?php echo e($book->id); ?>">
                                Fully Borrowed
                            </span>
                        <?php endif; ?>
                        <span class="badge bg-warning" id="in-use-badge-<?php echo e($book->id); ?>"
                              style="<?php echo e($notReturnedCount < 1 ? 'display:none;' : ''); ?>">
                            In Use: <span id="in-use-count-<?php echo e($book->id); ?>"><?php echo e($notReturnedCount); ?></span>
                        </span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size:.75rem;">ID: <?php echo e($book->id); ?></small>
                </div>
                <div class="card-click-hint">Click to view details</div>
            </div>
        </div>

        
        <div class="modal fade" id="showModal<?php echo e($book->id); ?>" tabindex="-1"
             aria-labelledby="showModalLabel<?php echo e($book->id); ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="showModalLabel<?php echo e($book->id); ?>"><?php echo e($book->title); ?></h5>
                            <div class="show-modal-header-sub">by <?php echo e($book->author); ?></div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="show-modal-meta">
                            <?php if($book->available_copies > 0): ?>
                                <span class="badge bg-success">Available: <?php echo e($book->available_copies); ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Fully Borrowed</span>
                            <?php endif; ?>
                            <span class="badge bg-warning">
                                In Use: <span class="modal-in-use-<?php echo e($book->id); ?>"><?php echo e($notReturnedCount); ?></span>
                            </span>
                            <span class="badge" style="background:#e0f2e9;color:#00402c;border:1px solid #c3e6cb;">
                                Total Copies: <?php echo e($book->copies); ?>

                            </span>
                        </div>

                        <hr class="show-modal-divider">
                        <div class="action-section-label">Borrow This Book</div>

                        <?php if($book->available_copies < 1): ?>
                            <button class="btn btn-secondary w-100 mb-3" disabled>ALL COPIES BORROWED</button>
                        <?php else: ?>
                            <form class="borrow-form mb-3" data-book-id="<?php echo e($book->id); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="book_id" value="<?php echo e($book->id); ?>">
                                <div class="d-flex flex-column gap-2">
                                    <input type="text" name="student_name" class="form-control form-control-sm"
                                           placeholder="Student Name" required>
                                    <input type="text" name="course" class="form-control form-control-sm"
                                           placeholder="Course" required>
                                    <input type="text" name="section" class="form-control form-control-sm"
                                           placeholder="Section" required>
                                    <button type="submit" class="btn btn-success btn-sm w-100 borrow-btn"
                                            data-book-id="<?php echo e($book->id); ?>">
                                        BORROW
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <hr class="show-modal-divider">
                        <div class="action-section-label">Book Actions</div>
                        <div class="d-flex flex-column gap-2">
                            <button class="btn btn-warning btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#historyModal<?php echo e($book->id); ?>"
                                    data-book-id="<?php echo e($book->id); ?>">
                                IN USE (<span id="modal-in-use-count-<?php echo e($book->id); ?>"><?php echo e($notReturnedCount); ?></span>) — View History
                            </button>
                            <button type="button" class="btn-info-custom btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#copiesModal<?php echo e($book->id); ?>">
                                VIEW COPIES &amp; BARCODES
                            </button>
                            <a href="<?php echo e(route('books.edit', $book->id)); ?>" class="btn btn-primary btn-sm w-100">
                                EDIT BOOK
                            </a>
                            <form action="<?php echo e(route('books.destroy', $book->id)); ?>" method="POST" class="delete-form">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm w-100"
                                        onclick="return confirm('Are you sure you want to delete this book?');">
                                    DELETE BOOK
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="modal fade" id="historyModal<?php echo e($book->id); ?>" tabindex="-1"
             aria-labelledby="historyModalLabel<?php echo e($book->id); ?>" aria-hidden="true"
             data-book-id="<?php echo e($book->id); ?>">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">📋 Full Borrow History — <?php echo e($book->title); ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="historyModalBody<?php echo e($book->id); ?>">
                        <div class="text-center py-4">
                            <div class="spinner-border text-success" role="status"></div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <a href="<?php echo e(route('book-history.index')); ?>?search=<?php echo e(urlencode($book->title)); ?>"
                           target="_blank" class="btn btn-light btn-sm">Open Full History Page ↗</a>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="modal fade" id="copiesModal<?php echo e($book->id); ?>" tabindex="-1"
             aria-labelledby="copiesModalLabel<?php echo e($book->id); ?>" aria-hidden="true"
             data-book-id="<?php echo e($book->id); ?>">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Copies — <?php echo e($book->title); ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="copiesModalBody<?php echo e($book->id); ?>">
                        <div class="text-center">
                            <div class="spinner-border" role="status"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<!-- Return Confirmation Modal -->
<div class="modal fade" id="barcodeReturnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Book Return</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div style="font-size:3rem;">📖</div>
                    <h4 id="returnBookTitle" class="text-primary fw-bold mt-2"></h4>
                    <p id="returnBookAuthor" class="text-muted"></p>
                </div>
                <div class="p-3 rounded" style="background:#f8f9fa;">
                    <h6 class="fw-bold text-success">Borrower Information</h6>
                    <p class="mb-1"><strong>Name:</strong> <span id="borrowerNameText"></span></p>
                    <p class="mb-1"><strong>Course &amp; Section:</strong> <span id="borrowerCourseText"></span></p>
                    <p class="mb-0"><strong>Due Date:</strong> <span id="dueDateText"></span></p>
                </div>
                <div class="alert alert-warning mt-3">
                    ⚠ Please verify the physical book is being returned before confirming.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="barcodeReturnForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success">Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);

    /* ── Highlight from URL ── */
    const highlightBookId = urlParams.get('highlight_book');
    if (highlightBookId) {
        highlightBookCard(highlightBookId);
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    /* ── Toastr ── */
    if (typeof toastr !== 'undefined') {
        toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "5000" };
    }

    /* ── Modal open → load data ── */
    document.addEventListener('show.bs.modal', function (e) {
        const modal  = e.target;
        const bookId = modal.getAttribute('data-book-id');

        /* History */
        if (bookId && modal.id.startsWith('historyModal')) {
            const body = document.getElementById(`historyModalBody${bookId}`);
            if (!body) return;
            body.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-success" role="status"></div><p class="mt-2 text-muted small">Loading…</p></div>`;

            fetch(`/books/${bookId}/history`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
            .then(data => {
                if (!data.success) { body.innerHTML = `<p class="text-danger text-center py-3">⚠ ${data.message ?? 'Error'}</p>`; return; }
                const records = data.history;
                const total = records.length, active = records.filter(r => !r.returned_at).length, returned = records.filter(r => r.returned_at).length;
                if (!total) { body.innerHTML = `<div class="text-center py-4 text-muted"><div style="font-size:2.5rem;opacity:.4">📭</div><p class="mt-2">No borrow history yet.</p></div>`; return; }

                let html = `<div class="history-summary-bar"><div>Total borrows: <span class="text-primary">${total}</span></div><div>Currently out: <span style="color:#856404">${active}</span></div><div>Returned: <span style="color:#0a3622">${returned}</span></div></div>`;
                html += `<canvas id="ajaxHistoryChart${bookId}" height="110" class="mb-3"></canvas><div>`;

                records.forEach(record => {
                    const fmt = iso => iso ? new Date(iso).toLocaleString('en-PH', { month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit', hour12:true }) : 'N/A';
                    const fmtDate = iso => iso ? new Date(iso).toLocaleDateString('en-PH', { month:'short', day:'numeric', year:'numeric' }) : 'N/A';
                    let badge = '', returnedLine = '';
                    if (record.returned_at) {
                        badge = `<span class="history-status-returned">✓ Returned</span>`;
                        returnedLine = `<div class="mt-1" style="color:#0a3622;font-size:.82rem;">↩ Returned: ${fmt(record.returned_at)}</div>`;
                    } else if (record.is_overdue) {
                        badge = `<span class="history-status-overdue">⚠ Overdue</span>`;
                    } else {
                        badge = `<span class="history-status-active">📖 Active</span>`;
                    }
                    html += `<div class="history-list-item"><div class="d-flex justify-content-between align-items-start"><div><strong style="color:#00402c">${escapeHtml(record.student_name)}</strong><div class="text-muted" style="font-size:.8rem;">${escapeHtml(record.course)} — ${escapeHtml(record.section)}</div></div>${badge}</div><div class="mt-1" style="font-size:.82rem;color:#495057;">📅 Borrowed: ${fmt(record.borrowed_at)} &nbsp;|&nbsp; Due: ${fmtDate(record.due_date)}</div>${returnedLine}</div>`;
                });
                html += `</div>`;
                body.innerHTML = html;

                const ctx = document.getElementById(`ajaxHistoryChart${bookId}`);
                if (ctx) new Chart(ctx, { type:'pie', data:{ labels:['Available','Currently Borrowed'], datasets:[{ data:[data.available_copies, active], backgroundColor:['#078b24','#ff4d4d'], borderColor:'#fff', borderWidth:2 }] }, options:{ responsive:true, plugins:{ legend:{ position:'bottom' }, title:{ display:true, text:'Available vs Currently Borrowed', font:{ size:13 } } } } });
            })
            .catch(err => { body.innerHTML = `<p class="text-danger text-center py-3">⚠ Error loading history.<br><small>${err.message}</small></p>`; });
        }

        /* Copies */
        if (bookId && modal.id.startsWith('copiesModal')) {
            const modalBody = document.getElementById(`copiesModalBody${bookId}`);
            fetch(`/books/${bookId}/copies`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.copies.length > 0) {
                    let html = '<div class="row gy-3">';
                    data.copies.forEach(copy => {
                        const sc = copy.status === 'available' ? 'success' : copy.status === 'borrowed' ? 'warning' : 'secondary';
                        html += `<div class="col-md-6 text-center"><div class="p-3 border rounded"><h6 class="mb-1">${copy.copy_number}</h6><p class="mb-2 small text-muted"><strong>Barcode:</strong> ${copy.normalized_barcode}</p><div class="mt-2 d-flex justify-content-center gap-2"><span class="badge bg-${sc}">${copy.status.charAt(0).toUpperCase()+copy.status.slice(1)}</span><a href="/books/${bookId}/copies/${copy.id}/print" target="_blank" class="btn btn-sm btn-outline-primary">Print</a></div></div></div>`;
                    });
                    html += '</div>';
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = '<p class="text-muted">No copies found for this book.</p>';
                }
            })
            .catch(() => { modalBody.innerHTML = '<p class="text-danger">Error loading copies.</p>'; });
        }
    });

    /* ── AJAX Borrow ── */
    document.addEventListener('submit', function (e) {
        if (!e.target.classList.contains('borrow-form')) return;
        e.preventDefault();
        const form = e.target, bookId = form.dataset.bookId, borrowBtn = form.querySelector('.borrow-btn');
        borrowBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Borrowing...';
        borrowBtn.disabled = true;

        fetch("<?php echo e(route('borrow.store')); ?>", {
            method: 'POST', body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateBookCard(bookId, data.book.available_copies, data.active_borrowings);
                showNotification('success', data.message);
                form.reset();
            } else { showNotification('error', data.message); }
        })
        .catch(() => showNotification('error', 'An error occurred.'))
        .finally(() => { borrowBtn.innerHTML = 'BORROW'; borrowBtn.disabled = false; });
    });

    function updateBookCard(bookId, availableCopies, activeBorrowings) {
        const badge = document.getElementById(`available-badge-${bookId}`);
        if (badge) {
            badge.textContent = availableCopies > 0 ? `Available: ${availableCopies}` : 'Fully Borrowed';
            badge.className = `badge ${availableCopies > 0 ? 'bg-success' : 'bg-secondary'} available-badge`;
        }
        const inUseBadge = document.getElementById(`in-use-badge-${bookId}`);
        const inUseCount = document.getElementById(`in-use-count-${bookId}`);
        if (inUseCount) inUseCount.textContent = activeBorrowings;
        if (inUseBadge) inUseBadge.style.display = activeBorrowings > 0 ? '' : 'none';
        document.querySelectorAll(`.modal-in-use-${bookId}, #modal-in-use-count-${bookId}`).forEach(el => el.textContent = activeBorrowings);
        if (availableCopies < 1) {
            const showModal = document.getElementById(`showModal${bookId}`);
            if (showModal) { const bf = showModal.querySelector('.borrow-form'); if (bf) bf.outerHTML = '<button class="btn btn-secondary w-100 mb-3" disabled>ALL COPIES BORROWED</button>'; }
        }
    }

    function showNotification(type, message) { typeof toastr !== 'undefined' ? toastr[type](message) : alert(message); }

    /* ── Search ── */
    const searchInput = document.getElementById("bookSearch");
    const booksGrid   = document.getElementById("availableBooks");
    if (searchInput && booksGrid) {
        const allCards = Array.from(booksGrid.querySelectorAll(".book-card"));
        searchInput.addEventListener("keyup", function () {
            const q = this.value.toLowerCase();
            allCards.forEach(card => {
                const titleEl = card.querySelector(".book-title");
                const text = titleEl.textContent.toLowerCase();
                card.style.display = text.includes(q) ? 'block' : 'none';
                if (text.includes(q)) titleEl.innerHTML = q ? text.replace(new RegExp(`(${q})`, "gi"), "<mark>$1</mark>") : text;
            });
        });
    }

    /* ── QR Return ── */
    const returnBookId = urlParams.get('return_book_id');
    if (returnBookId) showReturnConfirmation(returnBookId);

    function showReturnConfirmation(bookId) {
        ['returnBookTitle','returnBookAuthor','borrowerNameText','borrowerCourseText','dueDateText'].forEach(id => document.getElementById(id).textContent = 'Loading...');
        fetch(`/books/${bookId}/borrowing-data`)
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(data => {
            if (data.success) {
                document.getElementById('returnBookTitle').textContent  = data.book.title;
                document.getElementById('returnBookAuthor').textContent = 'by ' + data.book.author;
                if (data.borrowing) {
                    document.getElementById('borrowerNameText').textContent   = data.borrowing.student_name;
                    document.getElementById('borrowerCourseText').textContent = data.borrowing.course + ' - ' + data.borrowing.section;
                    document.getElementById('dueDateText').textContent = new Date(data.borrowing.due_date).toLocaleDateString();
                } else {
                    document.getElementById('borrowerNameText').textContent   = 'No active borrowing';
                    document.getElementById('borrowerCourseText').textContent = 'N/A';
                    document.getElementById('dueDateText').textContent        = 'N/A';
                }
                document.getElementById('barcodeReturnForm').action = `/borrowing/${bookId}/process-return`;
                new bootstrap.Modal(document.getElementById('barcodeReturnModal')).show();
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        })
        .catch(err => alert('Error loading book information: ' + err));
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#barcodeReturnForm button[type="submit"]')) return;
        e.preventDefault();
        const form = document.getElementById('barcodeReturnForm'), submitBtn = e.target.closest('button'), origText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        submitBtn.disabled = true;
        fetch(form.action, { method:'POST', body:new FormData(form), headers:{ 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) { const m = bootstrap.Modal.getInstance(document.getElementById('barcodeReturnModal')); m ? m.hide() : null; showNotification('success', data.message); }
            else { showNotification('error', data.message); submitBtn.innerHTML = origText; submitBtn.disabled = false; }
        })
        .catch(() => { showNotification('error', 'An error occurred.'); submitBtn.innerHTML = origText; submitBtn.disabled = false; });
    });

    document.getElementById('barcodeReturnForm')?.addEventListener('submit', e => e.preventDefault());

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
});

function highlightBookCard(bookId) {
    const card = document.getElementById(`book-card-${bookId}`);
    if (!card) return;
    card.scrollIntoView({ behavior:'smooth', block:'center' });
    card.style.transition = 'all 0.5s ease';
    card.style.boxShadow  = '0 0 0 3px #198754';
    card.style.transform  = 'scale(1.02)';
    setTimeout(() => { card.style.boxShadow = ''; card.style.transform = ''; }, 3000);
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\project-name\resources\views/books/index.blade.php ENDPATH**/ ?>