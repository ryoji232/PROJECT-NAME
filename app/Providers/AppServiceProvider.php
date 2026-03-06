<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Borrowing;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pass borrowings with book to all views
        View::composer('*', function ($view) {
            $borrowings = Borrowing::with('book')
                ->whereNull('returned_at')
                ->get();

            $view->with('borrowings', $borrowings);
        });
    }
}
