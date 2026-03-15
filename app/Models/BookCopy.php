<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookCopy extends Model
{
    protected $fillable = [
        'book_id',
        'barcode',
        'copy_number',
        'status',
        'notes'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /* ---------- Scopes ---------- */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /* ---------- Barcode ---------- */
    public static function generateUniqueBarcode($bookId, $copyNumber)
    {
        // Generate a short, scanner-friendly alphanumeric barcode (no separators).
        // Format: {BOOK_PART}{COPY_PART}{RAND} (8 chars). Uses base36 to compress ids.
        do {
            $bookPart = strtoupper(str_pad(base_convert((int)$bookId, 10, 36), 4, '0', STR_PAD_LEFT));
            $copyPart = strtoupper(str_pad(base_convert((int)$copyNumber, 10, 36), 2, '0', STR_PAD_LEFT));
            $random = strtoupper(substr(Str::random(2), 0, 2));
            $barcode = $bookPart . $copyPart . $random; // ~8 chars
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Normalize a scanned barcode to uppercase alphanumeric only.
     */
    public static function normalizeBarcode($code)
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $code));
    }

    /**
     * Find a BookCopy by scanned barcode value. Very tolerant of formats.
     */
    public static function findByScannable($scanned)
    {
        if (!$scanned) {
            return null;
        }
        
        $scanned = (string) $scanned;
        
        // Clean the input: trim, uppercase, remove all non-alphanumeric
        $cleaned = strtoupper(preg_replace('/[^A-Z0-9]/i', '', trim($scanned)));
        
        if (strlen($cleaned) < 4) {
            return null;
        }
        
        // Try exact match first
        $copy = self::where('barcode', $cleaned)->first();
        if ($copy) {
            return $copy;
        }
        
        // Try case-insensitive match
        $copy = self::whereRaw('UPPER(barcode) = ?', [$cleaned])->first();
        if ($copy) {
            return $copy;
        }
        
        // Try partial match (in case there are hidden characters)
        return self::whereRaw("UPPER(REPLACE(barcode, ' ', '')) = ?", [$cleaned])->first();
    }
}

