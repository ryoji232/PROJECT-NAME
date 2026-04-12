<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BookHistoryController
 *
 * Completely independent from BorrowingController.
 * This controller owns ONLY the book-history page.
 * It uses $historyRecords (not $borrowings) to avoid any
 * collision with the $borrowings variable used in app.blade.php
 * for the notification bell.
 *
 * Records are NEVER deleted or hidden — every transaction ever
 * created will always appear here regardless of return status.
 */
class BookHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Start from the FULL borrowings table — no default filter, ever.
        // This query is completely self-contained; it does not share scope,
        // variable names, or logic with BorrowingController@index.
        $query = Borrowing::with(['book', 'bookCopy'])
                          ->orderBy('created_at', 'desc');

        // User-driven status filter only — never applied by default
        if ($request->filled('status')) {
            match ($request->status) {
                'borrowed' => $query->whereNull('returned_at'),
                'returned' => $query->whereNotNull('returned_at'),
                'overdue'  => $query->whereNull('returned_at')->where('due_date', '<', now()),
                default    => null,
            };
        }

        // Optional search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('course',        'like', "%{$search}%")
                  ->orWhere('section',       'like', "%{$search}%")
                  ->orWhereHas('book', function ($bq) use ($search) {
                      $bq->where('title',  'like', "%{$search}%")
                         ->orWhere('author', 'like', "%{$search}%");
                  });
            });
        }

        // Use a distinct variable name — NOT $borrowings — so app.blade.php
        // notification bell logic is never confused with history page data
        $historyRecords = $query->paginate(20)->withQueryString();

        // Stats are always computed from raw DB counts against the full table.
        // They are never derived from $historyRecords, so filters never distort them.
        $historyStats = [
            'total'    => DB::table('borrowings')->count(),
            'returned' => DB::table('borrowings')->whereNotNull('returned_at')->count(),
            'active'   => DB::table('borrowings')->whereNull('returned_at')->count(),
            'overdue'  => DB::table('borrowings')->whereNull('returned_at')
                                                 ->where('due_date', '<', now())
                                                 ->count(),
        ];

        // AJAX request — return only the table fragment so the blade can
        // swap it in place without a full page reload.
        if ($request->ajax()) {
            return response()->json([
                'table' => view('book-history.partials.table',
                                compact('historyRecords'))->render(),
            ]);
        }

        return view('book-history.index', compact('historyRecords', 'historyStats'));
    }
}