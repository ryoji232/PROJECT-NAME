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

    @stack('scripts')
</body>
</html>