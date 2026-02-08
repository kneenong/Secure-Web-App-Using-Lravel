{{-- resources/views/layouts/app.blade.php (User Layout) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - User Dashboard</title>
    <!-- Include user-specific CSS/JS -->
</head>
<body>
    <!-- User Navigation -->
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="{{ route('user.dashboard') }}">User Dashboard</a>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.dashboard') }}">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.profile') }}">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.security') }}">
                        <i class="fas fa-shield-alt"></i> Security
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.activity') }}">
                        <i class="fas fa-history"></i> Activity
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('user.settings') }}">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>