<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\BookCopy;

/**
 * RepairBookCopiesSeeder
 *
 * Run this once to fix all existing books that are missing BookCopy records.
 * This is safe to run multiple times — it only creates copies for books
 * that have ZERO BookCopy rows.
 *
 * Usage:
 *   php artisan db:seed --class=RepairBookCopiesSeeder
 */
class RepairBookCopiesSeeder extends Seeder
{
    public function run(): void
    {
        $books = Book::all();
        $fixed = 0;

        foreach ($books as $book) {
            $existingCopies = $book->bookCopies()->count();

            if ($existingCopies === 0) {
                // Create BookCopy records equal to the book's copies count
                $count = max(1, (int) $book->copies);

                for ($i = 1; $i <= $count; $i++) {
                    BookCopy::create([
                        'book_id'     => $book->id,
                        'copy_number' => "Copy {$i}",
                        'barcode'     => BookCopy::generateUniqueBarcode($book->id, $i),
                        'status'      => 'available',
                    ]);
                }

                // Sync available_copies with copies count (no active borrowings assumed for old records)
                $book->update(['available_copies' => $count]);

                $fixed++;
                $this->command->info("Fixed: [{$book->id}] {$book->title} — created {$count} copies.");
            } else {
                // Book already has copies; just make sure available_copies column is correct
                $activeBorrowings = $book->borrowings()->whereNull('returned_at')->count();
                $available = max(0, (int) $book->copies - $activeBorrowings);
                $book->update(['available_copies' => $available]);

                $this->command->line("OK: [{$book->id}] {$book->title} — already has {$existingCopies} copies.");
            }
        }

        $this->command->info("Done. Fixed {$fixed} book(s).");
    }
}