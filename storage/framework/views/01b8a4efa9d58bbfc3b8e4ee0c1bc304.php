<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Library System'); ?></title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>

    <?php echo $__env->make('partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <main class="container py-4">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <?php echo $__env->make('partials.modals', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            toastr.options = {
                closeButton:     true,
                progressBar:     true,
                positionClass:   'toast-top-right',
                timeOut:         4000,
                extendedTimeOut: 1000
            };
            <?php if(session('success')): ?>
                toastr.success("<?php echo e(addslashes(session('success'))); ?>");
            <?php endif; ?>
            <?php if(session('error')): ?>
                toastr.error("<?php echo e(addslashes(session('error'))); ?>");
            <?php endif; ?>
            <?php if(session('warning')): ?>
                toastr.warning("<?php echo e(addslashes(session('warning'))); ?>");
            <?php endif; ?>
            <?php if(session('info')): ?>
                toastr.info("<?php echo e(addslashes(session('info'))); ?>");
            <?php endif; ?>
        });
    </script>

    
    <script>
        window.__routes = {
            borrowByBarcode: "<?php echo e(route('borrow.by.barcode')); ?>"
        };
    </script>

    <script src="<?php echo e(asset('js/app.js')); ?>"></script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\Ryoji\PROJECT-NAME\resources\views/layouts/app.blade.php ENDPATH**/ ?>