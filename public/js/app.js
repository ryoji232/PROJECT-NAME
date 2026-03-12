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
    const SAFE = ['borrow_student_name', 'borrow_course', 'borrow_section'];

    function isFormField() {
        const el  = document.activeElement;
        const tag = el?.tagName?.toUpperCase();
        return tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || el?.isContentEditable;
    }

    function process(raw) {
        const barcode = raw.replace(/[^A-Za-z0-9]/g, '').toUpperCase();

        // Copy barcode → borrow flow
        if (/^[A-Z0-9]{4,12}$/.test(barcode)) {
            fetch(`/copies/scan/${encodeURIComponent(barcode)}`, { credentials: 'same-origin' })
                .then(r => r.json())
                .then(data => {
                    if (data?.success && data.copy) {
                        showBorrowModal(data.copy);
                    } else {
                        toastr.warning('Barcode not found — opening manual borrow modal');
                        showBorrowModal({ barcode, book: { title: 'Unknown Book', author: '' }, _fromScannerFallback: true });
                    }
                })
                .catch(err => toastr.error('Error: ' + err.message));
            return;
        }

        // Numeric → return flow
        const returnId = /^\d+$/.test(barcode)
            ? barcode
            : (raw.match(/\/return\/(\d+)|return\/(\d+)/) || [])[1];

        returnId ? showReturnModal(returnId) : toastr.error('Invalid barcode format');
    }

    document.addEventListener('keydown', function(e) {
        const el      = document.activeElement;
        const isSafe  = SAFE.includes(el?.id);
        const isField = isFormField();

        if ((isSafe || (isField && el?.id !== 'barcodeScannerInput')) && e.key.length === 1) return;

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

    // Borrow form submit
    document.getElementById('borrowSubmitBtn')?.addEventListener('click', function () {
        const form = document.getElementById('borrowByBarcodeForm');
        const btn  = this;
        btn.disabled = true;

        fetch("{{ route('borrow.by.barcode') }}", {
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