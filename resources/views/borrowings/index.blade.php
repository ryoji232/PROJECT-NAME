@extends('layouts.app')

@section('title', 'Borrowed Books — Library System')

@section('content')

<div class="hero-section">
    <h1>Borrowed Books</h1>
    <p>Currently checked-out books — process returns here</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Active Borrowings</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

@include('partials.alerts')

{{-- Stats --}}
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

{{-- Table --}}
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
                        @php $isOverdue = $borrowing->due_date && now()->gt($borrowing->due_date); @endphp
                        <tr class="{{ $isOverdue ? 'row-overdue' : '' }}">
                            <td class="text-muted" style="font-size:.82rem;">
                                {{ $borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator
                                    ? ($borrowings->currentPage() - 1) * $borrowings->perPage() + $loop->iteration
                                    : $loop->iteration }}
                            </td>
                            <td><strong>{{ $borrowing->book->title ?? 'N/A' }}</strong></td>
                            <td>{{ $borrowing->book->author ?? 'N/A' }}</td>
                            <td>{{ $borrowing->student_name }}</td>
                            <td>{{ $borrowing->course }} - {{ $borrowing->section }}</td>
                            <td>{{ $borrowing->borrowed_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                            <td>
                                @if($borrowing->due_date)
                                    <span @if($isOverdue) style="color:#721c24;font-weight:600;" @endif>
                                        {{ $borrowing->due_date->format('M d, Y') }}
                                    </span>
                                @else N/A @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $isOverdue ? 'status-overdue' : 'status-borrowed' }}">
                                    {{ $isOverdue ? '⚠ Overdue' : 'Borrowed' }}
                                </span>
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

        @if($borrowings instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="pagination-container">{{ $borrowings->links() }}</div>
        @endif

    @else
        <div class="no-records">
            <div class="no-records-icon">✅</div>
            <h3>No Active Borrowings</h3>
            <p>All books have been returned or no borrowings match your search.</p>
        </div>
    @endif
</div>

@endsection