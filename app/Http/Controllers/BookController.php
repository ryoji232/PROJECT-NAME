<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    // Show all books
    public function index()
    {
        // Eager-load only bookCopies — borrowings are loaded lazily per-book
        // via AJAX when a modal opens.
        // orderBy on the DB level so MySQL does the sort efficiently.
        $books = Book::with('bookCopies')
                     ->orderByRaw('LOWER(title)')
                     ->get();

        return view('books.index', compact('books'));
    }

    // Store a new book
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'copies' => 'nullable|integer|min:1|max:10',
            'barcode' => 'nullable|string|unique:books,barcode'
        ]);

        $bookData = [
            'title' => $validated['title'],
            'author' => $validated['author'],
            'copies' => $validated['copies'] ?? 1,
            'available_copies' => $validated['copies'] ?? 1,
        ];

        if (!empty($validated['barcode'])) {
            $bookData['barcode'] = $validated['barcode'];
        }

        $book = Book::create($bookData);
        // BookCopy records are created automatically via the Book model's `static::created` boot hook.

        return redirect()->back()->with('success', 'Book added successfully with ' . $book->copies . ' copies!');
    }

    // Show the edit form
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        return view('books.edit', compact('book'));
    }

    // Update a book
    // ─────────────────────────────────────────────────────────────────────
    // The "copies_to_add" field represents how many NEW copies the librarian
    // wants to ADD to the existing total — it does NOT replace the current
    // total.  The hard cap is 10 copies per book.
    // ─────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => 'required|string|max:255',
            'copies_to_add' => 'nullable|integer|min:0|max:10',
        ]);

        $book        = Book::findOrFail($id);
        $copiesToAdd = (int) ($validated['copies_to_add'] ?? 0);

        DB::transaction(function () use ($book, $copiesToAdd, $validated) {

            // ── 1. Update title / author ──────────────────────────────────
            $book->update([
                'title'  => $validated['title'],
                'author' => $validated['author'],
            ]);

            if ($copiesToAdd <= 0) {
                return; // nothing more to do
            }

            // ── 2. Enforce the 10-copy cap ────────────────────────────────
            $currentTotal = (int) $book->copies;
            $maxAllowed   = 10;
            $canAdd       = max(0, $maxAllowed - $currentTotal);
            $actualAdd    = min($copiesToAdd, $canAdd);

            if ($actualAdd <= 0) {
                return; // already at max — silently skip
            }

            // ── 3. Create the new BookCopy rows ───────────────────────────
            $book->createCopies($actualAdd);   // appends after existing copies

            // ── 4. Update the denormalised counters on the book row ───────
            $newTotal          = $currentTotal + $actualAdd;
            $actualAvailable   = BookCopy::where('book_id', $book->id)
                                         ->where('status', 'available')
                                         ->count();

            $book->update([
                'copies'           => $newTotal,
                'available_copies' => $actualAvailable,
            ]);
        });

        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    // Delete a book
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        foreach ($book->bookCopies as $copy) {
            $copy->borrowings()->delete();
            $copy->delete();
        }

        $book->borrowings()->delete();
        $book->delete();

        return redirect()->back()->with('success', 'Book deleted successfully!');
    }

    // Get current active borrowing for this book
    public function getBookStatus($bookId)
    {
        $book = Book::findOrFail($bookId);
        $borrowing = Borrowing::where('book_id', $bookId)
            ->whereNull('returned_at')
            ->first();

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'copies' => $book->copies
            ],
            'borrowing' => $borrowing ? [
                'id' => $borrowing->id,
                'student_name' => $borrowing->student_name,
                'course' => $borrowing->course,
                'section' => $borrowing->section,
                'due_date' => $borrowing->due_date ? $borrowing->due_date->format('Y-m-d') : 'N/A',
                'borrowed_at' => $borrowing->borrowed_at ? $borrowing->borrowed_at->format('Y-m-d') : 'N/A'
            ] : null
        ]);
    }

    // Scan barcode and return book status
    public function scanBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        if (preg_match('/^BK(\d+)$/', $barcode, $matches)) {
            $bookId = $matches[1];
            $book = Book::find($bookId);

            if ($book) {
                if ($book->copies <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This book is currently not available for borrowing.'
                    ], 422);
                }

                return response()->json([
                    'success' => true,
                    'book' => [
                        'id' => $book->id,
                        'title' => $book->title,
                        'author' => $book->author,
                        'barcode' => $book->barcode,
                        'copies' => $book->copies,
                        'available_copies' => $book->available_copies,
                        'is_available' => $book->is_available
                    ],
                    'action' => 'borrow'
                ]);
            }
        }

        $book = Book::where('barcode', $barcode)->first();

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found with this barcode.'
            ], 404);
        }
    }

    // Generate barcode sticker for book
    public function generateBookBarcode($id)
    {
        $book = Book::findOrFail($id);
        return view('books.barcode-sticker', compact('book'));
    }

    public function searchByBarcode(Request $request)
    {
        $barcode = $request->query('barcode');

        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'No barcode provided'
            ]);
        }

        $book = Book::where('barcode', $barcode)
                    ->orWhere('id', $barcode)
                    ->first();

        if ($book) {
            return response()->json([
                'success' => true,
                'book' => $book
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Book not found'
        ]);
    }

    public function getScanData($bookId)
    {
        try {
            $book = Book::find($bookId);

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found'
                ]);
            }

            return response()->json([
                'success' => true,
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'copies' => $book->copies,
                    'available_copies' => $book->available_copies ?? $book->copies,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching book data'
            ]);
        }
    }

    public function barcodeSticker($id)
    {
        $book = Book::findOrFail($id);
        return view('books.barcode-sticker', compact('book'));
    }

    public function getBookCopies($id)
    {
        try {
            $book = Book::with('bookCopies')->findOrFail($id);

            $copies = $book->bookCopies->map(function ($copy) {
                return [
                    'id' => $copy->id,
                    'copy_number' => $copy->copy_number ?? 'Copy',
                    'barcode' => $copy->barcode,
                    'status' => $copy->status,
                    'normalized_barcode' => \App\Models\BookCopy::normalizeBarcode($copy->barcode)
                ];
            });

            return response()->json([
                'success' => true,
                'copies' => $copies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching copies: ' . $e->getMessage()
            ], 500);
        }
    }

    // =========================================================
    // REPAIR — Fix a single book's copies and available_copies
    // Called via AJAX from the book modal when copies are missing.
    // =========================================================
    public function repairBook($id)
    {
        try {
            $book = Book::with('bookCopies')->findOrFail($id);

            // Step 1: Create book_copies rows if none exist
            if ($book->bookCopies->count() === 0) {
                $totalCopies = max(1, (int) $book->copies);
                for ($i = 1; $i <= $totalCopies; $i++) {
                    BookCopy::create([
                        'book_id'     => $book->id,
                        'copy_number' => "Copy {$i}",
                        'barcode'     => BookCopy::generateUniqueBarcode($book->id, $i),
                        'status'      => 'available',
                    ]);
                }
                // Reload relationship
                $book = $book->fresh(['bookCopies']);
            }

            // Step 2: Reconcile available_copies against actual book_copies state
            $actualAvailable = BookCopy::where('book_id', $book->id)
                ->where('status', 'available')
                ->count();

            if ((int) $book->available_copies !== $actualAvailable) {
                $book->update(['available_copies' => $actualAvailable]);
                $book = $book->fresh(['bookCopies']);
            }

            // Step 3: Return the repaired state so the blade can re-render
            $copies = $book->bookCopies->map(function ($copy) {
                return [
                    'id'          => $copy->id,
                    'copy_number' => $copy->copy_number ?? 'Copy',
                    'barcode'     => BookCopy::normalizeBarcode($copy->barcode),
                    'status'      => $copy->status,
                ];
            });

            return response()->json([
                'success'          => true,
                'book_id'          => $book->id,
                'available_copies' => $book->fresh()->available_copies,
                'total_copies'     => $book->copies,
                'copies'           => $copies,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Repair failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // REPAIR ALL — Fix every book in one request
    // Hit once after deployment: GET /books/repair-all
    // =========================================================
    public function repairAllBooks()
    {
        try {
            $books   = Book::with('bookCopies')->get();
            $results = [];

            DB::transaction(function () use ($books, &$results) {
                foreach ($books as $book) {
                    // 1. Create missing book_copies rows
                    if ($book->bookCopies->count() === 0) {
                        $totalCopies = max(1, (int) $book->copies);
                        for ($i = 1; $i <= $totalCopies; $i++) {
                            BookCopy::create([
                                'book_id'     => $book->id,
                                'copy_number' => "Copy {$i}",
                                'barcode'     => BookCopy::generateUniqueBarcode($book->id, $i),
                                'status'      => 'available',
                            ]);
                        }
                        $results[] = "Created {$totalCopies} copies for: {$book->title}";
                    }

                    // 2. Reconcile available_copies from source of truth
                    $actualAvailable = BookCopy::where('book_id', $book->id)
                        ->where('status', 'available')
                        ->count();

                    if ((int) $book->available_copies !== $actualAvailable) {
                        $book->update(['available_copies' => $actualAvailable]);
                        $results[] = "Synced available_copies to {$actualAvailable} for: {$book->title}";
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'All books repaired successfully.',
                'details' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Repair failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getBookHistory($id)
    {
        try {
            $book = Book::findOrFail($id);

            $history = Borrowing::where('book_id', $id)
                ->orderBy('borrowed_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($record) {
                    $isOverdue = !$record->returned_at
                        && $record->due_date
                        && now()->greaterThan($record->due_date);

                    return [
                        'id'           => $record->id,
                        'student_name' => $record->student_name,
                        'course'       => $record->course,
                        'section'      => $record->section,
                        'borrowed_at'  => $record->borrowed_at
                            ? $record->borrowed_at->setTimezone('Asia/Manila')->toISOString()
                            : ($record->created_at
                                ? $record->created_at->setTimezone('Asia/Manila')->toISOString()
                                : null),
                        'due_date'     => $record->due_date
                            ? $record->due_date->toDateString()
                            : null,
                        'returned_at'  => $record->returned_at
                            ? $record->returned_at->setTimezone('Asia/Manila')->toISOString()
                            : null,
                        'is_overdue'   => $isOverdue,
                    ];
                });

            return response()->json([
                'success'          => true,
                'book_id'          => $book->id,
                'book_title'       => $book->title,
                'available_copies' => $book->available_copies,
                'total_copies'     => $book->copies,
                'history'          => $history,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching history: ' . $e->getMessage(),
            ], 500);
        }
    }
}