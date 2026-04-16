


<div class="modal fade" id="barcodeReturnModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Book Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="modalCloseButton"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div style="font-size:3rem;">📚</div>
                    <h4 id="returnBookTitle" class="text-primary fw-bold mt-2"></h4>
                    <p id="returnBookAuthor" class="text-muted mb-0"></p>
                </div>

                <div class="p-3 rounded mb-3" style="background:#f8f9fa;">
                    <h6 class="fw-bold text-success">Borrower Information</h6>
                    <p class="mb-1"><strong>Name:</strong> <span id="borrowerNameText"></span></p>
                    <p class="mb-1"><strong>Course &amp; Section:</strong> <span id="borrowerCourseText"></span></p>
                    <p class="mb-0"><strong>Due Date:</strong> <span id="dueDateText"></span></p>
                </div>

                <div class="status-processing-area">
                    <div class="checkbox-container">
                        <input type="checkbox" id="confirmReturnCheckbox">
                        <label for="confirmReturnCheckbox">I confirm the physical book is returned</label>
                    </div>
                    <div id="statusBorrowed"   class="status-borrowed">Status: Currently Borrowed — Ready for Return</div>
                    <div id="statusProcessing" class="status-processing d-none">
                        Status: Processing Return…
                        <div class="processing-indicator">
                            <div class="spinner"></div><span>Processing…</span>
                        </div>
                    </div>
                    <div id="statusCompleted"  class="status-completed d-none">Status: Return Completed ✅</div>
                </div>

                <div class="alert alert-warning mt-3 mb-0">
                    ⚠️ Please verify the physical book is being returned before confirming.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelReturnBtn">Cancel</button>
                <form id="barcodeReturnForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-success" id="confirmReturnBtn" disabled>✅ Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="borrowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrow Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <h5 id="borrowBookTitle">Loading…</h5>
                    <small id="borrowBookAuthor" class="text-muted"></small>
                </div>
                <form id="borrowByBarcodeForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="book_copy_barcode" id="borrow_copy_barcode">
                    <div class="mb-2">
                        <input type="text" name="student_name" id="borrow_student_name"
                               class="form-control" placeholder="Student Name" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="course" id="borrow_course"
                               class="form-control" placeholder="Course" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="section" id="borrow_section"
                               class="form-control" placeholder="Section" required>
                    </div>
                    <div id="borrowAlert" class="alert d-none" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="borrowSubmitBtn" class="btn btn-success">Confirm Borrow</button>
            </div>
        </div>
    </div>
</div>



<div class="modal fade" id="globalBorrowModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754,#157347);color:#fff;">
                <div>
                    <h5 class="modal-title fw-bold">📥 Borrow Book</h5>
                    <small style="opacity:.85;" id="globalBorrowSubtitle">Scanned via barcode</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <div class="p-3 rounded mb-3" style="background:#f0fdf4;border:1px solid #a5d6a7;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-size:1.6rem;">📖</span>
                        <div>
                            <div class="fw-bold" id="globalBorrowBookTitle" style="color:#155724;">Loading…</div>
                            <small class="text-muted" id="globalBorrowBookAuthor"></small>
                        </div>
                    </div>
                    <div id="globalBorrowCopiesInfo" class="mt-1" style="font-size:.83rem;color:#198754;"></div>
                </div>

                <form id="globalBorrowForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="book_id" id="global_borrow_book_id">
                    <div class="mb-2">
                        <label class="form-label fw-semibold" style="font-size:.88rem;">Student Name</label>
                        <input type="text" name="student_name" id="global_borrow_student_name"
                               class="form-control" placeholder="e.g. Juan dela Cruz" required autocomplete="off">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:.88rem;">Course</label>
                            <input type="text" name="course" id="global_borrow_course"
                                   class="form-control" placeholder="e.g. BSIT" required autocomplete="off">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="font-size:.88rem;">Section</label>
                            <input type="text" name="section" id="global_borrow_section"
                                   class="form-control" placeholder="e.g. 2A" required autocomplete="off">
                        </div>
                    </div>
                    <div id="globalBorrowAlert" class="alert d-none mt-2" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="globalBorrowSubmitBtn" class="btn btn-success px-4">
                    ✅ Confirm Borrow
                </button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── A. Return modal: checkbox → button + reset on open ──────────────
    (function () {
        var modal    = document.getElementById('barcodeReturnModal');
        var checkbox = document.getElementById('confirmReturnCheckbox');
        var btn      = document.getElementById('confirmReturnBtn');
        if (!modal || !checkbox || !btn) return;

        checkbox.addEventListener('change', function () {
            btn.disabled = !this.checked;
        });

        modal.addEventListener('show.bs.modal', function () {
            checkbox.checked = false;
            btn.disabled     = true;
        });
    }());

    // ── B. Return modal: AJAX submit ─────────────────────────────────────
    //
    //  The form action is set to POST /return/{borrowingId} by either:
    //    • openReturnModal(borrowingId)          — borrowings table button
    //    • window.__openReturnModalForBook(bookId) — global scanner
    //
    //  We intercept the submit here so the return works via AJAX on every
    //  page (no full-page POST redirect that would lose the current page).
    (function () {
        var form = document.getElementById('barcodeReturnForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var submitBtn     = document.getElementById('confirmReturnBtn');
            var statusBorrow  = document.getElementById('statusBorrowed');
            var statusProcess = document.getElementById('statusProcessing');
            var statusDone    = document.getElementById('statusCompleted');

            if (submitBtn)     submitBtn.disabled = true;
            if (statusBorrow)  statusBorrow.classList.add('d-none');
            if (statusProcess) statusProcess.classList.remove('d-none');

            var action  = form.action;
            var csrf    = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            fetch(action, {
                method:  'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     csrf,
                    'Accept':           'application/json',
                    'Content-Type':     'application/x-www-form-urlencoded',
                },
                body: '_method=POST&_token=' + encodeURIComponent(csrf),
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (statusProcess) statusProcess.classList.add('d-none');

                if (data.success) {
                    if (statusDone) statusDone.classList.remove('d-none');

                    // Show toastr if available
                    if (window.toastr) toastr.success(data.message || 'Book returned successfully!');

                    // Close the modal after a short pause so the "Completed" banner is visible
                    setTimeout(function () {
                        var bsModal = bootstrap.Modal.getInstance(
                            document.getElementById('barcodeReturnModal')
                        );
                        if (bsModal) bsModal.hide();

                        // Reload the page so counts / tables reflect the return
                        window.location.reload();
                    }, 1200);
                } else {
                    if (statusBorrow) statusBorrow.classList.remove('d-none');
                    if (submitBtn)    submitBtn.disabled = false;
                    if (window.toastr) toastr.error(data.message || 'Return failed.');
                    else alert('❌ ' + (data.message || 'Return failed.'));
                }
            })
            .catch(function (err) {
                console.error('[ReturnModal] AJAX error:', err);
                if (statusProcess) statusProcess.classList.add('d-none');
                if (statusBorrow)  statusBorrow.classList.remove('d-none');
                if (submitBtn)     submitBtn.disabled = false;
                if (window.toastr) toastr.error('Network error. Please try again.');
                else alert('❌ Network error. Please try again.');
            });
        });
    }());

    // ── C. Global Borrow Modal: submit via AJAX → POST /borrow ──────────
    (function () {
        var submitBtn = document.getElementById('globalBorrowSubmitBtn');
        if (!submitBtn) return;

        submitBtn.addEventListener('click', function () {
            var form      = document.getElementById('globalBorrowForm');
            var alertEl   = document.getElementById('globalBorrowAlert');

            var bookId      = document.getElementById('global_borrow_book_id').value;
            var studentName = document.getElementById('global_borrow_student_name').value.trim();
            var course      = document.getElementById('global_borrow_course').value.trim();
            var section     = document.getElementById('global_borrow_section').value.trim();

            // Client-side validation
            if (!studentName || !course || !section) {
                showGlobalBorrowAlert('danger', 'Please fill in all fields before borrowing.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Borrowing…';

            var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            fetch('<?php echo e(route("borrow.store")); ?>', {
                method:  'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN':     csrf,
                    'Accept':           'application/json',
                },
                body: new FormData(form),
            })
            .then(function (r) {
                return r.json().catch(function () {
                    throw new Error('Non-JSON response (HTTP ' + r.status + ')');
                });
            })
            .then(function (data) {
                if (!data.success) {
                    showGlobalBorrowAlert('danger', data.message || 'Failed to borrow book.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '✅ Confirm Borrow';
                    return;
                }

                // Success
                if (window.toastr) toastr.success(data.message || 'Book borrowed successfully!');

                // Close modal
                var bsModal = bootstrap.Modal.getInstance(
                    document.getElementById('globalBorrowModal')
                );
                if (bsModal) bsModal.hide();

                // Reload so counts update
                window.location.reload();
            })
            .catch(function (err) {
                console.error('[GlobalBorrow] error:', err);
                showGlobalBorrowAlert('danger', err.message || 'Network error. Please try again.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✅ Confirm Borrow';
            });
        });

        // Reset modal state when it closes
        var modal = document.getElementById('globalBorrowModal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('global_borrow_student_name').value = '';
                document.getElementById('global_borrow_course').value       = '';
                document.getElementById('global_borrow_section').value      = '';
                var alertEl = document.getElementById('globalBorrowAlert');
                if (alertEl) alertEl.classList.add('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✅ Confirm Borrow';
            });
        }

        function showGlobalBorrowAlert(type, msg) {
            var el = document.getElementById('globalBorrowAlert');
            if (!el) return;
            el.className   = 'alert alert-' + type + ' mt-2';
            el.textContent = msg;
        }
    }());

});

// ── D. window.__openReturnModalForBook(bookId) ───────────────────────────
//
//  Called by the global scanner in app.blade.php when a book-ID barcode is
//  scanned and the book has no available copies (i.e. it is borrowed).
//  Fetches the most recent active borrowing from /books/{id}/current-borrowing
//  then populates and opens #barcodeReturnModal.
//
//  The form action is set to POST /return/{borrowingId} — the AJAX submit
//  handler wired in section B above handles the rest.
window.__openReturnModalForBook = function (bookId) {
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    fetch('/books/' + bookId + '/current-borrowing', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success || !data.borrowing) {
            if (window.toastr) toastr.warning('No active borrowing found for this book.');
            else alert('⚠️ No active borrowing found for this book.');
            return;
        }

        var b = data.borrowing;

        // Populate return modal fields
        var titleEl  = document.getElementById('returnBookTitle');
        var authorEl = document.getElementById('returnBookAuthor');
        var nameEl   = document.getElementById('borrowerNameText');
        var courseEl = document.getElementById('borrowerCourseText');
        var dueEl    = document.getElementById('dueDateText');

        // We need the book title too — fetch it from scan-data
        fetch('/books/' + bookId + '/scan-data', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(function (bookData) {
            if (titleEl)  titleEl.textContent  = bookData.book ? bookData.book.title  : 'Book #' + bookId;
            if (authorEl) authorEl.textContent = bookData.book ? bookData.book.author : '';
        })
        .catch(function () {
            if (titleEl) titleEl.textContent = 'Book #' + bookId;
        });

        if (nameEl)   nameEl.textContent   = b.student_name || '—';
        if (courseEl) courseEl.textContent = (b.course || '') + (b.section ? ' - ' + b.section : '');
        if (dueEl)    dueEl.textContent    = b.due_date ? b.due_date.substring(0, 10) : 'N/A';

        // Set the form action to the real return endpoint
        var form = document.getElementById('barcodeReturnForm');
        if (form) form.action = '/return/' + b.id;

        // Reset status indicators
        var statusBorrow  = document.getElementById('statusBorrowed');
        var statusProcess = document.getElementById('statusProcessing');
        var statusDone    = document.getElementById('statusCompleted');
        if (statusBorrow)  { statusBorrow.classList.remove('d-none'); }
        if (statusProcess) { statusProcess.classList.add('d-none'); }
        if (statusDone)    { statusDone.classList.add('d-none'); }

        // Open the modal
        var modal = new bootstrap.Modal(document.getElementById('barcodeReturnModal'));
        modal.show();
    })
    .catch(function (err) {
        console.error('[ReturnModal] fetch error:', err);
        if (window.toastr) toastr.error('Could not load borrowing data. Please try again.');
        else alert('❌ Could not load borrowing data.');
    });
};

// ── E. window.__openBorrowModalForBook(bookId) ────────────────────────────
//
//  Called by the global scanner in app.blade.php when a book-ID barcode is
//  scanned and the book has available copies.
//  Fetches book details from /books/{id}/scan-data then opens #globalBorrowModal.
window.__openBorrowModalForBook = function (bookId) {
    // Fetch book details first so we can populate the modal
    fetch('/books/' + bookId + '/scan-data', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success || !data.book) {
            if (window.toastr) toastr.warning('Book not found.');
            else alert('⚠️ Book not found.');
            return;
        }

        var book = data.book;

        if (!book.available_copies || book.available_copies <= 0) {
            // Race condition — by the time we fetched, book became unavailable
            window.__openReturnModalForBook(bookId);
            return;
        }

        // Populate global borrow modal
        document.getElementById('globalBorrowBookTitle').textContent  = book.title  || 'Unknown';
        document.getElementById('globalBorrowBookAuthor').textContent = book.author || '';
        document.getElementById('globalBorrowCopiesInfo').textContent =
            book.available_copies + ' of ' + book.copies + ' copies available';
        document.getElementById('global_borrow_book_id').value = bookId;

        // Focus the student name field once the modal is shown
        var modal    = document.getElementById('globalBorrowModal');
        var nameInput = document.getElementById('global_borrow_student_name');

        var bsModal = new bootstrap.Modal(modal);
        modal.addEventListener('shown.bs.modal', function onShown() {
            if (nameInput) nameInput.focus();
            modal.removeEventListener('shown.bs.modal', onShown);
        });

        bsModal.show();
    })
    .catch(function (err) {
        console.error('[BorrowModal] fetch error:', err);
        if (window.toastr) toastr.error('Could not load book data. Please try again.');
        else alert('❌ Could not load book data.');
    });
};
</script><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/partials/modals.blade.php ENDPATH**/ ?>