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
     *
     * Lookup priority:
     *  1. Pure numeric string → look up by primary key (id).
     *     The copy-sticker page encodes the copy's numeric ID so any
     *     scanner can read it without character-set ambiguity.
     *  2. Exact barcode string match (after normalisation).
     *  3. Case-insensitive barcode match.
     *  4. Whitespace-stripped barcode match.
     */
    public static function findByScannable($scanned)
    {
        if (!$scanned) {
            return null;
        }

        $scanned = (string) $scanned;

        // Clean the input: trim, uppercase, remove all non-alphanumeric
        $cleaned = strtoupper(preg_replace('/[^A-Z0-9]/i', '', trim($scanned)));

        if ($cleaned === '') {
            return null;
        }

        // ── Priority 1: pure numeric → find by copy ID ────────────────────
        // The printed sticker barcode encodes the numeric copy id directly,
        // which every scanner reads reliably without CODE128 character-set issues.
        if (ctype_digit($cleaned)) {
            $copy = self::with('book')->find((int) $cleaned);
            if ($copy) {
                return $copy;
            }
        }

        // Require at least 4 chars for barcode string lookups
        if (strlen($cleaned) < 4) {
            return null;
        }

        // ── Priority 2: exact barcode string match ────────────────────────
        $copy = self::where('barcode', $cleaned)->first();
        if ($copy) {
            return $copy;
        }

        // ── Priority 3: case-insensitive match ────────────────────────────
        $copy = self::whereRaw('UPPER(barcode) = ?', [$cleaned])->first();
        if ($copy) {
            return $copy;
        }

        // ── Priority 4: whitespace-stripped match ─────────────────────────
        return self::whereRaw("UPPER(REPLACE(barcode, ' ', '')) = ?", [$cleaned])->first();
    }
}