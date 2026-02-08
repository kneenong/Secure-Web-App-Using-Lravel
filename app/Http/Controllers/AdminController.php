<?php

namespace App\Http\Controllers;

// app/Http/Controllers/AdminController.php
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
        $this->middleware('log.access');
    }

    public function dashboard(): View
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'admins' => User::where('role', 'admin')->count(),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->orderBy('last_login_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users(): View
    {
        $users = User::withTrashed()->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function toggleUserStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        
        $action = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$action} successfully.");
    }

    public function promoteToAdmin(User $user)
    {
        $user->update(['role' => 'admin']);
        return back()->with('success', 'User promoted to admin.');
    }

    public function demoteToUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot demote yourself.');
        }

        $user->update(['role' => 'user']);
        return back()->with('success', 'User demoted to regular user.');
    }

    public function auditLogs(): View
    {
        $logs = AuditLog::with('user')->latest()->paginate(50);
        return view('admin.audit-logs', compact('logs'));
    }
}