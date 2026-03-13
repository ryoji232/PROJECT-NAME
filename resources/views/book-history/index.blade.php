@extends('layouts.app')

@section('title', 'Book History — Library System')

@section('content')

<div class="hero-section">
    <h1>Book History</h1>
    <p>Complete borrowing history — all transactions ever recorded</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>All Borrowing Records</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

@include('partials.alerts')

{{-- Stats --}}
<div class="stats-bar">
    <div class="stat-card" style="border-top: 4px solid #198754;">
        <div class="stat-value" style="color:#198754;">{{ $historyStats['total'] }}</div>
        <div class="stat-label">Total Records</div>
    </div>
    <div class="stat-card" style="border-top: 4px solid #0d6efd;">
        <div class="stat-value" style="color:#0d6efd;">{{ $historyStats['returned'] }}</div>
        <div class="stat-label">Returned</div>
    </div>
    <div class="stat-card stat-active" style="border-top: 4px solid #856404;">
        <div class="stat-value">{{ $historyStats['active'] }}</div>
        <div class="stat-label">Still Borrowed</div>
    </div>
    <div class="stat-card stat-overdue" style="border-top: 4px solid #721c24;">
        <div class="stat-value">{{ $historyStats['overdue'] }}</div>
        <div class="stat-label">Overdue</div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('book-history.index') }}" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label fw-semibold">Search</label>
            <input type="text" name="search" class="form-control"
                   placeholder="Student name, course, section, book title or author…"
                   value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-control">
                <option value="">— All Records —</option>
                <option value="borrowed"  {{ request('status') === 'borrowed'  ? 'selected' : '' }}>Currently Borrowed</option>
                <option value="returned"  {{ request('status') === 'returned'  ? 'selected' : '' }}>Returned</option>
                <option value="overdue"   {{ request('status') === 'overdue'   ? 'selected' : '' }}>Overdue</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-success w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('book-history.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="borrowings-table-container">
    @if($historyRecords->count() > 0)
        <div class="table-responsive">
            <table class="borrowings-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Borrower</th>
                        <th>Course & Section</th>
                        <th>Borrowed Date</th>
                        <th>Due Date</th>
                        <th>Returned Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($historyRecords as $record)
                        @php
                            $isReturned = !is_null($record->returned_at);
                            $isOverdue  = !$isReturned && $record->due_date && now()->gt($record->due_date);
                        @endphp
                        <tr class="{{ $isOverdue ? 'row-overdue' : '' }}">
                            <td class="text-muted" style="font-size:.82rem;">
                                {{ ($historyRecords->currentPage() - 1) * $historyRecords->perPage() + $loop->iteration }}
                            </td>
                            <td><strong>{{ $record->book->title ?? 'N/A' }}</strong></td>
                            <td>{{ $record->book->author ?? 'N/A' }}</td>
                            <td>{{ $record->student_name }}</td>
                            <td>{{ $record->course }} - {{ $record->section }}</td>
                            <td>{{ $record->borrowed_at?->format('M d, Y h:i A') ?? $record->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>
                                @if($record->due_date)
                                    <span @if($isOverdue && !$isReturned) style="color:#721c24;font-weight:600;" @endif>
                                        {{ $record->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($isReturned)
                                    <span style="color:#198754;">
                                        {{ $record->returned_at->format('M d, Y h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($isReturned)
                                    <span class="status-badge" style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;">
                                         Returned
                                    </span>
                                @elseif($isOverdue)
                                    <span class="status-badge status-overdue">⚠ Overdue</span>
                                @else
                                    <span class="status-badge status-borrowed">Borrowed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($historyRecords->hasPages())
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                <div class="text-muted" style="font-size:0.875rem;">
                    Showing
                    <strong>{{ $historyRecords->firstItem() }}</strong>–<strong>{{ $historyRecords->lastItem() }}</strong>
                    of <strong>{{ $historyRecords->total() }}</strong> records
                </div>
                <nav aria-label="Book history pagination">
                    {{ $historyRecords->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        @else
            <div class="px-3 py-2 border-top text-muted" style="font-size:0.875rem;">
                Showing all <strong>{{ $historyRecords->total() }}</strong> records
            </div>
        @endif

    @else
        <div class="no-records">
            <div class="no-records-icon">📭</div>
            <h3>No Records Found</h3>
            <p>No borrowing history matches your search or filter.</p>
        </div>
    @endif
</div>

@endsection