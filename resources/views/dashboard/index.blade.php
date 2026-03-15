@extends('layouts.app')

@section('title', 'Dashboard — Library System')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
@php
use Carbon\Carbon;

$withPenalties = $borrowings->filter(function ($b) {
    if (!$b->due_date || $b->returned_at) return false;
    $due = Carbon::parse($b->due_date);
    if (!$due->lt(Carbon::now())) return false;

    $days = (int) $due->diffInDays(Carbon::now());
    $b->days_overdue = $days;
    $b->penalty      = ceil($days / 7) * 10;
    return true;
});
@endphp

{{-- Hero --}}
<div class="hero-section">
    <h1>IETI College Library</h1>
    <p>Search, discover, and borrow books with ease</p>
</div>

{{-- Stats --}}
<div class="row mb-4">
    @foreach([['label'=>'TOTAL BOOKS','value'=>$books->count()],['label'=>'CURRENTLY BORROWED','value'=>$borrowings->count()],['label'=>'OVERDUE BOOKS','value'=>$withPenalties->count()]] as $stat)
    <div class="col-md-4">
        <div class="stats-box text-center">
            <div class="stats-badge">{{ $stat['value'] }}</div>
            <div class="fw-semibold">{{ $stat['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Penalty Rate Info --}}
<div class="penalty-info mb-4">
    <h6>📋 Penalty Rate: ₱10 per 7-day period overdue</h6>
    @foreach(['1–7 days'=>10,'8–14 days'=>20,'15–21 days'=>30,'22–28 days'=>40] as $period => $amount)
        <div class="penalty-period"><strong>{{ $period }}:</strong> ₱{{ $amount }}</div>
    @endforeach
    <small class="text-muted">Each additional 7-day period adds ₱10</small>
</div>

{{-- Recent Borrowings --}}
<div class="card shadow-sm mb-4">
    <div class="card-header">RECENT BORROWINGS</div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush" id="recentBorrowingsList">
            @forelse($recentBorrowings as $b)
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $b->student_name }}</strong> borrowed
                            <em>"{{ $b->book->title ?? 'Unknown Book' }}"</em>
                        </div>
                        <small class="text-muted">
                            {{ Carbon::parse($b->borrowed_at ?? $b->created_at)->format('M d, Y h:i A') }}
                        </small>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center text-muted py-3">
                    <h5>No recent borrowings</h5>
                    <p class="mb-0">Books borrowed will appear here</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Borrowers with Penalties --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger">BORROWERS WITH PENALTIES</div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($withPenalties as $b)
                @php
                    $days    = $b->days_overdue;
                    $periods = ceil($days / 7);
                    $penalty = $periods * 10;
                    $from    = ($periods - 1) * 7 + 1;
                    $to      = $periods * 7;
                @endphp
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $b->student_name }}</strong>
                            <span class="badge-days ms-2">{{ $days }} days overdue</span><br>
                            <em>"{{ $b->book->title ?? 'Unknown Book' }}"</em><br>
                            <span class="badge-penalty">Penalty: ₱{{ number_format($penalty, 2) }}</span>
                            <small class="text-muted ms-1">({{ $from }}–{{ $to }} days)</small><br>
                            <small class="text-muted">Due: {{ Carbon::parse($b->due_date)->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end">
                            <div class="text-danger fw-bold">₱{{ number_format($penalty, 2) }}</div>
                            <small class="text-muted">{{ $days }} days</small>
                        </div>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center text-muted py-4">
                    <h5>No penalties at the moment</h5>
                    <p class="mb-0">All books are returned on time!</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>

{{-- Most Borrowed Books Chart --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary">MOST BORROWED BOOKS</div>
    <div class="card-body">
        @if(!empty($chartData['labels']))
            <div class="chart-container">
                <canvas id="borrowedBooksChart"></canvas>
            </div>
        @else
            <div class="text-center text-muted py-4">
                <div style="font-size:2.5rem;opacity:.4;">📊</div>
                <p class="mt-2 mb-0">No borrowing data yet. Chart will appear once books are borrowed.</p>
            </div>
        @endif
    </div>
</div>

{{-- Most Popular Book --}}
@if($mostBorrowed?->book)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-purple">MOST POPULAR BOOK</div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-6 text-start">
                <h5>{{ $mostBorrowed->book->title }}</h5>
                <p class="text-muted">by {{ $mostBorrowed->book->author }}</p>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-0">{{ $mostBorrowed->borrow_count }}</div>
                    <small class="text-muted">Times Borrowed</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-2">
                    <div class="h5 mb-0">{{ $mostBorrowed->book->copies }}</div>
                    <small class="text-muted">Available Copies</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
(function () {
    const ctx = document.getElementById('borrowedBooksChart');
    if (!ctx) return;

    const labels = @json($chartData['labels'] ?? []);
    const data   = @json($chartData['data']   ?? []);
    const colors = ['#198754','#0d6efd','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0'];

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Times Borrowed',
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: {
                    ticks: {
                        callback: function(val, i) {
                            const l = labels[i] ?? '';
                            return l.length > 15 ? l.slice(0, 15) + '…' : l;
                        }
                    }
                }
            }
        }
    });

    // Refresh every 30 seconds
    setInterval(function () {
        fetch('/borrowings/realtime-data', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(resp => {
            if (!resp.success || !resp.chart_data?.labels?.length) return;
            chart.data.labels = resp.chart_data.labels;
            chart.data.datasets[0].data = resp.chart_data.data;
            chart.data.datasets[0].backgroundColor = colors.slice(0, resp.chart_data.labels.length);
            chart.update();

            // Update stat badges too
            const badges = document.querySelectorAll('.stats-badge');
            if (badges.length >= 2) {
                badges[0].textContent = resp.total_books;
                badges[1].textContent = resp.current_borrowings;
            }

            // Update recent borrowings list
            const list = document.getElementById('recentBorrowingsList');
            if (list && resp.recent_borrowings?.length) {
                list.innerHTML = resp.recent_borrowings.map(b => {
                    const date = new Date(b.borrowed_at).toLocaleDateString('en-US', {
                        month:'short', day:'numeric', year:'numeric',
                        hour:'2-digit', minute:'2-digit'
                    });
                    return `<li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>${b.student_name}</strong> borrowed <em>"${b.book_title}"</em></div>
                            <small class="text-muted">${date}</small>
                        </div>
                    </li>`;
                }).join('');
            }
        })
        .catch(err => console.error('Dashboard refresh error:', err));
    }, 30000);
})();
</script>
@endpush

@endsection