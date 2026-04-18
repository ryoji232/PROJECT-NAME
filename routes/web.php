<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\LibrarianAuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookHistoryController;

// Public Routes
Route::get('/', [LibrarianAuthController::class, 'showLoginForm'])->name('home');

// Setup: Generate copies for all books (run once)
Route::get('/setup/generate-copies', function () {
    $books = \App\Models\Book::all();
    $results = [];
    
    foreach ($books as $book) {
        if ($book->bookCopies()->count() == 0) {
            $book->createCopies($book->copies);
            $results[] = "✓ Created {$book->copies} copies for: {$book->title}";
        } else {
            $results[] = "✓ {$book->title} already has {$book->bookCopies()->count()} copies";
        }
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Copies generation completed',
        'results' => $results
    ]);
})->name('setup.generate.copies');

// Librarian Authentication
Route::get('/librarian/login', [LibrarianAuthController::class, 'showLoginForm'])->name('librarian.login');
Route::post('/librarian/login', [LibrarianAuthController::class, 'login'])->name('librarian.login.submit');
Route::post('/librarian/logout', [LibrarianAuthController::class, 'logout'])->name('librarian.logout'); 

// Protected Routes
Route::middleware(['auth:librarian'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [LibrarianAuthController::class, 'index'])->name('dashboard');
    
    // Profile Routes
    Route::get('/librarian/profile', [LibrarianAuthController::class, 'profile'])->name('librarian.profile');
    Route::get('/librarian/account', [LibrarianAuthController::class, 'account'])->name('librarian.account');
    Route::put('/librarian/update-profile', [LibrarianAuthController::class, 'updateProfile'])->name('librarian.update.profile');
    Route::put('/librarian/update-password', [LibrarianAuthController::class, 'updatePassword'])->name('librarian.update.password');
    
    // Books Routes
    // Repair-all must be declared BEFORE Route::resource so Laravel does not
    // mistake POST /books/repair-all for the resource store() route.
    Route::post('/books/repair-all', [BookController::class, 'repairAllBooks'])->name('books.repair.all');
    Route::resource('books', BookController::class);
    Route::get('/books/{bookId}/borrowing-data', [BookController::class, 'getBorrowingData'])->name('books.borrowing.data');
    Route::get('/books/{id}/copies', [BookController::class, 'getBookCopies'])->name('books.copies');
    Route::get('/books/{id}/history', [BookController::class, 'getBookHistory'])->name('books.history');
    Route::post('/books/{id}/repair', [BookController::class, 'repairBook'])->name('books.repair');
    
    // Barcode Routes
    Route::post('/books/scan-barcode', [BookController::class, 'scanBarcode'])->name('books.scan.barcode');
    Route::post('/borrow/by-barcode', [BorrowingController::class, 'borrowByBarcode'])->name('borrow.by.barcode');
    Route::get('/books/{id}/barcode-sticker', [BookController::class, 'generateBookBarcode'])->name('books.barcode.sticker');
    
    // Borrowing Routes
    Route::get('/borrowings', [BorrowingController::class, 'index'])->name('borrowings.index');
    Route::post('/borrow', [BorrowingController::class, 'store'])->name('borrow.store');
    Route::post('/return/{borrowing}', [BorrowingController::class, 'returnBook'])->name('borrow.return');
    
    // Barcode Routes
    Route::get('/borrowing/{id}/barcode', [BorrowingController::class, 'showBarcode'])->name('borrowing.barcode');
    Route::get('/borrowing/{id}/sticker', [BorrowingController::class, 'showBarcodeSticker'])->name('borrowing.sticker');
    Route::get('/borrowing/{id}/return-confirm', [BorrowingController::class, 'showReturnConfirmation'])->name('borrowing.return.confirm');
    Route::post('/borrowing/{id}/process-return', [BorrowingController::class, 'processReturn'])->name('borrowing.process.return');
    
    // Copy Barcode Scanner API
    // Handles three barcode formats:
    //   1. "{bookId}-{copyId}"  — new composite format (copy stickers printed after this fix)
    //   2. Pure numeric         — older stickers encoding only the copy id
    //   3. Alphanumeric string  — legacy stickers with the base36 barcode string
    Route::get('/copies/scan/{code}', function ($code) {
        $copy = null;

        // ── Format 1: composite "{bookId}-{copyId}" ───────────────────────
        // Split on "-" and validate both parts are numeric
        if (preg_match('/^(\d+)-(\d+)$/', $code, $m)) {
            $bookId = (int) $m[1];
            $copyId = (int) $m[2];
            // Must belong to the stated book — prevents cross-book mismatch
            $copy = \App\Models\BookCopy::with('book')
                ->where('id', $copyId)
                ->where('book_id', $bookId)
                ->first();
        }

        // ── Formats 2 & 3: fallback to existing findByScannable ──────────
        if (! $copy) {
            $copy = \App\Models\BookCopy::with('book')->findByScannable($code);
        }

        if (! $copy) {
            return response()->json(['success' => false, 'message' => 'Copy not found'], 404);
        }

        return response()->json([
            'success' => true,
            'copy' => [
                'id' => $copy->id,
                'barcode' => $copy->barcode,
                'status' => $copy->status,
                'copy_number' => $copy->copy_number,
                'book' => [
                    'id' => $copy->book->id,
                    'title' => $copy->book->title,
                    'author' => $copy->book->author,
                ]
            ]
        ]);
    })->name('copies.scan');
    
    // Book History — dedicated controller, completely independent from BorrowingController
    Route::get('/book-history', [BookHistoryController::class, 'index'])->name('book-history.index');
    
    // Real-time Data
    Route::get('/borrowings/realtime-data', [BorrowingController::class, 'getRealTimeData'])->name('borrowings.realtime.data');

    // Notification bell data — polled every 60s by the navbar JS
    Route::get('/notifications/data', [BorrowingController::class, 'getNotificationsData'])->name('notifications.data');
});

// Route to handle scanned barcodes
Route::post('/scan-process', function (Request $request) {
    $barcodeData = $request->input('barcode_data');
    
    // Extract ID from barcode data (assuming format: RET{id})
    if (preg_match('/RET(\d+)/', $barcodeData, $matches)) {
        $borrowingId = $matches[1];
        return redirect()->route('borrowing.return.confirm', $borrowingId);
    }
    
    return redirect()->route('dashboard')->with('error', 'Invalid barcode scanned.');
})->name('barcode.scan.process');

// Alternative: GET route for scanner compatibility
Route::get('/scan/{code}', function ($code) {
    if (preg_match('/RET(\d+)/', $code, $matches)) {
        $borrowingId = $matches[1];
        return redirect()->route('borrowing.return.confirm', $borrowingId);
    }
    return redirect()->route('dashboard')->with('error', 'Invalid barcode.');
})->name('barcode.scan.get');

// Auto-return scan route (for barcode scanners)
Route::get('/scan/{id}', function ($id) {
    $borrowing = \App\Models\Borrowing::with('book')->find($id);
    
    if (!$borrowing) {
        return redirect()->route('dashboard')->with('error', 'Borrowing record not found.');
    }
    
    if ($borrowing->returned_at) {
        return redirect()->route('borrowing.return.confirm', $id)
            ->with('info', 'This book was already returned on ' . $borrowing->returned_at->format('M d, Y'));
    }
    
    // Auto-redirect to return confirmation
    return redirect()->route('borrowing.return.confirm', $id);
})->name('barcode.scan.return');

// Direct return route for barcode scanners
Route::get('/return/{id}', function ($id) {
    $borrowing = \App\Models\Borrowing::with('book')->find($id);
    
    if (!$borrowing) {
        // If not found, show error page instead of redirecting
        return response()->view('errors.scan-error', [
            'message' => 'Book borrowing record not found. Please check the barcode.'
        ], 404);
    }
    
    if ($borrowing->returned_at) {
        // Show already returned page
        return view('already-returned', compact('borrowing'));
    }
    
    // Show return confirmation directly
    return view('confirm-return', compact('borrowing'));
})->name('direct.return');

Route::get('/borrowings/{id}/data', [BorrowingController::class, 'getBorrowingData'])->name('borrowings.data');

Route::get('/books/search-by-barcode', [BookController::class, 'searchByBarcode'])->name('books.search-by-barcode');
Route::get('/books/{book}/scan-data', [BookController::class, 'getScanData'])->name('books.scan-data');

// Route to get book data for scanning
Route::get('/books/{book}/scan-data', function($book) {
    $book = \App\Models\Book::find($book);
    
    if (!$book) {
        return response()->json([
            'success' => false,
            'message' => 'Book not found'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'book' => [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'copies' => $book->copies,
            'available_copies' => $book->available_copies,
        ]
    ]);
})->name('books.scan-data');

// Route to get current borrowing information
Route::get('/books/{book}/current-borrowing', function($book) {
    $book = \App\Models\Book::find($book);
    
    if (!$book) {
        return response()->json([
            'success' => false,
            'message' => 'Book not found'
        ], 404);
    }
    
    // Get the most recent active borrowing for this book
    $borrowing = \App\Models\Borrowing::where('book_id', $book->id)
        ->whereNull('returned_at')
        ->latest()
        ->first();
    
    return response()->json([
        'success' => true,
        'borrowing' => $borrowing ? [
            'id' => $borrowing->id,
            'student_name' => $borrowing->student_name,
            'course' => $borrowing->course,
            'section' => $borrowing->section,
            'due_date' => $borrowing->due_date,
        ] : null
    ]);
})->name('books.current-borrowing');

// Route to process book return
Route::post('/borrowing/{borrowing}/return', function($borrowing) {
    $borrowing = \App\Models\Borrowing::find($borrowing);
    
    if (!$borrowing) {
        return response()->json([
            'success' => false,
            'message' => 'Borrowing record not found'
        ]);
    }
    
    // Mark as returned
    $borrowing->returned_at = now();
    $borrowing->save();
    
    // Calculate available copies instead of updating a column
    // Get the book and count active borrowings
    $book = $borrowing->book;
    
    // Count how many copies are currently borrowed (not returned)
    $activeBorrowings = \App\Models\Borrowing::where('book_id', $book->id)
        ->whereNull('returned_at')
        ->count();

    return response()->json([
        'success' => true,
        'message' => 'Book successfully returned!',
        'active_borrowings' => $activeBorrowings
    ]);
})->name('borrowing.return');

// Simple return route for barcode stickers
Route::post('/books/{id}/quick-return', function($id) {
    $book = \App\Models\Book::with('bookCopies')->find($id);

    if (!$book) {
        return back()->with('error', 'Book not found.');
    }

    // Find the most recent active borrowing for this book (eager-load the copy)
    $borrowing = \App\Models\Borrowing::with('bookCopy')
        ->where('book_id', $id)
        ->whereNull('returned_at')
        ->latest()
        ->first();

    if (! $borrowing) {
        return back()->with('error', 'No active borrowing found for this book.');
    }

    // Run everything inside a transaction — mirrors BorrowingController::returnBook()
    \Illuminate\Support\Facades\DB::transaction(function () use ($borrowing) {
        // 1. Mark the borrowing as returned
        $borrowing->update(['returned_at' => now()]);

        // 2. Flip the specific BookCopy back to available (if linked)
        if ($borrowing->bookCopy) {
            $borrowing->bookCopy->update(['status' => 'available']);
        }

        // 3. Increment available_copies on the book (not total copies)
        $borrowing->book->increment('available_copies');
    });

    return back()->with('success', 'Book returned successfully!');
})->name('books.quick-return');

Route::get('/books/{book}/copies', [BookCopyController::class, 'index']);
Route::post('/books/{book}/copies/add', [BookCopyController::class, 'store']);
Route::post('/books/scan-copy-barcode', [BookCopyController::class, 'scan']);

// Printable copy sticker — passes both $copy and $book so the blade
// can show book-level info AND use the copy's unique barcode for borrow/return.
// Load $book by $bookId directly and verify the copy belongs to this book,
// so a mismatched copy ID in the URL can never display the wrong title on the sticker.
Route::get('/books/{book}/copies/{copy}/print', function ($bookId, $copyId) {
    $book = \App\Models\Book::findOrFail($bookId);
    $copy = \App\Models\BookCopy::where('id', $copyId)
                                ->where('book_id', $bookId)
                                ->firstOrFail();
    return view('books.copy-sticker', compact('copy', 'book'));
})->name('books.copy.print');

// Per-copy current-borrowing — returns the active (unreturned) borrowing
// for a specific BookCopy. Used by the copy sticker page's return modal
// to load borrower details and wire the return form to the correct record.
Route::get('/books/{book}/copy/{copy}/current-borrowing', function ($bookId, $copyId) {
    $borrowing = \App\Models\Borrowing::where('book_copy_id', $copyId)
        ->whereNull('returned_at')
        ->latest()
        ->first();

    return response()->json([
        'success'   => true,
        'borrowing' => $borrowing ? [
            'id'           => $borrowing->id,
            'student_name' => $borrowing->student_name,
            'course'       => $borrowing->course,
            'section'      => $borrowing->section,
            'due_date'     => $borrowing->due_date,
        ] : null,
    ]);
})->name('books.copy.current-borrowing');

// DEV: Regenerate all BookCopy barcodes to the new short format (local only)
Route::get('/dev/regenerate-copy-barcodes', function () {
    if (!app()->environment('local')) {
        abort(403);
    }

    $updated = 0;

    foreach (\App\Models\BookCopy::all() as $copy) {
        // Try to extract a numeric copy number from the stored `copy_number` (e.g. "Copy 1")
        if (preg_match('/(\d+)/', $copy->copy_number ?? '', $m)) {
            $num = (int) $m[1];
        } else {
            $num = $copy->id;
        }

        $new = \App\Models\BookCopy::generateUniqueBarcode($copy->book_id, $num);

        if ($new !== $copy->barcode) {
            $copy->barcode = $new;
            $copy->save();
            $updated++;
        }
    }

    return response()->json(['updated' => $updated]);

})->name('dev.regenerate.copy.barcodes');

// DEV: Check if barcode exists
Route::get('/dev/check-barcode/{code}', function ($code) {
    $copy = \App\Models\BookCopy::with('book')->where('barcode', $code)->first();
    if ($copy) {
        return response()->json(['found' => true, 'copy' => $copy, 'book' => $copy->book]);
    }
    
    // List all barcodes for debugging
    $allBarcodes = \App\Models\BookCopy::limit(10)->get(['id', 'barcode', 'book_id', 'copy_number'])->toArray();
    return response()->json(['found' => false, 'searched' => $code, 'sample_barcodes' => $allBarcodes]);
})->name('dev.check-barcode');

// DEV: List all barcodes
Route::get('/dev/list-barcodes', function () {
    $copies = \App\Models\BookCopy::with('book')->limit(20)->get();
    return response()->json(['total' => \App\Models\BookCopy::count(), 'samples' => $copies]);
})->name('dev.list-barcodes');

// DEV: Diagnostic endpoint - logs exactly what scanner sends
Route::post('/dev/scan-debug', function (\Illuminate\Http\Request $request) {
    $rawInput = $request->getContent();
    $json = json_decode($rawInput, true) ?? [];
    
    return response()->json([
        'received' => [
            'raw_bytes' => array_map(fn($c) => ord($c), str_split($json['barcode'] ?? $_POST['barcode'] ?? '')),
            'hex' => bin2hex($json['barcode'] ?? $_POST['barcode'] ?? ''),
            'length' => strlen($json['barcode'] ?? $_POST['barcode'] ?? ''),
            'string' => $json['barcode'] ?? $_POST['barcode'] ?? 'N/A'
        ]
    ]);
})->name('dev.scan-debug');