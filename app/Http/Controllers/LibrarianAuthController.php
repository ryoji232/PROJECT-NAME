<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LibrarianAuthController extends Controller
{
    protected $maxAttempts = 5; // Maximum login attempts
    protected $decayMinutes = 15; // Lockout time in minutes

    public function showLoginForm()
    {
       return view('auth.login');
    }

    public function login(Request $request)
    {
        // Check if the user is currently locked out
        if ($this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('librarian')->attempt($credentials, $request->remember)) {
            // Clear login attempts on successful login
            $this->clearLoginAttempts($request);
            
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // Increment login attempts on failure
        $this->incrementLoginAttempts($request);
        
        // Calculate remaining attempts
        $attemptsLeft = $this->maxAttempts - $this->limiter()->attempts($this->throttleKey($request));
        
        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->with('attempts_remaining', $attemptsLeft)
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Check if the user has too many login attempts
     */
    protected function hasTooManyLoginAttempts(Request $request)
    {
        return RateLimiter::tooManyAttempts(
            $this->throttleKey($request), 
            $this->maxAttempts
        );
    }

    /**
     * Increment login attempts
     */
    protected function incrementLoginAttempts(Request $request)
    {
        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);
    }

    /**
     * Clear login attempts
     */
    protected function clearLoginAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Send lockout response
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        $minutes = ceil($seconds / 60);

        return back()
            ->with('account_locked', true)
            ->with('lockout_time', $minutes)
            ->with('lockout_seconds', $seconds)
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Generate throttle key based on email and IP address
     */
    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Get the rate limiter instance
     */
    protected function limiter()
    {
        return app(\Illuminate\Cache\RateLimiter::class);
    }

    public function logout(Request $request)
    {
        Auth::guard('librarian')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/librarian/login');
    }

    // Add these new methods for profile management
    public function profile()
    {
        return view('profile.profile');
    }

    public function account()
    {
       return view('profile.account');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('librarian')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:librarians,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('librarian.account')->with('profile_success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::guard('librarian')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('librarian.account')->with('password_success', 'Password updated successfully!');
    }

   public function index()
    {
        $books = Book::all();
        $borrowings = Borrowing::with('book')->whereNull('returned_at')->get();
        
        // Get recent borrowings (last 5)
        $recentBorrowings = Borrowing::with('book')
            ->whereNull('returned_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate most borrowed book
        $mostBorrowed = Borrowing::select('book_id', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('book_id')
            ->orderBy('borrow_count', 'desc')
            ->first();
        
        if ($mostBorrowed) {
            $mostBorrowed->book = Book::find($mostBorrowed->book_id);
        }

        // Chart data for most borrowed books
        $chartData = [];
        $mostBorrowedBooks = Borrowing::select('book_id', DB::raw('COUNT(*) as borrow_count'))
            ->groupBy('book_id')
            ->orderBy('borrow_count', 'desc')
            ->limit(7)
            ->get();

        if ($mostBorrowedBooks->count() > 0) {
            $chartData = [
                'labels' => [],
                'data' => []
            ];

            foreach ($mostBorrowedBooks as $item) {
                $book = Book::find($item->book_id);
                if ($book) {
                    $chartData['labels'][] = $book->title;
                    $chartData['data'][] = $item->borrow_count;
                }
            }
        }

        return view('dashboard.index', compact(
            'books', 
            'borrowings', 
            'recentBorrowings', 
            'mostBorrowed',
            'chartData'
        ));
    }
}