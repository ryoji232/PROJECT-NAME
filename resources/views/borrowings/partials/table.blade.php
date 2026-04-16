{{--
    resources/views/borrowings/partials/table.blade.php
    Rendered by BorrowingController::index() for AJAX requests.
    Contains only the table + pagination — no layout wrapper.
    STATUS BADGES: Only "Borrowed" and "⚠ Overdue" — "On Time" removed.
--}}
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
                    $rowOffset = ($borrowings instanceof \Illuminate\Pagination\AbstractPaginator)
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
                            {{--
                                Only two statuses exist on this page:
                                  • ⚠ Overdue  — unreturned AND past due_date
                                  • Borrowed    — unreturned AND not yet past due_date
                                "On Time" has been removed — it was redundant with "Borrowed"
                                and inconsistent with the Book History page pattern.
                            --}}
                            @if($isOverdue)
                                <span class="status-badge status-overdue">⚠ Overdue</span>
                            @else
                                <span class="status-badge status-borrowed">Borrowed</span>
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

    {{-- Pagination — only rendered when $borrowings is a real paginator --}}
    @if($borrowings instanceof \Illuminate\Pagination\AbstractPaginator && $borrowings->hasPages())
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
    @else
        <div class="px-3 py-2 border-top text-muted" style="font-size:0.875rem;">
            Showing all <strong>{{ $borrowings->count() }}</strong> records
        </div>
    @endif

@else
    <div class="no-records">
        <div class="no-records-icon">📭</div>
        <h3>No Borrowed Books Found</h3>
        <p>No results match your search or filter.</p>
    </div>
@endif