<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Book;
use App\Models\BookCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    public function index()
    {
        $borrowings = Borrowing::with('book')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('borrowings.index', compact('borrowings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'course' => 'required|string|max:100',
            'section' => 'required|string|max:50',
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);
        
        // Check if book has available copies
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
                'student_id' => strtoupper(substr(str_replace(' ', '', $request->student_name), 0, 5)) . time(),
                'course' => $request->course,
                'section' => $request->section,
                'book_id' => $request->book_id,
                'book_copy_id' => $copy->id,
                'borrowed_at' => now(),
                'due_date' => now()->addDays(14),
            ]);

            $copy->update(['status' => 'borrowed']);
            
            // Decrement available_copies
            $book->decrement('available_copies');
        });

        $activeBorrowings = Borrowing::where('book_id', $request->book_id)
            ->whereNull('returned_at')
            ->count();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Book borrowed successfully!',
                'book' => $book->fresh(),
                'active_borrowings' => $activeBorrowings,
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Book borrowed successfully!');
    }

    // Borrow book using barcode
    public function borrowByBarcode(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'course' => 'required|string|max:100',
            'section' => 'required|string|max:50',
            'book_copy_barcode' => 'required|string',
        ]);

        $barcode = $request->book_copy_barcode;

        // Use BookCopy finder that handles normalized/scannable values
        $copy = BookCopy::findByScannable($barcode);
        if (! $copy || $copy->status !== 'available') {
            return response()->json([
                'success' => false,
                'message' => 'No available copy found with this barcode.'
            ], 404);
        }

        $book = $copy->book;
        
        // Check if book has available copies
        if ($book->available_copies <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'No available copies of this book.'
            ], 422);
        }

        DB::transaction(function () use ($request, $copy, $book) {
            Borrowing::create([
                'student_name' => $request->student_name,
                'student_id' => strtoupper(substr(str_replace(' ', '', $request->student_name), 0, 5)) . time(),
                'course' => $request->course,
                'section' => $request->section,
                'book_id' => $book->id,
                'book_copy_id' => $copy->id,
                'borrowed_at' => now(),
                'due_date' => now()->addDays(14),
            ]);

            $copy->update(['status' => 'borrowed']);
            
            // Decrement available_copies
            $book->decrement('available_copies');
        });

        return response()->json([
            'success' => true,
            'message' => 'Book borrowed successfully!',
            'book' => $book->fresh()
        ]);
    }

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
        $book = $borrowing->book;
        $copy = $borrowing->bookCopy;

        // Save the exact returned_at timestamp
        $borrowing->update(['returned_at' => now()]);

        if ($copy) {
            $copy->update(['status' => 'available']);
        }

        $book->increment('available_copies');
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

    public function showBarcode($id)
    {
        $borrowing = Borrowing::with('book')->findOrFail($id);
        
        // Generate the full URL that scanners will output
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
    $book = $borrowing->book;
    $copy = $borrowing->bookCopy;

    $borrowing->update(['returned_at' => now()]);

    if ($copy) {
        $copy->update(['status' => 'available']);
    }

    $book->increment('available_copies');
});

            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing return: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRealTimeData()
    {
        try {
            $totalBooks = Book::count();
            $currentBorrowings = Borrowing::whereNull('returned_at')->count();
            
            $recentBorrowings = Borrowing::with('book')
                ->whereNull('returned_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($borrowing) {
                    return [
                        'student_name' => $borrowing->student_name,
                        'book_title' => $borrowing->book->title ?? 'Unknown Book',
                        'borrowed_at' => $borrowing->borrowed_at ?? $borrowing->created_at,
                    ];
                });

            $mostBorrowedBooks = Borrowing::select('book_id', DB::raw('COUNT(*) as borrow_count'))
                ->groupBy('book_id')
                ->orderBy('borrow_count', 'desc')
                ->limit(7)
                ->get()
                ->map(function ($item) {
                    $book = Book::find($item->book_id);
                    return [
                        'title' => $book ? $book->title : 'Unknown Book',
                        'borrow_count' => $item->borrow_count
                    ];
                });

            return response()->json([
                'success' => true,
                'total_books' => $totalBooks,
                'current_borrowings' => $currentBorrowings,
                'recent_borrowings' => $recentBorrowings,
                'chart_data' => [
                    'labels' => $mostBorrowedBooks->pluck('title')->toArray(),
                    'data' => $mostBorrowedBooks->pluck('borrow_count')->toArray()
                ],
                'last_updated' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function getBorrowingData($id)
    {
        try {
            $borrowing = Borrowing::with('book')->find($id);
            
            if (!$borrowing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrowing record not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'borrowing' => [
                    'id' => $borrowing->id,
                    'student_name' => $borrowing->student_name,
                    'course' => $borrowing->course,
                    'section' => $borrowing->section,
                    'due_date' => $borrowing->due_date ? $borrowing->due_date->format('Y-m-d') : 'N/A',
                    'borrowed_at' => $borrowing->borrowed_at ? $borrowing->borrowed_at->format('Y-m-d') : 'N/A'
                ],
                'book' => [
                    'id' => $borrowing->book->id,
                    'title' => $borrowing->book->title,
                    'author' => $borrowing->book->author,
                    'copies' => $borrowing->book->copies
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching borrowing data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markCopyAsDamaged($id)
    {
        try {
            $copy = BookCopy::findOrFail($id);

            $copy->update([
                'status' => 'damaged',
                'notes' => 'Water damage'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Copy marked as damaged successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking copy as damaged: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bookHistory(Request $request)
    {
        // Get all borrowings with book information, ordered by most recent first
        // Use eager loading to prevent N+1 queries
        $query = Borrowing::with(['book', 'bookCopy'])->orderBy('created_at', 'desc');

        // Optional filter by status
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'borrowed') {
                $query->whereNull('returned_at');
            } elseif ($request->status === 'returned') {
                $query->whereNotNull('returned_at');
            }
        }

        // Optional search by book title or student name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('course', 'like', "%{$search}%")
                  ->orWhere('section', 'like', "%{$search}%")
                  ->orWhereHas('book', function($bookQuery) use ($search) {
                      $bookQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('author', 'like', "%{$search}%");
                  });
            });
        }

        // Paginate results
        $borrowings = $query->paginate(20)->withQueryString();

        return view('book-history.index', compact('borrowings'));
    }
}