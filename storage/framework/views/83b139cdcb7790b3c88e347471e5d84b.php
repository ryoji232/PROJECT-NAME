<?php
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
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
<div class="container">

    <div class="brand-with-profile">
        
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown"
               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="profile-icon" title="User Profile">👤</div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end profile-dropdown" aria-labelledby="profileDropdown">
                <li><h6 class="dropdown-header">Librarian Profile</h6></li>
                <li><a class="dropdown-item" href="<?php echo e(route('librarian.profile')); ?>">👤 Profile</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('librarian.account')); ?>">⚙️ Account</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form id="logout-form" action="<?php echo e(route('librarian.logout')); ?>" method="POST" class="d-none"><?php echo csrf_field(); ?></form>
                    <a class="dropdown-item" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
                </li>
            </ul>
        </div>

        <a class="navbar-brand fw-bold" href="<?php echo e(url('/dashboard')); ?>">Dashboard</a>
    </div>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('books.index') ? 'active' : ''); ?>"
                   href="<?php echo e(route('books.index')); ?>">Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('borrowings.index') ? 'active' : ''); ?>"
                   href="<?php echo e(route('borrowings.index')); ?>">Borrowed Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo e(request()->routeIs('book-history.index') ? 'active' : ''); ?>"
                   href="<?php echo e(route('book-history.index')); ?>">Book History</a>
            </li>

            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle position-relative" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    🔔
                    <?php if(count($notificationList) > 0): ?>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                            <?php echo e(count($notificationList)); ?>

                        </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notification">
                    <li><h6 class="dropdown-header">Due Date Notifications</h6></li>
                    <?php $__empty_1 = true; $__currentLoopData = $notificationList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="notification-item">
                            <strong><?php echo e($n['borrower']); ?></strong><br>
                            <small class="text-muted"><?php echo e($n['book']); ?></small><br>
                            <span class="time-display">
                                <span class="spent-time">Spent: <?php echo e($n['spendingTime']); ?></span>
                                <span class="remaining-time days-left <?php echo e($n['status']); ?>">
                                    | Left: <?php echo e($n['remainingTime']); ?>

                                </span>
                            </span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="text-center p-3 text-muted">No notifications</li>
                    <?php endif; ?>
                </ul>
            </li>
        </ul>
    </div>

</div>
</nav>


<input type="text" id="barcodeScannerInput" tabindex="-1"
       style="position:fixed;top:0;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;"><?php /**PATH C:\xampp\htdocs\project-name\resources\views/partials/navbar.blade.php ENDPATH**/ ?>