@extends('layouts.app')

@section('title', 'Borrowed Books — Library System')

@section('content')

<div class="hero-section">
    <h1>Borrowed Books</h1>
    <p>All currently borrowed books — process returns from here</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Active Borrowings</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

@include('partials.alerts')

{{-- Stats --}}
<div class="stats-bar">
    <div class="stat-card stat-active" style="border-top: 4px solid #856404;">
        <div class="stat-value">{{ $stats['active'] }}</div>
        <div class="stat-label">Currently Borrowed</div>
    </div>
    <div class="stat-card stat-overdue" style="border-top: 4px solid #721c24;">
        <div class="stat-value">{{ $stats['overdue'] }}</div>
        <div class="stat-label">Overdue</div>
    </div>
</div>

{{-- Search & Filter --}}
<div class="filter-card">
    <form method="GET" action="{{ route('borrowings.index') }}" class="row g-2 align-items-end">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Student name, course, section, book title or author…"
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Filter</label>
            <select name="filter" class="form-control">
                <option value="">— All Active —</option>
                <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>Overdue Only</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">🔍 Search</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('borrowings.index') }}" class="btn btn-outline-secondary w-100">✖ Clear</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="borrowings-table-container">
    @if($borrowings->count() > 0)
        <div class="table-responsive">
            <table class="borrowings-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Copy</th>
                        <th>Borrower</th>
                        <th>Course & Section</th>
                        <th>Borrowed Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Safe row offset: works whether $borrowings is a paginator or plain collection
                        $rowOffset = method_exists($borrowings, 'currentPage')
                            ? ($borrowings->currentPage() - 1) * $borrowings->perPage()
                            : 0;
                    @endphp
                    @foreach($borrowings as $borrowing)
                        @php
                            $isOverdue = $borrowing->due_date && now()->gt($borrowing->due_date);
                        @endphp
                        <tr class="{{ $isOverdue ? 'row-overdue' : '' }}">
                            <td class="text-muted" style="font-size:.82rem;">
                                {{ $rowOffset + $loop->iteration }}
                            </td>
                            <td><strong>{{ $borrowing->book->title ?? 'N/A' }}</strong></td>
                            <td>
                                @if($borrowing->bookCopy)
                                    <span class="text-muted" style="font-size:.82rem;">
                                        {{ $borrowing->bookCopy->copy_number ?? ('Copy #' . $borrowing->bookCopy->id) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $borrowing->student_name }}</td>
                            <td>{{ $borrowing->course }} - {{ $borrowing->section }}</td>
                            <td>
                                {{ $borrowing->borrowed_at?->format('M d, Y h:i A')
                                   ?? $borrowing->created_at?->format('M d, Y h:i A')
                                   ?? 'N/A' }}
                            </td>
                            <td>
                                @if($borrowing->due_date)
                                    <span @if($isOverdue) style="color:#721c24;font-weight:600;" @endif>
                                        {{ $borrowing->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($isOverdue)
                                    <span class="status-badge status-overdue">⚠ Overdue</span>
                                @else
                                    <span class="status-badge status-borrowed">📖 Borrowed</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn-return"
                                        onclick="openReturnModal({{ $borrowing->id }})">
                                    ↩ Return
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination — guarded so it only runs on a real paginator --}}
        @if(method_exists($borrowings, 'hasPages') && $borrowings->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted" style="font-size:0.875rem;">
                    Showing
                    <strong>{{ $borrowings->firstItem() }}</strong>–<strong>{{ $borrowings->lastItem() }}</strong>
                    of <strong>{{ $borrowings->total() }}</strong> records
                </div>
                <nav aria-label="Borrowings pagination">
                    {{ $borrowings->appends(request()->query())->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        @elseif(method_exists($borrowings, 'total'))
            <div class="px-3 py-2 border-top text-muted" style="font-size:0.875rem;">
                Showing all <strong>{{ $borrowings->total() }}</strong> records
            </div>
        @endif

    @else
        <div class="no-records">
            <div class="no-records-icon">📭</div>
            <h3>No Borrowed Books Found</h3>
            <p>
                @if(request('search') || request('filter'))
                    No results match your search or filter.
                    <a href="{{ route('borrowings.index') }}">Clear filters</a>
                @else
                    There are no active borrowings right now.
                @endif
            </p>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function openReturnModal(borrowingId) {
    fetch('/borrowings/' + borrowingId + '/data', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;

        const b  = data.borrowing;
        const bk = data.book;

        document.getElementById('returnBookTitle').textContent    = bk.title;
        document.getElementById('returnBookAuthor').textContent   = bk.author;
        document.getElementById('borrowerNameText').textContent   = b.student_name;
        document.getElementById('borrowerCourseText').textContent = b.course + ' - ' + b.section;
        document.getElementById('dueDateText').textContent        = b.due_date;

        const form = document.getElementById('barcodeReturnForm');
        form.action = '/return/' + borrowingId;

        // Reset modal state
        document.getElementById('confirmReturnCheckbox').checked = false;
        document.getElementById('confirmReturnBtn').disabled     = true;
        document.getElementById('statusBorrowed').classList.remove('d-none');
        document.getElementById('statusProcessing').classList.add('d-none');
        document.getElementById('statusCompleted').classList.add('d-none');

        const modal = new bootstrap.Modal(document.getElementById('barcodeReturnModal'));
        modal.show();
    })
    .catch(err => console.error('Error loading borrowing data:', err));
}
</script>
@endpush