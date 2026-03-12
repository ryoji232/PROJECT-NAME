@php
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Notification query — prefixed vars to avoid scope pollution
$notificationList = [];
try {
    $__now  = Carbon::now();
    $__rows = DB::table('borrowings')
        ->join('books', 'borrowings.book_id', '=', 'books.id')
        ->whereNull('borrowings.returned_at')
        ->orderBy('borrowings.borrowed_at', 'asc')
        ->select('borrowings.student_name', 'borrowings.borrowed_at', 'borrowings.created_at', 'books.title as book_title')
        ->get();

    foreach ($__rows as $__row) {
        $__borrowedAt     = Carbon::parse($__row->borrowed_at ?? $__row->created_at);
        $__spent          = $__borrowedAt->diffInSeconds($__now);
        $__remaining      = (14 * 86400) - $__spent;

        $__spentDays      = floor($__spent / 86400);
        $__spentHours     = floor(($__spent % 86400) / 3600);
        $__spentMins      = floor(($__spent % 3600) / 60);

        if ($__remaining > 0) {
            $__remDays    = floor($__remaining / 86400);
            $__remHours   = floor(($__remaining % 86400) / 3600);
            $__remMins    = floor(($__remaining % 3600) / 60);
            $__remText    = "{$__remDays}d {$__remHours}h {$__remMins}m";
        } else {
            $__remDays    = -floor(abs($__remaining) / 86400);
            $__remText    = 'Overdue';
        }

        $notificationList[] = [
            'borrower'      => $__row->student_name,
            'book'          => $__row->book_title,
            'spendingTime'  => "{$__spentDays}d {$__spentHours}h {$__spentMins}m",
            'remainingTime' => $__remText,
            'daysLeft'      => $__remDays,
            'status'        => $__remDays > 7 ? 'green' : ($__remDays >= 0 ? 'yellow' : 'red'),
        ];
    }

    usort($notificationList, fn($a, $b) => $a['daysLeft'] <=> $b['daysLeft']);
    unset($__now, $__rows, $__row, $__borrowedAt, $__spent, $__remaining,
          $__spentDays, $__spentHours, $__spentMins, $__remDays, $__remHours,
          $__remMins, $__remText);
} catch (\Exception $e) {
    $notificationList = [];
}
@endphp

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
<div class="container">

    <div class="brand-with-profile">
        {{-- Profile dropdown --}}
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown"
               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="profile-icon" title="User Profile">👤</div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end profile-dropdown" aria-labelledby="profileDropdown">
                <li><h6 class="dropdown-header">Librarian Profile</h6></li>
                <li><a class="dropdown-item" href="{{ route('librarian.profile') }}">👤 Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('librarian.account') }}">⚙️ Account</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form id="logout-form" action="{{ route('librarian.logout') }}" method="POST" class="d-none">@csrf</form>
                    <a class="dropdown-item" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                </li>
            </ul>
        </div>

        <a class="navbar-brand fw-bold" href="{{ url('/dashboard') }}">Dashboard</a>
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('books.index') ? 'active' : '' }}"
                   href="{{ route('books.index') }}">Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('borrowings.index') ? 'active' : '' }}"
                   href="{{ route('borrowings.index') }}">Borrowed Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('book-history.index') ? 'active' : '' }}"
                   href="{{ route('book-history.index') }}">Book History</a>
            </li>

            {{-- Notification bell --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle position-relative" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    🔔
                    @if(count($notificationList) > 0)
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                            {{ count($notificationList) }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notification">
                    <li><h6 class="dropdown-header">Due Date Notifications</h6></li>
                    @forelse($notificationList as $n)
                        <li class="notification-item">
                            <strong>{{ $n['borrower'] }}</strong><br>
                            <small class="text-muted">{{ $n['book'] }}</small><br>
                            <span class="time-display">
                                <span class="spent-time">Spent: {{ $n['spendingTime'] }}</span>
                                <span class="remaining-time days-left {{ $n['status'] }}">
                                    | Left: {{ $n['remainingTime'] }}
                                </span>
                            </span>
                        </li>
                    @empty
                        <li class="text-center p-3 text-muted">No notifications</li>
                    @endforelse
                </ul>
            </li>
        </ul>
    </div>

</div>
</nav>

{{-- Hidden barcode input (keeps scanner focused) --}}
<input type="text" id="barcodeScannerInput" tabindex="-1"
       style="position:fixed;top:0;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">