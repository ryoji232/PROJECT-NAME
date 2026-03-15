<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'copies',
        'barcode',
        'available_copies',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($book) {
            if (!$book->barcode) {
                $book->barcode = 'BK' . strtoupper(Str::random(8)) . time();
            }

            // Ensure available_copies is set when creating
            if (is_null($book->available_copies)) {
                $book->available_copies = $book->copies ?? 1;
            }
        });

        // After a book is created, auto-generate its BookCopy records
        static::created(function ($book) {
            if ($book->copies > 0 && $book->bookCopies()->count() === 0) {
                $book->createCopies((int) $book->copies);
            }
        });
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    // REMOVED: getAvailableCopiesAttribute accessor — it was shadowing the DB column
    // available_copies is now read directly from the database column,
    // which is kept in sync by BorrowingController (increment/decrement).

    public function getIsAvailableAttribute()
    {
        return $this->available_copies > 0;
    }

    public function getActiveBorrowingAttribute()
    {
        return $this->borrowings()
            ->whereNull('returned_at')
            ->first();
    }

    public function bookCopies()
    {
        return $this->hasMany(BookCopy::class);
    }

    /**
     * Create BookCopy records for this book.
     * Appends new copies after any existing ones.
     */
    public function createCopies(int $count)
    {
        $current = $this->bookCopies()->count();

        for ($i = 1; $i <= $count; $i++) {
            $copyNumber = $current + $i;

            BookCopy::create([
                'book_id'     => $this->id,
                'copy_number' => "Copy {$copyNumber}",
                'barcode'     => BookCopy::generateUniqueBarcode($this->id, $copyNumber),
                'status'      => 'available',
            ]);
        }
    }

    public function getAvailableCopiesCountAttribute()
    {
        return $this->bookCopies()->where('status', 'available')->count();
    }
}