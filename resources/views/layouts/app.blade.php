@php
use Illuminate\Support\Facades\Request;
setlocale(LC_TIME, 'fr_FR.UTF-8');
@endphp

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Strastoile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="{{ asset('style.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/x-icon">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
            <div>
                <a class="navbar-brand d-flex flex-row align-items-center" href="{{ route('home') }}">
                    <img id="logo" src="{{ asset('queerboxd.svg') }}" class="mr-2">
                    <p class="mb-0">Strastoile</p>
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav text-uppercase">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('films') ? 'active' : '' }}" href="/films">Films</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('reviews') ? 'active' : '' }}" href="/cinemas">Cinémas</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="d-flex flex-column align-items-center">
        <div id="content-wrap">
            @yield('content')
        </div>
    </main>

    <footer class="text-muted py-4 bg-transparent">
        <div class="container d-flex justify-content-md-center">
            <p class="text-center">© 2024 Strastoile</p>
        </div>
    </footer>

    <script src="{{ asset('script.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>