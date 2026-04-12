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

{{-- Search & Filter ─────────────────────────────────────────────────────────
     No submit/clear buttons.
     • Typing in the search box debounces 400 ms then fires an AJAX fetch.
     • Changing the filter dropdown fires immediately.
     Both send X-Requested-With: XMLHttpRequest → BorrowingController::index()
     detects this and returns {"table":"…"} instead of a full page response.
──────────────────────────────────────────────────────────────────────────── --}}
<div class="filter-card">
    <div class="row g-2 align-items-end">
        <div class="col-md-8">
            <label class="form-label fw-semibold">Search</label>
            <div style="position:relative;">
                <input type="text"
                       id="borrowingsSearch"
                       class="form-control"
                       placeholder="Student name, course, section, book title or author…"
                       value="{{ request('search') }}"
                       autocomplete="off">
                <div id="borrowingsSpinner"
                     style="display:none;position:absolute;right:.75rem;top:50%;transform:translateY(-50%);">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                        <span class="visually-hidden">Loading…</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Filter</label>
            <select id="borrowingsFilter" class="form-control">
                <option value="">— All Active —</option>
                <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>Overdue Only</option>
            </select>
        </div>
    </div>
</div>

{{-- Table container — AJAX swaps the innerHTML of this div on every filter change --}}
<div class="borrowings-table-container" id="borrowingsTableContainer">
    @include('borrowings.partials.table')
</div>

@endsection

@push('styles')
<style>
#borrowingsTableContainer {
    transition: opacity 0.18s ease;
}
#borrowingsTableContainer.is-loading {
    opacity: 0.35;
    pointer-events: none;
}
</style>
@endpush

@push('scripts')
{{-- ── Return modal ─────────────────────────────────────────────────────────
     openReturnModal() is called by the ↩ Return button rendered inside the
     partial table. It must be defined outside the IIFE so that newly
     injected table HTML (after an AJAX swap) can still reach it.
──────────────────────────────────────────────────────────────────────────── --}}
<script>
function openReturnModal(borrowingId) {
    fetch('/borrowings/' + borrowingId + '/data', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) return;

        var b  = data.borrowing;
        var bk = data.book;

        document.getElementById('returnBookTitle').textContent    = bk.title;
        document.getElementById('returnBookAuthor').textContent   = bk.author;
        document.getElementById('borrowerNameText').textContent   = b.student_name;
        document.getElementById('borrowerCourseText').textContent = b.course + ' - ' + b.section;
        document.getElementById('dueDateText').textContent        = b.due_date;

        var form = document.getElementById('barcodeReturnForm');
        form.action = '/return/' + borrowingId;

        // Reset confirmation state every time the modal opens
        document.getElementById('confirmReturnCheckbox').checked = false;
        document.getElementById('confirmReturnBtn').disabled     = true;
        document.getElementById('statusBorrowed').classList.remove('d-none');
        document.getElementById('statusProcessing').classList.add('d-none');
        document.getElementById('statusCompleted').classList.add('d-none');

        var modal = new bootstrap.Modal(document.getElementById('barcodeReturnModal'));
        modal.show();
    })
    .catch(function (err) {
        console.error('[Return] Error loading borrowing data:', err);
    });
}
</script>

{{-- ── Live filter engine ───────────────────────────────────────────────────
     Mirrors the pattern used by BookHistoryController / book-history/index.
     Key points:
       1. Every fetch sends X-Requested-With so the controller returns JSON.
       2. The AbortController cancels any in-flight request before starting
          a new one — prevents stale responses landing out of order.
       3. After every swap, attachPaginationHandlers() re-wires pagination
          links so they also trigger AJAX rather than full page loads.
──────────────────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    var searchInput  = document.getElementById('borrowingsSearch');
    var filterSelect = document.getElementById('borrowingsFilter');
    var container    = document.getElementById('borrowingsTableContainer');
    var spinner      = document.getElementById('borrowingsSpinner');
    var debounceTimer = null;
    var currentCtrl   = null;

    var baseUrl = '{{ route('borrowings.index') }}';

    // ── Core fetch ────────────────────────────────────────────────────────
    function fetchTable(extraParams) {
        // Cancel the previous in-flight request (if any)
        if (currentCtrl) currentCtrl.abort();
        currentCtrl = new AbortController();

        // Build query string from current input values
        var params = new URLSearchParams();
        var search = searchInput  ? searchInput.value.trim() : '';
        var filter = filterSelect ? filterSelect.value       : '';
        if (search) params.set('search', search);
        if (filter) params.set('filter', filter);

        // Pagination links pass their own params (page number, etc.)
        if (extraParams) {
            extraParams.forEach(function (val, key) {
                params.set(key, val);
            });
        }

        var url = baseUrl + (params.toString() ? '?' + params.toString() : '');

        // Keep the address bar in sync so a browser refresh restores filters
        window.history.replaceState(null, '', url);

        // Visual feedback
        if (container) container.classList.add('is-loading');
        if (spinner)   spinner.style.display = '';

        fetch(url, {
            signal: currentCtrl.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',   // ← triggers AJAX branch in controller
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            if (container && data.table !== undefined) {
                container.innerHTML = data.table;
                attachPaginationHandlers();
            }
        })
        .catch(function (err) {
            if (err.name === 'AbortError') return;  // cancelled intentionally — ignore
            console.error('[Borrowings] fetch error:', err);
        })
        .finally(function () {
            if (container) container.classList.remove('is-loading');
            if (spinner)   spinner.style.display = 'none';
            currentCtrl = null;
        });
    }

    // ── Input listeners ───────────────────────────────────────────────────

    // Debounce typing: wait 400 ms after the last keystroke before fetching
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { fetchTable(); }, 400);
        });
    }

    // Filter dropdown fires immediately on change (no debounce needed)
    if (filterSelect) {
        filterSelect.addEventListener('change', function () { fetchTable(); });
    }

    // ── Pagination handler ────────────────────────────────────────────────
    // Must be called after every container swap because the old links are
    // replaced with new DOM nodes that have no listeners yet.
    function attachPaginationHandlers() {
        if (!container) return;
        container.querySelectorAll('[aria-label="Borrowings pagination"] a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                var href   = this.getAttribute('href');
                var parsed = new URL(href, window.location.origin);

                // Sync the filter inputs with whatever the paginator link carries
                var pSearch = parsed.searchParams.get('search') || '';
                var pFilter = parsed.searchParams.get('filter') || '';
                if (searchInput  && searchInput.value  !== pSearch) searchInput.value  = pSearch;
                if (filterSelect && filterSelect.value !== pFilter) filterSelect.value = pFilter;

                fetchTable(parsed.searchParams);

                // Scroll the table back into view smoothly
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    // Wire pagination on initial page load
    attachPaginationHandlers();

}());
</script>
@endpush