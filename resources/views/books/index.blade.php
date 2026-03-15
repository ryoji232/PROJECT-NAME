{{--
    resources/views/books/index.blade.php
    ─────────────────────────────────────
    Displays all books as cards.
    Each card is clickable → opens a per-book show-modal.
    Each book card carries data-barcode attributes for every copy
    so the barcode scanner in app.js can map a scanned copy
    barcode → book id → open the correct showModal.
--}}
@extends('layouts.app')

@section('title', 'Books — Library System')

@section('content')

{{-- ── Hero ────────────────────────────────────────────────────────────── --}}
<div class="hero-section">
    <h1>Book Collection</h1>
    <p>Manage, search, and borrow books from the library catalogue</p>
</div>

{{-- ── Alerts ──────────────────────────────────────────────────────────── --}}
@include('partials.alerts')

{{-- ── Add-book + Search toolbar ───────────────────────────────────────── --}}
<div class="books-toolbar">

    {{-- Add Book form --}}
    <div class="books-toolbar__add">
        <button class="btn btn-success btn-sm"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#addBookCollapse"
                aria-expanded="false"
                aria-controls="addBookCollapse">
             Add New Book
        </button>
    </div>

    {{-- Search bar --}}
    <div class="books-toolbar__search">
        <div class="books-search-wrap">
            <span class="books-search-icon">🔍</span>
            <input type="text"
                   id="bookSearchInput"
                   class="books-search-input"
                   placeholder="Search by title or author…"
                   autocomplete="off"
                   spellcheck="false">
            <button class="books-search-clear" id="bookSearchClear" title="Clear search" aria-label="Clear search">✕</button>
        </div>
        <div class="books-search-meta" id="bookSearchMeta"></div>
    </div>

</div>

{{-- Collapsible add-book form --}}
<div class="collapse" id="addBookCollapse">
    <div class="card mb-3">
        <div class="card-header"> Add New Book</div>
        <div class="card-body">
            <form action="{{ route('books.store') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Book title" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Author</label>
                    <input type="text" name="author" class="form-control" placeholder="Author name" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Copies</label>
                    <input type="number" name="copies" class="form-control" value="1" min="1" max="10">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100">Add Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Scanner hint banner ─────────────────────────────────────────────── --}}
<div class="scanner-hint-bar">
    <span class="scanner-icon">📡</span>
    <span>Barcode scanner active — scan any book copy barcode to open its details instantly.</span>
</div>

{{-- ── Book grid ───────────────────────────────────────────────────────── --}}

{{-- No-results message shown by JS when search yields nothing --}}
<div id="noSearchResults" class="no-records" style="display:none;">
    <div class="no-records-icon">🔍</div>
    <h3>No books match your search</h3>
    <p>Try a different title or author name.</p>
</div>

@if($books->count() > 0)

    @php
        // Sort A–Z by title (collection, no extra query)
        $sortedBooks = $books->sortBy(function ($b) {
            return mb_strtolower(trim($b->title));
        });

        // Group by first letter; non-alpha titles go under '#'
        $grouped = $sortedBooks->groupBy(function ($b) {
            $first = mb_strtoupper(mb_substr(trim($b->title), 0, 1));
            return preg_match('/[A-Z]/', $first) ? $first : '#';
        });

        // Build the barcode → bookId index across all books
        $barcodeIndex = $sortedBooks->flatMap(function ($book) {
            return $book->bookCopies->mapWithKeys(function ($copy) use ($book) {
                return [
                    \App\Models\BookCopy::normalizeBarcode($copy->barcode) => $book->id
                ];
            });
        })->toArray();
    @endphp

    {{-- Alphabet jump-bar --}}
    <div class="alpha-jumpbar" id="alphaJumpbar">
        @foreach($grouped->keys()->sort() as $letter)
            <a href="#alpha-{{ $letter }}" class="alpha-jumpbar__link">{{ $letter }}</a>
        @endforeach
    </div>

    {{-- Master container — holds barcode index + all letter sections --}}
    <div id="bookGrid" data-barcode-index="{{ json_encode($barcodeIndex) }}">

        @foreach($grouped->sortKeys() as $letter => $letterBooks)

            {{-- ── Letter section ─────────────────────────────────── --}}
            <div class="alpha-section" id="alpha-{{ $letter }}" data-letter="{{ $letter }}">
                <div class="alpha-section__header">
                    <span class="alpha-section__letter">{{ $letter }}</span>
                    <span class="alpha-section__count">{{ $letterBooks->count() }} {{ Str::plural('book', $letterBooks->count()) }}</span>
                </div>
                <div class="book-grid">

        @foreach($letterBooks as $book)
            @php
                $availableCopies = $book->available_copies ?? 0;
                $totalCopies     = $book->copies ?? 0;
                $isAvailable     = $availableCopies > 0;

                // Collect all normalised copy barcodes for this book
                $copyBarcodes = $book->bookCopies
                    ->pluck('barcode')
                    ->map(fn($b) => \App\Models\BookCopy::normalizeBarcode($b))
                    ->filter()
                    ->values()
                    ->toArray();
            @endphp

            {{-- ── Book card ───────────────────────────────────────────── --}}
            {{--
                Primary open mechanism: Bootstrap data-bs-toggle="modal" +
                data-bs-target — works with zero JavaScript, immune to
                the scanner's stopImmediatePropagation.
                Secondary: onclick calls window.openBookShowModal (exposed
                in app.js) for programmatic use and scanner-triggered opens.
                Keyboard (Enter/Space) is handled by a delegated listener
                in the push-scripts block below.
            --}}
            <div class="book-card {{ $isAvailable ? '' : 'book-card--unavailable' }}"
                 data-book-id="{{ $book->id }}"
                 data-title="{{ mb_strtolower($book->title) }}"
                 data-author="{{ mb_strtolower($book->author) }}"
                 data-copy-barcodes="{{ json_encode($copyBarcodes) }}"
                 data-bs-toggle="modal"
                 data-bs-target="#showModal{{ $book->id }}"
                 role="button"
                 tabindex="0"
                 aria-label="Open details for {{ $book->title }}"
                 aria-haspopup="dialog">

                {{-- Availability badge --}}
                <span class="book-card__badge {{ $isAvailable ? 'book-card__badge--available' : 'book-card__badge--unavailable' }}">
                    {{ $isAvailable ? '✅ Available' : '❌ Unavailable' }}
                </span>

                {{-- Title & author --}}
                <h3 class="book-card__title">{{ $book->title }}</h3>
                <p class="book-card__author">by {{ $book->author }}</p>

                {{-- Copy counter --}}
                <div class="book-card__copies">
                    <span class="book-card__copies-available">{{ $availableCopies }}</span>
                    <span class="book-card__copies-label"> / {{ $totalCopies }} copies available</span>
                </div>

                {{-- Copy barcodes (small print, useful for librarian reference) --}}
                @if($book->bookCopies->count() > 0)
                    <div class="book-card__barcodes">
                        @foreach($book->bookCopies as $copy)
                            <span class="book-card__barcode-chip
                                         {{ $copy->status === 'available' ? 'book-card__barcode-chip--available' : 'book-card__barcode-chip--borrowed' }}"
                                  title="{{ $copy->copy_number }} — {{ ucfirst($copy->status) }}">
                                {{ \App\Models\BookCopy::normalizeBarcode($copy->barcode) }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="book-card__cta">Click or scan to open</div>
            </div>

            {{-- ── Per-book show modal ──────────────────────────────────── --}}
            {{--
                IMPORTANT: the id must be "showModal{book->id}".
                The openBookShowModal() function in app.js looks for
                exactly this id pattern.
            --}}
            <div class="modal fade" id="showModal{{ $book->id }}" tabindex="-1"
                 aria-labelledby="showModalLabel{{ $book->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">

                        <div class="modal-header show-modal-header">
                            <div>
                                <h5 class="modal-title fw-bold" id="showModalLabel{{ $book->id }}">
                                    {{ $book->title }}
                                </h5>
                                <small class="text-white-50">by {{ $book->author }}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white"
                                    data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">

                            {{-- ── Availability summary ─────────────────── --}}
                            <div class="show-modal-availability
                                        {{ $isAvailable ? 'show-modal-availability--ok' : 'show-modal-availability--full' }}">
                                @if($isAvailable)
                                    ✅ <strong>{{ $availableCopies }}</strong> of
                                    <strong>{{ $totalCopies }}</strong> copies available
                                @else
                                    ❌ All <strong>{{ $totalCopies }}</strong> copies currently borrowed
                                @endif
                            </div>

                            {{-- ── Copy list ────────────────────────────── --}}
                            @if($book->bookCopies->count() > 0)
                                <h6 class="fw-bold mb-2 mt-3">Book Copies</h6>
                                <div class="show-modal-copies-grid">
                                    @foreach($book->bookCopies as $copy)
                                        <div class="show-modal-copy
                                                    {{ $copy->status === 'available'
                                                        ? 'show-modal-copy--available'
                                                        : ($copy->status === 'damaged'
                                                            ? 'show-modal-copy--damaged'
                                                            : 'show-modal-copy--borrowed') }}">
                                            <div class="show-modal-copy__number">
                                                {{ $copy->copy_number ?? 'Copy' }}
                                            </div>
                                            <div class="show-modal-copy__barcode">
                                                {{ \App\Models\BookCopy::normalizeBarcode($copy->barcode) }}
                                            </div>
                                            <div class="show-modal-copy__status">
                                                @if($copy->status === 'available')
                                                     Available
                                                @elseif($copy->status === 'damaged')
                                                    🔧 Damaged
                                                @else
                                                     Borrowed
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- ── Borrow form (shown only when copies available) ── --}}
                            @if($isAvailable)
                                <div class="show-modal-borrow-section mt-4">
                                    <h6 class="fw-bold mb-3"> Borrow This Book</h6>
                                    <form class="book-borrow-form"
                                          action="{{ route('borrow.store') }}"
                                          data-book-id="{{ $book->id }}">
                                        @csrf
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <input type="text"
                                                       id="modal_student_name_{{ $book->id }}"
                                                       name="student_name"
                                                       class="form-control"
                                                       placeholder="Student Name"
                                                       autocomplete="off"
                                                       required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text"
                                                       id="modal_course_{{ $book->id }}"
                                                       name="course"
                                                       class="form-control"
                                                       placeholder="Course"
                                                       autocomplete="off"
                                                       required>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text"
                                                       id="modal_section_{{ $book->id }}"
                                                       name="section"
                                                       class="form-control"
                                                       placeholder="Section"
                                                       autocomplete="off"
                                                       required>
                                            </div>
                                        </div>
                                        <div class="alert borrow-form-alert d-none mt-2" role="alert"></div>
                                    </form>
                                </div>
                            @endif

                            {{-- ── Borrowing history (loaded via AJAX) ──── --}}
                            <div class="show-modal-history-section mt-4"
                                 id="historySection{{ $book->id }}">
                                <h6 class="fw-bold mb-2"> Borrowing History</h6>
                                <div class="show-modal-history-loading text-muted text-center py-2">
                                    <small>Loading history…</small>
                                </div>
                                <div class="show-modal-history-content d-none"></div>
                            </div>

                        </div>{{-- /modal-body --}}

                        <div class="modal-footer justify-content-between">
                            <div class="d-flex gap-2">
                                {{-- Barcode sticker link --}}
                                @if($book->bookCopies->count() > 0)
                                    <a href="{{ route('books.barcode.sticker', $book->id) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       target="_blank">
                                        🏷️ Print Stickers
                                    </a>
                                @endif

                                {{-- Edit link --}}
                                <a href="{{ route('books.edit', $book->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Edit
                                </a>
                            </div>

                            <div class="d-flex gap-2 align-items-center">
                                {{-- Borrow submit (visible when available) --}}
                                @if($isAvailable)
                                    <button type="button"
                                            class="btn btn-success borrow-submit-btn"
                                            data-book-id="{{ $book->id }}">
                                        Confirm Borrow
                                    </button>
                                @endif

                                {{-- Delete button --}}
                                <form action="{{ route('books.destroy', $book->id) }}"
                                      method="POST"
                                      class="book-delete-form"
                                      onsubmit="return confirm('Delete \'{{ addslashes($book->title) }}\'? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                         Delete
                                    </button>
                                </form>

                                <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {{-- /showModal{{ $book->id }} --}}

        @endforeach

                </div>{{-- /book-grid --}}
            </div>{{-- /alpha-section --}}

        @endforeach

    </div>{{-- /bookGrid --}}

@else
    <div class="no-records">
        <div class="no-records-icon">📚</div>
        <h3>No Books Yet</h3>
        <p>Use the "Add New Book" button above to add your first book.</p>
    </div>
@endif

@endsection


{{-- ═══════════════════════════════════════════════════════════════════════
     Page-specific styles
     ═══════════════════════════════════════════════════════════════════════ --}}
@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════════════
   Books page styles
   ═══════════════════════════════════════════════════════════════════════ */

/* ── Toolbar (add button + search) ───────────────────────────────────── */
.books-toolbar {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}
.books-toolbar__add { flex-shrink: 0; }
.books-toolbar__search { flex: 1; min-width: 220px; }

/* ── Search bar ───────────────────────────────────────────────────────── */
.books-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.books-search-icon {
    position: absolute;
    left: 0.85rem;
    font-size: 1rem;
    pointer-events: none;
    z-index: 1;
}
.books-search-input {
    width: 100%;
    padding: 0.55rem 2.4rem 0.55rem 2.3rem;
    border: 1.5px solid #dee2e6;
    border-radius: 2rem;
    font-size: 0.92rem;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
}
.books-search-input:focus {
    border-color: #198754;
    box-shadow: 0 0 0 3px rgba(25,135,84,0.15);
}
.books-search-input::placeholder { color: #adb5bd; }
.books-search-clear {
    position: absolute;
    right: 0.7rem;
    background: none;
    border: none;
    color: #adb5bd;
    font-size: 0.85rem;
    cursor: pointer;
    padding: 0.15rem 0.3rem;
    border-radius: 50%;
    line-height: 1;
    display: none;
}
.books-search-clear:hover { background: #f0f0f0; color: #495057; }
.books-search-clear.visible { display: block; }
.books-search-meta {
    font-size: 0.78rem;
    color: #6c757d;
    margin-top: 0.25rem;
    padding-left: 0.9rem;
    min-height: 1rem;
}

/* ── Scanner hint bar ─────────────────────────────────────────────────── */
.scanner-hint-bar {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: #e8f5e9;
    border: 1px solid #a5d6a7;
    border-radius: 0.6rem;
    padding: 0.65rem 1.1rem;
    margin-bottom: 1.5rem;
    font-size: 0.88rem;
    color: #1b5e20;
    font-weight: 500;
}
.scanner-hint-bar .scanner-icon { font-size: 1rem; }

/* ── Alphabet jump-bar ────────────────────────────────────────────────── */
.alpha-jumpbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-bottom: 1.75rem;
    padding: 0.6rem 0.8rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.6rem;
}
.alpha-jumpbar__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.4rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: #198754;
    text-decoration: none;
    background: #fff;
    border: 1px solid #c3e6cb;
    transition: background 0.15s, color 0.15s, transform 0.1s;
    letter-spacing: 0.3px;
}
.alpha-jumpbar__link:hover {
    background: #198754;
    color: #fff;
    border-color: #198754;
    transform: translateY(-2px);
}
.alpha-jumpbar__link.alpha-hidden { display: none; }

/* ── Letter section ───────────────────────────────────────────────────── */
.alpha-section { margin-bottom: 2.5rem; }
.alpha-section__header {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #198754;
}
.alpha-section__letter {
    font-size: 1.9rem;
    font-weight: 900;
    color: #198754;
    line-height: 1;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.alpha-section__count {
    font-size: 0.78rem;
    color: #6c757d;
    font-weight: 500;
    background: #f0fdf4;
    border: 1px solid #c3e6cb;
    border-radius: 1rem;
    padding: 0.15rem 0.55rem;
}

/* ── Book grid ────────────────────────────────────────────────────────── */
.book-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.25rem;
    margin-bottom: 0;
}

/* ── Book card ────────────────────────────────────────────────────────── */
.book-card {
    position: relative;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 1rem;
    padding: 1.4rem 1.2rem 1rem;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    text-align: center;
    outline: none;
    user-select: none;
}
.book-card:hover,
.book-card:focus {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(25, 135, 84, 0.15);
    border-color: #198754;
}
.book-card:focus { box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.3); }
.book-card--unavailable { opacity: 0.72; }
.book-card--unavailable:hover { border-color: #dc3545; box-shadow: 0 6px 18px rgba(220,53,69,0.1); }

/* ── Card badge ───────────────────────────────────────────────────────── */
.book-card__badge {
    position: absolute;
    top: 0.65rem;
    right: 0.65rem;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 0.4rem;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.book-card__badge--available   { background: #d4edda; color: #155724; }
.book-card__badge--unavailable { background: #f8d7da; color: #721c24; }

/* ── Card body elements ───────────────────────────────────────────────── */
.book-card__icon  { font-size: 2.8rem; margin-bottom: 0.5rem; }
.book-card__title {
    font-size: 1rem; font-weight: 700; color: #2c3e50;
    margin: 0 0 0.2rem; line-height: 1.3;
    /* two-line clamp */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.book-card__author { font-size: 0.83rem; color: #6c757d; margin: 0 0 0.6rem; }

.book-card__copies { font-size: 0.82rem; margin-bottom: 0.5rem; }
.book-card__copies-available { font-size: 1.3rem; font-weight: 800; color: #198754; }
.book-card__copies-label     { color: #6c757d; }

.book-card__barcodes {
    display: flex; flex-wrap: wrap;
    gap: 0.25rem; justify-content: center;
    margin: 0.4rem 0 0.6rem;
}
.book-card__barcode-chip {
    font-size: 0.65rem; font-family: monospace; font-weight: 600;
    padding: 0.15rem 0.4rem; border-radius: 0.3rem;
    letter-spacing: 0.5px;
}
.book-card__barcode-chip--available { background: #d4edda; color: #155724; }
.book-card__barcode-chip--borrowed  { background: #fff3cd; color: #856404; }

.book-card__cta {
    font-size: 0.72rem; color: #adb5bd; font-style: italic; margin-top: 0.4rem;
}

/* ── Show modal header ────────────────────────────────────────────────── */
.show-modal-header {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
    color: #fff;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 1.1rem 1.4rem;
}

/* ── Availability banner ──────────────────────────────────────────────── */
.show-modal-availability {
    padding: 0.7rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    text-align: center;
}
.show-modal-availability--ok   { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.show-modal-availability--full { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; }

/* ── Copy grid inside modal ───────────────────────────────────────────── */
.show-modal-copies-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 0.5rem;
}
.show-modal-copy {
    border-radius: 0.5rem;
    padding: 0.5rem 0.4rem;
    text-align: center;
    border: 1px solid #dee2e6;
}
.show-modal-copy--available { background: #f0fdf4; border-color: #a5d6a7; }
.show-modal-copy--borrowed  { background: #fffbeb; border-color: #fde68a; }
.show-modal-copy--damaged   { background: #fef2f2; border-color: #fca5a5; }

.show-modal-copy__number  { font-size: 0.72rem; font-weight: 700; color: #374151; margin-bottom: 0.15rem; }
.show-modal-copy__barcode { font-size: 0.65rem; font-family: monospace; color: #6c757d; margin-bottom: 0.15rem; word-break: break-all; }
.show-modal-copy__status  { font-size: 0.68rem; font-weight: 600; }

/* ── Borrow form section ──────────────────────────────────────────────── */
.show-modal-borrow-section {
    background: #f8f9fa;
    border-radius: 0.6rem;
    padding: 1rem 1rem 0.5rem;
    border: 1px solid #dee2e6;
}

/* ── History section ──────────────────────────────────────────────────── */
.show-modal-history-section { border-top: 1px solid #f0f0f0; padding-top: 0.75rem; }
.show-modal-history-table {
    width: 100%; font-size: 0.8rem;
    border-collapse: collapse; margin-top: 0.4rem;
}
.show-modal-history-table th {
    background: #198754; color: #fff;
    padding: 0.4rem 0.6rem; text-align: left;
    font-size: 0.75rem; text-transform: uppercase;
}
.show-modal-history-table td { padding: 0.4rem 0.6rem; border-bottom: 1px solid #f0f0f0; }
.show-modal-history-table tbody tr:last-child td { border-bottom: none; }
.show-modal-history-table tbody tr:hover { background: #f8f9fa; }

.history-tag {
    display: inline-block; padding: 0.15rem 0.4rem;
    border-radius: 0.3rem; font-size: 0.72rem; font-weight: 600;
}
.history-tag--returned { background: #d4edda; color: #155724; }
.history-tag--borrowed { background: #fff3cd; color: #856404; }
.history-tag--overdue  { background: #f8d7da; color: #721c24; }

/* ── Responsive ───────────────────────────────────────────────────────── */
@media (max-width: 576px) {
    .book-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
    .book-card { padding: 1rem 0.8rem 0.8rem; }
    .book-card__icon { font-size: 2rem; }
    .show-modal-copies-grid { grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); }
}
</style>
@endpush


{{-- ═══════════════════════════════════════════════════════════════════════
     Page-specific JavaScript
     ═══════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
/**
 * books/index.blade.php — inline JS
 *
 * Responsibilities:
 *  1. Expose window.__barcodeBookIndex so the global scanner in app.js
 *     can resolve a copy barcode → book id without an AJAX call.
 *  2. Load borrowing history lazily when a show-modal is first opened.
 *  3. Handle the borrow form AJAX submission inside the show-modal.
 *  4. Wire the "Confirm Borrow" footer button to the form inside the modal.
 */

(function () {
    'use strict';

    // ── 0. Live search ────────────────────────────────────────────────────
    //
    // Filters book cards and alpha-section headers as the user types.
    // No server round-trip — all books are already in the DOM.
    // Matches against data-title and data-author (lowercase, trimmed).
    // Hides empty letter sections and their jumpbar links automatically.

    var searchInput  = document.getElementById('bookSearchInput');
    var searchClear  = document.getElementById('bookSearchClear');
    var searchMeta   = document.getElementById('bookSearchMeta');
    var noResults    = document.getElementById('noSearchResults');
    var jumpbar      = document.getElementById('alphaJumpbar');

    function runSearch() {
        var raw   = searchInput ? searchInput.value : '';
        var query = raw.trim().toLowerCase();

        // Toggle clear button
        if (searchClear) {
            if (query.length > 0) {
                searchClear.classList.add('visible');
            } else {
                searchClear.classList.remove('visible');
            }
        }

        var cards        = document.querySelectorAll('.book-card[data-book-id]');
        var totalVisible = 0;

        cards.forEach(function (card) {
            var title  = (card.getAttribute('data-title')  || '').toLowerCase();
            var author = (card.getAttribute('data-author') || '').toLowerCase();
            var match  = query === '' || title.indexOf(query) !== -1 || author.indexOf(query) !== -1;
            card.style.display = match ? '' : 'none';
            if (match) totalVisible++;
        });

        // Hide/show alpha sections and their jumpbar links
        var sections = document.querySelectorAll('.alpha-section');
        sections.forEach(function (section) {
            var letter         = section.getAttribute('data-letter');
            var visibleInGroup = section.querySelectorAll('.book-card:not([style*="display: none"])').length;
            var hide           = visibleInGroup === 0;
            section.style.display = hide ? 'none' : '';

            // Sync jumpbar link
            if (jumpbar && letter) {
                var link = jumpbar.querySelector('[href="#alpha-' + letter + '"]');
                if (link) {
                    if (hide) link.classList.add('alpha-hidden');
                    else      link.classList.remove('alpha-hidden');
                }
            }
        });

        // No-results message
        if (noResults) {
            noResults.style.display = (query !== '' && totalVisible === 0) ? '' : 'none';
        }

        // Search meta text
        if (searchMeta) {
            if (query === '') {
                searchMeta.textContent = '';
            } else if (totalVisible === 0) {
                searchMeta.textContent = 'No books found';
            } else {
                searchMeta.textContent = totalVisible + ' book' + (totalVisible === 1 ? '' : 's') + ' found';
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', runSearch);
        searchInput.addEventListener('keydown', function (e) {
            // Escape clears the search
            if (e.key === 'Escape') {
                searchInput.value = '';
                runSearch();
                searchInput.blur();
            }
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function () {
            if (searchInput) searchInput.value = '';
            runSearch();
            if (searchInput) searchInput.focus();
        });
    }

    // ── 1. Expose barcode → bookId lookup map for the scanner ────────────
    var grid = document.getElementById('bookGrid');
    if (grid) {
        try {
            window.__barcodeBookIndex = JSON.parse(
                grid.getAttribute('data-barcode-index') || '{}'
            );
        } catch (e) {
            window.__barcodeBookIndex = {};
            console.warn('[Books] Could not parse barcode index:', e);
        }
    }

    // ── 1b. Background repair sweep ──────────────────────────────────────
    // On page load, silently repair any book that has no copy chips rendered.
    // This creates missing book_copies rows and syncs available_copies for
    // books that were inserted via raw SQL (bypassing Eloquent boot hooks).
    // Each repair runs as a separate sequential fetch so the page stays
    // responsive. We stagger them with a small delay to avoid hammering the
    // server simultaneously.
    (function runBackgroundRepairs() {
        var cards = document.querySelectorAll('.book-card[data-book-id]');
        var booksNeedingRepair = [];

        cards.forEach(function (card) {
            var bookId  = card.getAttribute('data-book-id');
            var modal   = document.getElementById('showModal' + bookId);
            if (!modal) return;
            var copiesGrid = modal.querySelector('.show-modal-copies-grid');
            var hasCopies  = copiesGrid && copiesGrid.children.length > 0;
            // Also check if the card itself has no barcode chips
            var hasChips = card.querySelector('.book-card__barcodes') &&
                           card.querySelector('.book-card__barcode-chip');
            if (!hasCopies || !hasChips) {
                booksNeedingRepair.push(bookId);
            }
        });

        if (booksNeedingRepair.length === 0) return;

        console.log('[Books] Background repair needed for', booksNeedingRepair.length, 'book(s)');

        // Process one at a time with a small stagger
        booksNeedingRepair.forEach(function (bookId, idx) {
            setTimeout(function () {
                var modal = document.getElementById('showModal' + bookId);
                if (modal && !modal.dataset.repaired) {
                    repairAndRenderCopies(modal, bookId);
                }
            }, idx * 300); // 300 ms between each repair request
        });
    }());

    // ── 2. On modal open: repair missing copies (if needed) + lazy-load history
    // Uses historySection.dataset.loaded as the flag (instead of a closure
    // variable) so that resetHistorySection() can clear it from outside and
    // force a re-fetch after a successful borrow — without a page reload.
    document.querySelectorAll('[id^="showModal"]').forEach(function (modalEl) {
        var bookId = modalEl.id.replace('showModal', '');
        var historySection = document.getElementById('historySection' + bookId);

        modalEl.addEventListener('show.bs.modal', function () {
            // ── 2a. Repair missing copies on first open ───────────────────
            // If the server rendered zero copy chips (book was seeded via raw
            // SQL and never got book_copies rows), call the repair endpoint
            // once to create them, then re-render the copies section live.
            var copiesGrid = modalEl.querySelector('.show-modal-copies-grid');
            var hasCopies  = copiesGrid && copiesGrid.children.length > 0;

            if (!hasCopies && !modalEl.dataset.repaired) {
                repairAndRenderCopies(modalEl, bookId);
            }

            // ── 2b. Lazy-load history ─────────────────────────────────────
            if (!historySection) return;
            // dataset.loaded === '1' means already fetched; anything else → fetch
            if (historySection.dataset.loaded === '1') return;

            var loadingEl = historySection.querySelector('.show-modal-history-loading');
            var contentEl = historySection.querySelector('.show-modal-history-content');

            fetch('/books/' + bookId + '/history', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json'
                }
            })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                if (!data.success) {
                    contentEl.innerHTML = '<p class="text-muted text-center py-2"><small>Could not load history.</small></p>';
                    contentEl.classList.remove('d-none');
                    if (loadingEl) loadingEl.classList.add('d-none');
                    return;
                }

                if (!data.history || data.history.length === 0) {
                    contentEl.innerHTML = '<p class="text-muted text-center py-2"><small>No borrowing history yet.</small></p>';
                } else {
                    var rows = data.history.map(function (record) {
                        var borrowedDate = record.borrowed_at
                            ? new Date(record.borrowed_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})
                            : '—';
                        var returnedDate = record.returned_at
                            ? new Date(record.returned_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'})
                            : '—';
                        var statusTag = record.returned_at
                            ? '<span class="history-tag history-tag--returned">Returned</span>'
                            : (record.is_overdue
                                ? '<span class="history-tag history-tag--overdue">⚠ Overdue</span>'
                                : '<span class="history-tag history-tag--borrowed">Borrowed</span>');

                        return '<tr>' +
                            '<td>' + escHtml(record.student_name) + '</td>' +
                            '<td>' + escHtml((record.course || '') + ' ' + (record.section || '')) + '</td>' +
                            '<td>' + borrowedDate + '</td>' +
                            '<td>' + returnedDate + '</td>' +
                            '<td>' + statusTag + '</td>' +
                        '</tr>';
                    }).join('');

                    contentEl.innerHTML =
                        '<div style="max-height:220px;overflow-y:auto;border-radius:0.5rem;overflow:hidden;">' +
                        '<table class="show-modal-history-table">' +
                            '<thead><tr>' +
                                '<th>Student</th>' +
                                '<th>Course/Section</th>' +
                                '<th>Borrowed</th>' +
                                '<th>Returned</th>' +
                                '<th>Status</th>' +
                            '</tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                        '</div>';
                }

                contentEl.classList.remove('d-none');
                if (loadingEl) loadingEl.classList.add('d-none');
                historySection.dataset.loaded = '1'; // cleared by resetHistorySection() on borrow
            })
            .catch(function (err) {
                if (contentEl) {
                    contentEl.innerHTML = '<p class="text-danger text-center py-2"><small>Error loading history.</small></p>';
                    contentEl.classList.remove('d-none');
                }
                if (loadingEl) loadingEl.classList.add('d-none');
                console.error('[BookHistory] Error:', err);
            });
        });
    });

    // ── 3a. Repair and render copies for books with missing book_copies ────
    //
    // Called on first modal open when no copy chips exist in the DOM.
    // Hits POST /books/{id}/repair which creates missing BookCopy rows,
    // reconciles available_copies, and returns the full copy list.
    // Re-renders the copies grid, availability banner, card chips, and
    // barcode index — all without a page reload.

    function repairAndRenderCopies(modalEl, bookId) {
        // Mark immediately so concurrent rapid opens don't double-call
        modalEl.dataset.repaired = '1';

        fetch('/books/' + bookId + '/repair', {
            method:  'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'Accept':           'application/json',
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success || !data.copies) return;

            var newAvailable = parseInt(data.available_copies, 10);
            var newTotal     = parseInt(data.total_copies, 10);

            // 1. Re-render the copies grid inside the modal
            renderCopiesGrid(modalEl, data.copies);

            // 2. Update the availability banner
            updateModalBanner(modalEl, newAvailable, newTotal);

            // 3. Show the borrow form if it was hidden due to 0 copies at render time
            if (newAvailable > 0) {
                var borrowSection = modalEl.querySelector('.show-modal-borrow-section');
                var confirmBtn    = modalEl.querySelector('.borrow-submit-btn');
                // Only show if element exists; if it was never rendered (server saw 0
                // copies), inject a minimal borrow form now.
                if (!borrowSection) {
                    injectBorrowForm(modalEl, bookId);
                } else {
                    borrowSection.style.display = '';
                }
                if (confirmBtn) confirmBtn.style.display = '';
            }

            // 4. Update the book card chips on the grid
            renderCardChips(bookId, data.copies);

            // 5. Update the card counter
            var card = document.querySelector('.book-card[data-book-id="' + bookId + '"]');
            if (card) {
                var availEl = card.querySelector('.book-card__copies-available');
                if (availEl) availEl.textContent = newAvailable;
                updateCardBadge(card, newAvailable <= 0);
            }

            // 6. Extend the global barcode index with the new copy barcodes
            if (!window.__barcodeBookIndex) window.__barcodeBookIndex = {};
            data.copies.forEach(function (copy) {
                if (copy.barcode) {
                    window.__barcodeBookIndex[copy.barcode.toUpperCase()] = parseInt(bookId, 10);
                }
            });
        })
        .catch(function (err) {
            console.error('[Repair] Failed for book ' + bookId + ':', err);
            // Reset the flag so the user can retry by reopening the modal
            delete modalEl.dataset.repaired;
        });
    }

    // Build the copies grid HTML and inject it into the modal body
    function renderCopiesGrid(modalEl, copies) {
        // Find or create the copies grid container
        var grid = modalEl.querySelector('.show-modal-copies-grid');

        if (!grid) {
            // The server rendered no copies section at all — build one
            var heading = document.createElement('h6');
            heading.className   = 'fw-bold mb-2 mt-3';
            heading.textContent = 'Book Copies';

            grid = document.createElement('div');
            grid.className = 'show-modal-copies-grid';

            var banner = modalEl.querySelector('.show-modal-availability');
            var parent = banner ? banner.parentNode : modalEl.querySelector('.modal-body');
            if (banner && parent) {
                parent.insertBefore(heading, banner.nextSibling);
                parent.insertBefore(grid,    heading.nextSibling);
            }
        }

        grid.innerHTML = copies.map(function (copy) {
            var statusClass = copy.status === 'available'
                ? 'show-modal-copy--available'
                : (copy.status === 'damaged'
                    ? 'show-modal-copy--damaged'
                    : 'show-modal-copy--borrowed');
            var statusIcon = copy.status === 'available'
                ? '✅ Available'
                : (copy.status === 'damaged' ? '🔧 Damaged' : '📤 Borrowed');

            return '<div class="show-modal-copy ' + statusClass + '">' +
                       '<div class="show-modal-copy__number">'  + escHtml(copy.copy_number) + '</div>' +
                       '<div class="show-modal-copy__barcode">' + escHtml(copy.barcode)     + '</div>' +
                       '<div class="show-modal-copy__status">'  + statusIcon                + '</div>' +
                   '</div>';
        }).join('');
    }

    // Update or create the card-level barcode chips
    function renderCardChips(bookId, copies) {
        var card = document.querySelector('.book-card[data-book-id="' + bookId + '"]');
        if (!card) return;

        var container = card.querySelector('.book-card__barcodes');
        if (!container) {
            container = document.createElement('div');
            container.className = 'book-card__barcodes';
            var ctaEl = card.querySelector('.book-card__cta');
            if (ctaEl) card.insertBefore(container, ctaEl);
            else card.appendChild(container);
        }

        container.innerHTML = copies.map(function (copy) {
            var chipClass = copy.status === 'available'
                ? 'book-card__barcode-chip--available'
                : 'book-card__barcode-chip--borrowed';
            return '<span class="book-card__barcode-chip ' + chipClass + '" ' +
                       'title="' + escHtml(copy.copy_number) + ' — ' + escHtml(copy.status) + '">' +
                       escHtml(copy.barcode) +
                   '</span>';
        }).join('');
    }

    // Update the availability banner text and colour
    function updateModalBanner(modalEl, newAvailable, newTotal) {
        var banner = modalEl.querySelector('.show-modal-availability');
        if (!banner) return;
        if (newAvailable <= 0) {
            banner.className = 'show-modal-availability show-modal-availability--full';
            banner.innerHTML = '❌ All <strong>' + newTotal + '</strong> copies currently borrowed';
        } else {
            banner.className = 'show-modal-availability show-modal-availability--ok';
            banner.innerHTML = '✅ <strong>' + newAvailable + '</strong> of <strong>'
                             + newTotal + '</strong> copies available';
        }
    }

    // Update the card availability badge and dim class
    function updateCardBadge(card, noStock) {
        var badge = card.querySelector('.book-card__badge');
        if (badge) {
            if (noStock) {
                badge.textContent = '❌ Unavailable';
                badge.className   = 'book-card__badge book-card__badge--unavailable';
                card.classList.add('book-card--unavailable');
            } else {
                badge.textContent = '✅ Available';
                badge.className   = 'book-card__badge book-card__badge--available';
                card.classList.remove('book-card--unavailable');
            }
        }
    }

    // Dynamically inject a borrow form for books that had 0 copies at render time
    // (server-side if-isAvailable was false, so no form HTML was emitted)
    function injectBorrowForm(modalEl, bookId) {
        var modalBody = modalEl.querySelector('.modal-body');
        if (!modalBody) return;

        // Borrow section
        var section       = document.createElement('div');
        section.className = 'show-modal-borrow-section mt-4';
        section.innerHTML =
            '<h6 class="fw-bold mb-3">📝 Borrow This Book</h6>' +
            '<form class="book-borrow-form" action="{{ route('borrow.store') }}" data-book-id="' + bookId + '">' +
                '<input type="hidden" name="_token" value="' + document.querySelector("meta[name=csrf-token]").content + '">' +
                '<input type="hidden" name="book_id" value="' + bookId + '">' +
                '<div class="row g-2">' +
                    '<div class="col-md-12">' +
                        '<input type="text" id="modal_student_name_' + bookId + '" name="student_name" ' +
                               'class="form-control" placeholder="Student Name" autocomplete="off" required>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<input type="text" id="modal_course_' + bookId + '" name="course" ' +
                               'class="form-control" placeholder="Course" autocomplete="off" required>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<input type="text" id="modal_section_' + bookId + '" name="section" ' +
                               'class="form-control" placeholder="Section" autocomplete="off" required>' +
                    '</div>' +
                '</div>' +
                '<div class="alert borrow-form-alert d-none mt-2" role="alert"></div>' +
            '</form>';

        modalBody.appendChild(section);

        // Confirm Borrow button in the footer
        var footer     = modalEl.querySelector('.modal-footer .d-flex.gap-2.align-items-center');
        var closeBtn   = footer ? footer.querySelector('[data-bs-dismiss="modal"]') : null;
        if (footer && closeBtn) {
            var confirmBtn       = document.createElement('button');
            confirmBtn.type      = 'button';
            confirmBtn.className = 'btn btn-success borrow-submit-btn';
            confirmBtn.setAttribute('data-book-id', bookId);
            confirmBtn.textContent = '📥 Confirm Borrow';
            confirmBtn.addEventListener('click', function () {
                submitBorrowForm(bookId);
            });
            footer.insertBefore(confirmBtn, closeBtn);
        }
    }

    // ── 3. Borrow form AJAX handler ───────────────────────────────────────
    //
    // On SUCCESS: updates the DOM in-place — no page reload.
    //   • Availability banner (count + colour)
    //   • Copy status chips inside the modal (first available → borrowed)
    //   • Barcode chips on the card (first available → borrowed)
    //   • Borrow form + Confirm button hidden when 0 copies remain
    //   • Book card badge + counter updated
    //   • Form fields cleared, history reset, modal closed, toastr shown
    // On FAILURE: inline alert inside the modal — no reload.

    function submitBorrowForm(bookId) {
        var modal     = document.getElementById('showModal' + bookId);
        if (!modal) return;

        var form      = modal.querySelector('.book-borrow-form');
        var alertEl   = modal.querySelector('.borrow-form-alert');
        var submitBtn = modal.querySelector('.borrow-submit-btn');

        if (!form) return;

        // Client-side validation
        var studentNameEl = form.querySelector('[name="student_name"]');
        var courseEl      = form.querySelector('[name="course"]');
        var sectionEl     = form.querySelector('[name="section"]');

        var studentName = studentNameEl ? studentNameEl.value.trim() : '';
        var course      = courseEl      ? courseEl.value.trim()      : '';
        var section     = sectionEl     ? sectionEl.value.trim()     : '';

        if (!studentName || !course || !section) {
            showBorrowAlert(alertEl, 'danger', 'Please fill in all fields before borrowing.');
            return;
        }

        if (submitBtn) submitBtn.disabled = true;

        fetch(form.action || '{{ route('borrow.store') }}', {
            method:  'POST',
            body:    new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function (r) {
            // Always parse JSON regardless of HTTP status.
            // If the body is not JSON (e.g. an HTML error page), surface a
            // clear message instead of the generic catch fallback.
            return r.json().catch(function () {
                throw new Error('Server returned a non-JSON response (HTTP ' + r.status + '). Check the Laravel log.');
            });
        })
        .then(function (data) {
            if (!data.success) {
                showBorrowAlert(alertEl, 'danger', data.message || 'Failed to borrow book.');
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            // ── Borrow succeeded ──────────────────────────────────────────
            // Show the success toast first so the user sees confirmation
            // even if any subsequent DOM update throws.
            toastr.success(data.message || 'Book borrowed successfully!');

            // Close the modal before updating the DOM so the user sees the
            // toast against the grid, not inside an open modal.
            try {
                bootstrap.Modal.getOrCreateInstance(modal).hide();
            } catch (e) {
                console.warn('[Borrow] Modal hide error:', e);
            }

            // Update DOM — wrapped so a rendering bug never hides the toast
            try {
                var newAvailable = (data.book && data.book.available_copies !== undefined)
                    ? parseInt(data.book.available_copies, 10) : null;
                var newTotal = (data.book && data.book.copies !== undefined)
                    ? parseInt(data.book.copies, 10) : null;

                updateBookCard(bookId, newAvailable, newTotal);
                updateModalBody(modal, bookId, newAvailable, newTotal);

                if (studentNameEl) studentNameEl.value = '';
                if (courseEl)      courseEl.value      = '';
                if (sectionEl)     sectionEl.value     = '';
                if (alertEl)       alertEl.classList.add('d-none');

                resetHistorySection(bookId);
            } catch (e) {
                // DOM update failed but borrow already succeeded — log only
                console.error('[Borrow] DOM update error:', e);
            }
        })
        .catch(function (err) {
            // Only reached if the fetch itself failed (network error) or
            // r.json() could not parse the response body.
            console.error('[Borrow] Fetch error:', err);
            showBorrowAlert(alertEl, 'danger', err.message || 'Network error. Please check your connection.');
            if (submitBtn) submitBtn.disabled = false;
        });
    }

    // Update the book card on the grid after a successful borrow
    function updateBookCard(bookId, newAvailable, newTotal) {
        var card    = document.querySelector('.book-card[data-book-id="' + bookId + '"]');
        if (!card) return;

        var noStock = (newAvailable !== null && newAvailable <= 0);

        // Copy counter
        var availEl = card.querySelector('.book-card__copies-available');
        if (availEl && newAvailable !== null) availEl.textContent = newAvailable;

        // Reuse shared badge helper
        updateCardBadge(card, noStock);

        // Flip the first available barcode chip to borrowed
        var firstAvailChip = card.querySelector('.book-card__barcode-chip--available');
        if (firstAvailChip) {
            firstAvailChip.classList.remove('book-card__barcode-chip--available');
            firstAvailChip.classList.add('book-card__barcode-chip--borrowed');
        }
    }

    // Update availability banner and copy chips inside the modal
    function updateModalBody(modal, bookId, newAvailable, newTotal) {
        var noStock = (newAvailable !== null && newAvailable <= 0);

        // Reuse shared banner helper
        if (newAvailable !== null && newTotal !== null) {
            updateModalBanner(modal, newAvailable, newTotal);
        }

        // Flip the first available copy chip to borrowed
        var firstAvailCopy = modal.querySelector('.show-modal-copy--available');
        if (firstAvailCopy) {
            firstAvailCopy.classList.remove('show-modal-copy--available');
            firstAvailCopy.classList.add('show-modal-copy--borrowed');
            var statusEl = firstAvailCopy.querySelector('.show-modal-copy__status');
            if (statusEl) statusEl.textContent = '📤 Borrowed';
        }

        // Hide the borrow form and confirm button when no copies remain
        if (noStock) {
            var borrowSection = modal.querySelector('.show-modal-borrow-section');
            if (borrowSection) borrowSection.style.display = 'none';

            var confirmBtn = modal.querySelector('.borrow-submit-btn');
            if (confirmBtn) confirmBtn.style.display = 'none';
        } else {
            // Copies still available — re-enable submit button for next borrow
            var confirmBtn2 = modal.querySelector('.borrow-submit-btn');
            if (confirmBtn2) confirmBtn2.disabled = false;
        }
    }

    // Reset the history section so it re-fetches on the next modal open.
    // Communicates with the show.bs.modal listener in section 2 via a
    // data attribute flag on the history section element.
    function resetHistorySection(bookId) {
        var section = document.getElementById('historySection' + bookId);
        if (!section) return;
        section.dataset.loaded = '';  // cleared flag triggers re-fetch
        var loadingEl = section.querySelector('.show-modal-history-loading');
        var contentEl = section.querySelector('.show-modal-history-content');
        if (loadingEl) loadingEl.classList.remove('d-none');
        if (contentEl) { contentEl.classList.add('d-none'); contentEl.innerHTML = ''; }
    }

    function showBorrowAlert(alertEl, type, message) {
        if (!alertEl) return;
        alertEl.className   = 'alert borrow-form-alert alert-' + type;
        alertEl.textContent = message;
    }

    // ── 4. Wire footer "Confirm Borrow" buttons ───────────────────────────
    document.querySelectorAll('.borrow-submit-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            submitBorrowForm(this.getAttribute('data-book-id'));
        });
    });

    // ── 5. Keyboard delegation for book cards (Enter / Space) ────────────
    //
    // Cards use data-bs-toggle so mouse clicks are handled natively by
    // Bootstrap. We still need to handle Enter/Space for keyboard users
    // because Bootstrap's data-bs-toggle only fires on real click events.
    // The scanner's keydown listener runs at capture phase and calls
    // window.openBookShowModal directly, so it is unaffected by this handler.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var target = e.target;
        if (target && target.classList && target.classList.contains('book-card')) {
            e.preventDefault();
            var bookId = target.getAttribute('data-book-id');
            if (bookId) window.openBookShowModal(bookId);
        }
    });

    // ── Helper: tiny HTML escaper ─────────────────────────────────────────
    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

}());
</script>
@endpush