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
    <div class="row g-2 align-items-end">
        {{-- Search input --}}
        <div class="col-md-7">
            <label class="form-label fw-semibold">Search</label>
            <div style="position:relative;">
                <input type="text"
                       id="borrowingsSearch"
                       class="form-control"
                       placeholder="Student name, course, section, book title or author…"
                       value="{{ request('search') }}"
                       autocomplete="off">
                <div id="searchSpinner" style="display:none;position:absolute;right:.75rem;top:50%;transform:translateY(-50%);">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                        <span class="visually-hidden">Loading…</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter dropdown — uses ?status= param, mirrors BookHistoryController --}}
        <div class="col-md-5">
            <label class="form-label fw-semibold">Status</label>
            <select id="borrowingsStatus" class="form-control">
                <option value="active"  {{ request('status', 'active') === 'active'  ? 'selected' : '' }}>— All Active —</option>
                <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue Only</option>
            </select>
        </div>
    </div>

    {{-- Active search indicator --}}
    <div id="borrowingsSearchMeta" class="mt-2" style="font-size:0.82rem; color:#6c757d; min-height:1.2rem;"></div>
</div>

{{-- Table container — AJAX swaps the innerHTML of this div on every filter/search change --}}
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
{{-- ── Return modal ────────────────────────────────────────────────────────────
     openReturnModal() must be global so the ↩ Return button inside the
     AJAX-swapped table HTML can still call it after every container replace.
────────────────────────────────────────────────────────────────────────────── --}}
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

{{-- ── Search & Filter engine ─────────────────────────────────────────────────
     Mirrors BookHistoryController's index.blade.php pattern exactly:
     • Uses ?status= (not ?filter=) — same key the controller reads
     • Empty status value  → no scope applied (all records)
     • 'active'            → whereNull('returned_at')
     • 'overdue'           → whereNull + overdue
     • Debounced live search (400 ms), same as Book History page
     • AbortController cancels in-flight requests on rapid input
────────────────────────────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    var searchInput   = document.getElementById('borrowingsSearch');
    var statusSelect  = document.getElementById('borrowingsStatus');
    var container     = document.getElementById('borrowingsTableContainer');
    var spinner       = document.getElementById('searchSpinner');
    var metaEl        = document.getElementById('borrowingsSearchMeta');
    var debounceTimer = null;
    var currentCtrl   = null;   // AbortController for the in-flight request

    var baseUrl = "{{ route('borrowings.index') }}";

    // ── Core fetch function — identical pattern to Book History ──────────
    function fetchTable(extraParams) {
        if (currentCtrl) currentCtrl.abort();
        currentCtrl = new AbortController();

        var params = new URLSearchParams();
        var search = searchInput  ? searchInput.value.trim() : '';
        var status = statusSelect ? statusSelect.value       : 'active';

        if (search) params.set('search', search);
        if (status) params.set('status', status);

        // Allow pagination links to inject extra params (e.g. page number)
        if (extraParams) {
            extraParams.forEach(function (val, key) {
                params.set(key, val);
            });
        }

        var url = baseUrl + (params.toString() ? '?' + params.toString() : '');

        // Sync the address bar so a browser refresh restores the exact view
        window.history.replaceState(null, '', url);

        // Visual feedback
        if (container) container.classList.add('is-loading');
        if (spinner)   spinner.style.display = '';

        fetch(url, {
            signal:  currentCtrl.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
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

            // Update meta text
            if (metaEl) {
                var rows = container ? container.querySelectorAll('tbody tr').length : 0;
                if (search) {
                    metaEl.textContent = rows > 0
                        ? rows + ' result(s) for "' + search + '"'
                        : 'No results found for "' + search + '"';
                } else {
                    metaEl.textContent = '';
                }
            }
        })
        .catch(function (err) {
            if (err.name === 'AbortError') return;
            console.error('[Borrowings] fetch error:', err);
            if (metaEl) {
                metaEl.innerHTML = '<span style="color:#dc3545;">⚠️ Failed to load results — check your connection and try again.</span>';
            }
        })
        .finally(function () {
            if (container) container.classList.remove('is-loading');
            if (spinner)   spinner.style.display = 'none';
            currentCtrl = null;
        });
    }

    // ── Input listeners — identical to Book History ───────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { fetchTable(); }, 400);
        });

        // Enter triggers immediately
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                fetchTable();
            }
            // Escape clears the search and reloads
            if (e.key === 'Escape') {
                searchInput.value = '';
                clearTimeout(debounceTimer);
                if (metaEl) metaEl.textContent = '';
                fetchTable();
            }
        });
    }

    // Status dropdown — fires immediately on change, same as Book History
    if (statusSelect) {
        statusSelect.addEventListener('change', function () { fetchTable(); });
    }

    // ── Pagination — intercept link clicks after every container swap ─────
    function attachPaginationHandlers() {
        if (!container) return;
        container.querySelectorAll('[aria-label="Borrowings pagination"] a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                var href   = this.getAttribute('href');
                var parsed = new URL(href, window.location.origin);

                // Sync inputs with whatever the paginator link carries
                var pSearch = parsed.searchParams.get('search') || '';
                var pStatus = parsed.searchParams.get('status') || 'active';
                if (searchInput  && searchInput.value  !== pSearch) searchInput.value  = pSearch;
                if (statusSelect && statusSelect.value !== pStatus) statusSelect.value = pStatus;

                fetchTable(parsed.searchParams);

                // Scroll smoothly back to the top of the table
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    attachPaginationHandlers();

}());
</script>
@endpush