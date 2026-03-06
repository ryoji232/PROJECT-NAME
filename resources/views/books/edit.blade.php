@extends('app')

@section('title', 'Edit Book')

@section('content')
<style>
    /* Apply the same color scheme as dashboard */
    body {
        background: #e9f7ef; 
        color: #00402c;
        min-height: 100vh;
        font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }

    .card {
        border-radius: 1rem;
        border: 1px solid #dee2e6;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }

    .card-header {
        border-radius: 1rem 1rem 0 0;
        font-weight: 700;
        background: #198754;
        color: #fff !important;
    }

    .btn-primary, .btn-success {
        background: #198754;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: white;
    }

    .btn-primary:hover, .btn-success:hover {
        background: #157347;
        transform: scale(1.03);
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 10px 18px;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: scale(1.03);
        color: white;
    }
</style>

<div class="container py-5">
    <div class="card shadow-lg">
        <div class="card-header">
            <h4 class="mb-0">Edit Book</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('books.update', $book->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" class="form-control"
                           value="{{ old('title', $book->title) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input type="text" name="author" class="form-control"
                           value="{{ old('author', $book->author) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Copies (Max 10)</label>
                    <input type="number" name="copies" class="form-control"
                           value="{{ old('copies', $book->copies) }}" min="1" max="10" required>
                    <small class="text-muted">You can create up to 10 copies per book</small>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('books.index') }}" class="btn btn-secondary">⬅ Back</a>
                    <button type="submit" class="btn btn-success">Update Book</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection