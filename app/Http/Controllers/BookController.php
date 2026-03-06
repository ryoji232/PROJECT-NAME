<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Show all books
    public function index()
    {
        $books = Book::with('bookCopies')->get();
        $borrowings = Borrowing::with('book')->get();

        return view('books.index', compact('books', 'borrowings'));
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
        // ensure available_copies is set to the initial copies count
        'available_copies' => $validated['copies'] ?? 1,
    ];

    // If barcode is provided, use it; otherwise it will be auto-generated
    if (!empty($validated['barcode'])) {
        $bookData['barcode'] = $validated['barcode'];
    }

    $book = Book::create($bookData);

    // Create BookCopy records for the number of copies specified
    if (!empty($book->copies) && $book->copies > 0) {
        $book->createCopies((int) $book->copies);
    }

    return redirect()->back()->with('success', 'Book added successfully with ' . $book->copies . ' copies!');
}

    // Show the edit form
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        return view('books.edit', compact('book'));
    }

    // Update a book
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'copies' => 'nullable|integer|min:1|max:10',
        ]);

        $book = Book::findOrFail($id);
        // If copies provided, also adjust available_copies accordingly (keep existing borrowed count)
        if (isset($validated['copies'])) {
            $newCopies = (int) $validated['copies'];
            // Recalculate available_copies conservatively: if there are active borrowings, don't increase available beyond copies minus borrowed
            $borrowed = $book->borrowings()->whereNull('returned_at')->count();
            $validated['available_copies'] = max(0, $newCopies - $borrowed);
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated successfully!');
    }

    // Delete a book
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
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
    
    // Handle short format: BK + book ID
    if (preg_match('/^BK(\d+)$/', $barcode, $matches)) {
        $bookId = $matches[1];
        $book = Book::find($bookId);
        
        if ($book) {
            // Continue with your existing logic...
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
    
    // Try to find book by original barcode (for backward compatibility)
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
}