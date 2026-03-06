<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'book_copy_id',
        'student_name', 
        'course',
        'section',
        'borrowed_at',
        'due_date',
        'returned_at',
        'penalty_amount',
        'penalty_status'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_date' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // Relationship to Book
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Relationship to BookCopy
    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }

    // Accessor for calculated penalty
    public function getPenaltyAttribute()
    {
        if ($this->returned_at || !$this->due_date) {
            return $this->penalty_amount ?? 0;
        }

        if (now()->greaterThan($this->due_date)) {
            $daysLate = now()->diffInDays($this->due_date);
            return $daysLate * 10; // ₱10 per day
        }

        return 0;
    }

    // Check if overdue
    public function getIsOverdueAttribute()
    {
        return !$this->returned_at && now()->greaterThan($this->due_date);
    }

     public function up()
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->string('course')->nullable()->after('student_id');
            $table->string('section')->nullable()->after('course');
            $table->dateTime('due_date')->nullable()->after('borrowed_at');
            $table->decimal('penalty', 8, 2)->default(0)->after('due_date');
        });
    }

    public function down()
    {
        Schema::table('borrowings', function (Blueprint $table) {
            $table->dropColumn(['course', 'section', 'due_date', 'penalty']);
        });
    }
}
