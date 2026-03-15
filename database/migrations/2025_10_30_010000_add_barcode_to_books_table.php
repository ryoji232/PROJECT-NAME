<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('barcode')->unique()->nullable()->after('copies');
        });

        // Generate barcodes for existing books
        $books = \App\Models\Book::all();
        foreach ($books as $book) {
            $book->barcode = 'BK' . strtoupper(Str::random(8)) . $book->id;
            $book->save();
        }

        Schema::table('books', function (Blueprint $table) {
            $table->string('barcode')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};