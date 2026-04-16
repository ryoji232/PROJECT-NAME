<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Library System')</title>

    {{-- CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('styles')
</head>
<body>

    @include('partials.navbar')

    <main class="container py-4">
        @yield('content')
    </main>

    @include('partials.modals')

    {{-- Scripts --}}
    {{-- jQuery must load before toastr — toastr is a jQuery plugin --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Flash notifications: fires toastr for add book / delete book / borrow --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toastr.options = {
                closeButton:     true,
                progressBar:     true,
                positionClass:   'toast-top-right',
                timeOut:         4000,
                extendedTimeOut: 1000
            };
            @if(session('success'))
                toastr.success("{{ addslashes(session('success')) }}");
            @endif
            @if(session('error'))
                toastr.error("{{ addslashes(session('error')) }}");
            @endif
            @if(session('warning'))
                toastr.warning("{{ addslashes(session('warning')) }}");
            @endif
            @if(session('info'))
                toastr.info("{{ addslashes(session('info')) }}");
            @endif
        });
    </script>

    {{-- Route URLs for static JS files that cannot use Blade directives --}}
    <script>
        window.__routes = {
            borrowByBarcode: "{{ route('borrow.by.barcode') }}"
        };
    </script>

    <script src="{{ asset('js/app.js') }}"></script>

    {{-- ═══════════════════════════════════════════════════════════════════
         GLOBAL BARCODE SCANNER ENGINE
         ───────────────────────────────────────────────────────────────────
         Listens for keyboard input on EVERY page of the app.
         Barcode scanners act as keyboards: they type characters rapidly
         (< 50 ms between keystrokes) and end with Enter.

         Two barcode types are handled:

           • NUMERIC (e.g. "42")
             → Printed by books/barcode-sticker  (encodes book.id)
             → Fetch /books/{id}/scan-data
               - available_copies > 0  → open #globalBorrowModal (borrow)
               - available_copies == 0 → open #barcodeReturnModal (return)

           • ALPHANUMERIC (e.g. "00420101AB")
             → Printed by books/copy-sticker (encodes BookCopy.barcode)
             → Handled by the existing app.js scanner (books page).
               On other pages: fetch /copies/scan/{code}, resolve to book,
               then follow the same available/unavailable branch above.

         The scanner is intentionally PASSIVE on pages that already have
         their own modal logic (books/index) for copy barcodes — the
         window.__barcodeBookIndex check prevents double-handling.
         ═══════════════════════════════════════════════════════════════════ --}}
    <script>
    (function () {
        'use strict';

        // ── Config ─────────────────────────────────────────────────────────
        // Maximum gap between keystrokes that still counts as the same scan.
        // Physical barcode scanners typically inject chars in < 30 ms.
        // Human typing is usually > 80 ms.  50 ms is a safe threshold.
        var MAX_SCAN_GAP_MS = 50;

        // Minimum number of characters in a barcode (ignore single-key noise)
        var MIN_BARCODE_LEN = 2;

        // ── State ───────────────────────────────────────────────────────────
        var buffer      = '';
        var lastKeyTime = 0;

        // ── Guard: skip if an input / textarea / contenteditable is focused ─
        // We never want to steal characters the user is intentionally typing
        // into a form field.
        function isInputFocused() {
            var el  = document.activeElement;
            if (!el) return false;
            var tag = el.tagName.toUpperCase();
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
            if (el.isContentEditable) return true;
            return false;
        }

        // ── Guard: skip if the hidden scanner input (navbar) is focused ─────
        // The navbar.blade.php keeps a hidden #barcodeScannerInput in focus on
        // the books page; the existing app.js handles those scans.  We still
        // run our own buffer here but let app.js take priority for copy-barcodes
        // by checking window.__barcodeBookIndex first.
        function isBooksPageScannerActive() {
            return typeof window.__barcodeBookIndex !== 'undefined' &&
                   Object.keys(window.__barcodeBookIndex).length > 0;
        }

        // ── Core: process a completed scan ──────────────────────────────────
        function processScan(raw) {
            var code = raw.trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (code.length < MIN_BARCODE_LEN) return;

            // ── Numeric-only → Book-ID barcode ────────────────────────────
            if (/^\d+$/.test(code)) {
                var bookId = parseInt(code, 10);
                if (bookId <= 0) return;
                handleBookIdScan(bookId);
                return;
            }

            // ── Alphanumeric → Copy barcode ───────────────────────────────
            // On the books page, app.js already handles this via the hidden
            // #barcodeScannerInput field — don't double-process.
            if (isBooksPageScannerActive()) {
                // app.js will pick this up through its own listener.
                return;
            }

            // On every other page, look up the copy via the API.
            handleCopyBarcodeScan(code);
        }

        // ── Handle a book-ID scan ────────────────────────────────────────────
        function handleBookIdScan(bookId) {
            fetch('/books/' + bookId + '/scan-data', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.book) {
                    showScanError('Book #' + bookId + ' not found.');
                    return;
                }

                var book = data.book;

                if (book.available_copies > 0) {
                    // Book available — open the global borrow modal
                    if (typeof window.__openBorrowModalForBook === 'function') {
                        window.__openBorrowModalForBook(bookId);
                    }
                } else {
                    // All copies borrowed — open the return modal
                    if (typeof window.__openReturnModalForBook === 'function') {
                        window.__openReturnModalForBook(bookId);
                    }
                }
            })
            .catch(function (err) {
                console.error('[GlobalScanner] Book lookup failed:', err);
                showScanError('Scanner error — could not look up book.');
            });
        }

        // ── Handle a copy-barcode scan (non-books pages) ─────────────────────
        function handleCopyBarcodeScan(code) {
            fetch('/copies/scan/' + encodeURIComponent(code), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept':           'application/json',
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success || !data.copy) {
                    showScanError('Copy "' + code + '" not found.');
                    return;
                }

                var copy   = data.copy;
                var bookId = copy.book ? copy.book.id : null;
                if (!bookId) { showScanError('Copy has no linked book.'); return; }

                if (copy.status === 'available') {
                    // Open the global borrow modal for this book
                    if (typeof window.__openBorrowModalForBook === 'function') {
                        window.__openBorrowModalForBook(bookId);
                    }
                } else {
                    // Copy is borrowed — open the return modal for the book
                    if (typeof window.__openReturnModalForBook === 'function') {
                        window.__openReturnModalForBook(bookId);
                    }
                }
            })
            .catch(function (err) {
                console.error('[GlobalScanner] Copy lookup failed:', err);
                showScanError('Scanner error — could not look up copy.');
            });
        }

        // ── Tiny error helper (toastr if loaded, else console.warn) ─────────
        function showScanError(msg) {
            if (window.toastr) {
                toastr.warning(msg);
            } else {
                console.warn('[GlobalScanner]', msg);
            }
        }

        // ── Keyboard listener ────────────────────────────────────────────────
        document.addEventListener('keydown', function (e) {
            var now = Date.now();

            // Reset buffer if gap is too large (new scan or human keystroke)
            if (now - lastKeyTime > MAX_SCAN_GAP_MS) {
                buffer = '';
            }
            lastKeyTime = now;

            // Enter = end of barcode
            if (e.key === 'Enter') {
                if (buffer.length >= MIN_BARCODE_LEN && !isInputFocused()) {
                    var scanned = buffer;
                    buffer = '';
                    processScan(scanned);
                } else {
                    buffer = '';
                }
                return;
            }

            // Ignore modifier-only keys, function keys, arrow keys, etc.
            if (e.key.length > 1) return;

            // If an input field is focused, let the field consume the character
            // but still record it in our buffer so we can detect scanner speed.
            buffer += e.key;
        }, true); // capture phase so we see events before page scripts

    }());
    </script>

    @stack('scripts')
</body>
</html>