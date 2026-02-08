{{-- resources/views/layouts/admin.blade.php (Admin Layout) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title')</title>
    <!-- Include admin-specific CSS/JS -->
</head>
<body>
    <!-- Admin Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users') }}">
                    <i class="fas fa-users"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.logs') }}">
                    <i class="fas fa-history"></i> System Logs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.analytics') }}">
                    <i class="fas fa-chart-bar"></i> Analytics
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.settings') }}">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>
</body>
</html>