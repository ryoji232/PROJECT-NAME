<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Copy Sticker - <?php echo e($copy->copy_number ?? $copy->id); ?></title>
    <style>
        html,body{height:100%;margin:0;padding:0;background:#fff;color:#000}
        .sticker{width:280px; height:120px; padding:12px; box-sizing:border-box; display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:Arial,Helvetica,sans-serif}
        .barcode{margin:6px 0}
        .code{text-align:center;font-size:13px;letter-spacing:1px}
        @media print{
            @page { margin: 4mm; }
            body{margin:0}
        }
    </style>
</head>
<body>
    <div class="sticker">
        <div style="font-weight:700; font-size:14px; margin-bottom:4px;"><?php echo e($copy->book->title ?? 'Book'); ?></div>
        <div style="font-size:12px; color:#333; margin-bottom:6px;"><?php echo e($copy->copy_number ?? 'Copy'); ?></div>
        <div class="barcode">
            <?php $printable = \App\Models\BookCopy::normalizeBarcode($copy->barcode); ?>
            <?php echo DNS1D::getBarcodeHTML($printable, 'C128', 3, 90); ?>

        </div>
        <div class="code"><?php echo e($printable); ?></div>
    </div>

    <script>
        // Auto-print and close after print
        window.addEventListener('load', function(){
            setTimeout(function(){ window.print(); }, 300);
        });
    </script>
</body>
</html><?php /**PATH C:\xampp\htdocs\project-name\resources\views/books/copy-sticker.blade.php ENDPATH**/ ?>