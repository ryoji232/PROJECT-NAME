@extends('app')

@section('title', 'Book History - Library System')

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

    .stats-bar { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
    .stat-card {
        flex: 1; min-width: 140px; background: #fff;
        border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 1.25rem 1rem; text-align: center;
    }
    .stat-card .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 0.3rem; }
    .stat-card .stat-label { font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
    .stat-total    .stat-value { color: #198754; }
    .stat-returned .stat-value { color: #0a3622; }
    .stat-active   .stat-value { color: #856404; }
    .stat-overdue  .stat-value { color: #721c24; }

    .filter-card {
        border-radius: 1rem; border: 1px solid #dee2e6;
        background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 1.5rem; margin-bottom: 2rem;
    }
    .filter-label { font-weight: 600; color: #00402c; margin-bottom: 0.5rem; display: block; }
    .filter-input, .filter-select {
        width: 100%; padding: 0.75rem; border: 1px solid #dee2e6;
        border-radius: 0.5rem; font-size: 0.95rem; background: #f8f9fa;
    }
    .filter-input:focus, .filter-select:focus {
        outline: none; border-color: #198754;
        box-shadow: 0 0 0 3px rgba(25,135,84,0.1); background: white;
    }

    .btn-primary {
        background: #198754; border: none; border-radius: 8px;
        font-weight: 600; transition: all 0.2s ease; padding: 10px 18px; color: white;
    }
    .btn-primary:hover { background: #157347; transform: scale(1.03); color: white; }
    .btn-light {
        background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px;
        font-weight: 600; transition: all 0.2s ease; padding: 10px 18px; color: #212529;
    }
    .btn-light:hover { background: #e2e6ea; transform: scale(1.03); color: #212529; }

    .history-table-container {
        background: #fff; border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 2rem;
    }
    .history-table { width: 100%; border-collapse: collapse; margin: 0; }
    .history-table thead { background: #198754; color: white; }
    .history-table th {
        padding: 1rem; text-align: left; font-weight: 700;
        font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 3px solid #157347;
    }
    .history-table tbody tr { border-bottom: 1px solid #e9ecef; transition: background 0.2s ease; }
    .history-table tbody tr:hover { background: #f8f9fa; }
    .history-table tbody tr.row-returned { background: #f0faf4; }
    .history-table tbody tr.row-returned:hover { background: #e6f4ec; }
    .history-table tbody tr.row-overdue { background: #fff8f8; }
    .history-table tbody tr.row-overdue:hover { background: #fef2f2; }
    .history-table td { padding: 1rem; color: #00402c; vertical-align: middle; }
    .history-table tbody tr:last-child { border-bottom: none; }

    .status-badge {
        display: inline-block; padding: 0.4rem 0.8rem; border-radius: 0.5rem;
        font-size: 0.85rem; font-weight: 600; text-transform: uppercase;
    }
    .status-borrowed { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    .status-returned { background: #d1e7dd; color: #0a3622; border: 1px solid #a3cfbb; }
    .status-overdue  { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; }

    .pagination-container {
        padding: 1.5rem; background: #f8f9fa; border-top: 1px solid #dee2e6;
        display: flex; justify-content: center; align-items: center;
    }
    .pagination { margin: 0; }
    .page-link {
        color: #198754; border: 1px solid #dee2e6; padding: 0.5rem 0.75rem;
        border-radius: 0.375rem; margin: 0 0.25rem; background: white; transition: all 0.2s ease;
    }
    .page-link:hover { background: #198754; color: white; border-color: #198754; transform: translateY(-1px); }
    .page-item.active .page-link { background: #198754; border-color: #198754; color: white; }

    .no-records { text-align: center; padding: 3rem; color: #6c757d; }
    .no-records-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }

    @media (max-width: 767px) {
        .hero-section { padding: 2rem 1rem; }
        .hero-section h1 { font-size: 1.8rem; }
        .history-table { font-size: 0.85rem; }
        .history-table th, .history-table td { padding: 0.75rem 0.5rem; }
        .stats-bar { flex-direction: column; }
    }
</style>

<div class="hero-section">
    <h1>Book History</h1>
    <p>Return and borrowing records for all books</p>
</div>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>All Transactions</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-light">⬅ Dashboard</a>
    </div>

    {{--
        $historyStats — always computed from raw DB::table() against the FULL table.
        Never derived from $historyRecords, so filters never distort the numbers.
    --}}
    <div class="stats-bar">
        <div class="stat-card stat-total">
            <div class="stat-value">{{ $historyStats['total'] }}</div>
            <div class="stat-label">Total Records</div>
        </div>
        <div class="stat-card stat-returned">
            <div class="stat-value">{{ $historyStats['returned'] }}</div>
            <div class="stat-label">Returned</div>
        </div>
        <div class="stat-card stat-active">
            <div class="stat-value">{{ $historyStats['active'] }}</div>
            <div class="stat-label">Still Borrowed</div>
        </div>
        <div class="stat-card stat-overdue">
            <div class="stat-value">{{ $historyStats['overdue'] }}</div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="{{ route('book-history.index') }}">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="filter-label">Search</label>
                    <input type="text" name="search" class="filter-input"
                        placeholder="Student name, book title, or author..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label class="filter-label">Status</label>
                    <select name="status" class="filter-select">
                        <option value="">All Records</option>
                        <option value="borrowed" {{ request('status') == 'borrowed' ? 'selected' : '' }}>Currently Borrowed</option>
                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        <option value="overdue"  {{ request('status') == 'overdue'  ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">🔍 Filter</button>
                    <a href="{{ route('book-history.index') }}" class="btn btn-light">Clear</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table uses $historyRecords — completely independent variable, never $borrowings --}}
    <div class="history-table-container">
        @if($historyRecords->count() > 0)
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrower</th>
                            <th>Course &amp; Section</th>
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
                                $isOverdue  = !$isReturned && $record->due_date && now()->greaterThan($record->due_date);
                                $rowClass   = $isReturned ? 'row-returned' : ($isOverdue ? 'row-overdue' : '');
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td style="color:#adb5bd; font-size:0.85rem;">
                                    @if($historyRecords instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        {{ ($historyRecords->currentPage() - 1) * $historyRecords->perPage() + $loop->iteration }}
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </td>
                                <td><strong>{{ $record->book->title ?? 'N/A' }}</strong></td>
                                <td>{{ $record->book->author ?? 'N/A' }}</td>
                                <td>{{ $record->student_name }}</td>
                                <td>{{ $record->course }} - {{ $record->section }}</td>
                                <td>
                                    {{ $record->borrowed_at ? $record->borrowed_at->format('M d, Y h:i A') : 'N/A' }}
                                </td>
                                <td>
                                    @if($record->due_date)
                                        <span @if($isOverdue) style="color:#721c24; font-weight:600;" @endif>
                                            {{ $record->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($isReturned)
                                        <span style="color:#198754; font-weight:600;">
                                            {{ $record->returned_at->format('M d, Y h:i A') }}
                                        </span>
                                    @else
                                        <span style="color:#6c757d; font-style:italic;">Not yet returned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isReturned)
                                        <span class="status-badge status-returned">✓ Returned</span>
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

            <div class="pagination-container">
                {{ $historyRecords->links() }}
            </div>
        @else
            <div class="no-records">
                <div class="no-records-icon">📭</div>
                <h3>No Records Found</h3>
                <p>No borrowing records match your criteria.</p>
            </div>
        @endif
    </div>

</div>
@endsection