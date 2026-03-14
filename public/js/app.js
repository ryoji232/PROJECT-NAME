/**
 * app.js — Global JavaScript
 * Library System | IETI College
 */

// ─── Scroll ──────────────────────────────────────────────────────────────────
function forceScrollToTop() {
    window.scrollTo(0, 0);
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
}

// ─── Modal helpers ───────────────────────────────────────────────────────────
let returnModal = null;

function resetReturnModal() {
    document.getElementById('confirmReturnCheckbox').checked = false;
    document.getElementById('confirmReturnBtn').disabled = true;
    document.getElementById('statusBorrowed').classList.remove('d-none');
    document.getElementById('statusProcessing').classList.add('d-none');
    document.getElementById('statusCompleted').classList.add('d-none');
    document.getElementById('cancelReturnBtn').disabled = false;
    document.getElementById('modalCloseButton').disabled = false;
}

function showBorrowModal(copy) {
    const modalEl = document.getElementById('borrowModal');
    if (!modalEl) return;

    document.getElementById('borrowBookTitle').textContent  = copy.book?.title  || 'Book';
    document.getElementById('borrowBookAuthor').textContent = copy.book?.author || '';
    document.getElementById('borrow_copy_barcode').value   = copy.barcode;
    document.getElementById('borrow_student_name').value   = '';
    document.getElementById('borrow_course').value         = '';
    document.getElementById('borrow_section').value        = '';
    document.getElementById('borrowAlert').classList.add('d-none');

    if (copy._fromScannerFallback) {
        const alert = document.getElementById('borrowAlert');
        alert.classList.remove('d-none', 'alert-danger');
        alert.classList.add('alert-warning');
        alert.textContent = 'Book details not found — please enter borrower info and confirm manually.';
    }

    new bootstrap.Modal(modalEl).show();
    setTimeout(() => document.getElementById('borrow_student_name')?.focus(), 300);
}

function showReturnModal(borrowingId) {
    resetReturnModal();

    ['returnBookTitle', 'borrowerNameText', 'borrowerCourseText', 'dueDateText'].forEach(id => {
        document.getElementById(id).textContent = 'Loading...';
    });
    document.getElementById('returnBookAuthor').textContent = '';

    fetch(`/borrowings/${borrowingId}/data`)
        .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
        .then(data => {
            if (!data.success || !data.borrowing) {
                toastr.error(data.message || 'No active borrowing found.');
                return;
            }
            document.getElementById('returnBookTitle').textContent   = data.book.title;
            document.getElementById('returnBookAuthor').textContent  = 'by ' + data.book.author;
            document.getElementById('borrowerNameText').textContent  = data.borrowing.student_name;
            document.getElementById('borrowerCourseText').textContent = `${data.borrowing.course} - ${data.borrowing.section}`;
            document.getElementById('dueDateText').textContent       = new Date(data.borrowing.due_date).toLocaleDateString();
            document.getElementById('barcodeReturnForm').action      = `/borrowing/${borrowingId}/process-return`;

            returnModal = returnModal || new bootstrap.Modal(document.getElementById('barcodeReturnModal'));
            returnModal.show();
        })
        .catch(err => toastr.error('Error loading book info: ' + err.message));
}

// ─── Barcode scanner ─────────────────────────────────────────────────────────
function initBarcodeScanner() {
    let buffer = '';
    let timer  = null;
    // IDs that are always safe to type into (old scanner-borrow modal).
    const SAFE_IDS = ['borrow_student_name', 'borrow_course', 'borrow_section'];
    // ID prefixes for per-book modal borrow form inputs (e.g. modal_student_name_5).
    const SAFE_PREFIXES = ['modal_student_name_', 'modal_course_', 'modal_section_'];

    function isSafeInput(el) {
        if (!el || !el.id) return false;
        if (SAFE_IDS.includes(el.id)) return true;
        return SAFE_PREFIXES.some(function (prefix) { return el.id.startsWith(prefix); });
    }

    function isFormField() {
        const el  = document.activeElement;
        const tag = el?.tagName?.toUpperCase();
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el?.isContentEditable;
    }

    function process(raw) {
        const barcode = raw.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

        // ── Alphanumeric → copy-level barcode ────────────────────────────────
        // Resolution order:
        //   1. Check window.__barcodeBookIndex (populated by books/index.blade.php)
        //      — zero network cost, instant lookup on the Books page.
        //   2. Fall back to AJAX /copies/scan/{code} so scanning works on ANY
        //      page in the app (dashboard, borrowings list, etc.).
        //
        // In both cases the resolved book id is passed to openBookShowModal(),
        // which opens the per-book modal (id="showModal{bookId}") — identical
        // to the behaviour when a librarian clicks the book card manually.
        if (/^[A-Z0-9]{4,12}$/.test(barcode)) {
            // ── Fast path: client-side index ─────────────────────────────────
            const localIndex = window.__barcodeBookIndex || {};
            if (localIndex[barcode] !== undefined) {
                openBookShowModal(localIndex[barcode]);
                return;
            }

            // ── Slow path: AJAX lookup ────────────────────────────────────────
            fetch(`/copies/scan/${encodeURIComponent(barcode)}`, { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data?.success && data.copy?.book?.id) {
                        openBookShowModal(data.copy.book.id);
                    } else {
                        toastr.error('Barcode not recognised. Please use the manual borrow form.');
                    }
                })
                .catch(err => toastr.error('Scanner error: ' + err.message));
            return;
        }

        // ── Numeric → return flow ─────────────────────────────────────────────
        const returnId = /^\d+$/.test(barcode)
            ? barcode
            : (raw.match(/\/return\/(\d+)|return\/(\d+)/) || [])[1];

        returnId ? showReturnModal(returnId) : toastr.error('Invalid barcode format');
    }

    // Opens the per-book showModal (same as clicking the book card).
    // Works on any page that has the modal rendered; silently does nothing otherwise.
    function openBookShowModal(bookId) {
        const modalEl = document.getElementById('showModal' + bookId);
        if (modalEl) {
            // Close any currently open modal first
            document.querySelectorAll('.modal.show').forEach(function(m) {
                const instance = bootstrap.Modal.getInstance(m);
                if (instance) instance.hide();
            });
            setTimeout(function() {
                new bootstrap.Modal(modalEl).show();
            }, 150);
        } else {
            // Not on the books page — nothing to open
            toastr.info('Book found. Navigate to the Books page to borrow it.');
        }
    }

    document.addEventListener('keydown', function(e) {
        const el      = document.activeElement;
        const safe    = isSafeInput(el);
        const isField = isFormField();

        // Let the keystroke through if the focus is in any known text input
        // (either by explicit SAFE id/prefix, or any form field that is NOT
        // the hidden barcode input shim).
        if ((safe || (isField && el?.id !== 'barcodeScannerInput')) && e.key.length === 1) return;

        if (e.key === 'Enter') {
            if (buffer.trim().length > 3) {
                e.preventDefault(); e.stopImmediatePropagation();
                const b = buffer.trim(); buffer = ''; clearTimeout(timer); process(b);
            }
            return;
        }

        if (e.key.length === 1 && /[A-Za-z0-9\-\/ ]/.test(e.key)) {
            e.preventDefault(); e.stopImmediatePropagation();
            buffer += e.key;
            clearTimeout(timer);

            if (/^[A-Z0-9]{8}$/i.test(buffer.trim())) {
                const b = buffer.trim(); buffer = ''; process(b); return;
            }

            timer = setTimeout(() => {
                if (buffer.trim().length > 3) { const b = buffer.trim(); buffer = ''; process(b); }
            }, 300);
        }
    }, true);
}

// ─── DOMContentLoaded ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Toastr defaults
    toastr.options = {
        closeButton: true, progressBar: true,
        positionClass: 'toast-top-right', timeOut: '5000'
    };

    // ── Flash-to-Toastr bridge ────────────────────────────────────────────────
    // window.__flash is injected by the layout (app.blade.php) so we never have
    // to read hidden DOM elements (innerText returns '' when display:none).
    if (window.__flash) {
        if (window.__flash.success) toastr.success(window.__flash.success);
        if (window.__flash.error)   toastr.error(window.__flash.error);
        if (window.__flash.info)    toastr.info(window.__flash.info);
        if (window.__flash.warning) toastr.warning(window.__flash.warning);
    }
    // ─────────────────────────────────────────────────────────────────────────

    // ── Manual Borrow AJAX handler ────────────────────────────────────────────
    // Intercepts any form that submits to the /borrow (borrow.store) route so
    // the result shows as a toastr toast instead of a plain page redirect.
    document.querySelectorAll('form[action*="/borrow"]').forEach(function (form) {
        // Skip the barcode borrow form — it already has its own handler above
        if (form.id === 'borrowByBarcodeForm') return;
        // Skip per-book modal borrow forms — they are handled by submitBorrowForm()
        // in books/index.blade.php and must not get a competing submit listener.
        if (form.classList.contains('book-borrow-form')) return;
        // Skip forms whose action contains '/borrowings' — the borrowings index
        // route is GET-only (search/filter) and must never be POST-intercepted.
        // The selector 'form[action*="/borrow"]' is intentionally broad but
        // /borrowings contains /borrow as a substring, causing a false match.
        var action = (form.getAttribute('action') || '').toLowerCase();
        if (action.indexOf('/borrowings') !== -1) return;
        if (action.indexOf('/borrowing/') !== -1) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            if (btn) btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    toastr.success(data.message || 'Book borrowed successfully!');
                    // Close any modal wrapping this form
                    const modalEl = form.closest('.modal');
                    if (modalEl) {
                        bootstrap.Modal.getInstance(modalEl)?.hide();
                    }
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.error(data.message || 'Failed to borrow book.');
                    if (btn) btn.disabled = false;
                }
            })
            .catch(function () {
                toastr.error('An error occurred while borrowing the book.');
                if (btn) btn.disabled = false;
            });
        });
    });
    // ─────────────────────────────────────────────────────────────────────────

    // Return modal singleton
    const returnModalEl = document.getElementById('barcodeReturnModal');
    if (returnModalEl) returnModal = new bootstrap.Modal(returnModalEl);

    // Scroll reset
    forceScrollToTop();
    window.addEventListener('load', forceScrollToTop);
    if (window.location.hash) history.replaceState(null, null, ' ');

    // Barcode scanner
    initBarcodeScanner();

    // Return checkbox gate
    document.getElementById('confirmReturnCheckbox')?.addEventListener('change', function () {
        document.getElementById('confirmReturnBtn').disabled = !this.checked;
    });

    // Borrow form submit (scanner-triggered borrow modal — borrowModal / borrowByBarcodeForm)
    document.getElementById('borrowSubmitBtn')?.addEventListener('click', function () {
        const form = document.getElementById('borrowByBarcodeForm');
        const btn  = this;
        btn.disabled = true;

        // The route URL is injected by the Blade layout into window.__routes
        // because this file is static JS and Blade directives are not processed here.
        const borrowByBarcodeUrl = (window.__routes && window.__routes.borrowByBarcode)
            ? window.__routes.borrowByBarcode
            : '/borrow/by-barcode';

        fetch(borrowByBarcodeUrl, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message || 'Borrowed successfully');
                bootstrap.Modal.getInstance(document.getElementById('borrowModal'))?.hide();
                setTimeout(() => location.reload(), 600);
            } else {
                const alert = document.getElementById('borrowAlert');
                alert.classList.remove('d-none');
                alert.classList.add('alert-danger');
                alert.textContent = data.message || 'Failed to borrow';
            }
        })
        .catch(() => toastr.error('Error processing borrow request'))
        .finally(() => { btn.disabled = false; });
    });

    // Return form submit
    document.getElementById('barcodeReturnForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = document.getElementById('confirmReturnBtn');
        const cancelBtn = document.getElementById('cancelReturnBtn');
        const closeBtn  = document.getElementById('modalCloseButton');

        document.getElementById('statusBorrowed').classList.add('d-none');
        document.getElementById('statusProcessing').classList.remove('d-none');
        submitBtn.disabled = cancelBtn.disabled = closeBtn.disabled = true;

        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
        .then(data => {
            if (data.success) {
                document.getElementById('statusProcessing').classList.add('d-none');
                document.getElementById('statusCompleted').classList.remove('d-none');
                toastr.success(data.message);
                setTimeout(() => { returnModal?.hide(); setTimeout(() => location.reload(), 500); }, 2000);
            } else {
                toastr.error(data.message);
                resetReturnModal();
            }
        })
        .catch(err => { toastr.error('Error processing return.'); resetReturnModal(); });
    });

    // Return modal events
    document.getElementById('barcodeReturnModal')?.addEventListener('hidden.bs.modal', resetReturnModal);
    document.getElementById('barcodeReturnModal')?.addEventListener('hide.bs.modal', function (e) {
        if (!document.getElementById('statusProcessing').classList.contains('d-none')) e.preventDefault();
    });
});

// Expose for inline usage
window.showReturnConfirmationModal = showReturnModal;

// openBookShowModal is defined inside initBarcodeScanner() so it can share
// the same closure scope as process(). We re-expose it here on window so that
// inline onclick="openBookShowModal(...)" attributes in Blade templates work.
// initBarcodeScanner() is called inside DOMContentLoaded (line ~240), so by
// the time any onclick fires the function is already assigned.
window.openBookShowModal = function (bookId) {
    const modalEl = document.getElementById('showModal' + bookId);
    if (!modalEl) {
        toastr.info('Book found. Navigate to the Books page to view details.');
        return;
    }
    // Close any currently open modal first, then open the target one.
    document.querySelectorAll('.modal.show').forEach(function (m) {
        const instance = bootstrap.Modal.getInstance(m);
        if (instance) instance.hide();
    });
    setTimeout(function () {
        new bootstrap.Modal(modalEl).show();
    }, 150);
};