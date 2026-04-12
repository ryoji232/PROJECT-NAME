<?php $__env->startSection('title', 'Book History — Library System'); ?>

<?php $__env->startSection('content'); ?>

<div class="hero-section">
    <h1>Book History</h1>
    <p>Complete borrowing history — all transactions ever recorded</p>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>All Borrowing Records</h2>
    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

<?php echo $__env->make('partials.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<div class="stats-bar">
    <div class="stat-card" style="border-top: 4px solid #198754;">
        <div class="stat-value" style="color:#198754;"><?php echo e($historyStats['total']); ?></div>
        <div class="stat-label">Total Records</div>
    </div>
    <div class="stat-card" style="border-top: 4px solid #0d6efd;">
        <div class="stat-value" style="color:#0d6efd;"><?php echo e($historyStats['returned']); ?></div>
        <div class="stat-label">Returned</div>
    </div>
    <div class="stat-card stat-active" style="border-top: 4px solid #856404;">
        <div class="stat-value"><?php echo e($historyStats['active']); ?></div>
        <div class="stat-label">Still Borrowed</div>
    </div>
    <div class="stat-card stat-overdue" style="border-top: 4px solid #721c24;">
        <div class="stat-value"><?php echo e($historyStats['overdue']); ?></div>
        <div class="stat-label">Overdue</div>
    </div>
</div>


<div class="filter-card">
    <div class="row g-2 align-items-end">
        <div class="col-md-7">
            <label class="form-label fw-semibold">Search</label>
            <div style="position:relative;">
                <input type="text" id="historySearch" class="form-control"
                       placeholder="Student name, course, section, book title or author…"
                       value="<?php echo e(request('search')); ?>"
                       autocomplete="off">
                <div id="searchSpinner" style="display:none;position:absolute;right:.75rem;top:50%;transform:translateY(-50%);">
                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                        <span class="visually-hidden">Loading…</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <label class="form-label fw-semibold">Status</label>
            <select id="historyStatus" class="form-control">
                <option value="">— All Records —</option>
                <option value="borrowed"  <?php echo e(request('status') === 'borrowed'  ? 'selected' : ''); ?>>Currently Borrowed</option>
                <option value="returned"  <?php echo e(request('status') === 'returned'  ? 'selected' : ''); ?>>Returned</option>
                <option value="overdue"   <?php echo e(request('status') === 'overdue'   ? 'selected' : ''); ?>>Overdue</option>
            </select>
        </div>
    </div>
</div>


<div class="borrowings-table-container" id="historyTableContainer">
    <?php echo $__env->make('book-history.partials.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
#historyTableContainer {
    transition: opacity 0.18s ease;
}
#historyTableContainer.is-loading {
    opacity: 0.35;
    pointer-events: none;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    'use strict';

    var searchInput   = document.getElementById('historySearch');
    var statusSelect  = document.getElementById('historyStatus');
    var container     = document.getElementById('historyTableContainer');
    var spinner       = document.getElementById('searchSpinner');
    var debounceTimer = null;
    var currentCtrl   = null;   // AbortController for the in-flight request

    var baseUrl = "<?php echo e(route('book-history.index')); ?>";

    // ── Core fetch function ───────────────────────────────────────────────
    function fetchTable(extraParams) {
        if (currentCtrl) currentCtrl.abort();
        currentCtrl = new AbortController();

        var params = new URLSearchParams();
        var search = searchInput  ? searchInput.value.trim() : '';
        var status = statusSelect ? statusSelect.value       : '';
        if (search) params.set('search', search);
        if (status) params.set('status', status);

        // Allow pagination etc. to inject extra params (e.g. page number)
        if (extraParams) {
            extraParams.forEach(function (val, key) {
                params.set(key, val);
            });
        }

        var url = baseUrl + (params.toString() ? '?' + params.toString() : '');

        // Update the address bar without reloading
        window.history.replaceState(null, '', url);

        // Visual feedback
        if (container) container.classList.add('is-loading');
        if (spinner)   spinner.style.display = '';

        fetch(url, {
            signal:  currentCtrl.signal,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
            }
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            if (container && data.table !== undefined) {
                container.innerHTML = data.table;
                attachPaginationHandlers();
            }
        })
        .catch(function (err) {
            if (err.name === 'AbortError') return;
            console.error('[BookHistory] fetch error:', err);
        })
        .finally(function () {
            if (container) container.classList.remove('is-loading');
            if (spinner)   spinner.style.display = 'none';
            currentCtrl = null;
        });
    }

    // ── Input listeners ───────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { fetchTable(); }, 400);
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function () { fetchTable(); });
    }

    // ── Pagination — intercept link clicks after every swap ───────────────
    function attachPaginationHandlers() {
        if (!container) return;
        container.querySelectorAll('[aria-label="Book history pagination"] a').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                var href   = this.getAttribute('href');
                var parsed = new URL(href, window.location.origin);

                // Sync inputs with whatever the paginator link carries
                var pSearch = parsed.searchParams.get('search') || '';
                var pStatus = parsed.searchParams.get('status') || '';
                if (searchInput  && searchInput.value  !== pSearch) searchInput.value  = pSearch;
                if (statusSelect && statusSelect.value !== pStatus) statusSelect.value = pStatus;

                fetchTable(parsed.searchParams);

                // Scroll smoothly back to the top of the table
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    attachPaginationHandlers();

}());
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/book-history/index.blade.php ENDPATH**/ ?>