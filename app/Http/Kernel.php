// app/Http/Kernel.php
protected $routeMiddleware = [
    // ... existing middleware
    'role' => \App\Http\Middleware\CheckRole::class,
    'log.access' => \App\Http\Middleware\LogLastAccess::class,
];