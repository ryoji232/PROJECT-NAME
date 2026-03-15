<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Book; // ✅ ADD THIS

return new class extends Migration
{
    public function up()
    {
        Book::all()->each(function ($book) {
            if ($book->copies && $book->copies > 0) {
                $book->createCopies($book->copies);
            }
        });
    }

    public function down(): void
    {
        // optional rollback logic
    }
};
