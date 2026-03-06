<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Library System')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>


.nav-link .badge { font-size: 0.7rem; }

.dropdown-menu-notification {
    min-width: 350px;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.notification-item {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item strong {
    color: #2c3e50; 
}

.notification-item:last-child { border-bottom: none; }

.days-left { font-weight: bold; }
.days-left.green { color: #28a745; }      /* Safe */
.days-left.yellow { color: #FFC107; }      /* Warning */
.days-left.red { color: #DC3545; }        /* Overdue */

.time-display {
    font-size: 0.85rem;
    margin-top: 5px;
    display: block;
    line-height: 1.4;
}

.time-display .spent-time {
    color: #6c757d;
}

.time-display .remaining-time {
    font-weight: bold;
}

/* ====== Navbar Styles ====== */
.navbar-custom {
    background-color: #2c3e50;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1020;
}

.navbar-custom .nav-link {
    color: rgba(255, 255, 255, 0.85);
    padding: 0.75rem 1rem;
    margin: 0 0.25rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.navbar-custom .nav-link:hover,
.navbar-custom .nav-link.active {
    color: #fff;
    background-color: #3498db; 
}

.navbar-custom .navbar-brand {
    color: #fff;
    font-size: 1.4rem;
    letter-spacing: 0.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.navbar-custom .dropdown-toggle {
    font-size: 1.25rem;
}

.navbar-custom .badge {
    font-size: 0.65rem;
    padding: 0.25em 0.4em;
}

.dropdown-header {
    background-color: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 1px solid #e9ecef;
}

/* Profile icon styles */
.profile-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.9rem;
    border: 2px solid rgba(255,255,255,0.2);
}

.profile-dropdown {
    min-width: 200px;
}

.profile-dropdown .dropdown-item {
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.profile-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
    color: #2c3e50;
}

.profile-dropdown .dropdown-divider {
    margin: 0.5rem 0;
}

/* Library brand with profile */
.brand-with-profile {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Prevent body scroll when modal is open */
body.modal-open {
    overflow: hidden;
    padding-right: 0 !important;
}

/* Completely prevent any focus-related scrolling */
#barcodeScannerInput {
    position: fixed !important;
    top: 0 !important;
    left: -1000px !important;
    width: 1px !important;
    height: 1px !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

/* Ensure no scroll jumps on page load */
html, body {
    scroll-behavior: auto !important;
}

/* Processing Flow Styles */
.status-processing-area {
    text-align: center;
    margin: 15px 0;
}

.checkbox-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.checkbox-container input {
    margin-right: 10px;
    transform: scale(1.2);
}

.processing-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 10px;
}

.spinner {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-radius: 50%;
    border-top: 4px solid #3498db;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin-right: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.status-borrowed {
    background: #ffeaa7;
    color: #856404;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    margin: 15px 0;
}

.status-processing {
    background: #d1ecf1;
    color: #0c5460;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    margin: 15px 0;
}

.status-completed {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
    font-weight: 600;
    margin: 15px 0;
}

.d-none {
    display: none;
}

/* Modal backdrop fix */
.modal-backdrop {
    z-index: 1040;
}

.modal {
    z-index: 1050;
}

/* Slight stronger entrance for modals to emphasize fade-in */
.modal.fade .modal-dialog {
    transform: translateY(-10px);
    transition: transform 220ms ease, opacity 220ms ease;
}
.modal.show .modal-dialog {
    transform: translateY(0);
}
</style>
</head>
<body>

@php
use Carbon\Carbon;

$notificationList = [];

if (isset($borrowings) && count($borrowings) > 0) {
    foreach ($borrowings as $borrowing) {
        if (!isset($borrowing->book)) continue;

        // Borrowed date (fallback to created_at)
        $borrowedAt = Carbon::parse($borrowing->borrowed_at ?? $borrowing->created_at);

        // Always set due date to 14 days after borrowedAt
        $dueDate = $borrowedAt->copy()->addDays(14);

        // Time spent since borrowing
        $now = Carbon::now();
        $timeSpentSeconds = $borrowedAt->diffInSeconds($now);

        // Convert time spent to days/hours/minutes
        $spentDays = floor($timeSpentSeconds / 86400);
        $spentHours = floor(($timeSpentSeconds % 86400) / 3600);
        $spentMinutes = floor(($timeSpentSeconds % 3600) / 60);
        $spendingTime = "{$spentDays}d {$spentHours}h {$spentMinutes}m"; // Shortened format

        // Calculate remaining time from 14 days minus spent time
        $totalAllowedSeconds = 14 * 86400;
        $remainingSeconds = $totalAllowedSeconds - $timeSpentSeconds;

        if ($remainingSeconds > 0) {
            $remainingDays = floor($remainingSeconds / 86400);
            $remainingHours = floor(($remainingSeconds % 86400) / 3600);
            $remainingMinutes = floor(($remainingSeconds % 3600) / 60);
            $remainingTime = "{$remainingDays}d {$remainingHours}h {$remainingMinutes}m"; // Shortened format
        } else {
            $remainingTime = "Overdue";
            $remainingDays = -floor(abs($remainingSeconds) / 86400); // Show negative days
        }

        // Status color logic
        if ($remainingDays > 7) {
            $status = 'green'; // Safe
        } elseif ($remainingDays >= 0) {
            $status = 'yellow'; // Warning
        } else {
            $status = 'red'; // Overdue
        }

        $notificationList[] = [
            'borrower' => $borrowing->student_name,
            'book' => $borrowing->book->title,
            'author' => $borrowing->book->author,
            'spendingTime' => $spendingTime,
            'remainingTime' => $remainingTime,
            'daysLeft' => $remainingDays,
            'status' => $status
        ];
    }

    // Sort notifications by remaining days (overdue first)
    usort($notificationList, fn($a, $b) => $a['daysLeft'] <=> $b['daysLeft']);
}
@endphp

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
<div class="container">
    <div class="brand-with-profile">
        <!-- Profile Icon Dropdown -->
        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle p-0" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="profile-icon" title="User Profile">
                    👤
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end profile-dropdown" aria-labelledby="profileDropdown">
                <li><h6 class="dropdown-header">Librarian Profile</h6></li>
                <li><a class="dropdown-item" href="{{ route('librarian.profile') }}">
                    <span>👤 Profile</span>
                </a></li>
                <li><a class="dropdown-item" href="{{ route('librarian.account') }}">
                    <span>⚙️ Account</span>
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <!-- Logout Form -->
                    <form id="logout-form" action="{{ route('librarian.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Library Brand -->
        <a class="navbar-brand fw-bold" href="{{ url('/dashboard') }}">
            Dashboard
        </a>
    </div>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('books.index') ? 'active' : '' }}" href="{{ route('books.index') }}">Books</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('borrowings.index') ? 'active' : '' }}" href="{{ route('borrowings.index') }}">Borrowed Books</a>
            </li>
            <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('book-history.index') ? 'active' : '' }}" href="{{ route('book-history.index') }}">Book History</a>
</li>

            <!-- Notification Bell -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle position-relative" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    🔔
                    @if(count($notificationList) > 0)
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                            {{ count($notificationList) }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-notification" aria-labelledby="navbarDropdown">
                    <li><h6 class="dropdown-header">Due Date Notifications</h6></li>
                    
                    @if(count($notificationList) === 0)
                        <li class="text-center p-3 text-muted">No notifications</li>
                    @else
                        @foreach($notificationList as $n)
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
                        @endforeach
                    @endif
                </ul>
            </li>
        </ul>
    </div>
</div>
</nav>

<main class="container py-4">
@yield('content')
</main>

<!-- Hidden barcode scanner input - COMPLETELY HIDDEN AND NON-INTERACTIVE -->
<input type="text" id="barcodeScannerInput" tabindex="-1" style="position: fixed; top: 0; left: -1000px; width: 1px; height: 1px; opacity: 0; pointer-events: none;">

<!-- Barcode Return Confirmation Modal -->
<div class="modal fade" id="barcodeReturnModal" tabindex="-1" aria-labelledby="barcodeReturnModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="barcodeReturnModalLabel">Confirm Book Return</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="modalCloseButton"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div style="font-size: 3rem;">📖</div>
                    <h4 id="returnBookTitle" class="text-primary fw-bold mt-2"></h4>
                    <p id="returnBookAuthor" class="text-muted"></p>
                </div>
                
                <div class="borrower-info p-3 rounded" style="background: #f8f9fa;">
                    <h6 class="fw-bold text-success">Borrower Information</h6>
                    <p id="returnBorrowerName" class="mb-1"><strong>Name:</strong> <span id="borrowerNameText"></span></p>
                    <p id="returnBorrowerCourse" class="mb-1"><strong>Course & Section:</strong> <span id="borrowerCourseText"></span></p>
                    <p id="returnDueDate" class="mb-0"><strong>Due Date:</strong> <span id="dueDateText"></span></p>
                </div>
                
                <!-- Processing Flow Elements -->
                <div class="status-processing-area">
                    <div class="checkbox-container">
                        <input type="checkbox" id="confirmReturnCheckbox">
                        <label for="confirmReturnCheckbox">I confirm the physical book is returned</label>
                    </div>
                    
                    <div id="statusBorrowed" class="status-borrowed">
                        Status: Currently Borrowed - Ready for Return
                    </div>
                    
                    <div id="statusProcessing" class="status-processing d-none">
                        <div>Status: Processing Return...</div>
                        <div class="processing-indicator">
                            <div class="spinner"></div>
                            <span>UP Processing...</span>
                        </div>
                    </div>
                    
                    <div id="statusCompleted" class="status-completed d-none">
                        Status: Return Completed Successfully
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i> Please verify the physical book is being returned before confirming.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelReturnBtn">Cancel</button>
                <form id="barcodeReturnForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success" id="confirmReturnBtn" disabled>✅ Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>

            <!-- Borrow Modal (shown when a copy barcode is scanned) -->
            <div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="borrowModalLabel">Borrow Book</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <h5 id="borrowBookTitle">Loading...</h5>
                                <small id="borrowBookAuthor" class="text-muted"></small>
                            </div>

                            <form id="borrowByBarcodeForm">
                                @csrf
                                <input type="hidden" name="book_copy_barcode" id="borrow_copy_barcode">

                                <div class="mb-2">
                                    <input type="text" name="student_name" id="borrow_student_name" class="form-control" placeholder="Student Name" required>
                                </div>
                                <div class="mb-2">
                                    <input type="text" name="course" id="borrow_course" class="form-control" placeholder="Course" required>
                                </div>
                                <div class="mb-2">
                                    <input type="text" name="section" id="borrow_section" class="form-control" placeholder="Section" required>
                                </div>

                                <div id="borrowAlert" class="alert d-none" role="alert"></div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="borrowSubmitBtn" class="btn btn-success">Confirm Borrow</button>
                        </div>
                    </div>
                </div>
            </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>


// Global modal instance
let returnModal = null;

// Utility function to force scroll to top
function forceScrollToTop() {
    window.scrollTo(0, 0);
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };

    // Initialize modal instance
    const modalElement = document.getElementById('barcodeReturnModal');
    returnModal = new bootstrap.Modal(modalElement);

    

    // Execute immediately and on load
    forceScrollToTop();
    window.addEventListener('load', forceScrollToTop);
    
    // Prevent any hash-based scrolling
    if (window.location.hash) {
        history.replaceState(null, null, ' ');
    }

    // ===== BARCODE SCANNER - SIMPLE & PROFESSIONAL =====
    let scanBuffer = '';
    const SAFE_INPUTS = ['borrow_student_name', 'borrow_course', 'borrow_section'];
    let scanTimer = null;

        // Keep a hidden, focusable input active so scanner input goes to the page
        const scannerInput = document.getElementById('barcodeScannerInput');

        function isUserTypingIntoField() {
            const el = document.activeElement;
            if (!el) return false;
            const tag = (el.tagName || '').toUpperCase();
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
            if (el.isContentEditable) return true;
            return false;
        }

        function ensureScannerInputFocused() {
            try {
                if (!scannerInput) return;
                if (isUserTypingIntoField()) return; // don't steal focus from user
                // Only focus if focus is on body or document (or nothing)
                const active = document.activeElement;
                if (!active || active === document.body || active === document.documentElement) {
                    scannerInput.focus({ preventScroll: true });
                    console.log('🔒 Scanner input focused');
                }
            } catch (e) {
                // ignore focus exceptions
            }
        }

        // Try to focus on load, and whenever window regains focus or user interacts
        ensureScannerInputFocused();
        window.addEventListener('focus', ensureScannerInputFocused);
        document.addEventListener('click', ensureScannerInputFocused, true);
        document.addEventListener('mousemove', ensureScannerInputFocused, { passive: true });
        // If user explicitly focuses a form field, do not refocus the scanner input

    // Listen on keydown at CAPTURE phase (before other handlers)
    document.addEventListener('keydown', handleScannerKey, true);

    // If the browser address bar is focused, keystrokes won't reach the page.
    // Show a console hint when we detect focus on the address bar (best-effort):
    window.addEventListener('blur', function() {
        // When window loses focus it's likely the user switched to another app or address bar
        console.log('Window blurred — ensure the browser page (not address bar) is focused before scanning.');
    });

    function handleScannerKey(e) {
        const activeEl = document.activeElement;
        const isSafeInput = activeEl && SAFE_INPUTS.includes(activeEl.id);
        const tag = activeEl && activeEl.tagName ? activeEl.tagName.toUpperCase() : null;
        const isFormField = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';

        // Allow normal input in safe fields OR any form field (so add/edit forms remain usable)
        if ((isSafeInput || (isFormField && activeEl.id !== 'barcodeScannerInput')) && e.key.length === 1) {
            return;
        }

        // Handle Enter key - process buffer as barcode
        if (e.key === 'Enter') {
            if (scanBuffer.trim().length > 3) {
                e.preventDefault();
                e.stopImmediatePropagation();
                const barcode = scanBuffer.trim();
                scanBuffer = '';
                clearTimeout(scanTimer);
                processBarcode(barcode);
            }
            return;
        }

        // Accumulate alphanumeric characters
        if (e.key.length === 1 && /[A-Za-z0-9\-\/ ]/.test(e.key)) {
            e.preventDefault();
            e.stopImmediatePropagation();
            scanBuffer += e.key;

            clearTimeout(scanTimer);
            const buf = scanBuffer.trim();

            // Process immediately if we have exactly 8 alphanumeric (copy barcode)
            if (/^[A-Z0-9]{8}$/i.test(buf)) {
                scanBuffer = '';
                processBarcode(buf);
                return;
            }

            // Otherwise, wait 300ms for more input
            scanTimer = setTimeout(() => {
                if (scanBuffer.trim().length > 3) {
                    const barcode = scanBuffer.trim();
                    scanBuffer = '';
                    processBarcode(barcode);
                }
            }, 300);
        }
    }

    function processBarcode(barcode) {
        console.log('🔍 BARCODE SCANNED:', barcode);

        // Clean it up
        const cleaned = barcode.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
        console.log('📝 Cleaned:', cleaned);

        // Try as copy barcode first
        if (/^[A-Z0-9]{4,12}$/.test(cleaned)) {
            console.log('🔎 Looking up copy...');
            
            // Send request with credentials and robust diagnostics
            fetch(`/copies/scan/${encodeURIComponent(cleaned)}`, { credentials: 'same-origin' })
                .then(r => {
                    console.log('🔁 Fetch response:', { status: r.status, url: r.url, redirected: r.redirected });
                    const ct = r.headers.get('content-type') || '';
                    if (!r.ok) {
                        return r.text().then(text => {
                            console.error('🚨 Non-OK response body:', text);
                            throw new Error(`HTTP ${r.status}`);
                        });
                    }

                    // If not JSON, log the body for debugging
                    if (!ct.includes('application/json')) {
                        return r.text().then(text => {
                            console.warn('⚠️ Expected JSON but got:', ct);
                            console.log('⚠️ Response text:', text);
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                throw new Error('Invalid JSON response from server');
                            }
                        });
                    }

                    return r.json();
                })
                .then(data => {
                    console.log('✅ API Response:', data);
                    if (data && data.success && data.copy) {
                        console.log('📖 Found copy, showing modal');
                        showBorrowModal(data.copy);
                    } else {
                        console.warn('❌ Barcode not found or unexpected response', data);
                        // Fallback: open a simple manual borrow modal so the librarian can proceed
                        toastr.warning('Barcode not found — opening manual borrow modal');
                        const fallback = { barcode: cleaned, book: { title: 'Unknown Book', author: '' }, _fromScannerFallback: true };
                        showBorrowModal(fallback);
                    }
                })
                .catch(err => {
                    console.error('❌ Fetch/Error:', err);
                    toastr.error('Error: ' + err.message);
                });
            return;
        }

        // Try as return ID
        let returnId = null;
        if (/^\d+$/.test(cleaned)) {
            returnId = cleaned;
        } else {
            const m = barcode.match(/\/return\/(\d+)|return\/(\d+)/);
            if (m) returnId = m[1] || m[2];
        }

        if (returnId) {
            console.log('🔄 Processing return ID:', returnId);
            showReturnConfirmationModal(returnId);
        } else {
            console.warn('❌ Invalid format');
            toastr.error('Invalid barcode format');
        }
    }

    // Show borrow modal pre-filled with copy/book info
    function showBorrowModal(copy) {
        console.log('showBorrowModal called with:', copy);
        
        const modalElement = document.getElementById('borrowModal');
        if (!modalElement) {
            console.error('Borrow modal element not found!');
            return;
        }

        document.getElementById('borrowBookTitle').textContent = copy.book.title || 'Book';
        document.getElementById('borrowBookAuthor').textContent = copy.book.author || '';
        document.getElementById('borrow_copy_barcode').value = copy.barcode;
        document.getElementById('borrow_student_name').value = '';
        document.getElementById('borrow_course').value = '';
        document.getElementById('borrow_section').value = '';
        document.getElementById('borrowAlert').classList.add('d-none');
        // If this modal was opened as a fallback after a failed lookup, show a simple warning
        if (copy && copy._fromScannerFallback) {
            const alert = document.getElementById('borrowAlert');
            alert.classList.remove('d-none');
            alert.classList.remove('alert-danger');
            alert.classList.add('alert-warning');
            alert.textContent = 'Book details not found — please enter borrower information and confirm manually.';
        }

        const bm = new bootstrap.Modal(modalElement);
        console.log('Showing modal...');
        bm.show();
        console.log('Modal shown');
        
        setTimeout(() => {
            const nameInput = document.getElementById('borrow_student_name');
            if (nameInput) {
                nameInput.focus();
                console.log('Focused on name input');
            }
        }, 300);
    }

    // Handle borrow modal submit
    document.getElementById('borrowSubmitBtn').addEventListener('click', function() {
        const form = document.getElementById('borrowByBarcodeForm');
        const formData = new FormData(form);
        const btn = this;
        btn.disabled = true;
        fetch("{{ route('borrow.by.barcode') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message || 'Borrowed successfully');
                const bm = bootstrap.Modal.getInstance(document.getElementById('borrowModal'));
                if (bm) bm.hide();
                setTimeout(() => window.location.reload(), 600);
            } else {
                const alert = document.getElementById('borrowAlert');
                alert.classList.remove('d-none');
                alert.classList.add('alert-danger');
                alert.textContent = data.message || 'Failed to borrow';
            }
        })
        .catch(err => {
            console.error('Borrow error', err);
            toastr.error('Error processing borrow request');
        })
        .finally(() => { btn.disabled = false; });
    });

    function showReturnConfirmationModal(borrowingId) {
        console.log("Showing return confirmation for ID:", borrowingId);
        
        // Reset modal state first
        resetModalState();
        
        // Show loading state
        document.getElementById('returnBookTitle').textContent = 'Loading...';
        document.getElementById('returnBookAuthor').textContent = '';
        document.getElementById('borrowerNameText').textContent = 'Loading...';
        document.getElementById('borrowerCourseText').textContent = 'Loading...';
        document.getElementById('dueDateText').textContent = 'Loading...';
        
        // Fetch borrowing data
        fetch(`/borrowings/${borrowingId}/data`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Received API data:", data);
                
                if (data.success) {
                    document.getElementById('returnBookTitle').textContent = data.book.title;
                    document.getElementById('returnBookAuthor').textContent = 'by ' + data.book.author;
                    
                    if (data.borrowing) {
                        document.getElementById('borrowerNameText').textContent = data.borrowing.student_name;
                        document.getElementById('borrowerCourseText').textContent = data.borrowing.course + ' - ' + data.borrowing.section;
                        document.getElementById('dueDateText').textContent = new Date(data.borrowing.due_date).toLocaleDateString();
                        
                        document.getElementById('barcodeReturnForm').action = `/borrowing/${borrowingId}/process-return`;
                        
                        // Use the global modal instance
                        if (returnModal) {
                            returnModal.show();
                        } else {
                            // Fallback: create new instance if global one doesn't exist
                            returnModal = new bootstrap.Modal(document.getElementById('barcodeReturnModal'));
                            returnModal.show();
                        }
                    } else {
                        toastr.error('No active borrowing found for this book.');
                    }
                } else {
                    toastr.error(data.message || 'Failed to load book information');
                }
            })
            .catch(error => {
                console.error('Error fetching borrowing data:', error);
                toastr.error('Error loading book information: ' + error.message);
            });
    }

    // Reset modal to initial state
    function resetModalState() {
    document.getElementById('confirmReturnCheckbox').checked = false;
    document.getElementById('confirmReturnBtn').disabled = true;
    document.getElementById('statusBorrowed').classList.remove('d-none');
    document.getElementById('statusProcessing').classList.add('d-none');
    document.getElementById('statusCompleted').classList.add('d-none');
    document.getElementById('cancelReturnBtn').disabled = false;
    document.getElementById('modalCloseButton').disabled = false;
}

    // Checkbox change handler
    document.getElementById('confirmReturnCheckbox').addEventListener('change', function() {
        document.getElementById('confirmReturnBtn').disabled = !this.checked;
    });

    // Handle barcode return form submission - IMPROVED VERSION
    document.getElementById('barcodeReturnForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('confirmReturnBtn');
    const cancelBtn = document.getElementById('cancelReturnBtn');
    const closeBtn = document.getElementById('modalCloseButton');
    
    // Show processing state
    document.getElementById('statusBorrowed').classList.add('d-none');
    document.getElementById('statusProcessing').classList.remove('d-none');
    document.getElementById('statusCompleted').classList.add('d-none');
    
    // Disable buttons during processing
    submitBtn.disabled = true;
    cancelBtn.disabled = true;
    closeBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show completed status
            document.getElementById('statusProcessing').classList.add('d-none');
            document.getElementById('statusCompleted').classList.remove('d-none');
            
            toastr.success(data.message);
            
            // Close modal after a brief delay to show completion status
            setTimeout(() => {
                if (returnModal) {
                    returnModal.hide();
                }
                
                // Reset form state
                resetModalState();
                
                // Reload the page to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }, 2000);
            
        } else {
            toastr.error(data.message);
            resetModalState();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred while processing the return.');
        resetModalState();
    });
});

    // Reset modal when it's hidden
    document.getElementById('barcodeReturnModal').addEventListener('hidden.bs.modal', function() {
    resetModalState();
});

    // Prevent modal from closing during processing
    document.getElementById('barcodeReturnModal').addEventListener('hide.bs.modal', function(e) {
    const isProcessing = !document.getElementById('statusProcessing').classList.contains('d-none');
    if (isProcessing) {
        e.preventDefault();
        return false;
    }
});

    // Intercept ALL navigation and force scroll to top
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Force scroll to top immediately when any link is clicked
            forceScrollToTop();
            
            // For internal navigation, ensure no scrolling happens
            if (this.href && this.href.startsWith(window.location.origin)) {
                // Add a small delay to ensure the scroll happens after navigation
                setTimeout(forceScrollToTop, 10);
                setTimeout(forceScrollToTop, 100);
            }
        });
    });

    // Special handling for navbar links
    document.querySelectorAll('.navbar-nav .nav-link, .navbar-brand, .dropdown-item').forEach(link => {
        link.addEventListener('click', function(e) {
            forceScrollToTop();
            // Additional insurance for navbar clicks
            setTimeout(forceScrollToTop, 50);
            setTimeout(forceScrollToTop, 150);
        });
    });

    // Test function
    window.testBarcode = function(id) {
        showReturnConfirmationModal(id);
    };
});



// Prevent any focus-related scrolling
document.addEventListener('focusin', function(e) {
    if (e.target.id === 'barcodeScannerInput') {
        // Immediately blur the input if it somehow gets focused
        e.target.blur();
    }
});
</script>
</body>
</html>
