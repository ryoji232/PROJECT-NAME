@extends('app')

@section('title', 'Borrowed Books - Library System')

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
        padding: 1.1rem 1rem; text-align: center;
    }
    .stat-card .stat-value { font-size: 1.9rem; font-weight: 800; line-height: 1; margin-bottom: 0.25rem; }
    .stat-card .stat-label { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
    .stat-active  .stat-value { color: #856404; }
    .stat-overdue .stat-value { color: #721c24; }

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

    .borrowings-table-container {
        background: #fff; border-radius: 1rem; border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 2rem;
    }
    .borrowings-table { width: 100%; border-collapse: collapse; margin: 0; }
    .borrowings-table thead { background: #198754; color: white; }
    .borrowings-table th {
        padding: 1rem; text-align: left; font-weight: 700;
        font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px;
        border-bottom: 3px solid #157347;
    }
    .borrowings-table tbody tr { border-bottom: 1px solid #e9ecef; transition: background 0.2s ease; }
    .borrowings-table tbody tr:hover { background: #f8f9fa; }
    .borrowings-table tbody tr.row-overdue { background: #fff8f8; }
    .borrowings-table tbody tr.row-overdue:hover { background: #fef2f2; }
    .borrowings-table td { padding: 0.85rem 1rem; color: #00402c; vertical-align: middle; }
    .borrowings-table tbody tr:last-child { border-bottom: none; }

    .status-badge {
        display: inline-block; padding: 0.35rem 0.75rem; border-radius: 0.5rem;
        font-size: 0.82rem; font-weight: 600; text-transform: uppercase; white-space: nowrap;
    }
    .status-borrowed { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    .status-overdue  { background: #f8d7da; color: #721c24; border: 1px solid #f5c2c7; }

    .btn-return {
        background: #198754; color: white; border: none; border-radius: 6px;
        padding: 0.4rem 0.9rem; font-size: 0.85rem; font-weight: 600;
        cursor: pointer; transition: all 0.2s ease; white-space: nowrap;
    }
    .btn-return:hover { background: #157347; transform: scale(1.04); }

    .pagination-container {
        padding: 1.25rem; background: #f8f9fa; border-top: 1px solid #dee2e6;
        display: flex; justify-content: center;
    }
    .pagination { margin: 0; }
    .page-link {
        color: #198754; border: 1px solid #dee2e6; padding: 0.5rem 0.75rem;
        border-radius: 0.375rem; margin: 0 0.2rem; background: white; transition: all 0.2s ease;
    }
    .page-link:hover { background: #198754; color: white; border-color: #198754; }
    .page-item.active .page-link { background: #198754; border-color: #198754; color: white; }

    .no-records { text-align: center; padding: 3rem; color: #6c757d; }
    .no-records-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }

    @media (max-width: 767px) {
        .hero-section { padding: 2rem 1rem; }
        .hero-section h1 { font-size: 1.8rem; }
        .borrowings-table { font-size: 0.82rem; }
        .borrowings-table th, .borrowings-table td { padding: 0.6rem 0.5rem; }
        .stats-bar { flex-direction: column; }
    }
</style>

<div class="hero-section">
    <h1> Borrowed Books</h1>
    <p>Currently checked-out books — process returns here</p>
</div>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Active Borrowings</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-light">⬅ Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ⚠️ {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-card stat-active">
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-label">Currently Borrowed</div>
        </div>
        <div class="stat-card stat-overdue">
            <div class="stat-value">{{ $stats['overdue'] }}</div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>


    <!-- Table -->
    <div class="borrowings-table-container">
        @if($borrowings->count() > 0)
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
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowings as $borrowing)
                            @php
                                $isOverdue = $borrowing->due_date && now()->greaterThan($borrowing->due_date);
                            @endphp
                            <tr class="{{ $isOverdue ? 'row-overdue' : '' }}">
                                <td style="color:#adb5bd; font-size:0.82rem;">
                                    @if($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        {{ ($borrowings->currentPage() - 1) * $borrowings->perPage() + $loop->iteration }}
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </td>
                                <td><strong>{{ $borrowing->book->title ?? 'N/A' }}</strong></td>
                                <td>{{ $borrowing->book->author ?? 'N/A' }}</td>
                                <td>{{ $borrowing->student_name }}</td>
                                <td>{{ $borrowing->course }} - {{ $borrowing->section }}</td>
                                <td>{{ $borrowing->borrowed_at ? $borrowing->borrowed_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                <td>
                                    @if($borrowing->due_date)
                                        <span style="{{ $isOverdue ? 'color:#721c24; font-weight:600;' : '' }}">
                                            {{ $borrowing->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($isOverdue)
                                        <span class="status-badge status-overdue">⚠ Overdue</span>
                                    @else
                                        <span class="status-badge status-borrowed">Borrowed</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('borrow.return', $borrowing->id) }}"
                                          onsubmit="return confirm('Confirm return of this book?')">
                                        @csrf
                                        <button type="submit" class="btn-return">↩ Return</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-container">
                @if ($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $borrowings->links() }}
                @endif
            </div>
        @else
            <div class="no-records">
                <div class="no-records-icon">✅</div>
                <h3>No Active Borrowings</h3>
                <p>All books have been returned or no borrowings match your search.</p>
            </div>
        @endif
    </div>
</div>
@endsection