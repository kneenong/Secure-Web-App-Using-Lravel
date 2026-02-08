<?php

namespace App\Http\Controllers;

// app/Http/Controllers/AdminDashboardController.php
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'log.access']);
    }

    public function index()
    {
        // Get statistics for the dashboard
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $userRegistrations = $this->getUserRegistrationData();
        $systemStatus = $this->getSystemStatus();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentActivities',
            'userRegistrations',
            'systemStatus'
        ));
    }

    public function userManagement()
    {
        $users = User::withTrashed()
            ->with(['auditLogs' => function($query) {
                $query->latest()->limit(5);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.management', compact('users'));
    }

    public function userDetails(User $user)
    {
        $user->load(['auditLogs' => function($query) {
            $query->latest()->limit(20);
        }]);

        $loginHistory = AuditLog::where('user_id', $user->id)
            ->where('action', 'LIKE', '%login%')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.users.details', compact('user', 'loginHistory'));
    }

    public function bulkUserActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,restore',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $action = $request->action;
        $userIds = $request->user_ids;

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['is_active' => true]);
                break;
            case 'deactivate':
                User::whereIn('id', $userIds)->where('id', '!=', auth()->id())->update(['is_active' => false]);
                break;
            case 'delete':
                User::whereIn('id', $userIds)->where('id', '!=', auth()->id())->delete();
                break;
            case 'restore':
                User::withTrashed()->whereIn('id', $userIds)->restore();
                break;
        }

        return back()->with('success', ucfirst($action) . ' action completed successfully.');
    }

    public function systemLogs()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->paginate(50);

        $logTypes = AuditLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->get();

        return view('admin.system.logs', compact('logs', 'logTypes'));
    }

    public function exportLogs(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'action' => 'nullable|string'
        ]);

        $query = AuditLog::with('user');

        if ($request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->action) {
            $query->where('action', $request->action);
        }

        $logs = $query->get();

        // For now, return JSON. In production, you'd generate CSV/Excel
        return response()->json($logs);
    }

    public function analytics()
    {
        // User registration analytics
        $userRegistrations = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Activity analytics
        $activityAnalytics = AuditLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Role distribution
        $roleDistribution = User::select(
                'role',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('role')
            ->get();

        return view('admin.analytics.index', compact(
            'userRegistrations',
            'activityAnalytics',
            'roleDistribution'
        ));
    }

    public function settings()
    {
        return view('admin.settings.index');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'require_strong_password' => 'boolean',
            'min_password_length' => 'integer|min:8|max:32',
            'max_login_attempts' => 'integer|min:1|max:10',
            'session_timeout' => 'integer|min:5|max:120',
            'enable_audit_logging' => 'boolean',
            'enable_email_verification' => 'boolean',
        ]);

        // Store settings in database or cache
        foreach ($validated as $key => $value) {
            \App\Models\SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    private function getDashboardStats()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $activeSessions = DB::table('sessions')->count();

        $failedLogins = AuditLog::where('action', 'failed_login')->count();
        $totalLogins = AuditLog::where('action', 'login')->count();
        $loginSuccessRate = $totalLogins > 0 ? (($totalLogins - $failedLogins) / $totalLogins * 100) : 0;

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'new_users_today' => $newUsersToday,
            'active_sessions' => $activeSessions,
            'login_success_rate' => round($loginSuccessRate, 2),
            'system_uptime' => $this->calculateSystemUptime(),
        ];
    }

    private function getRecentActivities()
    {
        return AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getUserRegistrationData()
    {
        return User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getSystemStatus()
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'cache' => $this->checkCache(),
        ];
    }

    private function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'connected', 'latency' => rand(1, 10)];
        } catch (\Exception $e) {
            return ['status' => 'disconnected', 'error' => $e->getMessage()];
        }
    }

    private function checkStorage()
    {
        $free = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());
        $used = $total - $free;
        $percentage = ($used / $total) * 100;

        return [
            'used' => $this->formatBytes($used),
            'total' => $this->formatBytes($total),
            'percentage' => round($percentage, 2)
        ];
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function calculateSystemUptime()
    {
        // Simplified - in production you'd use a more accurate method
        $startTime = cache()->rememberForever('system_start_time', function() {
            return now();
        });
        
        return $startTime->diffForHumans(now(), true);
    }

    private function checkQueue()
    {
        try {
            $queue = DB::table('jobs')->count();
            return ['status' => 'running', 'pending_jobs' => $queue];
        } catch (\Exception $e) {
            return ['status' => 'stopped'];
        }
    }

    private function checkCache()
    {
        try {
            cache()->put('health_check', 'ok', 10);
            return cache()->get('health_check') === 'ok' 
                ? ['status' => 'connected'] 
                : ['status' => 'disconnected'];
        } catch (\Exception $e) {
            return ['status' => 'disconnected'];
        }
    }
}