@extends('app')

@section('title', 'Barcode - Library System')

@section('content')
<style>
    body {
        background: #e9f7ef; 
        color: #00402c;
        min-height: 100vh;
        font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }

    .barcode-card {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        max-width: 480px;
        margin: 0 auto;
        margin-top: 2.5rem;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .barcode-title {
        color: #198754;
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .barcode-subtitle {
        color: #00402c;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .barcode-box {
        background: #fff;
        border-radius: 0.75rem;
        border: 1px solid #dee2e6;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        overflow: hidden;
        max-width: 100%;
    }

    .barcode-details {
        background: #f8f9fa;
        color: #00402c;
        border-radius: 0.75rem;
        border: 1px solid #dee2e6;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .barcode-actions {
        margin-top: 1.5rem;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .btn-success {
        background: #198754;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: white;
    }

    .btn-success:hover {
        background: #157347;
        transform: scale(1.03);
        color: white;
    }

    .btn-outline-primary {
        background: #fff;
        border: 2px solid #198754;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: #198754;
    }

    /* Ensure barcode scales properly */
    #barcode {
        max-width: 100%;
        height: auto;
        display: block;
    }

    @media (max-width: 600px) {
        .barcode-card {
            padding: 1.5rem 1rem;
            margin-top: 1.5rem;
        }
        
        .barcode-title {
            font-size: 1.5rem;
        }
        
        .barcode-actions {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .barcode-actions .btn {
            width: 100%;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<div class="container">
    <div class="barcode-card">
        <div class="barcode-title">Barcode</div>
        <div class="barcode-subtitle">Scan to Auto-Return Book</div>

        <div class="alert alert-success mb-4">
    <strong>How it works:</strong><br>
   When you scan this barcode, it will automatically open the return confirmation page.<br>
</div>

        <div class="barcode-box">
            <svg id="barcode"></svg>
            <div style="margin-top: 10px; font-family: monospace; font-size: 12px; color: #666; text-align: center;">
                Scan with barcode scanner<br>
                <small>Code: {{ $barcodeData }}</small>
            </div>
        </div>

        <div class="barcode-details">
            <h6>Book Details</h6>
            <p><strong>Title:</strong> {{ $borrowing->book->title }}</p>
            <p><strong>Author:</strong> {{ $borrowing->book->author }}</p>
            <hr>
            <h6>Borrower</h6>
            <p><strong>Name:</strong> {{ $borrowing->student_name }}</p>
            <p><strong>Course & Section:</strong> {{ $borrowing->course }} - {{ $borrowing->section }}</p>
            
            @if(!$borrowing->returned_at)
                <div class="alert alert-warning mt-3">
                    <strong>Status:</strong> Currently Borrowed - Ready for Return
                </div>
            @else
                <div class="alert alert-success mt-3">
                    <strong>Status:</strong> Already Returned
                </div>
            @endif
        </div>

        <div class="barcode-actions">
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary">⬅ Back</a>
            
        </div>
    </div>
</div>

<script>
// Generate barcode with just the numeric ID for easier scanning
JsBarcode("#barcode", "{{ $borrowing->id }}", {
    format: "CODE128",
    width: 2,
    height: 80,
    displayValue: true,
    fontSize: 16,
    margin: 10,
    background: "#ffffff",
    lineColor: "#00402c"
});
</script>   
</script>
@endsection