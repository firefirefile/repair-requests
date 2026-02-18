<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ремонтная служба') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 70px; }
        .navbar { position: fixed; top: 0; width: 100%; z-index: 1000; }
    </style>
    <script>
        // Immediately set dark mode from localStorage to prevent flash
        (function() {
            if (localStorage.getItem('darkMode') === 'true') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Ремонтная служба') }}
            </a>

            <div class="d-flex align-items-center">
                <x-dark-mode-toggle />

                @auth
                    <div class="dropdown ms-4">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        {{ Auth::user()->name }} ({{ Auth::user()->role }})
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if(Auth::user()->role === 'dispatcher')
                            <li><a class="dropdown-item" href="{{ route('dispatcher.requests.index') }}">Панель диспетчера</a></li>
                        @elseif(Auth::user()->role === 'master')
                            <li><a class="dropdown-item" href="{{ route('master.requests.index') }}">Мои заявки</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Выйти</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <div>
                    <a href="{{ route('login') }}" class="btn btn-primary">Войти</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-primary">Регистрация</a>
                </div>
            @endauth
        </div>
    </nav>

    <main class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
