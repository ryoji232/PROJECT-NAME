{{--
    resources/views/book-history/partials/table.blade.php
    Rendered by BookHistoryController when the request is AJAX.
    Contains only the table + pagination — no layout wrapper.
--}}
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