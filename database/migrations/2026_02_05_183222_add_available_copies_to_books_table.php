<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Add the new column (adjust type if necessary, e.g., 'integer')
            $table->integer('available_copies')->after('copies'); // You can place it after the 'copies' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Define how to reverse the change (e.g., drop the column)
            $table->dropColumn('available_copies');
        });
    }
};

