<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Book Barcode Sticker</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            background: #e9f7ef;
            text-align: center;
            margin: 0;
            padding: 20px;
        }
        .sticker {
            width: 270px;
            border-radius: 1rem;
            border: 2px solid #198754;
            background: #fff;
            padding: 18px 12px 14px 12px;
            margin: 0 auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        h3 {
            margin: 7px 0 4px 0;
            color: #00402c;
            font-size: 1.15rem;
            font-weight: 700;
            line-height: 1.3;
        }
        p {
            margin: 2px 0;
            font-size: 12.5px;
            color: #00402c;
        }
        .barcode-box {
            background: #f8f9fa;
            border-radius: 0.75rem;
            border: 1px solid #dee2e6;
            padding: 10px 0;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }
        .due-label {
            color: #198754;
            font-weight: 600;
            font-size: 13px;
        }
        .scan-info {
            font-size: 11px;
            color: #6c757d;
            margin-top: 8px;
            font-style: italic;
        }
        .barcode-number {
            font-family: monospace;
            font-size: 10px;
            color: #00402c;
            margin-top: 5px;
            font-weight: 600;
        }
        strong {
            color: #198754;
        }
    </style>
</head>
<body>
    <div class="sticker">
        <h3>{{ e($borrowing->book->title) }}</h3>
        <p><strong>Author:</strong> {{ e($borrowing->book->author) }}</p>
        <p><strong>Borrower:</strong> {{ e($borrowing->student_name) }}</p>
        <p><strong>Course:</strong> {{ e($borrowing->course) }} - {{ e($borrowing->section) }}</p>
        <p class="due-label"><strong>Due:</strong> {{ e(\Carbon\Carbon::parse($borrowing->due_date)->format('M d, Y')) }}</p>
        <div class="barcode-box">
            <svg id="barcode"></svg>
            <div class="barcode-number">Borrowing ID: {{ $borrowing->id }}</div>
        </div>
        <p class="scan-info">Scan at library to return book</p>
    </div>

    <script>
    // Generate scannable barcode for sticker78
    JsBarcode("#barcode", "{{ $borrowing->id     }}", {
        format: "CODE128",
        width: 1.8,
        height: 45,
        displayValue: true,
        fontSize: 12,
        margin: 8,
        background: "#f8f9fa",
        lineColor: "#00402c"
    });
</script>
</body>
</html>