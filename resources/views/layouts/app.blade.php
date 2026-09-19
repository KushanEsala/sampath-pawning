<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #4a90e2;
            --primary-dark: #357abd;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Navbar Styles */
        .navbar {
            background: var(--white) !important;
            box-shadow: var(--shadow);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color) !important;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
        }

        .navbar-brand::before {
            content: "\f013";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .nav-link {
            color: var(--secondary-color) !important;
            font-weight: 600;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-link::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        /* Add icons before nav links */
        .nav-link[href*="login"]::before {
            content: "\f2f6";
        }

        .nav-link[href*="register"]::before {
            content: "\f234";
        }

        /* Dropdown Styles */
        .dropdown-toggle {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white) !important;
            border-radius: 25px;
            padding: 0.5rem 1.5rem !important;
            transition: all 0.3s ease;
        }

        .dropdown-toggle::before {
            content: "\f007";
            margin-right: 0.5rem;
        }

        .dropdown-toggle:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow-hover);
            border-radius: 10px;
            margin-top: 0.5rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-item {
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dropdown-item::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            content: "\f2f5";
            color: var(--danger-color);
        }

        .dropdown-item:hover {
            background: var(--light-bg);
            color: var(--primary-color);
            padding-left: 2rem;
        }

        /* Main Content Area */
        main {
            min-height: calc(100vh - 100px);
        }

        /* Navbar Toggler */
        .navbar-toggler {
            border: 2px solid var(--primary-color);
            padding: 0.5rem 0.75rem;
            transition: all 0.3s ease;
        }

        .navbar-toggler:hover {
            background: var(--primary-color);
            transform: scale(1.05);
        }

        .navbar-toggler:hover .navbar-toggler-icon {
            filter: brightness(0) invert(1);
        }

        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.25rem;
            }

            .nav-link {
                padding: 0.75rem 1rem !important;
            }

            .dropdown-toggle {
                width: 100%;
                text-align: left;
            }
        }

        /* Loading Animation for Page Transitions */
        #app {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Additional Utility Styles */
        .shadow-sm {
            box-shadow: var(--shadow) !important;
        }

        .container {
            position: relative;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
             <a class="navbar-brand" href="{{ url('/') }}">
                    <i class="fas fa-file-alt"></i>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ url('/') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>