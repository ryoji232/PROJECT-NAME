@extends('app')

@section('title', 'Books')

@section('content')
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

    /* ── Compact Book Cards ── */
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
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .book-card-inner:hover {
        box-shadow: 0 6px 20px rgba(25,135,84,0.15);
        transform: translateY(-2px);
    }

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

    /* ── SHOW button ── */
    .btn-show {
        background: #198754;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: .9rem;
        padding: 9px 0;
        width: 100%;
        letter-spacing: .4px;
        transition: background .18s, transform .18s;
        cursor: pointer;
        margin-top: .85rem;
    }
    .btn-show:hover { background: #157347; transform: scale(1.02); color: #fff; }

    /* ── Shared button styles ── */
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

    /* ── All modals ── */
    .modal-content {
        border-radius: 1rem; border: 1px solid #dee2e6; background: #fff; color: #00402c;
    }
    .modal-header {
        background: #198754; color: #fff;
        border-radius: 1rem 1rem 0 0; border-bottom: none;
    }
    .modal-title { font-weight: 700; }

    /* ── Show modal extras ── */
    .show-modal-header-sub { font-size: .88rem; opacity: .85; margin-top: .15rem; }

    .show-modal-meta {
        display: flex; gap: .6rem; flex-wrap: wrap;
        align-items: center; margin-bottom: 1.2rem;
    }

    .show-modal-divider { border: none; border-top: 1px solid #dee2e6; margin: 1.1rem 0; }

    .action-section-label {
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .6px; color: #6c757d; margin-bottom: .6rem;
    }

    /* ── List items ── */
    .list-group-item {
        background: transparent; border: none;
        border-bottom: 1px solid #e3e3e3; color: #333;
        font-size: 1rem; padding: 1rem 1.2rem;
    }
    .list-group-item:last-child { border-bottom: none; }
    .list-group-item strong { color: #198754; }
    .list-group-item .text-danger  { color: #dc3545 !important; }
    .list-group-item .text-success { color: #198754 !important; }

    .text-muted   { color: #6c757d !important; }
    .text-primary { color: #198754 !important; }

    @media (max-width: 767px) {
        .hero-section { padding: 2rem 1rem; }
        .modal-content { border-radius: .7rem; }
    }
</style>

<!-- Hero Section -->
<div class="hero-section">
    <h1>Book Resource Library</h1>
    <p>Borrow Today, Learn for a Lifetime</p>
</div>

<!-- Add Book Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Add a New Book</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-light">⬅ Dashboard</a>
</div>

<form action="{{ route('books.store') }}" method="POST" class="add-book-form">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <input type="text" name="title" class="form-control" placeholder="Book Title" required>
        </div>
        <div class="col-md-4">
            <input type="text" name="author" class="form-control" placeholder="Author" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="copies" class="form-control" placeholder="Copies (default 1)" min="1">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Add Book</button>
        </div>
    </div>
</form>

<!-- Available Books -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 id="books">Available Books</h2>
    <div class="search-box">
        <input type="text" id="bookSearch" class="form-control" placeholder="🔍 Search by title...">
    </div>
</div>

<div class="row g-3" id="availableBooks">
    @foreach($books as $book)
        @php
            $bookHistory      = $borrowings->where('book_id', $book->id);
            $returnedCount    = $bookHistory->whereNotNull('returned_at')->count();
            $notReturnedCount = $bookHistory->whereNull('returned_at')->count();
        @endphp

        {{-- ══════════════════════════════════════════
             COMPACT CARD — title, author, badges, SHOW
        ══════════════════════════════════════════ --}}
        <div class="col-md-4 book-card" data-book-id="{{ $book->id }}" id="book-card-{{ $book->id }}">
            <div class="book-card-inner">
                <div>
                    <h5 class="book-title">{{ $book->title }}</h5>
                    <small class="text-muted">by {{ $book->author }}</small>

                    <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
                        @if($book->available_copies > 0)
                            <span class="badge bg-success available-badge" id="available-badge-{{ $book->id }}">
                                Available: {{ $book->available_copies }}
                            </span>
                        @else
                            <span class="badge bg-secondary available-badge" id="available-badge-{{ $book->id }}">
                                Fully Borrowed
                            </span>
                        @endif

                        <span class="badge bg-warning" id="in-use-badge-{{ $book->id }}"
                              style="{{ $notReturnedCount < 1 ? 'display:none;' : '' }}">
                            In Use: <span id="in-use-count-{{ $book->id }}">{{ $notReturnedCount }}</span>
                        </span>
                    </div>

                    <small class="text-muted d-block mt-1" style="font-size:.75rem;">ID: {{ $book->id }}</small>
                </div>

                <!-- SHOW triggers the action modal -->
                <button
                    type="button"
                    class="btn-show"
                    data-bs-toggle="modal"
                    data-bs-target="#showModal{{ $book->id }}">
                    SHOW
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             SHOW MODAL — book info + all action buttons
        ══════════════════════════════════════════ --}}
        <div class="modal fade" id="showModal{{ $book->id }}" tabindex="-1"
             aria-labelledby="showModalLabel{{ $book->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="showModalLabel{{ $book->id }}">
                                {{ $book->title }}
                            </h5>
                            <div class="show-modal-header-sub">by {{ $book->author }}</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <!-- Status badges -->
                        <div class="show-modal-meta">
                            @if($book->available_copies > 0)
                                <span class="badge bg-success">Available: {{ $book->available_copies }}</span>
                            @else
                                <span class="badge bg-secondary">Fully Borrowed</span>
                            @endif
                            <span class="badge bg-warning">
                                In Use: <span class="modal-in-use-{{ $book->id }}">{{ $notReturnedCount }}</span>
                            </span>
                            <span class="badge"
                                  style="background:#e0f2e9;color:#00402c;border:1px solid #c3e6cb;">
                                Total Copies: {{ $book->copies }}
                            </span>
                        </div>

                        <hr class="show-modal-divider">

                        <!-- ── BORROW ── -->
                        <div class="action-section-label">Borrow This Book</div>

                        @if($book->available_copies < 1)
                            <button class="btn btn-secondary w-100 mb-3" disabled>
                                ALL COPIES BORROWED
                            </button>
                        @else
                            <form class="borrow-form mb-3" data-book-id="{{ $book->id }}">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <div class="d-flex flex-column gap-2">
                                    <input type="text" name="student_name"
                                           class="form-control form-control-sm"
                                           placeholder="Student Name" required>
                                    <input type="text" name="course"
                                           class="form-control form-control-sm"
                                           placeholder="Course" required>
                                    <input type="text" name="section"
                                           class="form-control form-control-sm"
                                           placeholder="Section" required>
                                    <button type="submit"
                                            class="btn btn-success btn-sm w-100 borrow-btn"
                                            data-book-id="{{ $book->id }}">
                                        BORROW
                                    </button>
                                </div>
                            </form>
                        @endif

                        <hr class="show-modal-divider">

                        <!-- ── Other actions ── -->
                        <div class="action-section-label">Book Actions</div>

                        <div class="d-flex flex-column gap-2">

                            <button class="btn btn-warning btn-sm w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#historyModal{{ $book->id }}"
                                    data-book-id="{{ $book->id }}">
                                IN USE
                                (<span id="modal-in-use-count-{{ $book->id }}">{{ $notReturnedCount }}</span>)
                                — View History
                            </button>

                            <button type="button" class="btn-info-custom btn-sm w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#copiesModal{{ $book->id }}">
                                VIEW COPIES &amp; BARCODES
                            </button>

                            <a href="{{ route('books.edit', $book->id) }}"
                               class="btn btn-primary btn-sm w-100">
                                EDIT BOOK
                            </a>

                            <form action="{{ route('books.destroy', $book->id) }}"
                                  method="POST" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100"
                                    onclick="return confirm('Are you sure you want to delete this book?');">
                                    DELETE BOOK
                                </button>
                            </form>

                        </div>
                    </div><!-- /.modal-body -->

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light"
                                data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             HISTORY MODAL
        ══════════════════════════════════════════ --}}
        <div class="modal fade" id="historyModal{{ $book->id }}" tabindex="-1"
             aria-labelledby="historyModalLabel{{ $book->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="historyModalLabel{{ $book->id }}">
                            Borrow History — {{ $book->title }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($bookHistory->count() > 0)
                            <p class="fw-semibold">
                                Total Borrowed:
                                <span class="text-primary">{{ $bookHistory->count() }}</span> time(s)
                            </p>
                            <canvas id="historyChart{{ $book->id }}" height="100"></canvas>
                            <hr>
                            <ul class="list-group list-group-flush">
                                @foreach($bookHistory as $history)
                                    <li class="list-group-item">
                                        <strong>{{ $history->student_name }}</strong><br>
                                        <small>
                                            Borrowed on
                                            {{ \Carbon\Carbon::parse($history->borrowed_at ?? $history->created_at)
                                                ->timezone('Asia/Manila')->format('M d, Y - h:i A') }}
                                        </small>
                                        @if($history->returned_at)
                                            <br><span class="text-success">
                                                Returned on
                                                {{ \Carbon\Carbon::parse($history->returned_at)
                                                    ->timezone('Asia/Manila')->format('M d, Y - h:i A') }}
                                            </span>
                                        @else
                                            <br><span class="text-danger">Not yet returned</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted text-center mb-0">No borrow history for this book.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             COPIES MODAL
        ══════════════════════════════════════════ --}}
        <div class="modal fade" id="copiesModal{{ $book->id }}" tabindex="-1"
             aria-labelledby="copiesModalLabel{{ $book->id }}" aria-hidden="true"
             data-book-id="{{ $book->id }}">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="copiesModalLabel{{ $book->id }}">
                            Copies — {{ $book->title }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="copiesModalBody{{ $book->id }}">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart init for this book -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('historyChart{{ $book->id }}');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ['Available', 'Borrowed'],
                            datasets: [{
                                data: [{{ $book->copies }}, {{ $notReturnedCount }}],
                                backgroundColor: ['#078b24', '#ff4d4d'],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' },
                                title: {
                                    display: true,
                                    text: 'Available vs Borrowed Copies',
                                    font: { size: 14 }
                                }
                            }
                        }
                    });
                }
            });
        </script>

    @endforeach
</div><!-- /#availableBooks -->

<!-- QR Return Confirmation Modal -->
<div class="modal fade" id="barcodeReturnModal" tabindex="-1"
     aria-labelledby="barcodeReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrReturnModalLabel">Confirm Book Return</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div style="font-size:3rem;">📖</div>
                    <h4 id="returnBookTitle" class="text-primary fw-bold mt-2"></h4>
                    <p id="returnBookAuthor" class="text-muted"></p>
                </div>
                <div class="borrower-info p-3 rounded" style="background:#f8f9fa;">
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
                    @csrf
                    <button type="submit" class="btn btn-success">Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /* ── URL highlight ── */
    const urlParams = new URLSearchParams(window.location.search);
    const highlightBookId = urlParams.get('highlight_book');
    if (highlightBookId) {
        highlightBookCard(highlightBookId);
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    /* ── Toastr ── */
    toastr.options = {
        closeButton: true, progressBar: true,
        positionClass: "toast-top-right", timeOut: "5000"
    };

    /* ── Load copies when copiesModal opens ── */
    document.addEventListener('show.bs.modal', function (e) {
        const modal  = e.target;
        const bookId = modal.getAttribute('data-book-id');
        if (bookId && modal.id.startsWith('copiesModal')) {
            const modalBody = document.getElementById(`copiesModalBody${bookId}`);
            fetch(`/books/${bookId}/copies`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.copies.length > 0) {
                        let html = '<div class="row gy-3">';
                        data.copies.forEach(copy => {
                            const statusColor = copy.status === 'available' ? 'success'
                                              : copy.status === 'borrowed'   ? 'warning' : 'secondary';
                            const printUrl = `/books/${bookId}/copies/${copy.id}/print`;
                            html += `
                                <div class="col-md-6 text-center">
                                    <div class="p-3 border rounded">
                                        <h6 class="mb-1">${copy.copy_number}</h6>
                                        <p class="mb-2 small text-muted">
                                            <strong>Barcode:</strong> ${copy.normalized_barcode}
                                        </p>
                                        <div class="mt-2 d-flex justify-content-center gap-2">
                                            <span class="badge bg-${statusColor}">
                                                ${copy.status.charAt(0).toUpperCase() + copy.status.slice(1)}
                                            </span>
                                            <a href="${printUrl}" target="_blank"
                                               class="btn btn-sm btn-outline-primary">Print</a>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        html += '</div>';
                        modalBody.innerHTML = html;
                    } else {
                        modalBody.innerHTML = '<p class="text-muted">No copies found for this book.</p>';
                    }
                })
                .catch(() => {
                    modalBody.innerHTML = '<p class="text-danger">Error loading copies. Please try again.</p>';
                });
        }
    });

    /* ── AJAX Borrow — delegated so it works inside the show modal ── */
    document.addEventListener('submit', function (e) {
        if (!e.target.classList.contains('borrow-form')) return;
        e.preventDefault();

        const form      = e.target;
        const bookId    = form.dataset.bookId;
        const formData  = new FormData(form);
        const borrowBtn = form.querySelector('.borrow-btn');

        borrowBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Borrowing...';
        borrowBtn.disabled  = true;

        fetch("{{ route('borrow.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateBookCard(bookId, data.book.available_copies, data.active_borrowings);
                showNotification('success', data.message);
                form.reset();
                broadcastBookBorrowed();
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => showNotification('error', 'An error occurred while borrowing the book.'))
        .finally(() => {
            borrowBtn.innerHTML = 'BORROW';
            borrowBtn.disabled  = false;
        });
    });

    /* ── Update card + modal badges after borrow ── */
    function updateBookCard(bookId, availableCopies, activeBorrowings) {
        // Card available badge
        const badge = document.getElementById(`available-badge-${bookId}`);
        if (badge) {
            if (availableCopies > 0) {
                badge.textContent = `Available: ${availableCopies}`;
                badge.className   = 'badge bg-success available-badge';
            } else {
                badge.textContent = 'Fully Borrowed';
                badge.className   = 'badge bg-secondary available-badge';
            }
        }

        // Card in-use badge
        const inUseBadge = document.getElementById(`in-use-badge-${bookId}`);
        const inUseCount = document.getElementById(`in-use-count-${bookId}`);
        if (inUseCount) inUseCount.textContent = activeBorrowings;
        if (inUseBadge) inUseBadge.style.display = activeBorrowings > 0 ? '' : 'none';

        // Modal in-use counts
        document.querySelectorAll(`.modal-in-use-${bookId}, #modal-in-use-count-${bookId}`)
            .forEach(el => el.textContent = activeBorrowings);

        // If fully borrowed, swap form to disabled button inside show modal
        if (availableCopies < 1) {
            const showModal = document.getElementById(`showModal${bookId}`);
            if (showModal) {
                const borrowForm = showModal.querySelector('.borrow-form');
                if (borrowForm) {
                    borrowForm.outerHTML =
                        '<button class="btn btn-secondary w-100 mb-3" disabled>ALL COPIES BORROWED</button>';
                }
            }
        }
    }

    function showNotification(type, message) {
        typeof toastr !== 'undefined' ? toastr[type](message) : alert(message);
    }

    function broadcastBookBorrowed() {
        window.dispatchEvent(new CustomEvent('bookBorrowed', {
            detail: { timestamp: new Date().toISOString() }
        }));
        localStorage.setItem('bookBorrowed', Date.now().toString());
        if (window.opener) window.opener.postMessage('bookBorrowed', '*');
    }

    /* ── Search ── */
    const searchInput    = document.getElementById("bookSearch");
    const availableBooks = document.getElementById("availableBooks");

    if (searchInput && availableBooks) {
        const allCards = Array.from(availableBooks.querySelectorAll(".book-card"));
        searchInput.addEventListener("keyup", function () {
            const query = this.value.toLowerCase();
            allCards.forEach(card => {
                const titleEl   = card.querySelector(".book-title");
                const titleText = titleEl.textContent.toLowerCase();
                if (titleText.includes(query)) {
                    card.style.display = 'block';
                    titleEl.innerHTML  = query
                        ? titleText.replace(new RegExp(`(${query})`, "gi"), "<mark>$1</mark>")
                        : titleText;
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    /* ── QR Return ── */
    const returnBookId = urlParams.get('return_book_id');
    if (returnBookId) showReturnConfirmation(returnBookId);

    function showReturnConfirmation(bookId) {
        ['returnBookTitle','returnBookAuthor','borrowerNameText','borrowerCourseText','dueDateText']
            .forEach(id => document.getElementById(id).textContent = 'Loading...');

        fetch(`/books/${bookId}/borrowing-data`)
            .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(data => {
                if (data.success) {
                    document.getElementById('returnBookTitle').textContent  = data.book.title;
                    document.getElementById('returnBookAuthor').textContent = 'by ' + data.book.author;
                    if (data.borrowing) {
                        document.getElementById('borrowerNameText').textContent   = data.borrowing.student_name;
                        document.getElementById('borrowerCourseText').textContent =
                            data.borrowing.course + ' - ' + data.borrowing.section;
                        document.getElementById('dueDateText').textContent =
                            new Date(data.borrowing.due_date).toLocaleDateString();
                    } else {
                        document.getElementById('borrowerNameText').textContent   = 'No active borrowing';
                        document.getElementById('borrowerCourseText').textContent = 'N/A';
                        document.getElementById('dueDateText').textContent        = 'N/A';
                    }
                    document.getElementById('barcodeReturnForm').action =
                        `/borrowing/${bookId}/process-return`;
                    new bootstrap.Modal(document.getElementById('barcodeReturnModal')).show();
                    window.history.replaceState({}, document.title, window.location.pathname);
                } else {
                    alert('Error: ' + (data.message || 'Failed to load book information'));
                }
            })
            .catch(err => alert('Error loading book information: ' + err));
    }

    /* ── Confirm return ── */
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#barcodeReturnForm button[type="submit"]')) return;
        e.preventDefault();

        const form      = document.getElementById('barcodeReturnForm');
        const submitBtn = e.target.closest('button');
        const origText  = submitBtn.innerHTML;

        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';
        submitBtn.disabled  = true;

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const modalEl = document.getElementById('barcodeReturnModal');
                const m       = bootstrap.Modal.getInstance(modalEl);
                m ? m.hide() : modalEl.querySelector('[data-bs-dismiss="modal"]').click();
                showNotification('success', data.message);
            } else {
                showNotification('error', data.message);
                submitBtn.innerHTML = origText;
                submitBtn.disabled  = false;
            }
        })
        .catch(() => {
            showNotification('error', 'An error occurred while returning the book.');
            submitBtn.innerHTML = origText;
            submitBtn.disabled  = false;
        });
    });

    const barcodeReturnForm = document.getElementById('barcodeReturnForm');
    if (barcodeReturnForm) {
        barcodeReturnForm.addEventListener('submit', e => e.preventDefault());
    }
});

function highlightBookCard(bookId) {
    const card = document.getElementById(`book-card-${bookId}`);
    if (!card) return;
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    card.style.transition = 'all 0.5s ease';
    card.style.boxShadow  = '0 0 0 3px #198754';
    card.style.transform  = 'scale(1.02)';
    setTimeout(() => { card.style.boxShadow = ''; card.style.transform = ''; }, 3000);
}
</script>

@endsection