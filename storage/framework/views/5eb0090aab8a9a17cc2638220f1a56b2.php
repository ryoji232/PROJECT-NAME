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
            'spentSeconds'  => (int) $__spent,
            'remainingSeconds' => (int) $__remaining,
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

            
            <li class="nav-item dropdown" id="notifDropdownWrapper">
                <a class="nav-link dropdown-toggle position-relative" href="#" role="button"
                   data-bs-toggle="dropdown" aria-expanded="false" id="notifBellBtn">
                    🔔
                    <span id="notifBadge"
                          class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill"
                          style="<?php echo e(count($notificationList) > 0 ? '' : 'display:none'); ?>">
                        <?php echo e(count($notificationList)); ?>

                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notification" id="notifDropdownList">
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
       style="position:fixed;top:0;left:-9999px;width:1px;height:1px;opacity:0;pointer-events:none;">


<script>
(function () {
    'use strict';

    // ── State ────────────────────────────────────────────────────
    var notifData    = [];   // live array, mutated by ticker
    var tickInterval = null;

    // ── Seed from server-rendered PHP so first render is instant ─
    <?php if(count($notificationList) > 0): ?>
    notifData = <?php echo json_encode($notificationList, 15, 512) ?>.map(function (n) {
        return {
            borrower:         n.borrower,
            book:             n.book,
            status:           n.status,
            daysLeft:         n.daysLeft,
            _spentSec:        n.spentSeconds,
            _remainingSec:    n.remainingSeconds,
        };
    });
    <?php endif; ?>

    // ── Helpers ───────────────────────────────────────────────────
    function pad(n) { return n < 10 ? '0' + n : n; }

    function fmtSpent(sec) {
        sec = Math.max(0, Math.floor(sec));
        var d = Math.floor(sec / 86400);
        var h = Math.floor((sec % 86400) / 3600);
        var m = Math.floor((sec % 3600) / 60);
        var s = sec % 60;
        return d + 'd ' + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
    }

    function fmtRemaining(sec) {
        if (sec <= 0) return 'Overdue';
        sec = Math.floor(sec);
        var d = Math.floor(sec / 86400);
        var h = Math.floor((sec % 86400) / 3600);
        var m = Math.floor((sec % 3600) / 60);
        var s = sec % 60;
        return d + 'd ' + pad(h) + 'h ' + pad(m) + 'm ' + pad(s) + 's';
    }

    function statusClass(remainingSec) {
        if (remainingSec <= 0)              return 'red';
        if (remainingSec <= 7 * 86400)      return 'yellow';
        return 'green';
    }

    // ── Render the dropdown list ──────────────────────────────────
    function renderNotifications() {
        var list  = document.getElementById('notifDropdownList');
        var badge = document.getElementById('notifBadge');
        if (!list) return;

        // Update badge
        if (badge) {
            if (notifData.length > 0) {
                badge.textContent    = notifData.length;
                badge.style.display  = '';
            } else {
                badge.style.display  = 'none';
            }
        }

        if (notifData.length === 0) {
            list.innerHTML =
                '<li><h6 class="dropdown-header">Due Date Notifications</h6></li>' +
                '<li class="text-center p-3 text-muted">No notifications</li>';
            return;
        }

        var html = '<li><h6 class="dropdown-header">Due Date Notifications</h6></li>';

        notifData.forEach(function (n) {
            var cls     = statusClass(n._remainingSec);
            var remText = fmtRemaining(n._remainingSec);
            var sptText = fmtSpent(n._spentSec);

            html +=
                '<li class="notification-item">' +
                    '<strong>' + escHtml(n.borrower) + '</strong><br>' +
                    '<small class="text-muted">' + escHtml(n.book) + '</small><br>' +
                    '<span class="time-display">' +
                        '<span class="spent-time">Spent: ' + sptText + '</span>' +
                        '<span class="remaining-time days-left ' + cls + '"> | Left: ' + remText + '</span>' +
                    '</span>' +
                '</li>';
        });

        list.innerHTML = html;
    }

    // Simple HTML escaper to avoid XSS
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Second-by-second ticker ───────────────────────────────────
    function startTicker() {
        if (tickInterval) clearInterval(tickInterval);
        tickInterval = setInterval(function () {
            notifData.forEach(function (n) {
                n._spentSec     += 1;
                n._remainingSec -= 1;
            });
            // Only paint if the dropdown is actually open (performance)
            var wrapper = document.getElementById('notifDropdownWrapper');
            if (wrapper && wrapper.classList.contains('show')) {
                renderNotifications();
            }
        }, 1000);
    }

    // ── Fetch fresh data from server ──────────────────────────────
    function fetchNotifications() {
        fetch('/notifications/data', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json'
            }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) return;

            notifData = (data.notifications || []).map(function (n) {
                return {
                    borrower:      n.borrower,
                    book:          n.book,
                    daysLeft:      n.daysLeft,
                    _spentSec:     n.spentSeconds,
                    _remainingSec: n.remainingSeconds,
                };
            });

            // Re-render if dropdown is open; always update badge
            var badge = document.getElementById('notifBadge');
            if (badge) {
                if (notifData.length > 0) {
                    badge.textContent   = notifData.length;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }
            }

            var wrapper = document.getElementById('notifDropdownWrapper');
            if (wrapper && wrapper.classList.contains('show')) {
                renderNotifications();
            }
        })
        .catch(function (err) {
            console.warn('[Notifications] fetch error:', err);
        });
    }

    // ── Boot ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Re-render immediately when bell is clicked
        var btn = document.getElementById('notifBellBtn');
        if (btn) {
            btn.addEventListener('click', function () {
                // Small delay so Bootstrap has time to add .show to wrapper
                setTimeout(renderNotifications, 50);
            });
        }

        // Start ticking
        startTicker();

        // Sync with server every 60 seconds
        setInterval(fetchNotifications, 60000);
    });

})();
</script><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/partials/navbar.blade.php ENDPATH**/ ?>