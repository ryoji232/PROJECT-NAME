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
    Schema::table('borrowings', function (Blueprint $table) {
        $table->string('student_name')->after('book_id');
        $table->string('course')->after('student_name');
        $table->string('section')->after('course');
    });
}

public function down(): void
{
    Schema::table('borrowings', function (Blueprint $table) {
        $table->dropColumn(['student_name', 'course', 'section']);
    });
}
};