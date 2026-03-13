<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    // =========================================================
    // BORROWINGS INDEX — Action page (return button lives here)
    // Only shows currently active/unreturned borrowings by default
    // =========================================================
    public function index(Request $request)
    {
        // Default: only show currently borrowed (unreturned) books
        // This is the action page — librarians use this to process returns
        $query = Borrowing::with(['book', 'bookCopy'])->orderBy('created_at', 'desc')
                          ->whereNull('returned_at');

        // Optional search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('course', 'like', "%{$search}%")
                  ->orWhere('section', 'like', "%{$search}%")
                  ->orWhereHas('book', function ($bq) use ($search) {
                      $bq->where('title', 'like', "%{$search}%")
                         ->orWhere('author', 'like', "%{$search}%");
                  });
            });
        }

        // Optional overdue filter on top of active-only
        if ($request->filled('filter') && $request->filter === 'overdue') {
            $query->where('due_date', '<', now());
        }

        $borrowings = $query->paginate(20)->withQueryString();

        // Stats — raw DB, independent of any filter
        $stats = [
            'active'  => DB::table('borrowings')->whereNull('returned_at')->count(),
            'overdue' => DB::table('borrowings')->whereNull('returned_at')->where('due_date', '<', now())->count(),
        ];

        return view('borrowings.index', compact('borrowings', 'stats'));
    }


    // =========================================================
    // STORE — Borrow a book
    // =========================================================
    public function store(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'course'       => 'required|string|max:100',
            'section'      => 'required|string|max:50',
            'book_id'      => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);

        if ($book->available_copies <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No available copies of this book.'
            ], 422);
        }

        $copy = BookCopy::where('book_id', $request->book_id)
            ->where('status', 'available')
            ->firstOrFail();

        DB::transaction(function () use ($request, $copy, $book) {
            Borrowing::create([
                'student_name' => $request->student_name,
                'student_id'   => strtoupper(substr(str_replace(' ', '', $request->student_name), 0, 5)) . time(),
                'course'       => $request->course,
                'section'      => $request->section,
                'book_id'      => $request->book_id,
                'book_copy_id' => $copy->id,
                'borrowed_at'  => now(),
                'due_date'     => now()->addDays(14),
            ]);

            $copy->update(['status' => 'borrowed']);
            $book->decrement('available_copies');
        });

        $activeBorrowings = Borrowing::where('book_id', $request->book_id)
            ->whereNull('returned_at')->count();

        if ($request->ajax()) {
            return response()->json([
                'success'         => true,
                'message'         => 'Book borrowed successfully!',
                'book'            => $book->fresh(),
                'active_borrowings' => $activeBorrowings,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Book borrowed successfully!');
    }

    // =========================================================
    // BORROW BY BARCODE
    // =========================================================
    public function borrowByBarcode(Request $request)
    {
        $request->validate([
            'student_name'      => 'required|string|max:255',
            'course'            => 'required|string|max:100',
            'section'           => 'required|string|max:50',
            'book_copy_barcode' => 'required|string',
        ]);

        $copy = BookCopy::findByScannable($request->book_copy_barcode);

        if (! $copy || $copy->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'No available copy found with this barcode.'
            ], 404);
        }

        $book = $copy->book;

        if ($book->available_copies <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No available copies of this book.'
            ], 422);
        }

        DB::transaction(function () use ($request, $copy, $book) {
            Borrowing::create([
                'student_name' => $request->student_name,
                'student_id'   => strtoupper(substr(str_replace(' ', '', $request->student_name), 0, 5)) . time(),
                'course'       => $request->course,
                'section'      => $request->section,
                'book_id'      => $book->id,
                'book_copy_id' => $copy->id,
                'borrowed_at'  => now(),
                'due_date'     => now()->addDays(14),
            ]);

            $copy->update(['status' => 'borrowed']);
            $book->decrement('available_copies');
        });

        return response()->json([
            'success' => true,
            'message' => 'Book borrowed successfully!',
            'book'    => $book->fresh(),
        ]);
    }

    // =========================================================
    // RETURN BOOK
    // =========================================================
    public function returnBook(Borrowing $borrowing)
    {
        if ($borrowing->returned_at) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This book has already been returned.'
                ], 422);
            }
            return redirect()->route('borrowings.index')->with('error', 'This book has already been returned.');
        }

        DB::transaction(function () use ($borrowing) {
            $borrowing->update(['returned_at' => now()]);

            if ($borrowing->bookCopy) {
                $borrowing->bookCopy->update(['status' => 'available']);
            }

            $borrowing->book->increment('available_copies');
        });

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Book returned successfully!',
                'returned_at' => $borrowing->fresh()->returned_at
                                    ->setTimezone('Asia/Manila')
                                    ->format('M d, Y h:i A'),
            ]);
        }

        return redirect()->route('borrowings.index')->with('success', 'Book returned successfully!');
    }

    // =========================================================
    // PROCESS RETURN (barcode scanner flow)
    // =========================================================
    public function processReturn($id)
    {
        try {
            $borrowing = Borrowing::with(['book', 'bookCopy'])->findOrFail($id);

            if ($borrowing->returned_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This book has already been returned.'
                ], 422);
            }

            DB::transaction(function () use ($borrowing) {
                $borrowing->update(['returned_at' => now()]);

                if ($borrowing->bookCopy) {
                    $borrowing->bookCopy->update(['status' => 'available']);
                }

                $borrowing->book->increment('available_copies');
            });

            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing return: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // BARCODE VIEWS
    // =========================================================
    public function showBarcode($id)
    {
        $borrowing  = Borrowing::with('book')->findOrFail($id);
        $barcodeData = route('direct.return', $borrowing->id);
        return view('barcode', compact('borrowing', 'barcodeData'));
    }

    public function showBarcodeSticker($id)
    {
        $borrowing = Borrowing::with('book')->findOrFail($id);
        return view('barcode-sticker', compact('borrowing'));
    }

    public function showReturnConfirmation($id)
    {
        $borrowing = Borrowing::findOrFail($id);
        return view('confirm-return', compact('borrowing'));
    }

    // =========================================================
    // REAL-TIME DATA (dashboard widget)
    // =========================================================
    public function getRealTimeData()
    {
        try {
            $totalBooks        = Book::count();
            $currentBorrowings = Borrowing::whereNull('returned_at')->count();

            $recentBorrowings = Borrowing::with('book')
                ->whereNull('returned_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(fn($b) => [
                    'student_name' => $b->student_name,
                    'book_title'   => $b->book->title ?? 'Unknown Book',
                    'borrowed_at'  => $b->borrowed_at ?? $b->created_at,
                ]);

            $mostBorrowedBooks = Borrowing::select('book_id', DB::raw('COUNT(*) as borrow_count'))
                ->groupBy('book_id')
                ->orderBy('borrow_count', 'desc')
                ->limit(7)
                ->get()
                ->map(function ($item) {
                    $book = Book::find($item->book_id);
                    return [
                        'title'       => $book ? $book->title : 'Unknown Book',
                        'borrow_count' => $item->borrow_count,
                    ];
                });

            return response()->json([
                'success'          => true,
                'total_books'      => $totalBooks,
                'current_borrowings' => $currentBorrowings,
                'recent_borrowings'  => $recentBorrowings,
                'chart_data'       => [
                    'labels' => $mostBorrowedBooks->pluck('title')->toArray(),
                    'data'   => $mostBorrowedBooks->pluck('borrow_count')->toArray(),
                ],
                'last_updated' => now()->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // NOTIFICATION DATA — Real-time bell panel
    // Returns raw seconds so the JS ticker can count down live
    // =========================================================
    public function getNotificationsData()
    {
        try {
            $now  = now();
            $rows = DB::table('borrowings')
                ->join('books', 'borrowings.book_id', '=', 'books.id')
                ->whereNull('borrowings.returned_at')
                ->orderBy('borrowings.borrowed_at', 'asc')
                ->select(
                    'borrowings.student_name',
                    'borrowings.borrowed_at',
                    'borrowings.created_at',
                    'books.title as book_title'
                )
                ->get();

            $notifications = [];

            foreach ($rows as $row) {
                $borrowedAt = \Carbon\Carbon::parse($row->borrowed_at ?? $row->created_at);
                $spent      = (int) $borrowedAt->diffInSeconds($now);
                $remaining  = (14 * 86400) - $spent;

                $spentDays  = floor($spent / 86400);
                $spentHours = floor(($spent % 86400) / 3600);
                $spentMins  = floor(($spent % 3600) / 60);

                if ($remaining > 0) {
                    $remDays  = floor($remaining / 86400);
                    $remHours = floor(($remaining % 86400) / 3600);
                    $remMins  = floor(($remaining % 3600) / 60);
                    $remText  = "{$remDays}d {$remHours}h {$remMins}m";
                    $status   = $remDays > 7 ? 'green' : 'yellow';
                } else {
                    $remDays  = (int) -floor(abs($remaining) / 86400);
                    $remText  = 'Overdue';
                    $status   = 'red';
                }

                $notifications[] = [
                    'borrower'         => $row->student_name,
                    'book'             => $row->book_title,
                    'spendingTime'     => "{$spentDays}d {$spentHours}h {$spentMins}m",
                    'remainingTime'    => $remText,
                    'daysLeft'         => $remDays,
                    'spentSeconds'     => $spent,       // raw — JS ticks from here
                    'remainingSeconds' => $remaining,   // raw — JS ticks from here
                    'status'           => $status,
                ];
            }

            usort($notifications, fn($a, $b) => $a['daysLeft'] <=> $b['daysLeft']);

            return response()->json([
                'success'       => true,
                'notifications' => $notifications,
                'count'         => count($notifications),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // =========================================================
    // GET BORROWING DATA (for barcode modal)
    // =========================================================
    public function getBorrowingData($id)
    {
        try {
            $borrowing = Borrowing::with('book')->findOrFail($id);

            return response()->json([
                'success' => true,
                'borrowing' => [
                    'id'           => $borrowing->id,
                    'student_name' => $borrowing->student_name,
                    'course'       => $borrowing->course,
                    'section'      => $borrowing->section,
                    'due_date'     => $borrowing->due_date?->format('Y-m-d') ?? 'N/A',
                    'borrowed_at'  => $borrowing->borrowed_at?->format('Y-m-d') ?? 'N/A',
                ],
                'book' => [
                    'id'     => $borrowing->book->id,
                    'title'  => $borrowing->book->title,
                    'author' => $borrowing->book->author,
                    'copies' => $borrowing->book->copies,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching borrowing data: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // MARK COPY AS DAMAGED
    // =========================================================
    public function markCopyAsDamaged($id)
    {
        try {
            $copy = BookCopy::findOrFail($id);
            $copy->update(['status' => 'damaged', 'notes' => 'Water damage']);

            return response()->json([
                'success' => true,
                'message' => 'Copy marked as damaged successfully!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking copy as damaged: ' . $e->getMessage()
            ], 500);
        }
    }
}