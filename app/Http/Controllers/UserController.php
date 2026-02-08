<?php

namespace App\Http\Controllers;

// app/Http/Controllers/UserDashboardController.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use App\Models\AuditLog;

class UserDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:user', 'log.access']);
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get user's recent activities
        $recentActivities = AuditLog::where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        // Get login history
        $loginHistory = AuditLog::where('user_id', $user->id)
            ->where('action', 'LIKE', '%login%')
            ->latest()
            ->limit(5)
            ->get();

        // Get account statistics
        $accountStats = [
            'account_age' => $user->created_at->diffForHumans(),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
            'total_logins' => AuditLog::where('user_id', $user->id)->where('action', 'login')->count(),
            'profile_completion' => $this->calculateProfileCompletion($user),
        ];

        return view('user.dashboard.index', compact(
            'user',
            'recentActivities',
            'loginHistory',
            'accountStats'
        ));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('user.profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'regex:/^[0-9\-\+\s\(\)]{10,20}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'occupation' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        // Sanitize inputs
        $sanitizedData = array_map(function($value) {
            return is_string($value) ? strip_tags($value) : $value;
        }, $validated);

        $user->update($sanitizedData);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ]);

        $user = auth()->user();
        
        // Delete old image if exists
        if ($user->profile_image) {
            Storage::delete('public/profile-images/' . $user->profile_image);
        }

        // Store new image
        $path = $request->file('profile_image')->store('profile-images', 'public');
        $user->update(['profile_image' => basename($path)]);

        return back()->with('success', 'Profile image updated successfully.');
    }

    public function security()
    {
        $user = auth()->user();
        
        // Get security-related information
        $securityInfo = [
            'last_password_change' => $user->updated_at->diffForHumans(),
            'two_factor_enabled' => false, // You can implement 2FA later
            'active_sessions' => $this->getActiveSessions(),
            'password_strength' => $this->checkPasswordStrength($user),
        ];

        return view('user.security.index', compact('user', 'securityInfo'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log password change
        AuditLog::create([
            'action' => 'password_changed',
            'details' => json_encode(['user_id' => $user->id]),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function activityLog()
    {
        $user = auth()->user();
        
        $activities = AuditLog::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('user.activity.index', compact('activities'));
    }

    public function exportActivity(Request $request)
    {
        $user = auth()->user();
        
        $activities = AuditLog::where('user_id', $user->id)
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->get();

        // For now, return JSON. In production, you'd generate CSV/Excel
        return response()->json($activities);
    }

    public function settings()
    {
        $user = auth()->user();
        $preferences = json_decode($user->preferences ?? '{}', true);

        return view('user.settings.index', compact('user', 'preferences'));
    }

    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'security_alerts' => 'boolean',
            'newsletter' => 'boolean',
            'theme' => 'in:light,dark,system',
            'timezone' => 'timezone',
        ]);

        $preferences = json_decode($user->preferences ?? '{}', true);
        $preferences = array_merge($preferences, $validated);

        $user->update(['preferences' => json_encode($preferences)]);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function downloadPersonalData()
    {
        $user = auth()->user();
        
        $data = [
            'personal_information' => $user->only(['name', 'email', 'phone', 'date_of_birth', 'occupation']),
            'account_information' => [
                'created_at' => $user->created_at,
                'last_login' => $user->last_login_at,
                'role' => $user->role,
                'status' => $user->is_active ? 'Active' : 'Inactive',
            ],
            'activity_log' => AuditLog::where('user_id', $user->id)
                ->select('action', 'ip_address', 'created_at')
                ->limit(100)
                ->get(),
        ];

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="personal-data-' . $user->id . '.json"',
        ]);
    }

    private function calculateProfileCompletion($user)
    {
        $fields = [
            'name' => 20,
            'email' => 20,
            'phone' => 15,
            'date_of_birth' => 15,
            'occupation' => 15,
            'bio' => 15,
        ];

        $completion = 0;
        foreach ($fields as $field => $weight) {
            if (!empty($user->$field)) {
                $completion += $weight;
            }
        }

        return min($completion, 100);
    }

    private function getActiveSessions()
    {
        // Simplified - in production, use session management
        return [
            'current_session' => [
                'ip' => request()->ip(),
                'browser' => request()->header('User-Agent'),
                'last_activity' => now()->diffForHumans(),
            ]
        ];
    }

    private function checkPasswordStrength($user)
    {
        // This is a simplified check. In production, use a proper strength checker
        $password = $user->password;
        $strength = 0;

        if (strlen($password) >= 8) $strength++;
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $strength++;

        return [
            'score' => $strength,
            'level' => $strength >= 4 ? 'Strong' : ($strength >= 3 ? 'Good' : 'Weak'),
            'last_changed' => $user->updated_at->diffForHumans(),
        ];
    }
}